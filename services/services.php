<?php

return array(
    'models' => array(
        'Survey' => function () {
            if (class_exists('\App\Survey\Model\Survey')) {
                return new \App\Survey\Model\Survey();
            } else {
                return new \Nails\Survey\Model\Survey();
            }
        },
        'Response' => function () {
            if (class_exists('\App\Survey\Model\Response')) {
                return new \App\Survey\Model\Response();
            } else {
                return new \Nails\Survey\Model\Response();
            }
        }
    )
);
