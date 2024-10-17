<?php

namespace App\database\Models;

use App\database\Connector\Modal;
use PDOStatement;

class User extends Modal {
    protected static string $table_name = 'users';
    public static function myUser(int $fromId): bool|PDOStatement {
        $user = User::where('fromid',$fromId)->first();
        if (!$user){
            return User::create(['fromid' => $fromId]);
        }
        if ($user->deleted_at!==null) {
            User::update(['deleted_at'=>null, 'fromid'=>$fromId]);
        }
        return (bool) $user;
    }
}