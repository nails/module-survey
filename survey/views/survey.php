<div class="nails-survey survey">
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <h1 class="text-center">
                    <?=$oSurvey->label?>
                </h1>
                <?php

                if (!empty($oSurvey->header)) {
                    echo cmsAreaWithData($oSurvey->header);
                }

                $aFormConfig = array(
                    'form_attr'   => $oSurvey->form_attributes,
                    'fields'      => $oSurvey->form->fields->data,
                    'buttons'     => array(
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
        </div>
    </div>
</div>