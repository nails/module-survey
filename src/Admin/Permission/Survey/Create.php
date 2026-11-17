<?php

namespace Nails\Survey\Admin\Permission\Survey;

use Nails\Admin\Interfaces\Permission;

class Create implements Permission
{
    public function label(): string
    {
        return 'Can create surveys';
    }

    public function group(): string
    {
        return 'Surveys';
    }
}
