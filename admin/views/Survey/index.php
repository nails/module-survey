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
                    <th class="id text-center">ID</th>
                    <th class="label">Label</th>
                    <th class="active boolean">Active</th>
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
                            <td class="id text-center">
                                <?=number_format($survey->id)?>
                            </td>
                            <td class="label">
                                <?=$survey->label?>
                            </td>
                            <?=adminHelper('loadBoolCell', $survey->is_active)?>
                            <?=adminHelper('loadDatetimeCell', $survey->modified)?>
                            <?=adminHelper('loadUserCell', $survey->modified_by)?>
                            <td class="actions">
                                <?php

                                echo anchor($survey->url, 'View', 'class="btn btn-xs btn-default" target="_blank"');

                                if (userHasPermission('admin:survey:survey:edit')) {
                                    if ($survey->responses->count === 0) {
                                        echo anchor('admin/survey/survey/edit/' . $survey->id, 'Edit', 'class="btn btn-xs btn-primary"');
                                    }
                                }

                                if (userHasPermission('admin:survey:survey:copy')) {
                                    echo anchor('admin/survey/survey/copy/' . $survey->id, 'Copy', 'class="btn btn-xs btn-primary"');
                                }

                                if (userHasPermission('admin:survey:survey:response')) {
                                    if ($survey->responses->count > 0) {

                                        if ($survey->responses->count != $survey->responses->count_submitted) {
                                            $sCount = $survey->responses->count_submitted . '/' . $survey->responses->count;
                                        } else {
                                            $sCount = $survey->responses->count;
                                        }

                                        echo anchor(
                                            'admin/survey/survey/response/' . $survey->id,
                                            'Responses &ndash; ' . $sCount,
                                            'class="btn btn-xs btn-warning"'
                                        );
                                    }
                                }

                                if (userHasPermission('admin:survey:survey:delete')) {
                                    if ($survey->responses->count === 0) {
                                        echo anchor(
                                            'admin/survey/survey/delete/' . $survey->id,
                                            'Delete',
                                            'class="btn btn-xs btn-danger confirm" data-body="This action is also not undoable." data-title="Confirm Delete"'
                                        );
                                    }
                                }

                                ?>
                            </td>
                        </tr>
                        <?php
                    }

                } else {
                    ?>
                    <tr>
                        <td colspan="6" class="no-data">
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
