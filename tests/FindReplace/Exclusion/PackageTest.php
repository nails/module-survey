<?php

namespace Tests\FindReplace\Exclusion;

use Nails\Survey\FindReplace\Exclusion\Package;
use PHPUnit\Framework\TestCase;

class PackageTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        if (!defined('NAILS_DB_PREFIX')) {
            define('NAILS_DB_PREFIX', 'nails_');
        }
    }

    public function testHidden(): void
    {
        self::assertSame(
            [
                NAILS_DB_PREFIX . 'survey_survey.token_stats',
            ],
            (new Package())->hidden()
        );
    }

    public function testReadOnly(): void
    {
        self::assertSame(
            [
                NAILS_DB_PREFIX . 'survey_survey.form_attributes',
                NAILS_DB_PREFIX . 'survey_survey.cta_attributes',
                NAILS_DB_PREFIX . 'survey_survey.notification_email',
                NAILS_DB_PREFIX . 'survey_response.email',
            ],
            (new Package())->readOnly()
        );
    }
}
