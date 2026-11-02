<?php

namespace Tests\FindReplace\Resolver;

use Nails\FindReplace\Factory\Matched;
use Nails\Survey\FindReplace\Resolver\Survey;
use PHPUnit\Framework\TestCase;

class SurveyTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        if (!defined('NAILS_DB_PREFIX')) {
            define('NAILS_DB_PREFIX', 'nails_');
        }
    }

    public function testTables(): void
    {
        self::assertSame([NAILS_DB_PREFIX . 'survey_survey'], (new Survey())->tables());
    }

    public function testResolvesByLabel(): void
    {
        $table = NAILS_DB_PREFIX . 'survey_survey';
        $resolver = $this->resolver([
            (object) ['id' => 4, 'label' => 'Customer feedback'],
        ]);

        $hits = $resolver->resolve($table, [
            new Matched($table, 'header', 'html', 'example', '4', 'example'),
            new Matched($table, 'footer', 'html', 'example', '4', 'example'),
        ]);

        self::assertCount(1, $hits);
        self::assertSame('Survey', $hits['4']->getEntityLabel());
        self::assertSame('4', $hits['4']->getEntityId());
        self::assertSame('Customer feedback', $hits['4']->getTitle());
        self::assertSame('https://example.com/admin/survey/survey/edit/4', $hits['4']->getActions()[0]->getUrl());
    }

    public function testNoMatchesResolvesNothing(): void
    {
        self::assertSame([], $this->resolver([])->resolve(NAILS_DB_PREFIX . 'survey_survey', []));
    }

    /**
     * @param list<\stdClass> $rows
     */
    private function resolver(array $rows): Survey
    {
        return new class ($rows) extends Survey {
            /**
             * @param list<\stdClass> $stub
             */
            public function __construct(private readonly array $stub)
            {
            }

            protected function rows(string $table, string $columns, array $ids): array
            {
                return $this->stub;
            }

            protected function adminUrl(): string
            {
                return 'https://example.com/admin/survey';
            }
        };
    }
}
