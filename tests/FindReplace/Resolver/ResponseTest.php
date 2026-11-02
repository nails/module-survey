<?php

namespace Tests\FindReplace\Resolver;

use Nails\FindReplace\Factory\Matched;
use Nails\Survey\FindReplace\Resolver\Response;
use PHPUnit\Framework\TestCase;

class ResponseTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        if (!defined('NAILS_DB_PREFIX')) {
            define('NAILS_DB_PREFIX', 'nails_');
        }
    }

    public function testTables(): void
    {
        self::assertSame(
            [NAILS_DB_PREFIX . 'survey_response', NAILS_DB_PREFIX . 'survey_response_answer'],
            (new Response())->tables()
        );
    }

    public function testResolvesResponsesThroughTheirSurvey(): void
    {
        $table = NAILS_DB_PREFIX . 'survey_response';
        $hits = $this->resolver()->resolve($table, [
            $this->match($table, '20', 'name'),
            $this->match($table, '21', 'name'),
        ]);

        self::assertCount(2, $hits);
        self::assertSame('Survey Response', $hits['20']->getEntityLabel());
        self::assertSame('20', $hits['20']->getEntityId());
        self::assertSame('Customer feedback › Jane Smith', $hits['20']->getTitle());
        self::assertSame('View', $hits['20']->getActions()[0]->getLabel());
        self::assertSame('https://example.com/admin/survey/response/4/view/20', $hits['20']->getActions()[0]->getUrl());
        self::assertSame('Customer feedback', $hits['21']->getTitle());
    }

    public function testResolvesAnswersThroughTheirResponse(): void
    {
        $table = NAILS_DB_PREFIX . 'survey_response_answer';
        $hits = $this->resolver()->resolve($table, [
            $this->match($table, '300', 'text'),
            $this->match($table, '300', 'data'),
            $this->match($table, '301', 'text'),
            $this->match($table, '302', 'text'),
        ]);

        self::assertSame(['300', '301'], array_map('strval', array_keys($hits)));

        self::assertSame('Survey Answer', $hits['300']->getEntityLabel());
        self::assertSame('300', $hits['300']->getEntityId());
        self::assertSame('Customer feedback › Jane Smith', $hits['300']->getTitle());
        self::assertSame('Customer feedback › Response #21', $hits['301']->getTitle());

        $actions = $hits['300']->getActions();
        self::assertCount(2, $actions);
        self::assertSame('Edit', $actions[0]->getLabel());
        self::assertSame('https://example.com/admin/survey/response/4/edit/300', $actions[0]->getUrl());
        self::assertSame('View response', $actions[1]->getLabel());
        self::assertSame('https://example.com/admin/survey/response/4/view/20', $actions[1]->getUrl());
    }

    public function testNoMatchesResolvesNothing(): void
    {
        self::assertSame([], $this->resolver()->resolve(NAILS_DB_PREFIX . 'survey_response', []));
    }

    private function match(string $table, string $id, string $column): Matched
    {
        return new Matched($table, $column, 'plaintext', 'example', $id, 'example');
    }

    private function resolver(): Response
    {
        return new class ([
            NAILS_DB_PREFIX . 'survey_survey' => [
                (object) ['id' => 4, 'label' => 'Customer feedback'],
            ],
            NAILS_DB_PREFIX . 'survey_response' => [
                (object) ['id' => 20, 'survey_id' => 4, 'name' => 'Jane Smith'],
                (object) ['id' => 21, 'survey_id' => 4, 'name' => null],
            ],
            NAILS_DB_PREFIX . 'survey_response_answer' => [
                (object) ['id' => 300, 'survey_response_id' => 20],
                (object) ['id' => 301, 'survey_response_id' => 21],
                (object) ['id' => 302, 'survey_response_id' => 99],
            ],
        ]) extends Response {
            /**
             * @param array<string, list<\stdClass>> $stub Rows keyed by table.
             */
            public function __construct(private readonly array $stub)
            {
            }

            protected function rows(string $table, string $columns, array $ids): array
            {
                return array_values(array_filter(
                    $this->stub[$table] ?? [],
                    fn(\stdClass $row): bool => in_array((string) $row->id, $ids, true)
                ));
            }

            protected function adminUrl(): string
            {
                return 'https://example.com/admin/survey';
            }
        };
    }
}
