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
    public function index()
    {
        $iSurveyId = (int) $this->uri->rsegment(3);

        if (empty($iSurveyId)) {
            show_404();
        }

        $oSurveyModel = Factory::model('Survey', 'nailsapp/module-survey');
        $oSurvey      = $oSurveyModel->getById($iSurveyId);

        if (!empty($oSurvey)) {

            if ($this->input->post()) {

                dumpanddie($_POST);
            }

            $this->data['oSurvey'] = $oSurvey;

            $this->load->view('structure/header', $this->data);
            $this->load->view('survey/survey', $this->data);
            $this->load->view('structure/footer', $this->data);

        } else {

            show_404();
        }
    }
}
