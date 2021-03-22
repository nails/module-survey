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

use Nails\Common\Service\Asset;
use Nails\Factory;
use Nails\FormBuilder;
use Nails\Survey\Controller\Base;
use Nails\Survey\Constants;

/**
 * Class Stats
 */
class Stats extends Base
{
    /**
     * @param \Nails\Survey\Resource\Survey $oSurvey
     *
     * @throws \Nails\Common\Exception\FactoryException
     * @throws \Nails\Common\Exception\ViewNotFoundException
     */
    public function index(\Nails\Survey\Resource\Survey $oSurvey)
    {
        /** @var Asset $oAsset */
        $oAsset = Factory::service('Asset');
        $oAsset->load('stats.min.js', Constants::MODULE_SLUG);

        /** @var \Nails\Common\Service\View $oView */
        $oView = Factory::service('View');
        $oView
            ->setData([
                'oSurvey' => $oSurvey,
            ])
            ->load([
                'structure/header',
                'survey/stats',
                'structure/footer',
            ]);
    }

    // --------------------------------------------------------------------------

    /**
     * @throws \Nails\Common\Exception\FactoryException
     * @throws \Nails\Common\Exception\ModelException
     */
    public function _remap()
    {
        /** @var \Nails\Common\Service\Uri $oUri */
        $oUri = Factory::service('Uri');
        /** @var \Nails\Survey\Model\Survey $oSurveyModel */
        $oSurveyModel = Factory::model('Survey', Constants::MODULE_SLUG);

        $iSurveyId    = (int) $oUri->rsegment(3);
        $sSurveyToken = $oUri->rsegment(4);

        //  Get the Survey
        $oSurvey = $oSurveyModel->getById(
            $iSurveyId,
            [
                new \Nails\Common\Helper\Model\Expand('form', new \Nails\Common\Helper\Model\Expand('fields')),
                new \Nails\Common\Helper\Model\Expand('responses'),
            ]
        );

        if (empty($oSurvey) || !$oSurvey->is_active || $oSurvey->token_stats != $sSurveyToken) {
            show404();

        } elseif (!$oSurvey->allow_public_stats) {
            show404();
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
