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
use Nails\Survey\Controller\Base;

class Response extends Base
{
    public function _remap()
    {
        $oUri           = Factory::service('Uri');
        $iResponseId    = (int) $oUri->rsegment(3);
        $sResponseToken = $oUri->rsegment(4);
        $oResponseModel = Factory::model('Response', 'nails/module-survey');

        $oResponse = $oResponseModel->getById($iResponseId, ['expand' => ['survey']]);
        if (empty($oResponse) || $oResponse->access_token != $sResponseToken) {
            show404();
        }

        redirect($oResponse->survey->url . '/' . $oResponse->id . '/' . $oResponse->access_token);
    }
}
