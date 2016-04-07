<div class="group-survey edit">
    <?=form_open(null, 'id="main-survey"')?>
    <ul class="tabs">
        <li class="tab active">
            <a href="#" data-tab="survey">
                Survey Page
            </a>
        </li>
        <li class="tab">
            <a href="#" data-tab="fields">
                Survey Fields
            </a>
        </li>
        <li class="tab">
            <a href="#" data-tab="submission">
                Submission Behaviour
            </a>
        </li>
        <li class="tab">
            <a href="#" data-tab="thankyou">
                Thank You Page
            </a>
        </li>
        <li class="tab">
            <a href="#" data-tab="advanced">
                Advanced
            </a>
        </li>
    </ul>
    <section class="tabs">
        <div class="tab-page survey active">
            <div class="fieldset">
            <?php

            $aField = array(
                'key'         => 'label',
                'label'       => 'Label',
                'placeholder' => 'Define the survey\'s label',
                'required'    => true,
                'default'     => !empty($survey->label) ? $survey->label : ''
            );
            echo form_field($aField);

            // --------------------------------------------------------------------------

            $aField = array(
                'key'     => 'header',
                'label'   => 'Header',
                'default' => !empty($survey->header) ? $survey->header : ''
            );
            echo form_field_cms_widgets($aField);

            // --------------------------------------------------------------------------

            $aField = array(
                'key'     => 'footer',
                'label'   => 'Footer',
                'default' => !empty($survey->footer) ? $survey->footer : ''
            );
            echo form_field_cms_widgets($aField);

            // --------------------------------------------------------------------------

            $aField = array(
                'key'         => 'cta_label',
                'label'       => 'Button Label',
                'placeholder' => 'Define the text on the survey\'s submit button, defaults to "Submit".',
                'default'     => !empty($survey->cta->label) ? $survey->cta->label : ''
            );
            echo form_field($aField);

            // --------------------------------------------------------------------------

            $aField = array(
                'key'     => 'has_captcha',
                'label'   => 'Captcha',
                'default' => !empty($survey->has_captcha)
            );
            echo form_field_boolean($aField);

            ?>
            </div>
        </div>
        <div class="tab-page fields">
            <div class="table-responsive">
                <table id="survey-fields">
                    <thead>
                        <tr>
                            <th class="order">
                                Order
                            </th>
                            <th class="type">
                                Type
                            </th>
                            <th class="field-label">
                                Label
                            </th>
                            <th class="field-sub-label">
                                Sub Label
                            </th>
                            <th class="placeholder">
                                Placeholder
                            </th>
                            <th class="required">
                                Required
                            </th>
                            <th class="default">
                                Default Value
                            </th>
                            <th class="attributes">
                                Custom Field Attributes
                            </th>
                            <th class="remove">
                                &nbsp;
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php

                    if (!empty($_POST['fields'])) {

                        $aFields = $_POST['fields'];

                        //  Cast as objects to match database output
                        foreach ($aFields as &$aField) {

                            if (empty($aField['options'])) {
                                $aField['options'] = array();
                            }

                            foreach ($aField['options'] as &$aOption) {
                                $aOption = (object) $aOption;
                            }

                            $aField = (object) $aField;
                        }


                    } elseif (!empty($survey->fields->data)) {

                        $aFields = $survey->fields->data;

                    } else {

                        $aFields = array();
                    }

                    $i = 0;

                    foreach ($aFields as $oField) {

                        ?>
                        <tr>
                            <td class="order">
                                <b class="fa fa-bars handle"></b>
                                <?php

                                echo form_hidden(
                                    'fields[' . $i . '][id]',
                                    !empty($oField->id) ? $oField->id : ''
                                );

                                ?>
                            </td>
                            <td class="type">
                                <?php

                                echo form_dropdown(
                                    'fields[' . $i . '][type]',
                                    $aFieldTypes,
                                    set_value('fields[' . $i . '][type]', $oField->type),
                                    'class="select2 field-type"'
                                );

                                ?>
                                <a href="#survey-field-options-<?=$i?>" class="fancybox btn btn-xs btn-warning">
                                    Manage Options
                                </a>
                            </td>
                            <td class="field-label">
                                <?php

                                echo form_input(
                                    'fields[' . $i . '][label]',
                                    set_value('fields[' . $i . '][label]', $oField->label),
                                    'placeholder="The field\'s label"'
                                );

                                ?>
                            </td>
                            <td class="field-sub-label">
                                <?php

                                echo form_input(
                                    'fields[' . $i . '][sub_label]',
                                    set_value('fields[' . $i . '][sub_label]', $oField->sub_label),
                                    'placeholder="The field\'s sub-label"'
                                );

                                ?>
                            </td>
                            <td class="placeholder">
                                <?php

                                echo form_input(
                                    'fields[' . $i . '][placeholder]',
                                    set_value('fields[' . $i . '][placeholder]', $oField->placeholder),
                                    'placeholder="The field\'s placeholder"'
                                );

                                ?>
                            </td>
                            <td class="required">
                                <?php

                                echo form_checkbox(
                                    'fields[' . $i . '][is_required]',
                                    true,
                                    !empty($oField->is_required)
                                );

                                ?>
                            </td>
                            <td class="default">
                                <?php

                                echo '<div class="js-supports-default-value" style="display: none;">';
                                echo form_dropdown(
                                    'fields[' . $i . '][default_value]',
                                    $aFieldDefaultValues,
                                    set_value('fields[' . $i . '][default_value]', $oField->default_value),
                                    'class="select2 field-default"'
                                );

                                echo form_input(
                                    'fields[' . $i . '][default_value_custom]',
                                    set_value(
                                        'fields[' . $i . '][default_value_custom]',
                                        $oField->default_value_custom
                                    ),
                                    'placeholder="The default value"'
                                );
                                echo '</div>';

                                echo '<div class="js-no-default-value text-muted" style="display: none;">';
                                echo 'Field type does not support default values';
                                echo '</div>';

                                ?>
                            </td>
                            <td class="attributes">
                                <?php

                                echo form_input(
                                    'fields[' . $i . '][custom_attributes]',
                                    set_value(
                                        'fields[' . $i . '][custom_attributes]',
                                        $oField->custom_attributes
                                    ),
                                    'placeholder="Any custom attributes"'
                                );

                                ?>
                            </td>
                            <td class="remove">
                                <a href="#" class="remove-field" data-field-number="<?=$i?>">
                                    <b class="fa fa-times-circle fa-lg"></b>
                                </a>
                            </td>
                        </tr>
                        <?php

                        $i++;
                    }

                    ?>
                    </tbody>
                </table>
            </div>
            <p>
                <a href="#" id="add-field" class="btn btn-xs btn-success">
                    Add Field
                </a>
            </p>
        </div>
        <div class="tab-page submission">
            <div class="fieldset">
                <?php

                $aField = array(
                    'key'         => 'notification_email',
                    'label'       => 'Notify',
                    'placeholder' => 'A comma separated list of email addresses to notify when a survey is submitted.',
                    'default'     => !empty($survey->notification_email) ? implode(', ', $survey->notification_email) : ''
                );

                echo form_field($aField);

                // --------------------------------------------------------------------------

                $aField = array(
                    'key'     => 'thankyou_email',
                    'label'   => 'Email',
                    'info'    => 'Send the user a thank you email',
                    'id'      => 'do-send-thankyou',
                    'default' => !empty($survey->thankyou_email->send)
                );

                echo form_field_boolean($aField);

                // --------------------------------------------------------------------------

                echo '<div id="send-thankyou-options">';
                $aField = array(
                    'key'         => 'thankyou_email_subject',
                    'label'       => 'Subject',
                    'placeholder' => 'Define the subject of the thank you email',
                    'default'     => !empty($survey->thankyou_email->subject) ? $survey->thankyou_email->subject : ''
                );
                echo form_field($aField);

                // --------------------------------------------------------------------------

                $aField = array(
                    'key'         => 'thankyou_email_body',
                    'label'       => 'Body',
                    'placeholder' => 'Define the body of the thank you email',
                    'class'       => 'wysiwyg-basic',
                    'default'     => !empty($survey->thankyou_email->body) ? $survey->thankyou_email->body : ''
                );
                echo form_field_wysiwyg($aField);
                echo '</div>';

                ?>
            </div>
        </div>
        <div class="tab-page thankyou">
            <div class="fieldset">
                <?php

                $aField = array(
                    'key'         => 'thankyou_page_title',
                    'label'       => 'Title',
                    'placeholder' => 'Define the title of the thank you page',
                    'required'    => true,
                    'default'     => !empty($survey->thankyou_page->title) ? $survey->thankyou_page->title : ''
                );
                echo form_field($aField);

                // --------------------------------------------------------------------------

                $aField = array(
                    'key'         => 'thankyou_page_body',
                    'label'       => 'Body',
                    'placeholder' => 'Define the body of the thank you page',
                    'default'     => !empty($survey->thankyou_page->body) ? $survey->thankyou_page->body : ''
                );
                echo form_field_cms_widgets($aField);

                ?>
            </div>
        </div>
        <div class="tab-page advanced">
            <div class="fieldset">
                <?php

                $aField = array(
                    'key'         => 'cta_attributes',
                    'label'       => 'Button Attributes',
                    'default'     => !empty($survey->cta->attributes) ? $survey->cta->attributes : ''
                );
                echo form_field($aField);

                // --------------------------------------------------------------------------

                $aField = array(
                    'key'         => 'form_attributes',
                    'label'       => 'Survey Attributes',
                    'placeholder' => 'Define any custom attributes which should be attached to the survey.',
                    'default'     => !empty($survey->survey->attributes) ? $survey->survey->attributes : ''
                );
                echo form_field($aField);

                ?>
            </div>
        </div>
    </section>
    <div id="field-options">
    <?php

    $i = 0;
    foreach ($aFields as $oField) {

        ?>
        <div id="survey-field-options-<?=$i?>" class="survey-field-options">
            <table data-option-count="<?=!empty($oField->options) ? count($oField->options) : 0?>">
                <thead>
                    <tr>
                        <th class="option-label">
                            Label
                        </th>
                        <th class="option-selected">
                            Selected
                        </th>
                        <th class="option-disabled">
                            Disabled
                        </th>
                        <th class="option-remove">
                            &nbsp;
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php

                    if (!empty($oField->options)) {

                        $x = 0;

                        foreach ($oField->options->data as $oOption) {

                            ?>
                            <tr>
                                <td class="option-label">
                                    <?php

                                    echo form_input(
                                        'fields[' . $i . '][options][' . $x . '][label]',
                                        $oOption->label
                                    );

                                    echo form_hidden(
                                        'fields[' . $i . '][options][' . $x . '][id]',
                                        !empty($oOption->id) ? $oOption->id : ''
                                    );

                                    ?>
                                </td>
                                <td class="option-selected">
                                    <?php

                                    echo form_checkbox(
                                        'fields[' . $i . '][options][' . $x . '][is_selected]',
                                        true,
                                        !empty($oOption->is_selected)
                                    );

                                    ?>
                                </td>
                                <td class="option-disabled">
                                    <?php

                                    echo form_checkbox(
                                        'fields[' . $i . '][options][' . $x . '][is_disabled]',
                                        true,
                                        !empty($oOption->is_disabled)
                                    );

                                    ?>
                                </td>
                                <td class="option-remove">
                                    <a href="#" class="remove-option">
                                        <b class="fa fa-times-circle fa-lg"></b>
                                    </a>
                                </td>
                            </tr>
                            <?php

                            $x++;
                        }
                    }

                    ?>
                </tbody>
            </table>
            <p>
                <button type="button" class="btn btn-xs btn-success add-option" data-field-number="<?=$i?>">
                    Add Option
                </button>
            </p>
        </div>
        <?php

        $i++;
    }

    ?>
    </div>
    <p>
        <button type="submit" class="btn btn-primary">
            Save Changes
        </button>
    </p>
    <?=form_close()?>
