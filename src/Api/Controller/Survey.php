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

use Nails\Api\Controller\Base;
use Nails\Api\Exception\ApiException;
use Nails\Factory;

class Survey extends Base
{
    /**
     * Returns aggregated stats for a given survey
     */
    public function getStats()
    {
        //  Get Survey
        $oInput       = Factory::service('Input');
        $oSurveyModel = Factory::model('Survey', 'nailsapp/module-survey');
        $oSurvey      = $oSurveyModel->getById($oInput->get('survey_id'));

        if (empty($oSurvey)) {
            throw new ApiException('Invalid Survey ID', 404);
        }

        //  Verify the token:
        //  - If the token supplied is the surveys access token then the user must have survey stats permissions.
        //  - If the token supplied matches the stats token then public stats must be turned on.
        $sToken = $oInput->get('access_token');
        if ($oSurvey->access_token == $sToken) {
            if (!userHasPermission('admin:survey:survey:response')) {
                throw new ApiException('You are not authorised to see survey stats', 401);
            }
        } elseif ($oSurvey->access_token_stats == $sToken) {
            if (!$oSurvey->allow_public_stats) {
                throw new ApiException('You are not authorised to see survey stats', 401);
            }
        } else {
            throw new ApiException('Invalid Access Token', 401);
        }

        //  Get field
        $iFieldId        = $oInput->get('field_id');
        $oFormFieldModel = Factory::model('FormField', 'nailsapp/module-form-builder');
        $oField          = $oFormFieldModel->getById($iFieldId);

        if (empty($oField)) {
            throw new ApiException('Invalid Field ID', 404);
        }

        //  Get Field Type Driver
        $oFieldTypeModel = Factory::model('FieldType', 'nailsapp/module-form-builder');
        $oFieldType      = $oFieldTypeModel->getBySlug($oField->type);

        if (empty($oFieldType)) {
            throw new ApiException('Invalid Field Type', 404);
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

        $oResponseAnswerModel = Factory::model('ResponseAnswer', 'nailsapp/module-survey');
        $aResponses           = $oResponseAnswerModel->getAll(null, null, $aData);

        return Factory::factory('ApiResponse', 'nailsapp/module-api')
                      ->setData([
                          'chart' => $oFieldType->getStatsChartData($aResponses),
                          'text'  => $oFieldType->getStatsTextData($aResponses),
                      ])
                      ->setMeta([
                          'response_count' => count($aResponses),
                      ]);
    }
}
