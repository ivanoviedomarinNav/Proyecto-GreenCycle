<?php

namespace App\Policies;

use App\Models\Tree;
use App\Models\User;

class TreePolicy
{
    public function view(User $user, Tree $tree): bool
    {
        return $user->id === $tree->user_id;
    }

    public function update(User $user, Tree $tree): bool
    {
        return $user->id === $tree->user_id;
    }
}
