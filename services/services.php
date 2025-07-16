<?php

use Nails\Survey\Factory;
use Nails\Survey\Model;
use Nails\Survey\Resource;

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
        'ResponseAnswer' => function (): Model\Response\Answer {
            if (class_exists('\App\Survey\Model\Response\Answer')) {
                return new \App\Survey\Model\Response\Answer();
            } else {
                return new Model\Response\Answer();
            }
        },
    ],
    'resources' => [
        'Survey'              => function ($resource, $model): Resource\Survey {
            if (class_exists('\App\Common\Resource\Survey')) {
                return new \App\Common\Resource\Survey($resource, $model);
            } else {
                return new Resource\Survey($resource, $model);
            }
        },
        'SurveyCta'           => function ($resource, $model = null): Resource\Survey\Cta {
            //  @todo (Pablo 2025-07-15) - this should be a factory
            if (class_exists('\App\Common\Resource\Survey\Cta')) {
                return new \App\Common\Resource\Survey\Cta($resource);
            } else {
                return new Resource\Survey\Cta($resource);
            }
        },
        'SurveyThankYouEmail' => function ($resource, $model = null): Resource\Survey\ThankYou\Email {
            //  @todo (Pablo 2025-07-15) - this should be a factory
            if (class_exists('\App\Common\Resource\Survey\ThankYou\Email')) {
                return new \App\Common\Resource\Survey\ThankYou\Email($resource);
            } else {
                return new Resource\Survey\ThankYou\Email($resource);
            }
        },
        'SurveyThankYouPage'  => function ($resource, $model = null): Resource\Survey\ThankYou\Page {
            //  @todo (Pablo 2025-07-15) - this should be a factory
            if (class_exists('\App\Common\Resource\Survey\ThankYou\Page')) {
                return new \App\Common\Resource\Survey\ThankYou\Page($resource);
            } else {
                return new Resource\Survey\ThankYou\Page($resource);
            }
        },
        'Response'            => function ($resource, $model): Resource\Response {
            if (class_exists('\App\Common\Resource\Response')) {
                return new \App\Common\Resource\Response($resource, $model);
            } else {
                return new Resource\Response($resource, $model);
            }
        },
        'ResponseAnswer'      => function ($resource, $model): Resource\Response\Answer {
            if (class_exists('\App\Common\Resource\Response\Answer')) {
                return new \App\Common\Resource\Response\Answer($resource, $model);
            } else {
                return new Resource\Response\Answer($resource, $model);
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
        'EmailSave'         => function (): Factory\Email\Save {
            if (class_exists('\App\Survey\Factory\Email\Save')) {
                return new \App\Survey\Factory\Email\Save();
            } else {
                return new Factory\Email\Save();
            }
        },
    ],
];
