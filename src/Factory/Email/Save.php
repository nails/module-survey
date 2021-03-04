<?php

namespace Nails\Survey\Factory\Email;

use Nails\Email\Factory\Email;
use Nails\Factory;

/**
 * Class Save
 *
 * @package Nails\Survey\Factory\Email
 */
class Save extends Email
{
    /**
     * The email's type
     *
     * @var string
     */
    protected $sType = 'survey_save';

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
            'response' => [
                'id'  => 123,
                'url' => 'https://example.com',
            ],
        ];
    }
}
