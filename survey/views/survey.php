<?php

/**
 * @var \Nails\Survey\Resource\Survey   $oSurvey
 * @var \Nails\Survey\Resource\Response $oResponse
 * @var bool                            $bIsCaptchaEnabled
 * @var bool                            $bIsAdminPreviewInactive
 * @var bool                            $bIsAdminPreviewAnon
 * @var string                          $sSaveEmailWarning
 */

?>
<div class="nails-survey survey">
    <?php

    if ($sSaveEmailWarning) {
        ?>
        <input type="checkbox" class="survey-modal__close" id="save-warning-modal-checkbox">
        <div class="survey-modal">
            <div class="survey-modal__content">
                <p>
                    Your response was saved, however we were unable to send you an email
                    with your link, which is shown below:
                </p>
                <p class="alert alert-info">
                    <?=$oResponse->url?>
                </p>
                <p>
                    Revisit this page any time to submit your response.
                </p>
                <p class="text-center">
                    <label class="btn btn-primary" for="save-warning-modal-checkbox">
                        Close
                    </label>
                </p>
            </div>
        </div>
        <?php
    } elseif (!empty($bIsAdminPreviewInactive) || !empty($bIsAdminPreviewAnon)) {
        ?>
        <input type="checkbox" class="survey-modal__close" id="admin-warning-modal-checkbox">
        <div class="survey-modal">
            <div class="survey-modal__content">
                <p>
                    <?php
                    if (!empty($bIsAdminPreviewInactive)) {
                        ?>
                        This survey is not currently active; however, you have permission to manage surveys so this
                        page is rendering as a preview.
                        <?php
                    } elseif (!empty($bIsAdminPreviewAnon)) {
                        ?>
                        This survey does not allow anonymous submissions, this would normally prevent this page from
                        rendering; however, you have permission to manage surveys so this page is rendering as a
                        preview.
                        <?php
                    }
                    ?>
                </p>
                <p class="text-center">
                    <label class="btn btn-primary" for="admin-warning-modal-checkbox">
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
        <?php

        $sSaveEmail = $oResponse->email ?? activeUser('email');

        echo formBuilderRender([
            'form_attr'     => $oSurvey->form_attributes,
            'form_header'   => $oSurvey->allow_save ? <<<EOT
                <input type="checkbox" class="survey-modal__close" id="survey-modal-save" checked>
                <div class="survey-modal">
                    <div class="survey-modal__content">
                        <p>
                            Provide your email so we can send you a special link which you can use to
                            come back and complete your response later.
                        </p>
                        <p>
                            <input type="email" name="email" value="$sSaveEmail" placeholder="Your email address">
                        </p>
                        <p class="text-center">
                            <button class="btn btn-primary" name="action" value="save" formnovalidate>
                                Save
                            </button>
                            <label class="btn btn-secondary" for="survey-modal-save">
                                Cancel
                            </label>
                        </p>
                    </div>
                </div>
            EOT: null,
            'has_captcha'   => $bIsCaptchaEnabled,
            'captcha_error' => !empty($captchaError) ? $captchaError : null,
            'fields'        => $oSurvey->form->fields->data,
            'buttons'       => array_filter([
                $oSurvey->allow_save ? [
                    'tag'   => 'label',
                    'label' => 'Save &amp; Continue Later',
                    'class' => 'btn btn-secondary',
                    'attr'  => 'for="survey-modal-save" id="nails-survey-button-save"',
                ] : null,
                [
                    'label' => $oSurvey->cta->label ?: 'Submit Response',
                    'name'  => 'action',
                    'value' => 'submit',
                    'attr'  => $oSurvey->cta->attributes . ' id="nails-survey-button-submit" formnovalidate',
                ],
            ]),
        ]);

        ?>
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
