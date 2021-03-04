<?php

namespace Nails\Survey\Resource;

use Nails\Common\Exception\FactoryException;
use Nails\Common\Resource\Entity;
use Nails\Common\Resource\ExpandableField;
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

    /** @var ExpandableField */
    public $responses;

    /** @var ExpandableField */
    public $responses_submitted;

    /** @var ExpandableField */
    public $responses_open;

    // --------------------------------------------------------------------------

    /**
     * Survey constructor.
     *
     * @param array $mObj
     *
     * @throws FactoryException
     */
    public function __construct($mObj = [])
    {
        $mObj->url       = siteUrl('survey/' . $mObj->id . '/' . $mObj->token);
        $mObj->url_stats = siteUrl('survey/stats/' . $mObj->id . '/' . $mObj->token_stats);

        // --------------------------------------------------------------------------

        $mObj->header             = json_decode($mObj->header);
        $mObj->footer             = json_decode($mObj->footer);
        $mObj->notification_email = json_decode($mObj->notification_email);
        $mObj->stats_header       = json_decode($mObj->stats_header);
        $mObj->stats_footer       = json_decode($mObj->stats_footer);

        // --------------------------------------------------------------------------

        $mObj->cta = Factory::resource('SurveyCta', Constants::MODULE_SLUG, [
            'label'      => $mObj->cta_label,
            'attributes' => $mObj->cta_attributes,
        ]);
        unset($mObj->cta_label);
        unset($mObj->cta_attributes);

        // --------------------------------------------------------------------------

        $mObj->thankyou_email = Factory::resource('SurveyThankYouEmail', Constants::MODULE_SLUG, [
            'send'    => $mObj->thankyou_email,
            'subject' => $mObj->thankyou_email_subject,
            'body'    => $mObj->thankyou_email_body,
        ]);
        unset($mObj->thankyou_email_subject);
        unset($mObj->thankyou_email_body);

        // --------------------------------------------------------------------------

        $mObj->thankyou_page = Factory::resource('SurveyThankYouPage', Constants::MODULE_SLUG, [
            'title' => $mObj->thankyou_page_title,
            'body'  => $mObj->thankyou_page_body,
        ]);
        unset($mObj->thankyou_page_title);
        unset($mObj->thankyou_page_body);

        // --------------------------------------------------------------------------

        parent::__construct($mObj);
    }
}
