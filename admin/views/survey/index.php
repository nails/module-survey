<div class="group-survey browse">
    <p>
        Browse all custom surveys.
    </p>
    <?=adminHelper('loadSearch', $search);?>
    <?=adminHelper('loadPagination', $pagination);?>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th class="id">ID</th>
                    <th class="label">Label</th>
                    <th class="datetime">Modified</th>
                    <th class="user">Modified By</th>
                    <th class="actions">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php

            if ($surveys) {

                foreach ($surveys as $survey) {

                    ?>
                    <tr>
                        <td class="id">
                            <?=number_format($survey->id)?>
                        </td>
                        <td class="label">
                            <?=$survey->label?>
                        </td>
                        <?=adminHelper('loadDatetimeCell', $survey->modified)?>
                        <?=adminHelper('loadUserCell', $survey->modified_by)?>
                        <td class="actions">
                        <?php

                        echo anchor($survey->url, 'View', 'class="btn btn-xs btn-default" target="_blank"');

                        if (userHasPermission('admin:survey:survey:edit')) {

                            echo anchor('admin/survey/survey/edit/' . $survey->id, 'Edit', 'class="btn btn-xs btn-primary"');
                        }

                        if (userHasPermission('admin:survey:survey:responses')) {

                            echo anchor(
                                'admin/survey/survey/responses/' . $survey->id,
                                'View Responses (' . number_surveyat($survey->responses->count) . ')',
                                'class="btn btn-xs btn-warning"'
                            );
                        }

                        if (userHasPermission('admin:survey:survey:delete')) {

                            echo anchor(
                                'admin/survey/survey/delete/' . $survey->id,
                                'Delete',
                                'class="btn btn-xs btn-danger confirm" data-body="This action is also not undoable." data-title="Confirm Delete"'
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
                    <td colspan="5" class="no-data">
                        No Surveys Found
                    </td>
                </tr>
                <?php

            }

            ?>
            </tbody>
        </table>
    </div>
    <?=adminHelper('loadPagination', $pagination)?>
</div>
