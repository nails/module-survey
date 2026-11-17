<?php

namespace Nails\Survey\Admin\Permission\Survey;

use Nails\Admin\Interfaces\Permission;

class Delete implements Permission
{
    public function label(): string
    {
        return 'Can delete surveys';
    }

    public function group(): string
    {
        return 'Surveys';
    }
}
