<?php

/**
 * This class provides some common Survey controller functionality
 *
 * @package     Nails
 * @subpackage  module-survey
 * @category    Controller
 * @author      Nails Dev Team
 * @link
 */

namespace Nails\Survey\Controller;

use Nails\Common\Exception\FactoryException;
use Nails\Common\Exception\NailsException;
use Nails\Common\Service\Event;
use Nails\Factory;
use Nails\Survey\Events;
use ReflectionException;

abstract class Base extends \Nails\Common\Controller\Base
{
    /**
     * @throws FactoryException
     * @throws NailsException
     * @throws ReflectionException
     */
    public function __construct()
    {
        parent::__construct();

        /** @var Event $oEvent */
        $oEvent = Factory::service('Event');
        $oEvent
            ->trigger(Events::CONTROLLER_CONSTRUCT_PRE, Events::getEventNamespace())
            ->trigger(Events::CONTROLLER_CONSTRUCT_POST, Events::getEventNamespace());
    }
}
