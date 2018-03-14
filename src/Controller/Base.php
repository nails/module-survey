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

use Nails\Factory;

// --------------------------------------------------------------------------

/**
 * Allow the app to add functionality, if needed
 */
if (class_exists('\App\Survey\Controller\Base')) {
    abstract class BaseMiddle extends \App\Survey\Controller\Base
    {
    }
} else {
    abstract class BaseMiddle extends \App\Controller\Base
    {
    }
}

// --------------------------------------------------------------------------

abstract class Base extends BaseMiddle
{
}
