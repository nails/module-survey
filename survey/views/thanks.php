<div class="nails-survey thanks">
    <div class="container">
        <h1 class="text-center">
            <?=$oSurvey->label?>
        </h1>
        <h2 class="text-center">
            <?=$oSurvey->thankyou_page->title ?: 'Thank You'?>
        </h2>
        <p class="text-center">
            <?=$oSurvey->thankyou_page->body ?: 'Your responses have been recorded.'?>
        </p>
    </div>
</div>