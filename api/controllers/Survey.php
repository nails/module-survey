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

namespace Nails\Api\Survey;

use Nails\Api\Controller\Base;
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
            return [
                'status' => 404,
                'error'  => 'Invalid Survey ID.',
            ];
        }

        //  Verify the token:
        //  - If the token supplied is the surveys access token then the user must have survey stats permissions.
        //  - If the token supplied matches the stats token then public stats must be turned on.
        $sToken = $oInput->get('access_token');
        if ($oSurvey->access_token == $sToken) {
            if (!userHasPermission('admin:survey:survey:response')) {
                return [
                    'status' => 401,
                    'error'  => 'You are not authorised to see survey stats.',
                ];
            }
        } elseif ($oSurvey->access_token_stats == $sToken) {
            if (!$oSurvey->allow_public_stats) {
                return [
                    'status' => 401,
                    'error'  => 'You are not authorised to see survey stats.',
                ];
            }
        } else {
            return [
                'status' => 400,
                'error'  => 'Invalid Access Token.',
            ];
        }

        //  Get field
        $iFieldId        = $oInput->get('field_id');
        $oFormFieldModel = Factory::model('FormField', 'nailsapp/module-form-builder');
        $oField          = $oFormFieldModel->getById($iFieldId);

        if (empty($oField)) {
            return [
                'status' => 404,
                'error'  => 'Invalid Field ID.',
            ];
        }

        //  Get Field Type Driver
        $oFieldTypeModel = Factory::model('FieldType', 'nailsapp/module-form-builder');
        $oFieldType      = $oFieldTypeModel->getBySlug($oField->type);

        if (empty($oFieldType)) {
            return [
                'status' => 404,
                'error'  => 'Invalid Field Type.',
            ];
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

        //  Format into a data table
        $aOut = [
            'response_count' => count($aResponses),
            'data'           => [
                'chart' => $oFieldType->getStatsChartData($aResponses),
                'text'  => $oFieldType->getStatsTextData($aResponses),
            ],
        ];

        return $aOut;
    }
}
