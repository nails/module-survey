<?php

/**
 * This class handles survey submissions
 *
 * @package     Nails
 * @subpackage  module-survey
 * @category    Controller
 * @author      Nails Dev Team
 * @link
 */

use Nails\Common\Exception\NailsException;
use Nails\Common\Exception\FactoryException;
use Nails\Common\Exception\ModelException;
use Nails\Common\Exception\ViewNotFoundException;
use Nails\Common\Helper\Model\Expand;
use Nails\Email;
use Nails\Factory;
use Nails\FormBuilder;
use Nails\Survey\Constants;
use Nails\Survey\Controller\Base;
use Nails\Survey\Resource;

/**
 * Class Survey
 */
class Survey extends Base
{
    /**
     * Renders the survey form
     *
     * @param Resource\Survey   $oSurvey   The active survey
     * @param Resource\Response $oResponse The active response
     *
     * @throws FactoryException
     * @throws ViewNotFoundException
     */
    public function index(
        Resource\Survey $oSurvey,
        ?Resource\Response $oResponse,
        bool $bIsAdminPreviewInactive,
        bool $bIsAdminPreviewAnon
    ) {
        /** @var \Nails\Common\Service\Input $oInput */
        $oInput = Factory::service('Input');
        /** @var \Nails\Common\Service\View $oView */
        $oView = Factory::service('View');
        /** @var \Nails\Common\Service\Asset $oAsset */
        $oAsset = Factory::service('Asset');
        /** @var \Nails\Common\Service\Session $oSession */
        $oSession = Factory::service('Session');
        /** @var \Nails\Common\Service\UserFeedback $oUserFeedback */
        $oUserFeedback = Factory::service('UserFeedback');
        /** @var \Nails\Captcha\Service\Captcha $oCaptcha */
        $oCaptcha = Factory::service('Captcha', \Nails\Captcha\Constants::MODULE_SLUG);
        /** @var \Nails\Survey\Model\Response $oResponseModel */
        $oResponseModel = Factory::model('Response', Constants::MODULE_SLUG);
        /** @var \Nails\Survey\Model\Response\Answer $oResponseAnswerModel */
        $oResponseAnswerModel = Factory::model('ResponseAnswer', Constants::MODULE_SLUG);

        if (!empty($oResponse) && $oResponse->status === $oResponseModel::STATUS_SUBMITTED) {
            show404();
        }

        //  If there is a response, mux in any previously saved answers
        if (!empty($oResponse)) {
            foreach ($oSurvey->form->fields->data as $oField) {
                /** @var Resource\Response\Answer $oAnswer */
                foreach ($oResponse->answers->data as $oAnswer) {
                    if ($oAnswer->form_field_id === $oField->id) {

                        /**
                         * Populate the form fields with data from the responses
                         *
                         * This is a bit of a tambourine dance due to some poor design decisions
                         * made in the early stages of the form builder.
                         *
                         * In most cases a response will have either a text value or a option value
                         * however in the case of Likert scales we use the data component to store
                         * the result. This is POSTed as an array, so we need to mux the values
                         * and data into a similar array so that the view can recompile properly.
                         *
                         * Believe me, I dislike this too and it limits/confuses how to use these
                         * components.
                         *
                         * — Pablo (01/03/2021)
                         */

                        /** @var FormBuilder\Interfaces\FieldType $sFieldType */
                        $sFieldType = $oField->type;

                        if ($sFieldType::supportsOptions()) {
                            $mValue = $oAnswer->form_field_option_id;

                        } else {
                            $mValue = $oAnswer->text;
                        }

                        if (property_exists($oField, 'value') && !is_array($oField->value)) {
                            $oField->value = [$oField->value];
                        }

                        if (property_exists($oField, 'value') && is_array($oField->value)) {
                            $oField->value[] = $mValue;
                        } else {
                            $oField->value = $mValue;
                        }

                        $mData = $oAnswer->data;

                        if (property_exists($oField, 'data') && !is_array($oField->data)) {
                            $oField->data = [$oField->data];
                        }

                        if (property_exists($oField, 'data') && is_array($oField->data)) {
                            $oField->data[] = $mData;
                        } else {
                            $oField->data = $mData;
                        }
                    }
                }
            }
        }

        if ($oInput->post()) {

            try {

                if (!empty($bIsAdminPreviewInactive)) {
                    throw new NailsException('Survey is not active.');

                } elseif (!empty($bIsAdminPreviewAnon)) {
                    throw new NailsException('Anonymous submissions are disabled for this survey.');
                }

                $bIsSave = $oInput->post('action') === 'save';

                //  Validate
                $bIsFormValid = $bIsSave
                    ? true
                    : formBuilderValidate(
                        $oSurvey->form->fields->data,
                        $oInput->post('field')
                    );

                $bIsCaptchaValid = $bIsSave || !$oCaptcha->isEnabled()
                    ? true
                    : $oSurvey->form->has_captcha && $oCaptcha->verify();

                if ($bIsFormValid && $bIsCaptchaValid) {

                    //  For each response, extract all the components
                    $aParsedResponse = formBuilderParseResponse(
                        $oSurvey->form->fields->data,
                        (array) $oInput->post('field')
                    );

                    $aResponseData = [];
                    $iOrder        = 0;
                    foreach ($aParsedResponse as $oRow) {
                        $aResponseData[] = [
                            'survey_response_id'   => $oSurvey->id,
                            'form_field_id'        => $oRow->field_id,
                            'form_field_option_id' => $oRow->option_id,
                            'text'                 => $oRow->text,
                            'data'                 => $oRow->data,
                            'order'                => $iOrder,
                        ];
                        $iOrder++;
                    }

                    if (empty($oResponse)) {

                        $aResponse = [
                            'survey_id' => $oSurvey->id,
                            'user_id'   => (int) activeUser('id') ?: null,
                        ];

                        if ($bIsSave) {
                            $aResponse['email'] = $oInput->post('email') ?: null;
                        }

                        $oResponse = $oResponseModel->create($aResponse, true);
                        if (empty($oResponse)) {
                            throw new NailsException(
                                'Failed save response. ' . $oResponseModel->lastError()
                            );
                        }

                    } elseif ($bIsSave && empty($oResponse->email)) {

                        $sSaveEmail = $oInput->post('email') ?: ($oResponseModel->email ?? null);

                        if (valid_email($sSaveEmail)) {
                            $oResponseModel->email = $sSaveEmail;
                            $oResponseModel->update(
                                $oResponse->id,
                                [
                                    'email' => $oResponseModel->email,
                                ]
                            );
                        }
                    }

                    $oResponseAnswerModel->deleteWhere([
                        ['survey_response_id', $oResponse->id],
                    ]);

                    foreach ($aResponseData as $aResponseRow) {

                        $aResponseRow['survey_response_id'] = $oResponse->id;

                        if (!$oResponseAnswerModel->create($aResponseRow)) {
                            throw new NailsException(
                                'Failed save response. ' . $oResponseAnswerModel->lastError()
                            );
                        }
                    }

                    //  Mark response as submitted
                    if (!$bIsSave && !$oResponseModel->setSubmitted($oResponse->id)) {
                        throw new NailsException(
                            'Failed to mark response as submitted. ' . $oResponseModel->lastError()
                        );
                    }

                    //  Send a notification email
                    if (!$bIsSave && !empty($oSurvey->notification_email)) {

                        $oResponse = $oResponseModel->skipCache()->getById(
                            $oResponse->id,
                            [
                                new Expand('answers', new Expand\Group([
                                    new Expand('question'),
                                    new Expand('option'),
                                ])),
                            ]
                        );

                        if ($oResponse->answers->count > 0) {

                            $aResponses = [];

                            foreach ($oResponse->answers->data as $oAnswer) {

                                if (!empty($oAnswer->option)) {
                                    $sAnswer = $oAnswer->option->label;

                                } elseif (!empty($oAnswer->text)) {
                                    $sAnswer = $oAnswer->text;

                                } else {
                                    $sAnswer = '<i>Did not answer</i>';
                                }

                                $aResponses[] = (object) [
                                    'q' => $oAnswer->question->label,
                                    'a' => $sAnswer,
                                ];
                            }


                            /** @var \Nails\Survey\Factory\Email\Notification $oEmail */
                            $oEmail = Factory::factory('EmailNotification', Constants::MODULE_SLUG);
                            $oEmail
                                ->data([
                                    'survey'    => (object) [
                                        'id'    => $oSurvey->id,
                                        'label' => $oSurvey->label,
                                    ],
                                    'responses' => $aResponses,
                                ]);

                            foreach ($oSurvey->notification_email as $sEmail) {
                                try {

                                    $oEmail
                                        ->to($sEmail)
                                        ->send();

                                } catch (\Nails\Email\Exception\EmailerException $e) {
                                    //  Do something with this?
                                }
                            }
                        }
                    }

                    if ($bIsSave) {

                        $sSaveEmail = $oInput->post('email') ?: ($oResponseModel->email ?? null);

                        try {

                            /** @var \Nails\Survey\Factory\Email\Save $oEmail */
                            $oEmail = Factory::factory('EmailSave', Constants::MODULE_SLUG);
                            $oEmail
                                ->to($sSaveEmail)
                                ->data([
                                    'survey'   => [
                                        'id'    => $oSurvey->id,
                                        'label' => $oSurvey->label,
                                    ],
                                    'response' => [
                                        'id'  => $oResponse->id,
                                        'url' => $oResponse->url,
                                    ],
                                ])
                                ->send();

                        } catch (\Exception $e) {
                            $oSession->setFlashData('save-email-warning', $e->getMessage());
                        }

                        $oUserFeedback->success('Your response was saved successfully.');

                        redirect($oResponse->url);
                    }

                    $oView
                        ->load([
                            'structure/header',
                            'survey/thanks',
                            'structure/footer',
                        ]);
                    return;

                } elseif (!$bIsCaptchaValid) {
                    throw new FormBuilder\Exception\ValidationException(
                        'You failed the captcha test.'
                    );

                } else {
                    throw new FormBuilder\Exception\ValidationException(
                        lang('fv_there_were_errors')
                    );
                }

            } catch (\Exception $e) {
                $this->data['error'] = $e->getMessage();
            }
        }

        $oAsset->load('survey.min.css', Constants::MODULE_SLUG);

        $oView
            ->setData([
                'oSurvey'                 => $oSurvey,
                'oResponse'               => $oResponse,
                'bIsAdminPreviewInactive' => $bIsAdminPreviewInactive,
                'bIsAdminPreviewAnon'     => $bIsAdminPreviewAnon,
                'bIsCaptchaEnabled'       => $oSurvey->form->has_captcha && $oCaptcha->isEnabled(),
                'sSaveEmailWarning'       => $oSession->getFlashData('save-email-warning'),
            ])
            ->load([
                'structure/header',
                'survey/survey',
                'structure/footer',
            ]);
    }

