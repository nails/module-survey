<?php

/**
 * This config file defines email types for this module.
 *
 * @package     Nails
 * @subpackage  module-survey
 * @category    Config
 * @author      Nails Dev Team
 * @link
 */

$config['email_types'] = [
    (object) [
        'slug'            => 'survey_notification',
        'name'            => 'Survey: Submission Notification',
        'description'     => 'Email sent when a survey is submitted',
        'template_header' => '',
        'template_body'   => 'survey/email/survey_notification',
        'template_footer' => '',
        'default_subject' => 'Response to {{survey.label}} survey has been received',
        'can_unsubscribe' => true,
        'factory'         => 'nails/module-survey::EmailNotification',
    ],
];
