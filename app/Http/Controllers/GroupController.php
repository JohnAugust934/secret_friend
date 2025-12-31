<?php

namespace App\Http\Controllers;

use App\Models\Group;
use Illuminate\Http\Request;
use App\Http\Requests\StoreGroupRequest;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use App\Models\Pairing;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail; // <--- NOVO
use App\Mail\DrawResult;             // <--- NOVO

class GroupController extends Controller
{
    // ... (create, store, show, join, joinStore methods mantidos iguais) ...
    // Vou omitir os métodos que não mudaram para poupar espaço, 
    // mas deves manter o código que já tinhas nesses métodos.

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
        $group->members()->syncWithoutDetaching([Auth::id() => ['wishlist' => $request->wishlist]]);
        return redirect()->route('groups.show', $group)->with('success', 'Você entrou no grupo com sucesso!');
    }

    // --- MÉTODO ATUALIZADO COM ENVIO DE E-MAIL ---
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

        // 1. Realizar o Sorteio (Transação)
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

        // 2. Enviar E-mails (Fora da transação para não bloquear o banco se o email falhar)
        // Buscamos os pares recém-criados para enviar
        $pairings = Pairing::where('group_id', $group->id)->with(['santa', 'giftee'])->get();

        foreach ($pairings as $pair) {
            // Verifica se o usuário tem e-mail antes de tentar enviar
            if ($pair->santa && $pair->santa->email) {
                try {
                    Mail::to($pair->santa->email)->send(new DrawResult($group, $pair->santa, $pair->giftee));
                } catch (\Exception $e) {
                    // Logar erro mas não parar o fluxo para os outros usuários
                    \Illuminate\Support\Facades\Log::error("Falha ao enviar email para {$pair->santa->email}: " . $e->getMessage());
                }
            }
        }

        return back()->with('success', 'Sorteio realizado e e-mails enviados com sucesso!');
    }
    // -----------------------------------------------

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
        return back()->with('success', "{$user->name} foi removido do grupo.");
    }
}
