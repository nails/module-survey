<?php

namespace Nails\Survey\Resource;

use Nails\Auth\Resource\User;
use Nails\Common\Exception\FactoryException;
use Nails\Common\Resource\DateTime;
use Nails\Common\Resource\Entity;
use Nails\Common\Resource\ExpandableField;

/**
 * Class Response
 *
 * @package Nails\Survey\Resource
 */
class Response extends Entity
{
    /** @var string */
    public $token;

    /** @var int */
    public $survey_id;

    /** @var Survey */
    public $survey;

    /** @var string */
    public $status;

    /** @var int */
    public $user_id;

    /** @var User */
    public $user;

    /** @var string */
    public $name;

    /** @var string */
    public $email;

    /** @var string */
    public $url;

    /** @var bool */
    public $is_deleted;

    /** @var DateTime */
    public $date_submitted;

    /** @var ExpandableField */
    public $answers;

    // --------------------------------------------------------------------------

    /**
     * Response constructor.
     *
     * @param array $mObj
     */
    public function __construct($mObj = [])
    {
        $mObj->url = siteUrl('survey/response/' . $mObj->id . '/' . $mObj->token);
        parent::__construct($mObj);
    }
}
