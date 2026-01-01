<?php

namespace App\Http\Controllers;

use App\Models\Group;
use Illuminate\Http\Request;
use App\Http\Requests\StoreGroupRequest;
use App\Http\Requests\UpdateGroupRequest;
use App\Http\Requests\StoreExclusionRequest;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use App\Models\Pairing;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\DrawResult;
use Illuminate\Support\Facades\Cache;
use App\Models\Exclusion;
use App\Services\DrawService;
use Illuminate\Support\Facades\Gate;
use App\Models\GroupMember; // <--- Importante para disparar o Observer corretamente

class GroupController extends Controller
{
    public function create()
    {
        return view('groups.create');
    }

    public function store(StoreGroupRequest $request)
    {
        $validated = $request->validated();
        $group = Group::create([
            'name' => $validated['name'],
            'event_date' => $validated['event_date'],
            'budget' => $validated['budget'],
            'description' => $validated['description'],
            'owner_id' => Auth::id(),
            'invite_token' => Str::upper(Str::random(6)),
        ]);

        // attach() dispara o evento 'created' no Pivot Model configurado
        $group->members()->attach(Auth::id(), ['wishlist' => $validated['wishlist'] ?? null]);

        return redirect()->route('groups.show', $group);
    }

    public function show(Group $group)
    {
        $group->load(['members', 'exclusions.participant', 'exclusions.excluded']);

        $myPair = null;
        if ($group->is_drawn) {
            $myPair = Pairing::where('group_id', $group->id)
                ->where('santa_id', Auth::id())
                ->with('giftee')
                ->first();
        }

        return view('groups.show', compact('group', 'myPair'));
    }

    public function join($token)
    {
        $group = Group::where('invite_token', $token)->firstOrFail();
        if ($group->members->contains(Auth::id())) {
            return redirect()->route('groups.show', $group)->with('info', 'Você já participa deste grupo!');
        }
        return view('groups.join', compact('group'));
    }

    public function joinStore(Request $request, $token)
    {
        $group = Group::where('invite_token', $token)->firstOrFail();

        // Verificamos manualmente para garantir que usamos 'attach' (que dispara eventos)
        // em vez de syncWithoutDetaching (que as vezes não dispara)
        if (!$group->members()->where('user_id', Auth::id())->exists()) {
            $group->members()->attach(Auth::id(), ['wishlist' => $request->wishlist]);
        }

        // Cache::forget removido! O Observer cuida disso.

        return redirect()->route('groups.show', $group)
            ->with('success', 'Você entrou no grupo com sucesso!');
    }

    public function draw(Group $group, DrawService $drawService)
    {
        Gate::authorize('draw', $group);

        if ($group->is_drawn) {
            return back()->with('error', 'O sorteio já foi realizado!');
        }

        if ($group->members->count() < 3) {
            return back()->with('error', 'É preciso ter pelo menos 3 participantes para usar restrições com segurança.');
        }

        try {
            $drawService->draw($group);

            $pairings = Pairing::where('group_id', $group->id)->with(['santa', 'giftee'])->get();

            foreach ($pairings as $pair) {
                if ($pair->santa && $pair->santa->email) {
                    Mail::to($pair->santa->email)->queue(new DrawResult($group, $pair->santa, $pair->giftee));
                }
            }

            return back()->with('success', 'Sorteio realizado! Os e-mails estão sendo enviados em segundo plano.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function storeExclusion(StoreExclusionRequest $request, Group $group)
    {
        if ($group->is_drawn) return back()->with('error', 'Sorteio já realizado.');

        $exists = Exclusion::where('group_id', $group->id)
            ->where('user_id', $request->user_id)
            ->where('excluded_id', $request->excluded_id)
            ->exists();

        if (!$exists) {
            Exclusion::create([
                'group_id' => $group->id,
                'user_id' => $request->user_id,
                'excluded_id' => $request->excluded_id,
            ]);
        }

        return back()->with('success', 'Restrição adicionada.');
    }

    public function destroyExclusion(Group $group, Exclusion $exclusion)
    {
        Gate::authorize('manageExclusions', $group);

        if ($group->is_drawn) return back()->with('error', 'Sorteio já realizado.');

        if ($exclusion->group_id !== $group->id) {
            abort(403, 'Ação inválida.');
        }

        $exclusion->delete();

        return back()->with('success', 'Restrição removida.');
    }

    public function updateWishlist(Request $request, Group $group)
    {
        if ($group->is_drawn) {
            return back()->with('error', 'O sorteio já foi realizado! Não é possível alterar o desejo.');
        }
        $request->validate(['wishlist' => 'nullable|string|max:1000']);

        // REFATORADO: Usamos o Modelo Pivot diretamente para garantir que o evento 'updated' dispare no Observer
        $memberPivot = GroupMember::where('group_id', $group->id)
            ->where('user_id', Auth::id())
            ->first();

        if ($memberPivot) {
            $memberPivot->update(['wishlist' => $request->wishlist]);
        }

        return back()->with('success', 'Sua lista de desejos foi atualizada!');
    }

    public function edit(Group $group)
    {
        Gate::authorize('update', $group);
        return view('groups.edit', compact('group'));
    }

    public function update(UpdateGroupRequest $request, Group $group)
    {
        Gate::authorize('update', $group);
        $validated = $request->validated();
        $group->update($validated);
        return redirect()->route('groups.show', $group)->with('success', 'Informações do grupo atualizadas com sucesso!');
    }

    public function destroy(Group $group)
    {
        Gate::authorize('delete', $group);
        $group->delete();
        return redirect()->route('dashboard')->with('success', 'Grupo excluído com sucesso!');
    }

    public function removeMember(Group $group, \App\Models\User $user)
    {
        Gate::authorize('removeMember', $group);

        if ($group->is_drawn) {
            return back()->with('error', 'Não é possível remover membros após o sorteio.');
        }
        if ($user->id === $group->owner_id) {
            return back()->with('error', 'O administrador não pode ser removido.');
        }

        // CORREÇÃO: Buscamos o registro primeiro.
        // Ao chamar ->delete() na instância do modelo, o Observer 'deleted' é disparado.
        $pivot = GroupMember::where('group_id', $group->id)
            ->where('user_id', $user->id)
            ->first();

        if ($pivot) {
            $pivot->delete();
        }

        return back()->with('success', "{$user->name} foi removido do grupo.");
    }

    public function membersList(Group $group)
    {
        $userId = Auth::id();
        $isMember = Cache::remember("group_member_check_{$group->id}_{$userId}", 30, function () use ($group, $userId) {
            return $group->members()->where('user_id', $userId)->exists();
        });

        if (!$isMember) {
            abort(403);
        }

        $html = Cache::remember("group_members_html_{$group->id}", 5, function () use ($group) {
            $group->load('members');
            return view('groups.partials.members-list', compact('group'))->render();
        });

        return $html;
    }
}
