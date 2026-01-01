<?php

namespace App\Policies;

use App\Models\Group;
use App\Models\User;

class GroupPolicy
{
    /**
     * Determina se o usuário pode atualizar o grupo.
     */
    public function update(User $user, Group $group): bool
    {
        return $user->id === $group->owner_id;
    }

    /**
     * Determina se o usuário pode deletar o grupo.
     */
    public function delete(User $user, Group $group): bool
    {
        return $user->id === $group->owner_id;
    }

    /**
     * Determina se o usuário pode realizar o sorteio.
     */
    public function draw(User $user, Group $group): bool
    {
        return $user->id === $group->owner_id;
    }

    /**
     * Determina se o usuário pode gerenciar exclusões (criar/deletar).
     */
    public function manageExclusions(User $user, Group $group): bool
    {
        return $user->id === $group->owner_id;
    }

    /**
     * Determina se o usuário pode remover membros.
     */
    public function removeMember(User $user, Group $group): bool
    {
        return $user->id === $group->owner_id;
    }
}
