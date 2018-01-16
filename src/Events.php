<?php

/**
 * The class provides a summary of the events fired by this module
 *
 * @package     Nails
 * @subpackage  module-common
 * @category    Events
 * @author      Nails Dev Team
 */

namespace Nails\Survey;

use Nails\Common\Events\Base;

class Events extends Base
{
    /**
     * Fired when a response is set as OPEN
     *
     * @param \stdClass $iId The ID of the response
     */
    const RESPONSE_OPEN = 'RESPONSE:OPEN';

    /**
     * Fired when a response is set as SUBMITTED
     *
     * @param \stdClass $iId The ID of the response
     */
    const RESPONSE_SUBMITTED = 'RESPONSE:SUBMITTED';
}
