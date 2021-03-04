<?php

/**
 * @var \Nails\Survey\Resource\Survey $oItem
 */

echo form_field([
    'key'         => 'cta_label',
    'label'       => 'Button Label',
    'placeholder' => 'Define the submit button\'s text.',
    'default'     => $oItem->cta->attributes ?? '',
    'placeholder' => 'Submit Response',
]);

echo form_field([
    'key'         => 'cta_attributes',
    'label'       => 'Button Attributes',
    'placeholder' => 'Define any custom attributes which should be attached to the submit button.',
    'default'     => $oItem->cta->attributes ?? '',
]);

echo form_field([
    'key'         => 'form_attributes',
    'label'       => 'Survey Attributes',
    'placeholder' => 'Define any custom attributes which should be attached to the survey.',
    'default'     => $oItem->form_attributes ?? '',
]);
