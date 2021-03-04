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
        'Survey'              => function ($oObj): Resource\Survey {
            if (class_exists('\App\Common\Resource\Survey')) {
                return new \App\Common\Resource\Survey($oObj);
            } else {
                return new Resource\Survey($oObj);
            }
        },
        'SurveyCta'           => function ($oObj): Resource\Survey\Cta {
            if (class_exists('\App\Common\Resource\Survey\Cta')) {
                return new \App\Common\Resource\Survey\Cta($oObj);
            } else {
                return new Resource\Survey\Cta($oObj);
            }
        },
        'SurveyThankYouEmail' => function ($oObj): Resource\Survey\ThankYou\Email {
            if (class_exists('\App\Common\Resource\Survey\ThankYou\Email')) {
                return new \App\Common\Resource\Survey\ThankYou\Email($oObj);
            } else {
                return new Resource\Survey\ThankYou\Email($oObj);
            }
        },
        'SurveyThankYouPage'  => function ($oObj): Resource\Survey\ThankYou\Page {
            if (class_exists('\App\Common\Resource\Survey\ThankYou\Page')) {
                return new \App\Common\Resource\Survey\ThankYou\Page($oObj);
            } else {
                return new Resource\Survey\ThankYou\Page($oObj);
            }
        },
        'Response'            => function ($oObj): Resource\Response {
            if (class_exists('\App\Common\Resource\Response')) {
                return new \App\Common\Resource\Response($oObj);
            } else {
                return new Resource\Response($oObj);
            }
        },
        'ResponseAnswer'      => function ($oObj): Resource\Response\Answer {
            if (class_exists('\App\Common\Resource\Response\Answer')) {
                return new \App\Common\Resource\Response\Answer($oObj);
            } else {
                return new Resource\Response\Answer($oObj);
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
