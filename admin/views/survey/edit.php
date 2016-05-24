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
                'key'      => 'is_active',
                'label'    => 'Active',
                'default'  => !empty($survey->is_active),
                'text_on'  => 'Yes',
                'text_off' => 'No'
            );
            echo form_field_boolean($aField);

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
                'key'        => 'has_captcha',
                'label'      => 'Captcha',
                'default'    => !empty($survey->form->has_captcha),
                'info'       => $bIsCaptchaEnabled ? '' : 'Captcha Module has not been configured; field will be silently ignored until Captcha is configured.',
                'info_class' => $bIsCaptchaEnabled ? '' : 'alert alert-warning'
            );
            echo form_field_boolean($aField);

            // --------------------------------------------------------------------------

            $aField = array(
                'key'     => 'is_minimal',
                'label'   => 'Minimal Layout',
                'default' => !empty($survey->is_minimal),
                'info'    => 'When minimal, the form will not feature the site\'s header and footer.',
            );
            echo form_field_boolean($aField);

            ?>
            </div>
        </div>
        <div class="tab-page fields">
            <?php

            if (form_error('fields')) {

                ?>
                <div class="alert alert-danger">
                    <?=form_error('fields')?>
                </div>
                <?php
            }

            $aFields = !empty($survey->form->fields->data) ? $survey->form->fields->data : array();
            echo adminLoadFormBuilderView('survey-fields', 'fields', $aFields);

            ?>
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
                    'placeholder' => 'Define any custom attributes which should be attached to the button.',
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
    <p>
        <button type="submit" class="btn btn-primary">
            Save Changes
        </button>
    </p>
    <?=form_close()?>
</div>
