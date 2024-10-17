<?php

namespace App\database\Models;

enum AdminRole: string {
    case superAdmin = 'superadmin';
    case Admin = 'admin';
    case User = 'user';
}
