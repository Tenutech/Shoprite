<?php

namespace App\Enums;

use App\Models\Role;

class RoleEnum
{
    const SUPER_ADMIN = 'Super Admin';
    const ADMIN = 'Admin';
    const MANAGER = 'Manager';
    const USER = 'User';
    const APPLICANT = 'Applicant';

    public static function values()
    {
        return [
            self::SUPER_ADMIN,
            self::ADMIN,
            self::MANAGER,
            self::USER,
            self::APPLICANT,
        ];
    }

    public static function getIdByName($name)
    {
        return Role::where('name', $name)->value('id');
    }

    public static function getNameById($id)
    {
        return Role::where('id', $id)->value('name');
    }
}