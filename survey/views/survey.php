<div class="nails-survey survey">
    <div class="container">
        <h1 class="text-center">
            <?=$oSurvey->label?>
        </h1>
        <?=form_open()?>
        <?php

        $iCounter = 0;

        foreach ($oSurvey->questions->data as $oQuestion) {

            ?>
            <div class="panel panel-default question">
                <div class="panel-heading">
                    Q<?=$iCounter+1?> - <?=$oQuestion->label?>
                </div>
                <div class="panel-body">
                <?php

                foreach ($oQuestion->options->data as $oOption) {

                    ?>
                    <div class="question__option">
                        <label>
                            <input type="radio" name="answer[<?=$oQuestion->id?>]" value="<?=$oOption->id?>">
                            <?=$oOption->label?>
                        </label>
                    </div>
                    <?php
                }

                ?>
                </div>
            </div>
            <?php

            $iCounter++;
        }

        ?>
        <p class="text-center">
            <button type="submit" name="foo" value="bar" class="btn btn-primary">
                Submit
            </button>
        </p>
        <?=form_close()?>
    </div>
</div>