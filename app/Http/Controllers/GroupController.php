<?php

namespace App\Http\Controllers;

use App\Models\Group;
use Illuminate\Http\Request;
use App\Http\Requests\StoreGroupRequest;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use App\Models\Pairing;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\DrawResult;
use Illuminate\Support\Facades\Cache;
use App\Models\Exclusion;       // <--- NOVO
use App\Services\DrawService;   // <--- NOVO

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
        $group->members()->attach(Auth::id(), ['wishlist' => $validated['wishlist'] ?? null]);
        return redirect()->route('groups.show', $group);
    }

    public function show(Group $group)
    {
        // ATUALIZADO: Carrega membros e as exclusões para exibir no painel
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

        $group->members()->syncWithoutDetaching([
            Auth::id() => ['wishlist' => $request->wishlist]
        ]);

        // LIMPA O CACHE PARA ATUALIZAR A LISTA
        Cache::forget("group_members_html_{$group->id}");
        Cache::forget("group_member_check_{$group->id}_" . Auth::id());

        return redirect()->route('groups.show', $group)
            ->with('success', 'Você entrou no grupo com sucesso!');
    }

    // --- MÉTODO ATUALIZADO PARA USAR O SERVIÇO DE EXCLUSÕES ---
    public function draw(Group $group, DrawService $drawService)
    {
        if (auth()->id() !== $group->owner_id) {
            abort(403, 'Apenas o administrador pode realizar o sorteio.');
        }

        if ($group->is_drawn) {
            return back()->with('error', 'O sorteio já foi realizado!');
        }

        // Para usar restrições, precisamos de margem de manobra (min 3 pessoas)
        if ($group->members->count() < 3) {
            return back()->with('error', 'É preciso ter pelo menos 3 participantes para usar restrições com segurança.');
        }

        try {
            // 1. Tenta realizar o sorteio respeitando as regras (via Service)
            $drawService->draw($group);

            // 2. Enviar E-mails (Só chega aqui se o sorteio der certo)
            $pairings = Pairing::where('group_id', $group->id)->with(['santa', 'giftee'])->get();

            foreach ($pairings as $pair) {
                if ($pair->santa && $pair->santa->email) {
                    try {
                        Mail::to($pair->santa->email)->send(new DrawResult($group, $pair->santa, $pair->giftee));
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::error("Falha ao enviar email para {$pair->santa->email}: " . $e->getMessage());
                    }
                }
            }

            return back()->with('success', 'Sorteio realizado com sucesso!');
        } catch (\Exception $e) {
            // Se o DrawService falhar (ex: impossível sortear com tantos bloqueios), exibe o erro
            return back()->with('error', $e->getMessage());
        }
    }
    // -----------------------------------------------

    // --- NOVOS MÉTODOS DE RESTRIÇÃO ---
    public function storeExclusion(Request $request, Group $group)
    {
        if (auth()->id() !== $group->owner_id) abort(403);
        if ($group->is_drawn) return back()->with('error', 'Sorteio já realizado.');

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'excluded_id' => 'required|exists:users,id|different:user_id',
        ]);

        // Verifica se já existe para não duplicar
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
        if (auth()->id() !== $group->owner_id) abort(403);
        if ($group->is_drawn) return back()->with('error', 'Sorteio já realizado.');

        $exclusion->delete();

        return back()->with('success', 'Restrição removida.');
    }
    // ----------------------------------

    public function updateWishlist(Request $request, Group $group)
    {
        if ($group->is_drawn) {
            return back()->with('error', 'O sorteio já foi realizado! Não é possível alterar o desejo.');
        }
        $request->validate(['wishlist' => 'nullable|string|max:1000']);
        $group->members()->updateExistingPivot(Auth::id(), ['wishlist' => $request->wishlist]);
        return back()->with('success', 'Sua lista de desejos foi atualizada!');
    }

    public function edit(Group $group)
    {
        if (auth()->id() !== $group->owner_id) {
            abort(403, 'Apenas o administrador pode editar este grupo.');
        }
        return view('groups.edit', compact('group'));
    }

    public function update(\App\Http\Requests\UpdateGroupRequest $request, Group $group)
    {
        if (auth()->id() !== $group->owner_id) {
            abort(403, 'Apenas o administrador pode editar este grupo.');
        }
        $validated = $request->validated();
        $group->update($validated);
        return redirect()->route('groups.show', $group)->with('success', 'Informações do grupo atualizadas com sucesso!');
    }

    public function destroy(Group $group)
    {
        if (auth()->id() !== $group->owner_id) {
            abort(403, 'Ação não autorizada');
        }
        $group->delete();
        return redirect()->route('dashboard')->with('success', 'Grupo excluído com sucesso!');
    }

    public function removeMember(Group $group, \App\Models\User $user)
    {
        if (auth()->id() !== $group->owner_id) {
            abort(403, 'Apenas o administrador pode remover membros.');
        }
        if ($group->is_drawn) {
            return back()->with('error', 'Não é possível remover membros após o sorteio.');
        }
        if ($user->id === $group->owner_id) {
            return back()->with('error', 'O administrador não pode ser removido.');
        }
        $group->members()->detach($user->id);

        // LIMPA O CACHE PARA ATUALIZAR A LISTA
        Cache::forget("group_members_html_{$group->id}");
        Cache::forget("group_member_check_{$group->id}_{$user->id}");

        return back()->with('success', "{$user->name} foi removido do grupo.");
    }

    // Retorna apenas o HTML da lista de membros (Otimizado para Supabase)
    public function membersList(Group $group)
    {
        // 1. Verificação de segurança (verifica se é membro)
        // Usamos o cache aqui também para não bater no banco só para ver permissão toda hora
        $userId = Auth::id();
        $isMember = Cache::remember("group_member_check_{$group->id}_{$userId}", 30, function () use ($group, $userId) {
            return $group->members()->where('user_id', $userId)->exists();
        });

        if (!$isMember) {
            abort(403);
        }

        // 2. Cache do HTML da lista (O PULO DO GATO 😺)
        // Guardamos o HTML pronto por 5 segundos.
        // Mesmo que 100 pessoas peçam, o banco só é consultado 1 vez a cada 5s.
        $html = Cache::remember("group_members_html_{$group->id}", 5, function () use ($group) {
            // Carrega os membros apenas se o cache expirou
            $group->load('members');
            return view('groups.partials.members-list', compact('group'))->render();
        });

        return $html;
    }
}
