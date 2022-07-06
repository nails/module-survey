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

use Nails\Common\Exception\NailsException;
use Nails\Factory;

// --------------------------------------------------------------------------

/**
 * Allow the app to add functionality, if needed
 * Negative conditional helps with static analysis
 */
if (!class_exists('\App\Survey\Controller\Base')) {
    abstract class BaseMiddle extends \App\Controller\Base
    {
    }
} else {
    abstract class BaseMiddle extends \App\Survey\Controller\Base
    {
        public function __construct()
        {
            if (!classExtends(static::class, \App\Controller\Base::class)) {
                throw new NailsException(sprintf(
                    'Class %s must extend %s',
                    parent::class,
                    \App\Controller\Base::class
                ));
            }
            parent::__construct();
        }
    }
}

// --------------------------------------------------------------------------

abstract class Base extends BaseMiddle
{
}
