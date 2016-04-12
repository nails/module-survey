<?php

/**
 * Manage Surveys
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

class Survey extends Base
{
    /**
     * Construct the model
     */
    public function __construct()
    {
        parent::__construct();

        $this->table             = NAILS_DB_PREFIX . 'survey_survey';
        $this->tablePrefix       = 's';
        $this->destructiveDelete = false;
    }

    // --------------------------------------------------------------------------

    public function getAll($iPage = null, $iPerPage = null, $aData = array(), $bIncludeDeleted = false)
    {
        $aItems = parent::getAll($iPage, $iPerPage, $aData, $bIncludeDeleted);

        if (!empty($aItems)) {

            if (!empty($aData['includeAll']) || !empty($aData['includeResponses'])) {
                $this->getManyAssociatedItems(
                    $aItems,
                    'responses',
                    'survey_id',
                    'Response',
                    'nailsapp/module-survey',
                    array(
                        'includeUser' => true
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

    protected function formatObject(
        &$oObj,
        $aData = array(),
        $aIntegers = array(),
        $aBools = array(),
        $aFloats = array()
    ) {
        parent::formatObject($oObj, $aData, $aIntegers, $aBools, $aFloats);

        $oObj->url = site_url('survey/' . $oObj->id . '/' . $oObj->access_token);
    }
}
