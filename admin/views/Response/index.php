<?php

/**
 * @var \Nails\Survey\Resource\Survey $oSurvey
 */

?>
<div class="group-survey responses" data-survey-id="<?=$oSurvey->id?>" data-survey-token="<?=$oSurvey->token?>">
    <?php

    if ($oSurvey->allow_public_stats) {

        ?>
        <div class="alert alert-info">
            <?=anchor($oSurvey->url_stats, 'View Public Stats', 'class="btn btn-primary btn-xs" target="_blank"')?>
            <button class="btn btn-xs btn-default js-copy-to-clipboard" data-clipboard-text="<?=$oSurvey->url_stats?>">
                <b class="fa fa-clipboard"></b>
                Copy Link
            </button>
            &nbsp;&mdash;
            Public stats are enabled for this survey; anybody with the link can view.
        </div>
        <?php
    }

    if ($oSurvey->responses->count) {

        ?>
        <div class="row">
            <div class="js-stats stats">
                <div>
                    <h2>
                        Aggregated Stats
                        <button class="btn btn-xs btn-warning pull-right js-show-respondents">
                            Show Respondents
                        </button>
                    </h2>
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
                                        <div class="js-chart-type chart-type hidden text-left">
                                            &mdash;
                                            <select class="select2"></select>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-sm-pull-6">
                                        <div class="js-loading loading-pulse hidden clearfix">
                                            <small>
                                                Loading data...
                                            </small>
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

                    ?>
                </div>
            </div>
            <div class="js-respondents respondents">
                <div>
                    <h2>
                        Respondents
                        <button class="btn btn-xs btn-warning pull-right js-hide-respondents">
                            Hide
                        </button>
                    </h2>
                    <table>
                        <thead>
                            <tr>
                                <th colspan="2">Respondent</th>
                                <th class="datetime">Submitted</th>
                                <th class="actions">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php

                            foreach ($oSurvey->responses->data as $oResponse) {

                                if (empty($oResponse->date_submitted)) {

                                    $sDisabled = 'disabled';
                                    $sChecked  = '';

                                } else {

                                    $sDisabled = '';
                                    $sChecked  = 'checked';
                                }

                                ?>
                                <tr class="js-response <?=$sDisabled?>">
                                    <td class="include-response">
                                        <input type="checkbox" <?=$sDisabled?> <?=$sChecked?> class="response" value="<?=$oResponse->id?>">
                                    </td>
                                    <td>
                                        <?php

                                        if (!empty($oResponse->user)) {

                                            echo $oResponse->user->first_name . ' ' . $oResponse->user->last_name;
                                            if (!empty($oResponse->user->email)) {

                                                echo '<small>';
                                                echo mailto($oResponse->user->email);
                                                echo '</small>';
                                            }

                                        } elseif (!empty($oResponse->name)) {

                                            echo $oResponse->name;
                                            if (!empty($oResponse->email)) {

                                                echo '<small>';
                                                echo mailto($oResponse->email);
                                                echo '</small>';
                                            }

                                        } else {

                                            echo 'Anonymous';
                                        }

                                        ?>
                                    </td>
                                    <?=adminHelper('loadDateTimeCell', $oResponse->date_submitted, 'Not yet submitted')?>
                                    <td class="actions">
                                        <?php

                                        if ($oResponse->status != 'SUBMITTED') {

                                            echo anchor(
                                                $oResponse->url,
                                                'View',
                                                'class="btn btn-xs btn-default" target="_blank"'
                                            );

                                            ?>
                                            <button class="btn btn-xs btn-default js-copy-to-clipboard" data-clipboard-text="<?=$oResponse->url?>">
                                                <b class="fa fa-clipboard"></b>
                                                Copy Link
                                            </button>
                                            <?php

                                        } else {

                                            echo anchor(
                                                'admin/survey/response/' . $oSurvey->id . '/view/' . $oResponse->id,
                                                'Answers',
                                                'class="btn btn-xs btn-primary fancybox" data-width="100%"'
                                            );
                                        }

                                        ?>
                                    </td>
                                </tr>
                                <?php
                            }

                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php
    } else {
        ?>
        <p class="alert alert-warning">
            There have been no responses to this survey yet.
        </p>
        <?php
    }

    ?>
</div>