</div>
<script type="template/mustache" id="template-field">
<tr>
    <td class="order">
        <b class="fa fa-bars handle"></b>
    </td>
    <td class="type">
        <?=form_dropdown('fields[{{fieldNumber}}][type]', $aFieldTypes, null, 'class="select2 field-type"')?>
        <a href="#survey-field-options-{{fieldNumber}}" class="fancybox btn btn-xs btn-warning">
            Manage Options
        </a>
    </td>
    <td class="field-label">
        <?=form_input(
            'fields[{{fieldNumber}}][label]',
            null,
            'placeholder="The field\'s label"'
        )?>
    </td>
    <td class="field-sub-label">
        <?=form_input(
            'fields[{{fieldNumber}}][sub_label]',
            null,
            'placeholder="The field\'s sub-label"'
        )?>
    </td>
    <td class="placeholder">
        <?=form_input(
            'fields[{{fieldNumber}}][placeholder]',
            null,
            'placeholder="The field\'s placeholder"'
        )?>
    </td>
    <td class="required">
        <?=form_checkbox(
            'fields[{{fieldNumber}}][is_required]',
            true
        )?>
    </td>
    <td class="default">
        <?=form_dropdown(
            'fields[{{fieldNumber}}][default_value]',
            $aFieldDefaultValues,
            null,
            'class="select2 field-default"'
        )?>
        <?=form_input(
            'fields[{{fieldNumber}}][default_value_custom]',
            null,
            'placeholder="The default value"'
        )?>
    </td>
    <td class="attributes">
        <?=form_input(
            'fields[{{fieldNumber}}][custom_attributes]',
            null,
            'placeholder="Any custom attributes"'
        )?>
    </td>
    <td class="remove">
        <a href="#" class="remove-field" data-field-number="{{fieldNumber}}">
            <b class="fa fa-times-circle fa-lg"></b>
        </a>
    </td>
