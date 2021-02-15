<?php

/**
 * @var \Nails\Survey\Resource\Survey $oItem
 * @var bool                          $bIsCaptchaEnabled
 */

echo form_field([
    'key'         => 'label',
    'label'       => 'Label',
    'placeholder' => 'Define the survey\'s label',
    'required'    => true,
    'default'     => $oItem->label ?? '',
]);

echo form_field_boolean([
    'key'      => 'is_active',
    'label'    => 'Active',
    'default'  => $oItem->is_active ?? false,
    'text_on'  => 'Yes',
    'text_off' => 'No',
]);

echo form_field_cms_widgets([
    'key'     => 'header',
    'label'   => 'Header',
    'default' => $oItem->header ?? '',
]);

echo form_field_cms_widgets([
    'key'     => 'footer',
    'label'   => 'Footer',
    'default' => $oItem->footer ?? '',
]);

echo form_field([
    'key'         => 'cta_label',
    'label'       => 'Button Label',
    'placeholder' => 'Define the text on the survey\'s submit button, defaults to "Submit".',
    'default'     => $oItem->cta->label ?? '',
]);

echo form_field_boolean([
    'key'        => 'has_captcha',
    'label'      => 'Captcha',
    'default'    => $oItem->form->has_captcha ?? $bIsCaptchaEnabled,
    'info'       => $bIsCaptchaEnabled ? '' : 'Captcha Module has not been configured',
    'info_class' => $bIsCaptchaEnabled ? '' : 'alert alert-warning',
]);

echo form_field_boolean([
    'key'     => 'is_minimal',
    'label'   => 'Minimal Layout',
    'default' => $oItem->is_minimal ?? false,
    'info'    => 'When minimal, the form will not feature the site\'s header and footer.',
]);
