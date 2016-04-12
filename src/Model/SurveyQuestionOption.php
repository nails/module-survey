<?php

/**
 * Manage Survey Question Options
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

class SurveyQuestionOption extends Base
{
    /**
     * Construct the model
     */
    public function __construct()
    {
        parent::__construct();

        $this->table             = NAILS_DB_PREFIX . 'survey_survey_question_option';
        $this->tablePrefix       = 'sqo';
        $this->destructiveDelete = false;
        $this->defaultSortColumn = 'order';
    }
}
