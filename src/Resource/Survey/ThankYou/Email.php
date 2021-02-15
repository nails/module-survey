<?php

namespace Nails\Survey\Resource\Survey\ThankYou;

use Nails\Common\Resource;

/**
 * Class Email
 *
 * @package Nails\Survey\Resource\Survey\ThankYou
 */
class Email extends Resource
{
    /** @var bool */
    public $send;

    /** @var string */
    public $subject;

    /** @var string */
    public $body;
}
