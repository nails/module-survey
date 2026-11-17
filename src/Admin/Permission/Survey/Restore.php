<?php

namespace Nails\Survey\Admin\Permission\Survey;

use Nails\Admin\Interfaces\Permission;

class Restore implements Permission
{
    public function label(): string
    {
        return 'Can restore surveys';
    }

    public function group(): string
    {
        return 'Surveys';
    }
}
