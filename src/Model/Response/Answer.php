<?php

/**
 * Manage Response Answers
 *
 * @package    Nails
 * @subpackage module-survey
 * @category   Model
 * @author     Nails Dev Team
 */

namespace Nails\Survey\Model\Response;

use Nails\Common\Exception\ModelException;
use Nails\Common\Model\Base;
use Nails\Survey\Constants;
use Nails\FormBuilder;

/**
 * Class Answer
 *
 * @package Nails\Survey\Model\Response
 */
class Answer extends Base
{
    /**
     * The table this model represents
     *
     * @var string
     */
    const TABLE = NAILS_DB_PREFIX . 'survey_response_answer';

    /**
     * The name of the resource to use (as passed to \Nails\Factory::resource())
     *
     * @var string
     */
    const RESOURCE_NAME = 'ResponseAnswer';

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
     * The default column to sort on
     *
     * @var string|null
     */
    const DEFAULT_SORT_COLUMN = 'order';

    // --------------------------------------------------------------------------

    /**
     * Answer constructor.
     *
     * @throws ModelException
     */
    public function __construct()
    {
        parent::__construct();
        $this
            ->hasOne('response', 'Response', Constants::MODULE_SLUG, 'survey_response_id')
            ->hasOne('question', 'FormField', FormBuilder\Constants::MODULE_SLUG, 'form_field_id')
            ->hasOne('option', 'FormFieldOption', FormBuilder\Constants::MODULE_SLUG, 'form_field_option_id');
    }
}
