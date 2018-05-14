<div class="nails-survey thanks">
    <div class="survey__thanks">
        <?php

        if (!empty($oSurvey->thankyou_page->body)) {
            echo cmsAreaWithData($oSurvey->thankyou_page->body);
        } else {
            echo 'Thank you, your responses have been recorded.';
        }

        ?>
    </div>
</div>
