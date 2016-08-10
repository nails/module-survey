<?php

/**
 * Generates Survey routes
 *
 * @package     Nails
 * @subpackage  module-survey
 * @category    Controller
 * @author      Nails Dev Team
 * @link
 */

namespace Nails\Routes\Survey;

use Nails\Factory;

class Routes
{
    /**
     * Returns an array of routes for this module
     * @return array
     */
    public function getRoutes()
    {
        return array(
            'survey/response/(.*)' => 'survey/response/index/$1',
            'survey/stats/(.*)'    => 'survey/stats/index/$1',
            'survey/(.*)'          => 'survey/survey/index/$1'
        );
    }
}
