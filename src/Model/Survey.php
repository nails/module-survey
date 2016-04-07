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
}
