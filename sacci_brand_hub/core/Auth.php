<?php

namespace Core;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;

/**
 * Authentication and authorization helper.
 */
class Auth
{
    public static function login(int $userId): void
    {
        $_SESSION['user_id'] = $userId;
    }

    public static function logout(): void
    {
        unset($_SESSION['user_id']);
        session_regenerate_id(true);
    }

    public static function check(): bool
    {
        return isset($_SESSION['user_id']);
    }

    public static function user(): ?array
    {
        if (!self::check()) {
            return null;
        }
        return User::find($_SESSION['user_id']);
    }

    /**
     * Determine if the current user has the given permission.
     */
    public static function hasPermission(string $permission): bool
    {
        $user = self::user();
        if (!$user) {
            return false;
        }
        $roles = Role::findByUserId($user['id']);
        foreach ($roles as $role) {
            $permissions = Permission::findByRoleId($role['id']);
            foreach ($permissions as $perm) {
                if ($perm['name'] === $permission) {
                    return true;
                }
            }
        }
        return false;
    }
}