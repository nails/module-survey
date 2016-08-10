<div class="nails-survey stats">
    <?php

    if (!empty($oSurvey->stats_header)) {
        echo cmsAreaWithData($oSurvey->stats_header);
    }

    // --------------------------------------------------------------------------

    ?>
    <div class="js-stats stats">
        <?php

        $i = 0;

        foreach ($oSurvey->form->fields->data as $oField) {

            $i++;

            ?>
            <div class="panel panel-default js-field" data-id="<?=$oField->id?>">
                <div class="panel-heading">
                    Q<?=$i?> &ndash; <strong><?=$oField->label?></strong>
                </div>
                <div class="panel-body">
                    <div class="row panel-sub-heading">
                        <div class="col-sm-6 col-sm-push-6 text-right">
                            <span class="js-response-count">0</span> Responses
                        </div>
                        <div class="col-sm-6 col-sm-pull-6">
                            <div class="js-loading loading-pulse hidden clearfix">
                                <small>
                                    Loading data...
                                </small>
                            </div>
                            <div class="js-chart-type chart-type hidden text-left">
                                <select class="select2"></select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-12">
                            <div class="js-error alert alert-danger hidden clearfix">
                            </div>
                            <div class="js-targets hidden clearfix">
                                <div class="js-chart-target target-chart"></div>
                                <ul class="js-text-target target-text"></ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php

        }

        echo '<ul class="hidden">';
        foreach ($oSurvey->responses->data as $oResponse) {

            if (empty($oResponse->date_submitted)) {

                $sDisabled = 'disabled';
                $sChecked = '';

            } else {

                $sDisabled = '';
                $sChecked = 'checked';
            }

            echo '<li class="js-response">';
            echo '<input type="checkbox" ' . $sDisabled . ' ' . $sChecked . ' class="response" value="' . $oResponse->id . '">';
            echo '</li>';
        }
        echo '</ul>';

        ?>
    </div>
    <?php

    // --------------------------------------------------------------------------

    if (!empty($oSurvey->stats_footer)) {
        echo cmsAreaWithData($oSurvey->stats_footer);
    }

    ?>
</div>
