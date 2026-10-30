<?php

namespace App\Policies;

use App\Models\Addon;
use App\Models\User;

class AddonPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canManageMenu();
    }

    public function create(User $user): bool
    {
        return $user->canManageMenu();
    }

    public function update(User $user, Addon $addon): bool
    {
        return $user->canManageMenu();
    }

    public function delete(User $user, Addon $addon): bool
    {
        return $user->canManageMenu();
    }
}
