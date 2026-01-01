<?php

namespace App\Observers;

use App\Models\GroupMember;
use Illuminate\Support\Facades\Cache;

class GroupMemberObserver
{
    /**
     * Handle the GroupMember "created" event.
     * Disparado quando alguém entra no grupo.
     */
    public function created(GroupMember $groupMember): void
    {
        $this->clearGroupCache($groupMember);
    }

    /**
     * Handle the GroupMember "updated" event.
     * Disparado quando alguém muda a wishlist.
     */
    public function updated(GroupMember $groupMember): void
    {
        // Limpa apenas o cache da lista HTML, pois o status de membro não mudou
        Cache::forget("group_members_html_{$groupMember->group_id}");
    }

    /**
     * Handle the GroupMember "deleted" event.
     * Disparado quando alguém sai ou é removido.
     */
    public function deleted(GroupMember $groupMember): void
    {
        $this->clearGroupCache($groupMember);
    }

    /**
     * Centraliza a lógica de limpeza.
     */
    private function clearGroupCache(GroupMember $groupMember): void
    {
        // 1. Limpa o HTML da lista de participantes (para todos verem a mudança)
        Cache::forget("group_members_html_{$groupMember->group_id}");

        // 2. Limpa a verificação de permissão daquele usuário específico
        Cache::forget("group_member_check_{$groupMember->group_id}_{$groupMember->user_id}");
    }
}
