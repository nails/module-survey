<?php

/**
 * Migration: 6
 * Started:   16/11/2026
 */

namespace Nails\Survey\Database\Migration;

use Nails\Admin\Traits\Database\Migration\PermissionMap;
use Nails\Common\Interfaces;
use Nails\Common\Traits;
use Nails\Survey\Admin\Permission;

/**
 * Class Migration6
 *
 * Repeatable because `feature/pre-new-admin` has no equivalent migration, so an app
 * arriving from that branch resumes above this number and would never run it.
 *
 * @package Nails\Survey\Database\Migration
 */
class Migration6 implements Interfaces\Database\Migration\Repeatable
{
    use Traits\Database\Migration;
    use PermissionMap;

    // --------------------------------------------------------------------------

    const MAP = [
        'admin:survey:survey:browse'        => Permission\Survey\Browse::class,
        'admin:survey:survey:create'        => Permission\Survey\Create::class,
        'admin:survey:survey:edit'          => Permission\Survey\Edit::class,
        'admin:survey:survey:delete'        => Permission\Survey\Delete::class,
        'admin:survey:survey:destroy'       => Permission\Survey\Destroy::class,
        'admin:survey:survey:restore'       => Permission\Survey\Restore::class,
        'admin:survey:survey:copy'          => Permission\Survey\Copy::class,
        'admin:survey:response:view'        => Permission\Response\View::class,
        'admin:survey:survey:response:edit' => Permission\Response\View::class,
    ];
}
