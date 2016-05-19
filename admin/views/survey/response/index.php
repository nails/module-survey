<div class="group-survey responses">
    <div class="row">
    <div class="js-stats stats">
        <h2>
            Aggregated Stats
            <button class="btn btn-xs btn-warning pull-right js-show-respondees">
                Show Respondees
            </button>
        </h2>
        <?php

        $i = 0;

        foreach ($survey->form->fields->data as $oField) {

            $i++;

            ?>
            <div class="panel panel-default js-field" data-id="<?=$oField->id?>">
                <div class="panel-heading">
                    Q<?=$i?> &ndash; <strong><?=$oField->label?></strong>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-sm-6 col-sm-push-6 text-right">
                            <span class="js-response-count">0</span> Responses
                            <div class="js-chart-type  text-left">
                                &mdash;
                                <select class="select2"></select>
                            </div>
                        </div>
                        <div class="col-sm-6 col-sm-pull-6">
                            <div class="js-loading loading-pulse clearfix">
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
    <div class="js-respondees respondees">
        <h2>
            Respondees
            <button class="btn btn-xs btn-warning pull-right js-hide-respondees">
                Hide
            </button>
        </h2>
        <table>
            <thead>
                <tr>
                    <th colspan="2">Respondee</th>
                    <th class="datetime">Submitted</th>
                    <th class="actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php

                if ($survey->responses->count) {

                    foreach ($survey->responses->data as $oResponse) {

                        ?>
                        <tr class="js-response">
                            <td class="include-response">
                                <input type="checkbox" checked="checked" class="response" value="<?=$oResponse->id?>">
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
                                        'Link',
                                        'class="btn btn-xs btn-default" target="_blank"'
                                    );

                                } else {

                                    echo anchor(
                                        'admin/survey/survey/response/' . $survey->id . '/view/' . $oResponse->id,
                                        'View Answers',
                                        'class="btn btn-xs btn-primary fancybox"'
                                    );
                                }

                                ?>
                            </td>
                        </tr>
                        <?php
                    }

                } else {

                    ?>
                    <tr>
                        <td colspan="3" class="no-data">
                            No Responses
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