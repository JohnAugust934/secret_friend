<?php

namespace App\Http\Controllers;

use App\Models\Group;
use Illuminate\Http\Request;
use App\Http\Requests\StoreGroupRequest; // <--- IMPORTANTE: Importamos a nova validação
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use App\Models\Pairing;
use Illuminate\Support\Facades\DB;

class GroupController extends Controller
{
    // Exibe o formulário de criação
    public function create()
    {
        return view('groups.create');
    }

    // Processa o formulário e salva no banco
    // Note que agora usamos StoreGroupRequest em vez de Request
    public function store(StoreGroupRequest $request)
    {
        // A validação acontece automaticamente antes de entrar aqui.
        // Se falhar, o Laravel manda o usuário de volta com os erros.

        $validated = $request->validated(); // Pegamos apenas os dados validados e limpos

        // Criação do Grupo
        $group = Group::create([
            'name' => $validated['name'],
            'event_date' => $validated['event_date'],
            'budget' => $validated['budget'],
            'description' => $validated['description'],
            'owner_id' => Auth::id(),
            'invite_token' => Str::upper(Str::random(6)), // Ainda vamos melhorar isso depois
        ]);

        // O dono entra como membro JÁ COM A WISHLIST
        $group->members()->attach(Auth::id(), [
            'wishlist' => $validated['wishlist'] ?? null
        ]);

        return redirect()->route('groups.show', $group);
    }

    // Exibe o grupo criado (Dashboard do grupo)
    public function show(Group $group)
    {
        $group->load('members');

        $myPair = null;
        if ($group->is_drawn) {
            $myPair = Pairing::where('group_id', $group->id)
                ->where('santa_id', Auth::id())
                ->with('giftee')
                ->first();
        }

        return view('groups.show', compact('group', 'myPair'));
    }

    // Exibe a tela de confirmação de entrada
    public function join($token)
    {
        $group = Group::where('invite_token', $token)->firstOrFail();

        if ($group->members->contains(Auth::id())) {
            return redirect()->route('groups.show', $group)
                ->with('info', 'Você já participa deste grupo!');
        }

        return view('groups.join', compact('group'));
    }

    // Processa a entrada no grupo
    public function joinStore(Request $request, $token)
    {
        $group = Group::where('invite_token', $token)->firstOrFail();

        $group->members()->syncWithoutDetaching([
            Auth::id() => ['wishlist' => $request->wishlist]
        ]);

        return redirect()->route('groups.show', $group)
            ->with('success', 'Você entrou no grupo com sucesso!');
    }

    public function draw(Group $group)
    {
        if (auth()->id() !== $group->owner_id) {
            abort(403, 'Apenas o administrador pode realizar o sorteio.');
        }

        if ($group->is_drawn) {
            return back()->with('error', 'O sorteio já foi realizado!');
        }

        $members = $group->members;
        if ($members->count() < 2) {
            return back()->with('error', 'É preciso ter pelo menos 2 participantes.');
        }

        DB::transaction(function () use ($group, $members) {
            $shuffled = $members->shuffle();
            $count = $shuffled->count();

            for ($i = 0; $i < $count; $i++) {
                $santa = $shuffled[$i];
                $gifteeIndex = ($i + 1) % $count;
                $giftee = $shuffled[$gifteeIndex];

                Pairing::create([
                    'group_id' => $group->id,
                    'santa_id' => $santa->id,
                    'giftee_id' => $giftee->id,
                ]);
            }

            $group->update(['is_drawn' => true]);
        });

        return back()->with('success', 'Sorteio realizado com sucesso! Todos já podem ver seus pares.');
    }

    public function updateWishlist(Request $request, Group $group)
    {
        // MANTIVEMOS O BLOQUEIO AQUI COMO SOLICITADO
        if ($group->is_drawn) {
            return back()->with('error', 'O sorteio já foi realizado! Não é possível alterar o desejo.');
        }

        $request->validate(['wishlist' => 'nullable|string|max:1000']);

        $group->members()->updateExistingPivot(Auth::id(), [
            'wishlist' => $request->wishlist
        ]);

        return back()->with('success', 'Sua lista de desejos foi atualizada!');
    }

    // Exibe o formulário de edição
    public function edit(Group $group)
    {
        if (auth()->id() !== $group->owner_id) {
            abort(403, 'Apenas o administrador pode editar este grupo.');
        }

        return view('groups.edit', compact('group'));
    }

    // Processa a atualização dos dados
    public function update(\App\Http\Requests\UpdateGroupRequest $request, Group $group)
    {
        if (auth()->id() !== $group->owner_id) {
            abort(403, 'Apenas o administrador pode editar este grupo.');
        }

        $validated = $request->validated();

        $group->update($validated);

        return redirect()->route('groups.show', $group)
            ->with('success', 'Informações do grupo atualizadas com sucesso!');
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
        // 1. Segurança: Só o dono pode remover
        if (auth()->id() !== $group->owner_id) {
            abort(403, 'Apenas o administrador pode remover membros.');
        }

        // 2. Segurança: Não pode remover se já foi sorteado
        if ($group->is_drawn) {
            return back()->with('error', 'Não é possível remover membros após o sorteio.');
        }

        // 3. Segurança: O dono não se pode remover a si mesmo por aqui
        if ($user->id === $group->owner_id) {
            return back()->with('error', 'O administrador não pode ser removido.');
        }

        // Remove a relação na tabela pivot
        $group->members()->detach($user->id);

        return back()->with('success', "{$user->name} foi removido do grupo.");
    }
}
