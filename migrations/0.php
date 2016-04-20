<?php

/**
 * Migration:   0
 * Started:     07/04/2016
 * Finalised:
 *
 * @package     Nails
 * @subpackage  module-survey
 * @category    Database Migration
 * @author      Nails Dev Team
 * @link
 */

namespace Nails\Database\Migration\Nailsapp\ModuleSurvey;

use Nails\Common\Console\Migrate\Base;

class Migration0 extends Base
{
    /**
     * Execute the migration
     * @return Void
     */
    public function execute()
    {
        $this->query("
            CREATE TABLE `{{NAILS_DB_PREFIX}}survey_survey` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `access_token` char(29) NOT NULL DEFAULT '',
                `label` varchar(150) NOT NULL DEFAULT '',
                `header` text NOT NULL,
                `footer` text NOT NULL,
                `cta_label` varchar(100) NOT NULL DEFAULT '',
                `cta_attributes` varchar(255) NOT NULL DEFAULT '',
                `form_attributes` varchar(255) NOT NULL DEFAULT '',
                `has_captcha` tinyint(1) unsigned NOT NULL DEFAULT '0',
                `notification_email` varchar(255) NOT NULL DEFAULT '',
                `thankyou_email` tinyint(1) unsigned NOT NULL DEFAULT '0',
                `thankyou_email_subject` varchar(150) NOT NULL DEFAULT '',
                `thankyou_email_body` text NOT NULL,
                `thankyou_page_title` varchar(150) NOT NULL DEFAULT '',
                `thankyou_page_body` text NOT NULL,
                `form_id` int(11) unsigned DEFAULT NULL,
                `is_minimal` tinyint(1) unsigned NOT NULL DEFAULT '0',
                `is_deleted` tinyint(1) unsigned NOT NULL DEFAULT '0',
                `created` datetime NOT NULL,
                `created_by` int(11) unsigned DEFAULT NULL,
                `modified` datetime NOT NULL,
                `modified_by` int(11) unsigned DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `created_by` (`created_by`),
                KEY `modified_by` (`modified_by`),
                KEY `form_id` (`form_id`),
                CONSTRAINT `{{NAILS_DB_PREFIX}}survey_survey_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `{{NAILS_DB_PREFIX}}user` (`id`) ON DELETE SET NULL,
                CONSTRAINT `{{NAILS_DB_PREFIX}}survey_survey_ibfk_2` FOREIGN KEY (`modified_by`) REFERENCES `{{NAILS_DB_PREFIX}}user` (`id`) ON DELETE SET NULL,
                CONSTRAINT `{{NAILS_DB_PREFIX}}survey_survey_ibfk_3` FOREIGN KEY (`form_id`) REFERENCES `{{NAILS_DB_PREFIX}}formbuilder_form` (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");
        $this->query("
            CREATE TABLE `{{NAILS_DB_PREFIX}}survey_response` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `access_token` char(29) NOT NULL DEFAULT '',
                `survey_id` int(11) unsigned NOT NULL,
                `status` enum('OPEN','SUBMITTED') NOT NULL DEFAULT 'OPEN',
                `user_id` int(11) unsigned DEFAULT NULL,
                `name` varchar(150) DEFAULT NULL,
                `email` varchar(255) DEFAULT NULL,
                `is_deleted` tinyint(1) unsigned NOT NULL DEFAULT '0',
                `date_submitted` datetime DEFAULT NULL,
                `created` datetime NOT NULL,
                `created_by` int(11) unsigned DEFAULT NULL,
                `modified` datetime NOT NULL,
                `modified_by` int(11) unsigned DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `form_id` (`survey_id`),
                KEY `created_by` (`created_by`),
                KEY `modified_by` (`modified_by`),
                KEY `user_id` (`user_id`),
                CONSTRAINT `{{NAILS_DB_PREFIX}}survey_response_ibfk_1` FOREIGN KEY (`survey_id`) REFERENCES `{{NAILS_DB_PREFIX}}survey_survey` (`id`) ON DELETE CASCADE,
                CONSTRAINT `{{NAILS_DB_PREFIX}}survey_response_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `{{NAILS_DB_PREFIX}}user` (`id`) ON DELETE SET NULL,
                CONSTRAINT `{{NAILS_DB_PREFIX}}survey_response_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `{{NAILS_DB_PREFIX}}user` (`id`) ON DELETE SET NULL,
                CONSTRAINT `{{NAILS_DB_PREFIX}}survey_response_ibfk_4` FOREIGN KEY (`modified_by`) REFERENCES `{{NAILS_DB_PREFIX}}user` (`id`) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");
    }
}
