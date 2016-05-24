<?php

/**
 * Migration:   2
 * Started:     18/05/2016
 * Finalised:   23/05/2016
 *
 * @package     Nails
 * @subpackage  module-survey
 * @category    Database Migration
 * @author      Nails Dev Team
 * @link
 */

namespace Nails\Database\Migration\Nailsapp\ModuleSurvey;

use Nails\Common\Console\Migrate\Base;

class Migration2 extends Base
{
    /**
     * Execute the migration
     * @return Void
     */
    public function execute()
    {
        $this->query("ALTER TABLE `{{NAILS_DB_PREFIX}}survey_survey` ADD `is_active` TINYINT(1)  UNSIGNED  NOT NULL  DEFAULT '0'  AFTER `form_id`;");
        $this->query("UPDATE `{{NAILS_DB_PREFIX}}survey_survey` SET is_active = 1;");
        $this->query("ALTER TABLE `{{NAILS_DB_PREFIX}}survey_response` ADD `name` VARCHAR(150)  NULL  DEFAULT NULL  AFTER `user_id`;");
        $this->query("ALTER TABLE `{{NAILS_DB_PREFIX}}survey_response` ADD `email` VARCHAR(300)  NULL  DEFAULT NULL  AFTER `name`;");

    }
}
