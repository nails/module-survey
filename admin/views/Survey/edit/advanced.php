<?php

/**
 * @var \Nails\Survey\Resource\Survey $oItem
 */

echo form_field([
    'key'         => 'cta_attributes',
    'label'       => 'Button Attributes',
    'placeholder' => 'Define any custom attributes which should be attached to the button.',
    'default'     => $oItem->cta->attributes ?? '',
]);

echo form_field([
    'key'         => 'form_attributes',
    'label'       => 'Survey Attributes',
    'placeholder' => 'Define any custom attributes which should be attached to the survey.',
    'default'     => $oItem->form_attributes ?? '',
]);
