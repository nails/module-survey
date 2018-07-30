<div class="table-responsive">
    <table>
        <thead>
            <tr>
                <th>Question</th>
                <th>Option</th>
                <th>Response</th>
                <?php

                if (userHasPermission('admin:survey:survey:response:edit')) {
                    ?>
                    <th class="actions">
                        Actions
                    </th>
                    <?php
                }

                ?>
            </tr>
        </thead>
        <tbody>
            <?php

            foreach ($response->answers->data as $oAnswer) {

                ?>
                <tr>
                    <td><?=!empty($oAnswer->question) ? $oAnswer->question->label : ''?></td>
                    <td><?=!empty($oAnswer->option) ? $oAnswer->option->label : ''?></td>
                    <td><?=$oAnswer->text?></td>
                    <?php

                    if (userHasPermission('admin:survey:survey:response:edit')) {

                        $sIsModal = !empty($isModal) ? '?isModal=1' : '';

                        echo '<td class="actions">';
                        echo anchor(
                            'admin/survey/survey/response/' . $response->survey_id . '/edit/' . $oAnswer->id . $sIsModal,
                            lang('action_edit'),
                            'class="btn btn-xs btn-primary"'
                        );
                        echo '</td>';
                    }

                    ?>
                </tr>
                <?php
            }

            ?>
        </tbody>
    </table>
</div>
