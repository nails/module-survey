<?php

/**
 * Manage Survey Questions
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

class SurveyQuestion extends Base
{
    /**
     * Construct the model
     */
    public function __construct()
    {
        parent::__construct();

        $this->table             = NAILS_DB_PREFIX . 'survey_survey_question';
        $this->tablePrefix       = 'sq';
        $this->destructiveDelete = false;
        $this->defaultSortColumn = 'order';
    }

    // --------------------------------------------------------------------------

    public function getAll($iPage = null, $iPerPage = null, $aData = array(), $bIncludeDeleted = false)
    {
        $aItems = parent::getAll($iPage, $iPerPage, $aData, $bIncludeDeleted);

        if (!empty($aItems)) {

            if (!empty($aData['includeAll']) || !empty($aData['includeOptions'])) {
                $this->getManyAssociatedItems(
                    $aItems,
                    'options',
                    'survey_question_id',
                    'SurveyQuestionOption',
                    'nailsapp/module-survey'
                );
            }
        }

        return $aItems;
    }
}
