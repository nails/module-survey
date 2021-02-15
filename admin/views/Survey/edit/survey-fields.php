<?php

/**
 * @var \Nails\Survey\Resource\Survey $oItem
 */

if (form_error('fields')) {
    ?>
    <div class="alert alert-danger">
        <?=form_error('fields')?>
    </div>
    <?php
}

echo adminLoadFormBuilderView(
    'survey-fields',
    'fields',
    $oItem->form->fields->data ?? []
);
