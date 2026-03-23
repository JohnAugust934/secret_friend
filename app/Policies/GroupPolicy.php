<?php

namespace App\Policies;

use App\Models\Group;
use App\Models\User;

class GroupPolicy
{
    /**
     * Determina se o usuario pode visualizar o grupo.
     */
    public function view(User $user, Group $group): bool
    {
        return $user->id === $group->owner_id
            || $group->members()->where('users.id', $user->id)->exists();
    }

    /**
     * Determina se o usuario pode atualizar o grupo.
     */
    public function update(User $user, Group $group): bool
    {
        return $user->id === $group->owner_id;
    }

    /**
     * Determina se o usuario pode deletar o grupo.
     */
    public function delete(User $user, Group $group): bool
    {
        return $user->id === $group->owner_id;
    }

    /**
     * Determina se o usuario pode realizar o sorteio.
     */
    public function draw(User $user, Group $group): bool
    {
        return $user->id === $group->owner_id;
    }

    /**
     * Determina se o usuario pode gerenciar exclusoes (criar/deletar).
     */
    public function manageExclusions(User $user, Group $group): bool
    {
        return $user->id === $group->owner_id;
    }

    /**
     * Determina se o usuario pode remover membros.
     */
    public function removeMember(User $user, Group $group): bool
    {
        return $user->id === $group->owner_id;
    }
}
