<?php

namespace App\database\Models;

use App\database\Connector\Modal;

class Admin extends Modal {
    protected static string $table_name = 'admin_roles';
    public static function isAdmin (int $userId): object|bool {
        return Admin::where('user_id', $userId)->first();
    }
}