<div class="nails-survey survey">
    <?php

    if (!empty($is_admin_preview)) {
        ?>
        <input type="checkbox" id="admin-preview-modal-checkbox">
        <div class="admin-preview-modal">
            <div class="admin-preview-modal__content">
                <p>
                    This survey does not allow anonymous submissions, this would normally
                    prevent this page from rendering; however, you have permission to manage
                    surveys so this page is rendering as a preview.
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
        ?>
        <div class="survey__header">
            <?=cmsAreaWithData($oSurvey->header)?>
        </div>
        <?php
    }

    ?>
    <div class="survey__body">
        <?=formBuilderRender([
            'form_attr'     => $oSurvey->form_attributes,
            'has_captcha'   => $oSurvey->form->has_captcha,
            'captcha_error' => !empty($captchaError) ? $captchaError : null,
            'fields'        => $oSurvey->form->fields->data,
            'buttons'       => [
                [
                    'label' => $oSurvey->cta->label,
                    'attr'  => $oSurvey->cta->attributes,
                ],
            ],
        ])?>
    </div>
    <?php

    if (!empty($oSurvey->footer)) {
        ?>
        <div class="survey__footer">
            <?=cmsAreaWithData($oSurvey->footer)?>
        </div>
        <?php
    }

    ?>
</div>
