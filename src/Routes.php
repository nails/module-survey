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

namespace Nails\Survey;

use Nails\Common\Interfaces\RouteGenerator;

class Routes implements RouteGenerator
{
    /**
     * Returns an array of routes for this module
     * @return array
     */
    public static function generate()
    {
        return [
            'survey/response/(.*)' => 'survey/response/index/$1',
            'survey/stats/(.*)'    => 'survey/stats/index/$1',
            'survey/(.*)'          => 'survey/survey/index/$1',
        ];
    }
}
