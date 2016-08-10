<?php

/**
 * Migration:   3
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

class Migration3 extends Base
{
    /**
     * Execute the migration
     * @return Void
     */
    public function execute()
    {
        $this->query("ALTER TABLE `{{NAILS_DB_PREFIX}}survey_survey` ADD `allow_anonymous_response` TINYINT(1)  UNSIGNED NOT NULL  DEFAULT '1'  AFTER `thankyou_page_body`;");
    }
}