</tr>
</script>
<script type="template/mustache" id="template-field-option-container">
<div id="survey-field-options-{{fieldNumber}}" class="survey-field-options">
    <table data-option-count="0">
        <thead>
            <tr>
                <th class="option-label">
                    Label
                </th>
                <th class="option-selected">
                    Selected
                </th>
                <th class="option-disabled">
                    Disabled
                </th>
                <th class="option-remove">
                    &nbsp;
                </th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
    <p>
        <button type="button" class="btn btn-xs btn-success add-option" data-field-number="{{fieldNumber}}">
            Add Option
        </button>
    </p>
</div>
</script>
<script type="template/mustache" id="template-field-option">
<tr>
    <td class="option-label">
        <?=form_input(
            'fields[{{fieldNumber}}][options][{{optionNumber}}][label]',
            null,
            'placeholder="Option Label"'
        )?>
    </td>
    <td class="option-selected">
        <?=form_checkbox(
            'fields[{{fieldNumber}}][options][{{optionNumber}}][is_selected]',
            true
        )?>
    </td>
    <td class="option-disabled">
        <?=form_checkbox(
            'fields[{{fieldNumber}}][options][{{optionNumber}}][is_disabled]',
            true
        )?>
    </td>
    <td class="option-remove">
        <a href="#" class="remove-option">
            <b class="fa fa-times-circle fa-lg"></b>
        </a>
    </td>
</tr>
</script>
