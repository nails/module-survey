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
use Nails\Factory;
use Nails\Survey\Controller\Base;
use Nails\Survey\Constants;

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
        /** @var \Nails\Common\Service\Uri $oUri */
        $oUri = Factory::service('Uri');
        /** @var \Nails\Survey\Model\Response $oResponseModel */
        $oResponseModel = Factory::model('Response', Constants::MODULE_SLUG);

        $iResponseId    = (int) $oUri->rsegment(3);
        $sResponseToken = $oUri->rsegment(4);

        /** @var \Nails\Survey\Resource\Response $oResponse */
        $oResponse = $oResponseModel->getById($iResponseId, ['expand' => ['survey']]);
        if (empty($oResponse) || $oResponse->token != $sResponseToken) {
            show404();
        }

        redirect($oResponse->survey->url . '/' . $oResponse->id . '/' . $oResponse->token);
    }
}
