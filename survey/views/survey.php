<div class="nails-survey survey">
    <?php

    if (!empty($oSurvey->header)) {
        echo cmsAreaWithData($oSurvey->header);
    }

    $aFormConfig = array(
        'form_attr'     => $oSurvey->form_attributes,
        'has_captcha'   => $oSurvey->has_captcha,
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