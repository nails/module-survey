<?php

use Nails\Common\Service\View;
use Nails\Factory;

/**
 * @var \Nails\Survey\Resource\Survey $oItem
 * @var bool                          $bIsCaptchaEnabled
 */

/** @var View $oView */
$oView = Factory::service('View');

?>
<div class="group-survey edit">
    <?php
    echo form_open(null, 'id="main-survey"');
    echo \Nails\Admin\Helper::tabs([
        [
            'label'   => 'Survey Page',
            'content' => function () use ($oView, $oItem, $bIsCaptchaEnabled) {
                return \Nails\Admin\Helper::loadInlineView('edit/survey-page', [
                    'bIsCaptchaEnabled' => $bIsCaptchaEnabled,
                ], true);
            },
        ],
        [
            'label'   => 'Survey Fields',
            'content' => function () use ($oView, $oItem) {
                return \Nails\Admin\Helper::loadInlineView('edit/survey-fields', [
                    'oItem' => $oItem,
                ], true);
            },
        ],
        [
            'label'   => 'Submision Behaviour',
            'content' => function () use ($oView, $oItem) {
                return \Nails\Admin\Helper::loadInlineView('edit/submission-behaviour', [
                    'oItem' => $oItem,
                ], true);
            },
        ],
        [
            'label'   => 'Thank You Page',
            'content' => function () use ($oView, $oItem) {
                return \Nails\Admin\Helper::loadInlineView('edit/thank-you-page', [
                    'oItem' => $oItem,
                ], true);
            },
        ],
        [
            'label'   => 'Stats',
            'content' => function () use ($oView, $oItem) {
                return \Nails\Admin\Helper::loadInlineView('edit/stats', [
                    'oItem' => $oItem,
                ], true);
            },
        ],
        [
            'label'   => 'Advanced',
            'content' => function () use ($oView, $oItem) {
                return \Nails\Admin\Helper::loadInlineView('edit/advanced', [
                    'oItem' => $oItem,
                ], true);
            },
        ],
    ]);

    echo \Nails\Admin\Helper::floatingControls($CONFIG['FLOATING_CONFIG']);
    echo form_close();

    ?>
</div>
