<?php

/**
 * @var \Nails\Survey\Resource\Survey $oItem
 */

echo form_field([
    'key'         => 'thankyou_page_title',
    'label'       => 'Title',
    'placeholder' => 'Define the title of the thank you page',
    'default'     => $oItem->thankyou_page->title ?? '',
]);

echo form_field_cms_widgets([
    'key'         => 'thankyou_page_body',
    'label'       => 'Body',
    'placeholder' => 'Define the body of the thank you page',
    'default'     => $oItem->thankyou_page->body ?? '',
]);
