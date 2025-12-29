<?php

namespace App\Http\Controllers;

use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Str; // Importante para gerar o token
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
    public function store(Request $request)
    {
        // 1. Validação (Adicionamos o wishlist)
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'event_date' => 'required|date|after:today',
            'budget' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'wishlist' => 'nullable|string|max:1000', // Novo campo
        ]);

        // 2. Criação do Grupo
        $group = Group::create([
            'name' => $validated['name'],
            'event_date' => $validated['event_date'],
            'budget' => $validated['budget'],
            'description' => $validated['description'],
            'owner_id' => Auth::id(),
            'invite_token' => Str::upper(Str::random(6)),
        ]);

        // 3. O dono entra como membro JÁ COM A WISHLIST
        $group->members()->attach(Auth::id(), [
            'wishlist' => $validated['wishlist'] ?? null
        ]);

        return redirect()->route('groups.show', $group);
    }

    // Exibe o grupo criado (Dashboard do grupo)
    public function show(Group $group)
    {
        // Carrega membros
        $group->load('members');

        // Verifica se o sorteio já ocorreu e busca quem eu tirei
        $myPair = null;
        if ($group->is_drawn) {
            $myPair = Pairing::where('group_id', $group->id)
                ->where('santa_id', Auth::id())
                ->with('giftee') // Traz os dados da pessoa sorteada
                ->first();
        }

        return view('groups.show', compact('group', 'myPair'));
    }

    // Exibe a tela de confirmação de entrada
    public function join($token)
    {
        // Busca o grupo pelo token
        $group = Group::where('invite_token', $token)->firstOrFail();

        // Se o usuário JÁ for membro, manda direto pro grupo
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

        // Adiciona o usuário atual aos membros
        // syncWithoutDetaching evita duplicidade e mantém os outros membros
        $group->members()->syncWithoutDetaching([
            Auth::id() => ['wishlist' => $request->wishlist] // Já salva a wishlist se tiver
        ]);

        return redirect()->route('groups.show', $group)
            ->with('success', 'Você entrou no grupo com sucesso!');
    }

    public function draw(Group $group)
    {
        // 1. Segurança: Apenas o dono pode sortear
        if (auth()->id() !== $group->owner_id) {
            abort(403, 'Apenas o administrador pode realizar o sorteio.');
        }

        // 2. Segurança: Verificar se já foi sorteado
        if ($group->is_drawn) {
            return back()->with('error', 'O sorteio já foi realizado!');
        }

        // 3. Segurança: Mínimo de 3 participantes (para ter graça, mas 2 funciona tecnicamente)
        $members = $group->members;
        if ($members->count() < 2) {
            return back()->with('error', 'É preciso ter pelo menos 2 participantes.');
        }

        // 4. O Algoritmo
        // Usamos uma Transaction para garantir que ou salva tudo ou não salva nada
        DB::transaction(function () use ($group, $members) {

            // Embaralha a coleção de membros aleatoriamente
            $shuffled = $members->shuffle();

            // Cria os pares
            $count = $shuffled->count();

            for ($i = 0; $i < $count; $i++) {
                $santa = $shuffled[$i];

                // O presenteado é o próximo da lista (ou o primeiro, se for o último item)
                $gifteeIndex = ($i + 1) % $count;
                $giftee = $shuffled[$gifteeIndex];

                Pairing::create([
                    'group_id' => $group->id,
                    'santa_id' => $santa->id,
                    'giftee_id' => $giftee->id,
                ]);
            }

            // Atualiza o status do grupo
            $group->update(['is_drawn' => true]);
        });

        return back()->with('success', 'Sorteio realizado com sucesso! Todos já podem ver seus pares.');
    }

    public function updateWishlist(Request $request, Group $group)
    {
        // BLOQUEIO DE SEGURANÇA NOVO
        if ($group->is_drawn) {
            return back()->with('error', 'O sorteio já foi realizado! Não é possível alterar o desejo.');
        }

        $request->validate(['wishlist' => 'nullable|string|max:1000']);

        $group->members()->updateExistingPivot(Auth::id(), [
            'wishlist' => $request->wishlist
        ]);

        return back()->with('success', 'Sua lista de desejos foi atualizada!');
    }

    public function destroy(Group $group)
    {
        // Segurança: Só o dono pode apagar
        if (auth()->id() !== $group->owner_id) {
            abort(403, 'Ação não autorizada');
        }

        $group->delete(); // O banco já está configurado com cascade, vai apagar tudo ligado ao grupo

        return redirect()->route('dashboard')->with('success', 'Grupo excluído com sucesso!');
    }
}
