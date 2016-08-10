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

use Nails\Factory;
use Nails\Cms\Exception\RenderException;

class Survey extends NAILS_Controller
{
    public function index($oSurvey, $oResponse)
    {
        $oResponseModel = Factory::model('Response', 'nailsapp/module-survey');
        $oCaptchaModel  = Factory::model('Captcha', 'nailsapp/module-captcha');

        $this->data['oSurvey']           = $oSurvey;
        $this->data['oResponse']         = $oResponse;
        $this->data['bIsCaptchaEnabled'] = $oCaptchaModel->isEnabled();

        if (!empty($oResponse) && $oResponse->status === $oResponseModel::STATUS_SUBMITTED) {

            show_404();

        } else {

            $oInput = Factory::service('Input');
            if ($oInput->post()) {

                try {

                    if (!empty($this->data['is_admin_preview'])) {
                        throw new \Exception('Anonymous submissions are disabled for this survey.');
                    }

                    //  Validate
                    $bisFormValid = formBuilderValidate(
                        $oSurvey->form->fields->data,
                        $oInput->post('field')
                    );

                    $bIsCaptchaValid = true;
                    if ($oSurvey->form->has_captcha && $this->data['bIsCaptchaEnabled']) {
                        if (!$oCaptchaModel->verify()) {
                            $this->data['captchaError'] = 'You failed the captcha test.';
                            $bIsCaptchaValid            = false;
                        }
                    }

                    if ($bisFormValid && $bIsCaptchaValid) {

                        //  For each response, extract all the components
                        $aParsedResponse = formBuilderParseResponse(
                            $oSurvey->form->fields->data,
                            $oInput->post('field')
                        );

                        $aResponseData = array();
                        $iOrder        = 0;
                        foreach ($aParsedResponse as $oRow) {
                            $aResponseData[] = array(
                                'survey_response_id'   => $oSurvey->id,
                                'form_field_id'        => $oRow->field_id,
                                'form_field_option_id' => $oRow->option_id,
                                'text'                 => $oRow->text,
                                'data'                 => $oRow->data,
                                'order'                => $iOrder
                            );
                            $iOrder++;
                        }


                        if (empty($oResponse)) {
                            $aResponse = array(
                                'survey_id' => $oSurvey->id,
                                'user_id'   => activeUser('id')
                            );

                            $oResponse = $oResponseModel->create($aResponse, true);
                            if (empty($oResponse)) {
                                throw new \Exception(
                                    'Failed save response. ' . $oResponseModel->lastError(),
                                    1
                                );
                            }
                        }

                        $oResponseAnswerModel = Factory::model('ResponseAnswer', 'nailsapp/module-survey');

                        foreach ($aResponseData as $aResponseRow) {

                            $aResponseRow['survey_response_id'] = $oResponse->id;

                            if (!$oResponseAnswerModel->create($aResponseRow)) {
                                throw new \Exception(
                                    'Failed save response. ' . $oResponseAnswerModel->lastError(),
                                    2
                                );
                            }
                        }

                        //  Mark response as submitted
                        if (!$oResponseModel->setSubmitted($oResponse->id)) {
                            throw new \Exception(
                                'Failed to mark response as submitted. ' . $oResponseModel->lastError(),
                                1
                            );
                        }

                        //  Show thank you page
                        $this->load->view('structure/header', $this->data);
                        $this->load->view('survey/thanks', $this->data);
                        $this->load->view('structure/footer', $this->data);
                        return;

                    } else {

                        $this->data['error'] = lang('fv_there_were_errors');
                    }

                } catch (\Exception $e) {

                    $this->data['error'] = $e->getMessage();
                }
            }

            $oAsset = Factory::service('Asset');
            $oAsset->load('survey.css', 'nailsapp/module-survey');

            $this->load->view('structure/header', $this->data);
            $this->load->view('survey/survey', $this->data);
            $this->load->view('structure/footer', $this->data);
        }
    }

    // --------------------------------------------------------------------------

    public function _remap()
    {
        $oUri           = Factory::service('Uri');
        $iSurveyId      = (int) $oUri->rsegment(3);
        $sSurveyToken   = $oUri->rsegment(4);
        $iResponseId    = (int) $oUri->rsegment(5);
        $sResponseToken = $oUri->rsegment(6);

        $oSurveyModel   = Factory::model('Survey', 'nailsapp/module-survey');
        $oResponseModel = Factory::model('Response', 'nailsapp/module-survey');

        Factory::helper('formbuilder', 'nailsapp/module-form-builder');

        //  Get the Survey
        $oSurvey = $oSurveyModel->getById($iSurveyId, array('includeForm' => true));
        if (empty($oSurvey) || !$oSurvey->is_active || $oSurvey->access_token != $sSurveyToken) {
            show_404();
        }

        //  Get the Response, if any
        if (!empty($iResponseId)) {
            $oResponse = $oResponseModel->getById($iResponseId);
            if (empty($oResponse) || $oResponse->access_token != $sResponseToken) {
                show_404();
            }
        } else {
            $oResponse = null;
        }

        //  Anonymous responses enabled?
        if (!$oSurvey->allow_anonymous_response && empty($oResponse)) {
            //  If user has survey permissions then assume they are an admin and allow the rendering
            //  of the survey, but prevent submission
            if (!userHasPermission('admin:survey:survey:*')) {
                show_404();
            } else {
                $this->data['is_admin_preview'] = true;
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
