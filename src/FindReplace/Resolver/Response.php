<?php

namespace Nails\Survey\FindReplace\Resolver;

use Nails\Common\Service\Database;
use Nails\Factory;
use Nails\FindReplace\Factory\Action;
use Nails\FindReplace\Factory\Hit;
use Nails\FindReplace\Factory\Matched;
use Nails\FindReplace\Interfaces\Resolver;

/**
 * Responses and their answers are both titled by the survey they belong to.
 */
class Response implements Resolver
{
    /**
     * The engine passes every match in a table at once; a long IN () list
     * overflows CodeIgniter's where_in parser, so IDs are queried in chunks.
     */
    const IDS_PER_QUERY = 500;

    public function tables(): array
    {
        return [
            NAILS_DB_PREFIX . 'survey_response',
            NAILS_DB_PREFIX . 'survey_response_answer',
        ];
    }

    public function resolve(string $table, array $matches): array
    {
        $ids = array_values(array_unique(array_map(fn(Matched $match): string => $match->getId(), $matches)));

        if ($ids === []) {
            return [];
        }

        return $table === NAILS_DB_PREFIX . 'survey_response_answer'
            ? $this->answers($table, $ids)
            : $this->responses($table, $ids);
    }

    /**
     * @param list<string> $ids
     *
     * @return array<string, Hit>
     */
    private function responses(string $table, array $ids): array
    {
        $responses = $this->rows($table, 'id, survey_id, name', $ids);
        $surveys = $this->surveys($responses);
        $adminUrl = $this->adminUrl();
        $hits = [];

        foreach ($responses as $response) {
            $id = (string) $response->id;
            $surveyId = (string) $response->survey_id;
            $title = array_filter([$surveys[$surveyId] ?? null, (string) $response->name]);

            $hits[$id] = new Hit(
                $table,
                $id,
                entityLabel: 'Survey Response',
                entityId: $id,
                title: implode(' › ', $title) ?: null,
                actions: [
                    new Action('View', $adminUrl . '/response/' . $surveyId . '/view/' . $id),
                ],
            );
        }

        return $hits;
    }

    /**
     * @param list<string> $ids
     *
     * @return array<string, Hit>
     */
    private function answers(string $table, array $ids): array
    {
        $answers = $this->rows($table, 'id, survey_response_id', $ids);
        $responseIds = array_values(array_unique(array_map(
            fn(\stdClass $answer): string => (string) $answer->survey_response_id,
            $answers
        )));

        $responses = [];

        if ($responseIds !== []) {
            foreach ($this->rows(NAILS_DB_PREFIX . 'survey_response', 'id, survey_id, name', $responseIds) as $response) {
                $responses[(string) $response->id] = $response;
            }
        }

        $surveys = $this->surveys(array_values($responses));
        $adminUrl = $this->adminUrl();
        $hits = [];

        foreach ($answers as $answer) {
            $id = (string) $answer->id;
            $responseId = (string) $answer->survey_response_id;
            $response = $responses[$responseId] ?? null;

            //  An answer is only reachable through its response's survey.
            if ($response === null) {
                continue;
            }

            $surveyId = (string) $response->survey_id;
            $title = array_filter([
                $surveys[$surveyId] ?? null,
                (string) $response->name ?: 'Response #' . $responseId,
            ]);

            $hits[$id] = new Hit(
                $table,
                $id,
                entityLabel: 'Survey Answer',
                entityId: $id,
                title: implode(' › ', $title),
                actions: [
                    new Action('Edit', $adminUrl . '/response/' . $surveyId . '/edit/' . $id),
                    new Action('View response', $adminUrl . '/response/' . $surveyId . '/view/' . $responseId, 'btn-default'),
                ],
            );
        }

        return $hits;
    }

    /**
     * Survey labels, keyed by survey ID.
     *
     * @param list<\stdClass> $responses
     *
     * @return array<string, string>
     */
    private function surveys(array $responses): array
    {
        $ids = array_values(array_unique(array_map(fn(\stdClass $response): string => (string) $response->survey_id, $responses)));
        $surveys = [];

        if ($ids === []) {
            return $surveys;
        }

        foreach ($this->rows(NAILS_DB_PREFIX . 'survey_survey', 'id, label', $ids) as $survey) {
            $surveys[(string) $survey->id] = (string) $survey->label;
        }

        return $surveys;
    }

    /**
     * @param list<string> $ids
     *
     * @return list<\stdClass>
     */
    protected function rows(string $table, string $columns, array $ids): array
    {
        /** @var Database $database */
        $database = Factory::service('Database');
        $rows = [];

        foreach (array_chunk($ids, static::IDS_PER_QUERY) as $chunk) {
            $database->select($columns);
            $database->where_in('id', $chunk);
            $rows = array_merge($rows, $database->get($table)->result());
        }

        return $rows;
    }

    protected function adminUrl(): string
    {
        return siteUrl('admin/survey');
    }
}
