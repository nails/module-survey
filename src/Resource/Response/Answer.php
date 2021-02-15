<?php

namespace Nails\Survey\Resource\Response;

use Nails\Common\Resource\Entity;
use Nails\Survey\Resource\Response;

/**
 * Class Answer
 *
 * @package Nails\Survey\Resource\Response
 */
class Answer extends Entity
{
    /** @var int */
    public $survey_response_id;

    /** @var Response */
    public $response;

    /** @var int */
    public $form_field_id;

    /** @var int */
    public $form_field_option_id;

    /** @var string */
    public $text;

    /** @var mixed */
    public $data;

    /** @var int */
    public $order;

    /** @var bool */
    public $is_deleted;

    /** @var Entity */
    public $question;

    /** @var Entity */
    public $answer;
}
