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
        'SurveyQuestion' => function () {
            if (class_exists('\App\Survey\Model\SurveyQuestion')) {
                return new \App\Survey\Model\SurveyQuestion();
            } else {
                return new \Nails\Survey\Model\SurveyQuestion();
            }
        },
        'SurveyQuestionOption' => function () {
            if (class_exists('\App\Survey\Model\SurveyQuestionOption')) {
                return new \App\Survey\Model\SurveyQuestionOption();
            } else {
                return new \Nails\Survey\Model\SurveyQuestionOption();
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
