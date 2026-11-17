<?php

namespace Nails\Survey\Admin\Permission\Survey;

use Nails\Admin\Interfaces\Permission;

class Destroy implements Permission
{
    public function label(): string
    {
        return 'Can destroy surveys';
    }

    public function group(): string
    {
        return 'Surveys';
    }
}
