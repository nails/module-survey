<?php

namespace Nails\Survey\Factory\Email;

use Nails\Email\Factory\Email;
use Nails\Factory;

/**
 * Class Notification
 *
 * @package Nails\Survey\Factory\Email
 */
class Notification extends Email
{
    /**
     * The email's type
     *
     * @var string
     */
    protected $sType = 'survey_notification';

    // --------------------------------------------------------------------------

    /**
     * Returns test data to use when sending test emails
     *
     * @return array
     */
    public function getTestData(): array
    {
        return [
            'survey'    => [
                'id'    => 123,
                'label' => 'The survey\'s label',
            ],
            'responses' => [
                [
                    'q' => 'The question',
                    'a' => 'The answer'
                ]
            ],
        ];
    }
}
