<?php

/**
 * This class handles survey response redirection
 *
 * @package     Nails
 * @subpackage  module-survey
 * @category    Controller
 * @author      Nails Dev Team
 * @link
 */

use Nails\Factory;

class Response extends NAILS_Controller
{
    public function _remap()
    {
        $oUri           = Factory::service('Uri');
        $iResponseId    = (int) $oUri->rsegment(3);
        $sResponseToken = $oUri->rsegment(4);
        $oResponseModel = Factory::model('Response', 'nailsapp/module-survey');

        //  Get the Survey
        $oResponse = $oResponseModel->getById($iResponseId, array('includeSurvey' => true));
        if (empty($oResponse) || $oResponse->access_token != $sResponseToken) {
            show_404();
        }

        redirect($oResponse->survey->url . '/' . $oResponse->id . '/' . $oResponse->access_token);
    }
}
