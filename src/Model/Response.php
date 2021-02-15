<?php

/**
 * Manage Responses
 *
 * @package     Nails
 * @subpackage  module-survey
 * @category    Model
 * @author      Nails Dev Team
 * @link
 */

namespace Nails\Survey\Model;

use Nails\Auth;
use Nails\Common\Exception\FactoryException;
use Nails\Common\Exception\ModelException;
use Nails\Common\Exception\NailsException;
use Nails\Common\Model\Base;
use Nails\Common\Service\Event;
use Nails\Factory;
use Nails\Survey\Constants;
use Nails\Survey\Events;

/**
 * Class Response
 *
 * @package Nails\Survey\Model
 */
class Response extends Base
{
    /**
     * The table this model represents
     *
     * @var string
     */
    const TABLE = NAILS_DB_PREFIX . 'survey_response';

    /**
     * The name of the resource to use (as passed to \Nails\Factory::resource())
     *
     * @var string
     */
    const RESOURCE_NAME = 'Response';

    /**
     * The provider of the resource to use (as passed to \Nails\Factory::resource())
     *
     * @var string
     */
    const RESOURCE_PROVIDER = Constants::MODULE_SLUG;

    /**
     * Whether this model uses destructive delete or not
     *
     * @var bool
     */
    const DESTRUCTIVE_DELETE = false;

    /**
     * Whether to automatically set tokens or not
     *
     * @var bool
     */
    const AUTO_SET_TOKEN = true;

    /**
     * The various response statuses
     *
     * @var string
     */
    const STATUS_OPEN      = 'OPEN';
    const STATUS_SUBMITTED = 'SUBMITTED';

    // --------------------------------------------------------------------------

    /**
     * Response constructor.
     *
     * @throws ModelException
     */
    public function __construct()
    {
        parent::__construct();
        $this->defaultSortColumn = 'created';
        $this->defaultSortOrder  = 'desc';

        $this
            ->hasOne('survey', 'Survey', Constants::MODULE_SLUG)
            ->hasOne('user', 'User', Auth\Constants::MODULE_SLUG)
            ->hasMany('answers', 'ResponseAnswer', 'survey_response_id', Constants::MODULE_SLUG);
    }

    // --------------------------------------------------------------------------

    /**
     * @inheritDoc
     */
    public function create(array $aData = [], $bReturnObject = false)
    {
        //  Generate an access token
        Factory::helper('string');
        $aData['token'] = generateToken();
        return parent::create($aData, $bReturnObject);
    }

    // --------------------------------------------------------------------------

    /**
     * @inheritDoc
     */
    public function update($iId, array $aData = []): bool
    {
        //  Ensure access tokens aren't updated
        unset($aData['token']);
        return parent::update($iId, $aData);
    }

    // --------------------------------------------------------------------------

    /**
     * Sets the status of a resonse to self::STATUS_OPEN
     *
     * @param int $iId The ID of the response
     *
     * @return bool
     * @throws ModelException
     * @throws FactoryException
     * @throws NailsException
     * @throws \ReflectionException
     */
    public function setOpen($iId): bool
    {
        $bResult = $this->update(
            $iId,
            [
                'status'         => self::STATUS_OPEN,
                'date_submitted' => null,
            ]
        );

        if ($bResult) {
            /** @var Event $oEventService */
            $oEventService = Factory::service('Event');
            $oEventService->trigger(Events::RESPONSE_OPEN, Events::getEventNamespace(), [$iId]);
        }

        return $bResult;
    }

    // --------------------------------------------------------------------------

    /**
     * Sets the status of a resonse to self::STATUS_SUBMITTED
     *
     * @param int $iId The ID of the response
     *
     * @return bool
     * @throws FactoryException
     * @throws ModelException
     * @throws NailsException
     * @throws \ReflectionException
     */
    public function setSubmitted($iId)
    {
        $bResult = $this->update(
            $iId,
            [
                'status'         => self::STATUS_SUBMITTED,
                'date_submitted' => ['NOW()', false],
            ]
        );

        if ($bResult) {
            /** @var Event $oEventService */
            $oEventService = Factory::service('Event');
            $oEventService->trigger(Events::RESPONSE_SUBMITTED, Events::getEventNamespace(), [$iId]);
        }

        return $bResult;
    }
}
