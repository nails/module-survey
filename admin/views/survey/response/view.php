<div class="table-responsive">
    <table>
        <thead>
            <th>
                Question
            </th>
            <th>
                Option
            </th>
            <th>
                Response
            </th>
        </thead>
        <tbody>
            <?php

            foreach ($response->answers->data as $oAnswer) {

                ?>
                <tr>
                    <td><?=!empty($oAnswer->question) ? $oAnswer->question->label : ''?></td>
                    <td><?=!empty($oAnswer->option) ? $oAnswer->option->label : ''?></td>
                    <td><?=$oAnswer->text?></td>
                </tr>
                <?php
            }

            ?>
        </tbody>
    </table>
</div>