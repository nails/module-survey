<?php

namespace Nails\Survey\Resource;

use Nails\Auth\Resource\User;
use Nails\Common\Exception\FactoryException;
use Nails\Common\Model\Base;
use Nails\Common\Resource\DateTime;
use Nails\Common\Resource\Entity;
use Nails\Common\Resource\ExpandableField;
use stdClass;

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
     */
    public function __construct(self|stdClass|array $resource = [], ?Base $model = null)
    {
        parent::__construct($resource, $model);
        $this->url = siteUrl('survey/response/' . $this->id . '/' . $this->token);
    }
}
