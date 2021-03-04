<?php

/**
 * Migration: 5
 * Started:   15/02/2021
 *
 * @package    Nails
 * @subpackage module-survey
 * @category   Database Migration
 * @author     Nails Dev Team
 * @link
 */

namespace Nails\Database\Migration\Nails\ModuleSurvey;

use Nails\Common\Console\Migrate\Base;
use Nails\Factory;

/**
 * Class Migration5
 *
 * @package Nails\Database\Migration\Nails\ModuleSurvey
 */
class Migration5 extends Base
{
    /**
     * Execute the migration
     *
     * @return Void
     */
    public function execute()
    {
        $this->query('ALTER TABLE `{{NAILS_DB_PREFIX}}survey_survey` CHANGE `access_token` `token` CHAR(29) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT \'\';');
        $this->query('ALTER TABLE `{{NAILS_DB_PREFIX}}survey_survey` CHANGE `access_token_stats` `token_stats` CHAR(29) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT \'\';');
        $this->query('ALTER TABLE `{{NAILS_DB_PREFIX}}survey_response` CHANGE `access_token` `token` CHAR(29) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT \'\';');
        $this->query('ALTER TABLE `{{NAILS_DB_PREFIX}}survey_survey` ADD `allow_save` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0  AFTER `allow_anonymous_response`;');
    }
}
