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

        $this->data['oSurvey']   = $oSurvey;
        $this->data['oResponse'] = $oResponse;

        if (!empty($oResponse) && $oResponse->status === $oResponseModel::STATUS_SUBMITTED) {

            $this->load->view('structure/header', $this->data);
            $this->load->view('survey/submitted', $this->data);
            $this->load->view('structure/footer', $this->data);

        } else {

            if ($this->input->post()) {

                try {

                    //  @todo - validate
                    //  @todo - update/create response

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

                } catch (\Exception $e) {
                    $this->data['error'] = $e->getMessage();
                }
            }

            $this->load->view('structure/header', $this->data);
            $this->load->view('survey/survey', $this->data);
            $this->load->view('structure/footer', $this->data);
        }
    }

    // --------------------------------------------------------------------------

    public function _remap()
    {
        $iSurveyId      = (int) $this->uri->rsegment(3);
        $sSurveyToken   = $this->uri->rsegment(4);
        $iResponseId    = (int) $this->uri->rsegment(5);
        $sResponseToken = $this->uri->rsegment(6);

        $oSurveyModel   = Factory::model('Survey', 'nailsapp/module-survey');
        $oResponseModel = Factory::model('Response', 'nailsapp/module-survey');

        Factory::helper('formbuilder', 'nailsapp/module-form-builder');

        //  Get the Survey
        $oSurvey = $oSurveyModel->getById($iSurveyId, array('includeForm' => true));
        if (empty($oSurvey) || $oSurvey->access_token != $sSurveyToken) {
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

        //  Surveys should be on blank screens
        $this->data['headerOverride'] = 'structure/header/blank';
        $this->data['footerOverride'] = 'structure/footer/blank';

        //  Load assets
        $oAsset = Factory::service('Asset');
        $oAsset->load('survey.css', 'nailsapp/module-survey');

        //  Show the survey
        $this->index($oSurvey, $oResponse);
    }
}
