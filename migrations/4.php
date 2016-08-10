<?php

/**
 * Migration:   4
 * Started:     10/08/2016
 * Finalised:   10/08/2016
 *
 * @package     Nails
 * @subpackage  module-survey
 * @category    Database Migration
 * @author      Nails Dev Team
 * @link
 */

namespace Nails\Database\Migration\Nailsapp\ModuleSurvey;

use Nails\Common\Console\Migrate\Base;

class Migration4 extends Base
{
    /**
     * Execute the migration
     * @return Void
     */
    public function execute()
    {
        $this->query("ALTER TABLE `{{NAILS_DB_PREFIX}}survey_survey` ADD `allow_public_stats` TINYINT(1)  UNSIGNED  NOT NULL  DEFAULT '0'  AFTER `allow_anonymous_response`;");
        $this->query("ALTER TABLE `{{NAILS_DB_PREFIX}}survey_survey` ADD `access_token_stats` CHAR(29)  NOT NULL  DEFAULT ''  AFTER `access_token`;");
        $this->query("ALTER TABLE `{{NAILS_DB_PREFIX}}survey_survey` ADD `stats_header` TEXT  NOT NULL  AFTER `allow_public_stats`;");
        $this->query("ALTER TABLE `{{NAILS_DB_PREFIX}}survey_survey` ADD `stats_footer` TEXT  NOT NULL  AFTER `stats_header`;");

        //  Generate a stats access token for any existing surveys
        //  @todo
    }
}
