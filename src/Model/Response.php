<?php

/**
 * Manage Responses
 *
 * @package     Nails
 * @subpackage  module-survey
 * @category    Model
 * @author      Nails Dev Team
 * @link
 */

namespace Nails\Survey\Model;

use Nails\Factory;
use Nails\Common\Model\Base;

class Response extends Base
{
    const STATUS_OPEN      = 'OPEN';
    const STATUS_SUBMITTED = 'SUBMITTED';

    // --------------------------------------------------------------------------

    /**
     * Construct the model
     */
    public function __construct()
    {
        parent::__construct();

        $this->table             = NAILS_DB_PREFIX . 'survey_response';
        $this->tableAlias       = 'sr';
        $this->destructiveDelete = false;
        $this->defaultSortColumn = 'created';
        $this->defaultSortOrder  = 'desc';
    }

    // --------------------------------------------------------------------------

    public function getAll($iPage = null, $iPerPage = null, $aData = array(), $bIncludeDeleted = false)
    {
        $aItems = parent::getAll($iPage, $iPerPage, $aData, $bIncludeDeleted);

        if (!empty($aItems)) {

            if (!empty($aData['includeAll']) || !empty($aData['includeSurvey'])) {
                $this->getSingleAssociatedItem(
                    $aItems,
                    'survey_id',
                    'survey',
                    'Survey',
                    'nailsapp/module-survey'
                );
            }

            if (!empty($aData['includeAll']) || !empty($aData['includeUser'])) {
                $this->getSingleAssociatedItem(
                    $aItems,
                    'user_id',
                    'user',
                    'User',
                    'nailsapp/module-auth'
                );
            }

            if (!empty($aData['includeAll']) || !empty($aData['includeAnswer'])) {
                $this->getManyAssociatedItems(
                    $aItems,
                    'answers',
                    'survey_response_id',
                    'ResponseAnswer',
                    'nailsapp/module-survey',
                    array(
                        'includeQuestion' => true,
                        'includeOption' => true
                    )
                );
            }
        }

        return $aItems;
    }

    // --------------------------------------------------------------------------

    public function create($aData = array(), $bReturnObject = false)
    {
        //  Generate an access token
        Factory::helper('string');
        $aData['access_token'] = generateToken();
        return parent::create($aData, $bReturnObject);
    }

    // --------------------------------------------------------------------------

    public function update($iId, $aData = array())
    {
        //  Ensure access tokens aren't updated
        unset($aData['access_token']);
        return parent::update($iId, $aData);
    }

    // --------------------------------------------------------------------------

    public function setOpen($iResponseId)
    {
        $bResult = $this->update(
            $iResponseId,
            array(
                'status'         => self::STATUS_OPEN,
                'date_submitted' => null
            )
        );

        if ($bResult) {
            $oEventService = Factory::service('Event');
            $oEventService->trigger('RESPONSE.OPEN', 'nailsapp/module-survey', $iResponseId);
        }

        return $bResult;
    }

    // --------------------------------------------------------------------------

    public function setSubmitted($iResponseId)
    {
        $bResult = $this->update(
            $iResponseId,
            array(
                'status'         => self::STATUS_SUBMITTED,
                'date_submitted' => array('NOW()', false)
            )
        );

        if ($bResult) {
            $oEventService = Factory::service('Event');
            $oEventService->trigger('RESPONSE.SUBMITTED', 'nailsapp/module-survey', $iResponseId);
        }

        return $bResult;
    }

    // --------------------------------------------------------------------------

    protected function formatObject(
        &$oObj,
        $aData = array(),
        $aIntegers = array(),
        $aBools = array(),
        $aFloats = array()
    ) {
        parent::formatObject($oObj, $aData, $aIntegers, $aBools, $aFloats);

        $oObj->url = site_url('survey/response/' . $oObj->id . '/' . $oObj->access_token);
    }
}
