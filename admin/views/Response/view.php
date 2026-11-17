<?php

/**
 * @var \Nails\Survey\Resource\Response $oResponse
 */

?>
<div class="table-responsive">
    <table>
        <thead>
            <tr>
                <th>Question</th>
                <th>Option</th>
                <th>Response</th>
                <?php

                if (userHasPermission(\Nails\Survey\Admin\Permission\Response\View::class)) {
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

            foreach ($oResponse->answers->data as $oAnswer) {

                ?>
                <tr>
                    <td><?=$oAnswer->question->label ?? ''?></td>
                    <td><?=$oAnswer->option->label ?? ''?></td>
                    <td><?=$oAnswer->text?></td>
                    <?php

                    if (userHasPermission(\Nails\Survey\Admin\Permission\Response\View::class)) {

                        $sIsModal = !empty($isModal) ? '?isModal=1' : '';

                        echo '<td class="actions">';
                        echo anchor(
                            \Nails\Survey\Admin\Controller\Response::url($oResponse->survey_id . '/edit/' . $oAnswer->id . $sIsModal),
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
