<div class="group-survey response edit">
    <p>
        This view allows you to edit the text component of a response.
    </p>
    <p class="alert alert-warning">
        <strong>Note:</strong> Some field types set a text value purely as a means of communicating the user's response
        and updating this will not alter things which are generated using other fields (e.g. statistics). Also, for
        some field types a text component doesn't make any sense.
    </p>
    <?php

    $sIsModal = !empty($isModal) ? '?isModal=1' : '';

    echo form_open(current_url() . $sIsModal);

    ?>
    <fieldset>
        <legend>Text</legend>
        <?php

        $aField = array(
            'key'      => 'text',
            'label'    => 'Text',
            'default'  => $answer->text
        );
        echo form_field_textarea($aField);

        ?>
    </fieldset>
    <div class="admin-floating-controls">
        <button type="submit" class="btn btn-primary">
            Save Changes
        </button>
    </div>
    <?=form_close()?>
</div>
