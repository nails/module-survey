<?php

/**
 * Manage Response Answers
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

class ResponseAnswer extends Base
{
    /**
     * Construct the model
     */
    public function __construct()
    {
        parent::__construct();

        $this->table             = NAILS_DB_PREFIX . 'survey_response_answer';
        $this->tablePrefix       = 'sr';
        $this->destructiveDelete = false;
        $this->defaultSortColumn = 'order';
    }

    // --------------------------------------------------------------------------

    public function getAll($iPage = null, $iPerPage = null, $aData = array(), $bIncludeDeleted = false)
    {
        $aItems = parent::getAll($iPage, $iPerPage, $aData, $bIncludeDeleted);

        if (!empty($aItems)) {

            if (!empty($aData['includeAll']) || !empty($aData['includeResponse'])) {
                $this->getSingleAssociatedItem(
                    $aItems,
                    'survey_response_id',
                    'response',
                    'Response',
                    'nailsapp/module-survey'
                );
            }

            if (!empty($aData['includeAll']) || !empty($aData['includeQuestion'])) {
                $this->getSingleAssociatedItem(
                    $aItems,
                    'form_field_id',
                    'question',
                    'FormField',
                    'nailsapp/module-form-builder'
                );
            }

            if (!empty($aData['includeAll']) || !empty($aData['includeOption'])) {
                $this->getSingleAssociatedItem(
                    $aItems,
                    'form_field_option_id',
                    'option',
                    'FormFieldOption',
                    'nailsapp/module-form-builder'
                );
            }
        }

        return $aItems;
    }
}
