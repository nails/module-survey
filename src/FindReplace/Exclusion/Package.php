<?php

namespace Nails\Survey\FindReplace\Exclusion;

use Nails\FindReplace\Interfaces\Exclusion;

class Package implements Exclusion
{
    public function hidden(): array
    {
        return [
            NAILS_DB_PREFIX . 'survey_survey.token_stats',
        ];
    }

    public function readOnly(): array
    {
        return [
            NAILS_DB_PREFIX . 'survey_survey.form_attributes',
            NAILS_DB_PREFIX . 'survey_survey.cta_attributes',
            NAILS_DB_PREFIX . 'survey_survey.notification_email',
            NAILS_DB_PREFIX . 'survey_response.email',
        ];
    }
}
