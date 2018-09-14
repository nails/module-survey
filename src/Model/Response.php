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

use Nails\Common\Model\Base;
use Nails\Factory;
use Nails\Survey\Events;

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
        $this->destructiveDelete = false;
        $this->defaultSortColumn = 'created';
        $this->defaultSortOrder  = 'desc';
        $this->addExpandableField([
            'trigger'   => 'survey',
            'type'      => self::EXPANDABLE_TYPE_SINGLE,
            'property'  => 'survey',
            'model'     => 'Survey',
            'provider'  => 'nails/module-survey',
            'id_column' => 'survey_id',
        ]);
        $this->addExpandableField([
            'trigger'   => 'user',
            'type'      => self::EXPANDABLE_TYPE_SINGLE,
            'property'  => 'user',
            'model'     => 'User',
            'provider'  => 'nails/module-auth',
            'id_column' => 'user_id',
        ]);
        $this->addExpandableField([
            'trigger'   => 'answers',
            'type'      => self::EXPANDABLE_TYPE_MANY,
            'property'  => 'answers',
            'model'     => 'ResponseAnswer',
            'provider'  => 'nails/module-survey',
            'id_column' => 'survey_response_id',
            'data'      => [
                'expand' => ['question', 'option'],
            ],
        ]);
    }

    // --------------------------------------------------------------------------

    public function create(array $aData = [], $bReturnObject = false)
    {
        //  Generate an access token
        Factory::helper('string');
        $aData['access_token'] = generateToken();
        return parent::create($aData, $bReturnObject);
    }

    // --------------------------------------------------------------------------

    public function update($iId, array $aData = [])
    {
        //  Ensure access tokens aren't updated
        unset($aData['access_token']);
        return parent::update($iId, $aData);
    }

    // --------------------------------------------------------------------------

    public function setOpen($iId)
    {
        $bResult = $this->update(
            $iId,
            [
                'status'         => self::STATUS_OPEN,
                'date_submitted' => null,
            ]
        );

        if ($bResult) {
            $oEventService = Factory::service('Event');
            $oEventService->trigger(Events::RESPONSE_OPEN, 'nails/module-survey', [$iId]);
        }

        return $bResult;
    }

    // --------------------------------------------------------------------------

    public function setSubmitted($iId)
    {
        $bResult = $this->update(
            $iId,
            [
                'status'         => self::STATUS_SUBMITTED,
                'date_submitted' => ['NOW()', false],
            ]
        );

        if ($bResult) {
            $oEventService = Factory::service('Event');
            $oEventService->trigger(Events::RESPONSE_SUBMITTED, 'nails/module-survey', [$iId]);
        }

        return $bResult;
    }

    // --------------------------------------------------------------------------

    protected function formatObject(
        &$oObj,
        array $aData = [],
        array $aIntegers = [],
        array $aBools = [],
        array $aFloats = []
    ) {
        parent::formatObject($oObj, $aData, $aIntegers, $aBools, $aFloats);
        $oObj->url = site_url('survey/response/' . $oObj->id . '/' . $oObj->access_token);
    }
}
