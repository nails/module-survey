<?php

namespace Nails\Survey\Resource;

use Nails\Common\Exception\FactoryException;
use Nails\Common\Model\Base;
use Nails\Common\Resource\Entity;
use Nails\Common\Resource\ExpandableFieldData;
use Nails\Factory;
use Nails\Survey\Constants;
use Nails\Survey\Resource\Survey\Cta;
use Nails\Survey\Resource\Survey\ThankYou\Email;
use Nails\Survey\Resource\Survey\ThankYou\Page;
use stdClass;

/**
 * Class Survey
 *
 * @package Nails\Survey\Resource
 */
class Survey extends Entity
{
    /** @var string */
    public $token;

    /** @var string */
    public $token_stats;

    /** @var string */
    public $label;

    /** @var stdClass[]|null */
    public $header;

    /** @var stdClass[]|null */
    public $footer;

    /** @var Cta */
    public $cta;

    /** @var int */
    public $form_id;

    /** @var string */
    public $form_attributes;

    /** @var Entity */
    public $form;

    /** @var string[]|null */
    public $notification_email;

    /** @var Email */
    public $thankyou_email;

    /** @var Page */
    public $thankyou_page;

    /** @var bool */
    public $allow_anonymous_response;

    /** @var bool */
    public $allow_save;

    /** @var bool */
    public $allow_public_stats;

    /** @var stdClass[]|null */
    public $stats_header;

    /** @var stdClass[]|null */
    public $stats_footer;

    /** @var bool */
    public $is_active;

    /** @var bool */
    public $is_minimal;

    /** @var string */
    public $url;

    /** @var string */
    public $url_stats;

    /** @var bool */
    public $is_deleted;

    /** @var ExpandableFieldData */
    public $responses;

    /** @var ExpandableFieldData */
    public $responses_submitted;

    /** @var ExpandableFieldData */
    public $responses_open;

    // --------------------------------------------------------------------------

    /**
     * Survey constructor.
     *
     * @throws FactoryException
     */
    public function __construct(Entity|stdClass|array $resource = [], ?Base $model = null)
    {
        parent::__construct($resource, $model);

        // --------------------------------------------------------------------------

        $this->url       = siteUrl('survey/' . $this->id . '/' . $this->token);
        $this->url_stats = siteUrl('survey/stats/' . $this->id . '/' . $this->token_stats);

        // --------------------------------------------------------------------------

        $this->header             = json_decode($this->header);
        $this->footer             = json_decode($this->footer);
        $this->notification_email = json_decode($this->notification_email);
        $this->stats_header       = json_decode($this->stats_header);
        $this->stats_footer       = json_decode($this->stats_footer);

        // --------------------------------------------------------------------------

        $this->cta = Factory::resource('SurveyCta', Constants::MODULE_SLUG, [
            'label'      => $this->cta_label,
            'attributes' => $this->cta_attributes,
        ]);
        unset($this->cta_label);
        unset($this->cta_attributes);

        // --------------------------------------------------------------------------

        $this->thankyou_email = Factory::resource('SurveyThankYouEmail', Constants::MODULE_SLUG, [
            'send'    => $this->thankyou_email,
            'subject' => $this->thankyou_email_subject,
            'body'    => $this->thankyou_email_body,
        ]);
        unset($this->thankyou_email_subject);
        unset($this->thankyou_email_body);

        // --------------------------------------------------------------------------

        $this->thankyou_page = Factory::resource('SurveyThankYouPage', Constants::MODULE_SLUG, [
            'title' => $this->thankyou_page_title,
            'body'  => $this->thankyou_page_body,
        ]);
        unset($this->thankyou_page_title);
        unset($this->thankyou_page_body);
    }
}
