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

use Nails\Factory;
use Nails\Api\Controller\Base;

class Survey extends Base
{
    /**
     * Returns aggregated stats for a given survey
     */
    public function getStats()
    {
        if (!userHasPermission('admin:survey:survey:response')) {

            return array(
                'status' => 401,
                'error' => 'You are not authorised to see survey stats.'
            );

        } else {

            //  Get Survey
            $oInput       = Factory::service('Input');
            $oSurveyModel = Factory::model('Survey', 'nailsapp/module-survey');
            $oSurvey      = $oSurveyModel->getById($oInput->get('survey_id'));

            if (empty($oSurvey)) {
                return array(
                    'status' => 404,
                    'error' => 'Invalid Survey ID.'
                );
            }

            //  Get responses
            $oResponseAnswerModel = Factory::model('ResponseAnswer', 'nailsapp/module-survey');
            $aResponses           = $oResponseAnswerModel->getAll(
                null,
                null,
                array(
                    'includeAnswer' => true,
                    'where' => array(
                        array('form_field_id', $oInput->get('field_id'))
                        //  @todo restrict to response IDs
                    )
                )
            );

            //  Format into a data table
            //  @todo - some charts don't make sense, perhaps need a way to define multiple charts (e.g likert)
            $aOut = array(
                'response_count' => count($aResponses),
                'data' => array(
                    'chart' => array(
                        'columns' => array(
                            'PIE' => array(
                                array('string', 'Statement'),
                                array('number', 'Value'),
                            ),
                            'BAR' => array(
                                array('string', 'Statement'),
                                array('number', 'Strongly Agree'),
                                array('number', 'Agree'),
                                array('number', 'Undecided'),
                                array('number', 'Disagree'),
                                array('number', 'Strongly Disagree')
                            ),
                            'COLUMN' => array(
                                array('string', 'Statement'),
                                array('number', 'Strongly Agree'),
                                array('number', 'Agree'),
                                array('number', 'Undecided'),
                                array('number', 'Disagree'),
                                array('number', 'Strongly Disagree')
                            )
                        ),
                        'rows' => array(
                            'PIE' => array(
                                array('Strongly Agree', 1),
                                array('Agree', 1),
                                array('Undecided', 1),
                                array('Disagree', 1),
                                array('Strongly Disagree', 1)
                            ),
                            'BAR' => array(
                                array('First Option', 1, 2, 4, 5, 6),
                                array('Second Option', 1, 2, 1, 3, 4),
                                array('Third Option', 1, 2, 0, 3, 6),
                                array('Fourth Option', 1, 2, 4, 5, 12),
                                array('Fifth Option', 1, 2, 4, 5, 12)
                            ),
                            'COLUMN' => array(
                                array('First Option', 1, 2, 4, 5, 6),
                                array('Second Option', 1, 2, 1, 3, 4),
                                array('Third Option', 1, 2, 0, 3, 6),
                                array('Fourth Option', 1, 2, 4, 5, 12),
                                array('Fifth Option', 1, 2, 4, 5, 12)
                            ),
                        )
                    ),
                    'text' => array(
                        'Donec id elit non mi porta gravida at eget metus. Nullam id dolor id nibh ultricies.',
                        'Donec id elit non mi porta gravida at eget metus. Nullam id dolor id nibh ultricies.',
                        'Donec id elit non mi porta gravida at eget metus. Nullam id dolor id nibh ultricies.',
                        'Donec id elit non mi porta gravida at eget metus. Nullam id dolor id nibh ultricies.'
                    ),
                )
            );

            foreach ($aResponses as $oResponse) {
                # code...
            }


            return $aOut;
        }
    }
}
