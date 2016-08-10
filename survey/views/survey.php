<div class="nails-survey survey">
    <?php

    if (!empty($is_admin_preview)) {
        ?>
        <input type="checkbox" id="admin-preview-modal-checkbox">
        <div class="admin-preview-modal">
            <div class="admin-preview-modal__content">
                <p>
                    This survey does not allow anonymous submissions, this would normally prevent this page from rendering;
                    however, you have permission to manage surveys so this page is rendering as a preview.
                </p>
                <p class="text-center">
                    <label class="btn btn-primary" for="admin-preview-modal-checkbox">
                        Close
                    </label>
                </p>
            </div>
        </div>
        <?php
    }

    if (!empty($oSurvey->header)) {
        echo cmsAreaWithData($oSurvey->header);
    }

    $aFormConfig = array(
        'form_attr'     => $oSurvey->form_attributes,
        'has_captcha'   => $oSurvey->form->has_captcha,
        'captcha_error' => !empty($captchaError) ? $captchaError : null,
        'fields'        => $oSurvey->form->fields->data,
        'buttons'       => array(
            array(
                'label' => $oSurvey->cta->label,
                'attr'  => $oSurvey->cta->attributes
            )
        )
    );

    echo formBuilderRender($aFormConfig);

    if (!empty($oSurvey->footer)) {
        echo cmsAreaWithData($oSurvey->footer);
    }

    ?>
</div>
