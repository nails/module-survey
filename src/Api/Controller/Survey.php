<?php

/**
 * the survey end point
 *
 * @package     Nails
 * @subpackage  module-survey
 * @category    Controller
 * @author      Nails Dev Team
 * @link
 */

namespace Nails\Survey\Api\Controller;

use Nails\Api;
use Nails\Common\Service\Input;
use Nails\Factory;
use Nails\FormBuilder;
use Nails\Survey\Constants;
use Nails\Survey\Model\Response\Answer;
use Nails\Survey\Resource\Response;

class Survey extends Api\Controller\Base
{
    /**
     * Returns aggregated stats for a given survey
     */
    public function getStats()
    {
        /** @var Input $oInput */
        $oInput = Factory::service('Input');
        /** @var \Nails\Survey\Model\Survey $oSurveyModel */
        $oSurveyModel = Factory::model('Survey', Constants::MODULE_SLUG);
        /** @var FormBuilder\Model\FormField $oFormFieldModel */
        $oFormFieldModel = Factory::model('FormField', FormBuilder\Constants::MODULE_SLUG);
        /** @var FormBuilder\Service\FieldType $oFieldTypeService */
        $oFieldTypeService = Factory::service('FieldType', FormBuilder\Constants::MODULE_SLUG);
        /** @var Answer $oResponseAnswerModel */
        $oResponseAnswerModel = Factory::model('ResponseAnswer', Constants::MODULE_SLUG);

        // --------------------------------------------------------------------------

        $oSurvey = $oSurveyModel->getById($oInput->get('survey_id'));
        if (empty($oSurvey)) {
            throw new Api\Exception\ApiException('Invalid Survey ID', 404);
        }

        /**
         * Verify the token:
         * - If the token supplied is the surveys access token then the user must have survey stats permissions.
         * - If the token supplied matches the stats token then public stats must be turned on.
         */
        $sToken = $oInput->get('access_token');
        if ($oSurvey->token == $sToken) {
            if (!userHasPermission('admin:survey:survey:response')) {
                throw new Api\Exception\ApiException('You are not authorised to see survey stats', 401);
            }

        } elseif ($oSurvey->token_stats == $sToken) {
            if (!$oSurvey->allow_public_stats) {
                throw new Api\Exception\ApiException('You are not authorised to see survey stats', 401);
            }

        } else {
            throw new Api\Exception\ApiException('Invalid Access Token', 401);
        }

        //  Get field
        $iFieldId = $oInput->get('field_id');
        $oField   = $oFormFieldModel->getById($iFieldId);

        if (empty($oField)) {
            throw new Api\Exception\ApiException('Invalid Field ID', 404);
        }

        //  Get Field Type Driver
        $oFieldType = $oFieldTypeService->getBySlug($oField->type);

        if (empty($oFieldType)) {
            throw new Api\Exception\ApiException('Invalid Field Type', 404);
        }

        //  Get responses
        $sResponseIds = $oInput->get('response_ids');
        $aResponseIds = explode(',', $sResponseIds);
        $aResponseIds = array_filter($aResponseIds);
        $aResponseIds = array_unique($aResponseIds);

        $aData = [
            'expand' => ['option'],
            'where'  => [
                ['form_field_id', $oField->id],
            ],
        ];

        if (!empty($aResponseIds)) {
            $aData['where_in'] = [
                ['survey_response_id', $aResponseIds],
            ];
        }

        /** @var Response[] $aResponses */
        $aResponses = $oResponseAnswerModel->getAll($aData);

        return Factory::factory('ApiResponse', Api\Constants::MODULE_SLUG)
            ->setData([
                'chart' => $oFieldType->getStatsChartData($aResponses),
                'text'  => $oFieldType->getStatsTextData($aResponses),
            ])
            ->setMeta([
                'response_count' => count($aResponses),
            ]);
    }
}
