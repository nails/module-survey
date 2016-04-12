<div class="survey responses">
    <table>
        <thead>
            <tr>
                <th>Respondee</th>
                <th>Status</th>
                <th class="datetime">Submitted</th>
                <th class="actions">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php

            if ($survey->responses->count) {

                foreach ($survey->responses->data as $oResponse) {

                    ?>
                    <tr>
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
                        <td><?=$oResponse->status?></td>
                        <?=adminHelper('loadDateTimeCell', $oResponse->date_submitted, 'Not yet submitted')?>
                        <td class="actions">

                        </td>
                    </tr>
                    <?php
                }

            } else {

                ?>
                <tr>
                    <td colspan="4" class="no-data">
                        No Responses
                    </td>
                </tr>
                <?php
            }

            ?>
        </tbody>
    </table>
</div>