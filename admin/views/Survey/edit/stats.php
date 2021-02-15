<?php

/**
 * @var \Nails\Survey\Resource\Survey $oItem
 */

echo form_field_boolean([
    'key'     => 'allow_public_stats',
    'label'   => 'Public Stats',
    'default' => $oItem->allow_public_stats ?? false,
    'data'    => [
        'revealer' => 'public-stats',
    ],
]);

echo form_field_cms_widgets([
    'key'     => 'stats_header',
    'label'   => 'Header',
    'default' => $oItem->stats_header ?? '',
    'data'    => [
        'revealer'  => 'public-stats',
        'reveal-on' => true,
    ],
]);

echo form_field_cms_widgets([
    'key'     => 'stats_footer',
    'label'   => 'Footer',
    'default' => $oItem->stats_footer ?? '',
    'data'    => [
        'revealer'  => 'public-stats',
        'reveal-on' => true,
    ],
]);
