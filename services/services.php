<?php

use Nails\Survey\Factory;
use Nails\Survey\Model;

return [
    'models'    => [
        'Survey'         => function (): Model\Survey {
            if (class_exists('\App\Survey\Model\Survey')) {
                return new \App\Survey\Model\Survey();
            } else {
                return new Model\Survey();
            }
        },
        'Response'       => function (): Model\Response {
            if (class_exists('\App\Survey\Model\Response')) {
                return new \App\Survey\Model\Response();
            } else {
                return new Model\Response();
            }
        },
        'ResponseAnswer' => function (): Model\ResponseAnswer {
            if (class_exists('\App\Survey\Model\ResponseAnswer')) {
                return new \App\Survey\Model\ResponseAnswer();
            } else {
                return new Model\ResponseAnswer();
            }
        },
    ],
    'factories' => [
        'EmailNotification' => function (): Factory\Email\Notification {
            if (class_exists('\App\Survey\Factory\Email\Notification')) {
                return new \App\Survey\Factory\Email\Notification();
            } else {
                return new Factory\Email\Notification();
            }
        },
    ],
];
