<?php

/**
 * Manage Response Answers
 *
 * @package     Nails
 * @subpackage  module-survey
 * @category    Model
 * @author      Nails Dev Team
 * @link
 */

namespace Nails\Survey\Model;

use Nails\Common\Model\Base;

class ResponseAnswer extends Base
{
    /**
     * Construct the model
     */
    public function __construct()
    {
        parent::__construct();
        $this->table             = NAILS_DB_PREFIX . 'survey_response_answer';
        $this->destructiveDelete = false;
        $this->defaultSortColumn = 'order';
        $this->addExpandableField([
            'trigger'   => 'response',
            'type'      => self::EXPANDABLE_TYPE_SINGLE,
            'property'  => 'response',
            'model'     => 'Response',
            'provider'  => 'nailsapp/module-survey',
            'id_column' => 'survey_response_id',
        ]);
        $this->addExpandableField([
            'trigger'   => 'question',
            'type'      => self::EXPANDABLE_TYPE_SINGLE,
            'property'  => 'question',
            'model'     => 'FormField',
            'provider'  => 'nailsapp/module-form-builder',
            'id_column' => 'form_field_id',
        ]);
        $this->addExpandableField([
            'trigger'   => 'option',
            'type'      => self::EXPANDABLE_TYPE_SINGLE,
            'property'  => 'option',
            'model'     => 'FormFieldOption',
            'provider'  => 'nailsapp/module-form-builder',
            'id_column' => 'form_field_option_id',
        ]);
    }
}
