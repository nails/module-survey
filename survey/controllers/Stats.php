<?php

/**
 * This class handles rendering survey response stats
 *
 * @package     Nails
 * @subpackage  module-survey
 * @category    Controller
 * @author      Nails Dev Team
 * @link
 */

use Nails\Factory;
use Nails\Survey\Controller\Base;

class Stats extends Base
{
    public function index($oSurvey)
    {
        $this->data['oSurvey'] = $oSurvey;

        $oAsset = Factory::service('Asset');
        $oAsset->load('stats.css', 'nailsapp/module-survey');
        $oAsset->load('https://www.gstatic.com/charts/loader.js');
        $oAsset->load('admin.survey.stats.min.js', 'nailsapp/module-survey');
        $oAsset->load('admin.survey.stats.charts.min.js', 'nailsapp/module-survey');
        $oAsset->inline(
            'var SurveyStats = new _ADMIN_SURVEY_STATS(
                ' . $oSurvey->id . ',
                "' . $oSurvey->access_token_stats . '"
            );',
            'JS'
        );

        $oView = Factory::service('View');
        $oView->load('structure/header', $this->data);
        $oView->load('survey/stats', $this->data);
        $oView->load('structure/footer', $this->data);
    }

    // --------------------------------------------------------------------------

    public function _remap()
    {
        $oUri         = Factory::service('Uri');
        $oSurveyModel = Factory::model('Survey', 'nailsapp/module-survey');
        $iSurveyId    = (int) $oUri->rsegment(3);
        $sSurveyToken = $oUri->rsegment(4);

        Factory::helper('formbuilder', 'nailsapp/module-form-builder');

        //  Get the Survey
        $oSurvey = $oSurveyModel->getById($iSurveyId, ['expand' => ['form', 'responses']]);
        if (empty($oSurvey) || !$oSurvey->is_active || $oSurvey->access_token_stats != $sSurveyToken) {
            show_404();
        }

        //  Public stats enabled?
        if (!$oSurvey->allow_public_stats) {
            show_404();
        }

        //  Are there any responses?
        if ($oSurvey->responses->count === 0) {
            show_404();
        }

        //  Minimal layout?
        if ($oSurvey->is_minimal) {
            $this->data['headerOverride'] = 'structure/header/blank';
            $this->data['footerOverride'] = 'structure/footer/blank';
        }

        //  Show the survey
        $this->index($oSurvey);
    }
}