    // --------------------------------------------------------------------------

    /**
     * Routes the request
     *
     * @throws FactoryException
     * @throws ModelException
     * @throws ViewNotFoundException
     */
    public function _remap()
    {
        /** @var \Nails\Common\Service\Uri $oUri */
        $oUri = Factory::service('Uri');
        /** @var \Nails\Survey\Model\Survey $oSurveyModel */
        $oSurveyModel = Factory::model('Survey', Constants::MODULE_SLUG);
        /** @var \Nails\Survey\Model\Response $oResponseModel */
        $oResponseModel = Factory::model('Response', Constants::MODULE_SLUG);

        Factory::helper('formbuilder', FormBuilder\Constants::MODULE_SLUG);

        $iSurveyId      = (int) $oUri->rsegment(3);
        $sSurveyToken   = $oUri->rsegment(4);
        $iResponseId    = (int) $oUri->rsegment(5);
        $sResponseToken = $oUri->rsegment(6);

        //  Get the Survey
        $oSurvey = $oSurveyModel->getById(
            $iSurveyId,
            [
                new Expand(
                    'form',
                    new Expand(
                        'fields',
                        new Expand('options')
                    )
                ),
            ]
        );

        if (empty($oSurvey) || $oSurvey->token != $sSurveyToken) {
            show404();

        } elseif (!$oSurvey->is_active && !userHasPermission('admin:survey:survey:*')) {
            show404();

        } elseif (!$oSurvey->is_active && userHasPermission('admin:survey:survey:*')) {
            $bIsAdminPreviewInactive = true;
        }

        //  Get the Response, if any
        if (!empty($iResponseId)) {
            $oResponse = $oResponseModel->getById($iResponseId, [new Expand('answers')]);
            if (empty($oResponse) || $oResponse->token != $sResponseToken) {
                show404();
            }
        } else {
            $oResponse = null;
        }

        //  Anonymous responses enabled?
        if (!$oSurvey->allow_anonymous_response && empty($oResponse)) {
            /**
             * If user has survey permissions then assume they are an admin and allow the rendering
             * of the survey, but prevent submission
             */
            if (!userHasPermission('admin:survey:survey:*')) {
                show404();

            } else {
                $bIsAdminPreviewAnon = true;
            }
        }

        //  Minimal layout?
        if ($oSurvey->is_minimal) {
            $this->data['headerOverride'] = 'structure/header/blank';
            $this->data['footerOverride'] = 'structure/footer/blank';
        }

        //  Show the survey
        $this->index($oSurvey, $oResponse, $bIsAdminPreviewInactive ?? false, $bIsAdminPreviewAnon ?? false);
    }
}
