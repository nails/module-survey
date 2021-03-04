<?php

/**
 * @var \Nails\Survey\Resource\Survey $oItem
 */

echo form_field_boolean([
    'key'     => 'allow_anonymous_response',
    'label'   => 'Allow Anonymous Responses',
    'info'    => 'If enabled, anyone with the survey link will be able to submit a response.',
    'default' => $oItem->allow_anonymous_response ?? false,
]);

echo form_field_boolean([
    'key'     => 'allow_save',
    'label'   => 'Allow Save',
    'info'    => 'If enabled, responses can be saved and come back to later.',
    'default' => $oItem->allow_save ?? false,
]);

echo form_field([
    'key'         => 'notification_email',
    'label'       => 'Notify',
    'placeholder' => 'A comma separated list of email addresses to notify when a survey is submitted.',
    'default'     => implode(', ', $oItem->notification_email ?? []),
]);

echo form_field_boolean([
    'key'     => 'thankyou_email',
    'label'   => 'Email',
    'info'    => 'Send the user a thank you email',
    'default' => $oItem->thankyou_email->send ?? false,
    'data'    => [
        'revealer' => 'thankyou-email',
    ],
]);

echo form_field([
    'key'         => 'thankyou_email_subject',
    'label'       => 'Subject',
    'placeholder' => 'Define the subject of the thank you email',
    'default'     => $oItem->thankyou_email->subject ?? '',
    'data'        => [
        'revealer'  => 'thankyou-email',
        'reveal-on' => true,
    ],
]);

echo form_field_wysiwyg([
    'key'         => 'thankyou_email_body',
    'label'       => 'Body',
    'placeholder' => 'Define the body of the thank you email',
    'class'       => 'wysiwyg-basic',
    'default'     => $oItem->thankyou_email->body ?? '',
    'data'        => [
        'revealer'  => 'thankyou-email',
        'reveal-on' => true,
    ],
]);
