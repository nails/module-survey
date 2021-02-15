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
use Nails\Email;
use Nails\Factory;
use Nails\FormBuilder;
use Nails\Survey\Constants;
use Nails\Survey\Controller\Base;

/**
 * Class Survey
 */
class Survey extends Base
{
    /**
     * Renders the survey form
     *
     * @param \Nails\Survey\Resource\Survey   $oSurvey   The active survey
     * @param \Nails\Survey\Resource\Response $oResponse The active response
     *
     * @throws \Nails\Common\Exception\FactoryException
     * @throws \Nails\Common\Exception\ViewNotFoundException
     */
    public function index(\Nails\Survey\Resource\Survey $oSurvey, \Nails\Survey\Resource\Response $oResponse = null)
    {
        /** @var \Nails\Common\Service\Input $oInput */
        $oInput = Factory::service('Input');
        /** @var \Nails\Common\Service\View $oView */
        $oView = Factory::service('View');
        /** @var \Nails\Common\Service\Asset $oAsset */
        $oAsset = Factory::service('Asset');
        /** @var \Nails\Captcha\Service\Captcha $oCaptcha */
        $oCaptcha = Factory::service('Captcha', \Nails\Captcha\Constants::MODULE_SLUG);
        /** @var \Nails\Survey\Model\Response $oResponseModel */
        $oResponseModel = Factory::model('Response', Constants::MODULE_SLUG);
        /** @var \Nails\Survey\Model\Response\Answer $oResponseAnswerModel */
        $oResponseAnswerModel = Factory::model('ResponseAnswer', Constants::MODULE_SLUG);

        $this->data['oSurvey']           = $oSurvey;
        $this->data['oResponse']         = $oResponse;
        $this->data['bIsCaptchaEnabled'] = $oCaptcha->isEnabled();

        if (!empty($oResponse) && $oResponse->status === $oResponseModel::STATUS_SUBMITTED) {
            show404();

        } else {

            if ($oInput->post()) {

                try {

                    if (!empty($this->data['bIsAdminPreviewInactive'])) {
                        throw new NailsException('Survey is not active.');

                    } elseif (!empty($this->data['bIsAdminPreviewAnon'])) {
                        throw new NailsException('Anonymous submissions are disabled for this survey.');
                    }

                    //  Validate
                    $bIsFormValid = formBuilderValidate(
                        $oSurvey->form->fields->data,
                        $oInput->post('field')
                    );

                    $bIsCaptchaValid = true;
                    if ($oSurvey->form->has_captcha && $this->data['bIsCaptchaEnabled']) {
                        if (!$oCaptcha->verify()) {
                            $this->data['captchaError'] = 'You failed the captcha test.';
                            $bIsCaptchaValid            = false;
                        }
                    }

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

                            $oResponse = $oResponseModel->create($aResponse, true);
                            if (empty($oResponse)) {
                                throw new NailsException(
                                    'Failed save response. ' . $oResponseModel->lastError()
                                );
                            }
                        }

                        foreach ($aResponseData as $aResponseRow) {

                            $aResponseRow['survey_response_id'] = $oResponse->id;

                            if (!$oResponseAnswerModel->create($aResponseRow)) {
                                throw new NailsException(
                                    'Failed save response. ' . $oResponseAnswerModel->lastError()
                                );
                            }
                        }

                        //  Mark response as submitted
                        if (!$oResponseModel->setSubmitted($oResponse->id)) {
                            throw new NailsException(
                                'Failed to mark response as submitted. ' . $oResponseModel->lastError()
                            );
                        }

                        //  Send a notification email
                        if (!empty($oSurvey->notification_email)) {

                            $oResponse = $oResponseModel->getById(
                                $oResponse->id,
                                [
                                    'expand' => [
                                        ['answers', ['expand' => ['question', 'option']]],
                                    ],
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

                        //  Show thank you page
                        $oView
                            ->load([
                                'structure/header',
                                'survey/thanks',
                                'structure/footer',
                            ]);
                        return;

                    } else {
                        $this->data['error'] = lang('fv_there_were_errors');
                    }

                } catch (\Exception $e) {
                    $this->data['error'] = $e->getMessage();
                }
            }

            $oAsset->load('survey.min.css', Constants::MODULE_SLUG);

            $oView
                ->load([
                    'structure/header',
                    'survey/survey',
                    'structure/footer',
                ]);
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Routes the request
     *
     * @throws \Nails\Common\Exception\FactoryException
     * @throws \Nails\Common\Exception\ModelException
     * @throws \Nails\Common\Exception\ViewNotFoundException
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
                new \Nails\Common\Helper\Model\Expand(
                    'form',
                    new \Nails\Common\Helper\Model\Expand(
                        'fields',
                        new \Nails\Common\Helper\Model\Expand('options')
                    )
                ),
            ]
        );

        if (empty($oSurvey) || $oSurvey->token != $sSurveyToken) {
            show404();

        } elseif (!$oSurvey->is_active && !userHasPermission('admin:survey:survey:*')) {
            show404();

        } elseif (!$oSurvey->is_active && userHasPermission('admin:survey:survey:*')) {
            $this->data['bIsAdminPreviewInactive'] = true;
        }

        //  Get the Response, if any
        if (!empty($iResponseId)) {
            $oResponse = $oResponseModel->getById($iResponseId);
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
                $this->data['bIsAdminPreviewAnon'] = true;
            }
        }

        //  Minimal layout?
        if ($oSurvey->is_minimal) {
            $this->data['headerOverride'] = 'structure/header/blank';
            $this->data['footerOverride'] = 'structure/footer/blank';
        }

        //  Show the survey
        $this->index($oSurvey, $oResponse);
    }
}
