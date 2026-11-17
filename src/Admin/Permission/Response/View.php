<?php

namespace Nails\Survey\Admin\Permission\Response;

use Nails\Admin\Interfaces\Permission;

class View implements Permission
{
    public function label(): string
    {
        return 'Can view survey responses';
    }

    public function group(): string
    {
        return 'Responses';
    }
}
