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
     * @throws FactoryException
     */
    public function __construct(self|stdClass|array $resource = [], ?Base $model = null)
    {
        parent::__construct($resource, $model);

        // --------------------------------------------------------------------------

        $entity->url       = siteUrl('survey/' . $entity->id . '/' . $entity->token);
        $entity->url_stats = siteUrl('survey/stats/' . $entity->id . '/' . $entity->token_stats);

        // --------------------------------------------------------------------------

        $entity->header             = json_decode($entity->header);
        $entity->footer             = json_decode($entity->footer);
        $entity->notification_email = json_decode($entity->notification_email);
        $entity->stats_header       = json_decode($entity->stats_header);
        $entity->stats_footer       = json_decode($entity->stats_footer);

        // --------------------------------------------------------------------------

        $entity->cta = Factory::resource('SurveyCta', Constants::MODULE_SLUG, [
            'label'      => $entity->cta_label,
            'attributes' => $entity->cta_attributes,
        ]);
        unset($entity->cta_label);
        unset($entity->cta_attributes);

        // --------------------------------------------------------------------------

        $entity->thankyou_email = Factory::resource('SurveyThankYouEmail', Constants::MODULE_SLUG, [
            'send'    => $entity->thankyou_email,
            'subject' => $entity->thankyou_email_subject,
            'body'    => $entity->thankyou_email_body,
        ]);
        unset($entity->thankyou_email_subject);
        unset($entity->thankyou_email_body);

        // --------------------------------------------------------------------------

        $entity->thankyou_page = Factory::resource('SurveyThankYouPage', Constants::MODULE_SLUG, [
            'title' => $entity->thankyou_page_title,
            'body'  => $entity->thankyou_page_body,
        ]);
        unset($entity->thankyou_page_title);
        unset($entity->thankyou_page_body);
    }
}
