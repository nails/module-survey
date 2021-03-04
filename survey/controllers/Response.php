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

use Nails\Common\Exception\FactoryException;
use Nails\Common\Exception\ModelException;
use Nails\Common\Helper\Model\Expand;
use Nails\Common\Service;
use Nails\Factory;
use Nails\Survey\Controller\Base;
use Nails\Survey\Constants;
use Nails\Survey\Model;
use Nails\Survey\Resource;

/**
 * Class Response
 */
class Response extends Base
{
    /**
     * @throws FactoryException
     * @throws ModelException
     */
    public function _remap()
    {
        /** @var Service\Uri $oUri */
        $oUri = Factory::service('Uri');
        /** @var Service\Session $oSession */
        $oSession = Factory::service('Session');
        /** @var Model\Response $oResponseModel */
        $oResponseModel = Factory::model('Response', Constants::MODULE_SLUG);

        $iResponseId    = (int) $oUri->rsegment(3);
        $sResponseToken = $oUri->rsegment(4);

        /** @var Resource\Response $oResponse */
        $oResponse = $oResponseModel->getById($iResponseId, [new Expand('survey')]);
        if (empty($oResponse) || $oResponse->token != $sResponseToken) {
            show404();
        }

        $oSession->keepFlashData();

        redirect($oResponse->survey->url . '/' . $oResponse->id . '/' . $oResponse->token);
    }
}
