<div class="nails-survey thanks">
    <?php

    if (!empty($oSurvey->thankyou_page->body)) {

        echo cmsAreaWithData($oSurvey->thankyou_page->body);

    } else {

        ?>
        <p class="text-center">
            Thank you, your responses have been recorded.
        </p>
        <?php
    }

    ?>
</div>
