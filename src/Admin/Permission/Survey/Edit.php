<?php

namespace Nails\Survey\Admin\Permission\Survey;

use Nails\Admin\Interfaces\Permission;

class Edit implements Permission
{
    public function label(): string
    {
        return 'Can edit surveys';
    }

    public function group(): string
    {
        return 'Surveys';
    }
}
