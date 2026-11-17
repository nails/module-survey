<?php

namespace Nails\Survey\Admin\Permission\Survey;

use Nails\Admin\Interfaces\Permission;

class Browse implements Permission
{
    public function label(): string
    {
        return 'Can browse surveys';
    }

    public function group(): string
    {
        return 'Surveys';
    }
}
