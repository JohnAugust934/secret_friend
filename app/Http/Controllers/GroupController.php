<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExclusionRequest;
use App\Http\Requests\StoreGroupRequest;
use App\Http\Requests\UpdateGroupRequest;
use App\Mail\DrawResult;
use App\Models\Exclusion;
use App\Models\Group;
use App\Models\GroupMember;
use App\Models\Pairing;
use App\Models\User;
use App\Services\DrawService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GroupController extends Controller
{
    public function create()
    {
        return view('groups.create');
    }

    public function store(StoreGroupRequest $request)
    {
        $validated = $request->validated();

        // SEGURANÇA: forceCreate() é intencional aqui — owner_id e invite_token são
        // definidos PROGRAMATICAMENTE (nunca pelo usuário), por isso não pertencem
        // ao $fillable. forceCreate() é o padrão do Laravel para esse cenário.
        $group = Group::forceCreate([
            'name'         => $validated['name'],
            'event_date'   => $validated['event_date'],
            'budget'       => $validated['budget'] ?? null,
            'budget_limit' => $validated['budget_limit'] ?? null,
            'location'     => $validated['location'] ?? null,
            'description'  => $validated['description'] ?? null,
            'owner_id'     => Auth::id(),
            'invite_token' => Str::upper(Str::random(6)),
        ]);

        $group->members()->attach(Auth::id(), ['wishlist' => $validated['wishlist'] ?? null]);

        return redirect()->route('groups.show', $group);
    }

    public function show(Group $group)
    {
        Gate::authorize('view', $group);

        // SEGURANÇA/PERFORMANCE: 'owner' adicionado ao eager load para evitar
        // N+1 query quando a view acessa $group->owner->name.
        $group->load(['owner', 'members', 'exclusions.participant', 'exclusions.excluded']);

        $myPair    = null;
        $drawRound = null;

        if ($group->is_drawn) {
            // Obtém o round mais recente para mostrar o sorteio actual
            $drawRound = Pairing::where('group_id', $group->id)->max('draw_round') ?? 1;

            $myPair = Pairing::where('group_id', $group->id)
                ->where('santa_id', Auth::id())
                ->where('draw_round', $drawRound)
                ->with('giftee')
                ->first();
        }

        return view('groups.show', compact('group', 'myPair', 'drawRound'));
    }

    public function join($token)
    {
        $group = Group::where('invite_token', $token)->firstOrFail();

        // SEGURANÇA/UX: Se o sorteio já foi realizado, o link de convite é invalidado.
        // Novos membros não podem entrar num grupo já sorteado.
        if ($group->is_drawn) {
            return view('groups.draw-closed', compact('group'));
        }

        if (! Auth::check()) {
            session(['invite_token' => $token]);

            return view('groups.invite-landing', compact('group'));
        }

        if ($group->members->contains(Auth::id())) {
            return redirect()->route('groups.show', $group)->with('info', 'Voce ja participa deste grupo!');
        }

        return view('groups.join', compact('group'));
    }

    public function joinStore(Request $request, $token)
    {
        if (! Auth::check()) {
            return redirect()->route('login', ['invite_token' => $token]);
        }

        $group = Group::where('invite_token', $token)->firstOrFail();

        // SEGURANÇA: Bloqueia entrada após o sorteio — mesmo via POST directo.
        if ($group->is_drawn) {
            return redirect()->route('groups.show', $group)
                ->with('error', 'Este grupo já foi sorteado. Não é possível entrar agora.');
        }

        if (! $group->members()->where('user_id', Auth::id())->exists()) {
            $group->members()->attach(Auth::id(), ['wishlist' => $request->wishlist]);
            // SEGURANÇA/CACHE: Invalida o cache de HTML de membros imediatamente
            // após um novo membro entrar, evitando exibir lista desatualizada.
            Cache::forget("group_members_html_{$group->id}");
        }

        $request->session()->forget('invite_token');

        return redirect()->route('groups.show', $group)
            ->with('success', 'Voce entrou no grupo com sucesso!');
    }

    public function draw(Group $group, DrawService $drawService)
    {
        Gate::authorize('draw', $group);

        try {
            $drawExecuted = DB::transaction(function () use ($group, $drawService) {
                /** @var Group|null $lockedGroup */
                $lockedGroup = Group::query()->whereKey($group->id)->lockForUpdate()->first();

                if (! $lockedGroup) {
                    throw new \RuntimeException('Grupo nao encontrado.');
                }

                if ($lockedGroup->members()->count() < 3) {
                    throw new \RuntimeException('E preciso ter pelo menos 3 participantes para usar restricoes com seguranca.');
                }

                // Determina o round anterior para histórico e prevenção de repetição.
                // Se nunca houve sorteio, lastRound = 0 (sem histórico a consultar).
                $lastRound = Pairing::where('group_id', $lockedGroup->id)->max('draw_round') ?? 0;

                // Carrega relações necessárias para o DrawService
                $lockedGroup->load(['members', 'exclusions']);

                $drawService->draw($lockedGroup, $lastRound);

                DB::table('draw_audits')->insert([
                    'group_id'   => $lockedGroup->id,
                    'user_id'    => Auth::id(),
                    'ip_address' => request()->ip(),
                    'draw_round' => $lastRound + 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                return true;
            });

            if (! $drawExecuted) {
                return back()->with('error', 'Ocorreu um erro inesperado.');
            }
        } catch (\Exception $e) {
            // SEGURANÇA: Nunca expõe getMessage() diretamente ao usuário —
            // pode conter nomes de tabelas, paths internos ou stack traces.
            // Apenas RuntimeException de domínio (negócio) exibe mensagem ao usuário.
            report($e);

            $safeMessage = $e instanceof \RuntimeException
                ? $e->getMessage()
                : 'Ocorreu um erro ao realizar o sorteio. Por favor, tente novamente.';

            return back()->with('error', $safeMessage);
        }

        // Notifica todos os santás por email (round mais recente)
        $latestRound = Pairing::where('group_id', $group->id)->max('draw_round') ?? 1;
        $pairings    = Pairing::where('group_id', $group->id)
            ->where('draw_round', $latestRound)
            ->with(['santa', 'giftee'])
            ->get();

        $mailErrors = 0;

        foreach ($pairings as $pair) {
            if (! $pair->santa || ! $pair->santa->email) {
                continue;
            }

            try {
                Mail::to($pair->santa->email)->queue(new DrawResult($group, $pair->santa, $pair->giftee));
            } catch (\Throwable $e) {
                report($e);
                $mailErrors++;
            }
        }

        $latestRound = Pairing::where('group_id', $group->id)->max('draw_round') ?? 1;
        $isRedraw    = $latestRound > 1;

        $successMsg = $isRedraw
            ? "Re-sorteio #{$latestRound} realizado! Os e-mails estao sendo enviados em segundo plano."
            : 'Sorteio realizado! Os e-mails estao sendo enviados em segundo plano.';

        if ($mailErrors > 0) {
            return back()->with(
                'warning',
                ($isRedraw ? "Re-sorteio #{$latestRound}" : 'Sorteio') . " concluido com sucesso, mas tivemos falha ao enfileirar {$mailErrors} notificacao(oes)."
            );
        }

        return back()->with('success', $successMsg);
    }

    public function storeExclusion(StoreExclusionRequest $request, Group $group)
    {
        if ($group->is_drawn) {
            return back()->with('error', 'Sorteio ja realizado.');
        }

        $exists = Exclusion::where('group_id', $group->id)
            ->where('user_id', $request->user_id)
            ->where('excluded_id', $request->excluded_id)
            ->exists();

        if (! $exists) {
            Exclusion::create([
                'group_id'    => $group->id,
                'user_id'     => $request->user_id,
                'excluded_id' => $request->excluded_id,
            ]);
        }

        return back()->with('success', 'Restricao adicionada.');
    }

    public function destroyExclusion(Group $group, Exclusion $exclusion)
    {
        Gate::authorize('manageExclusions', $group);

        if ($group->is_drawn) {
            return back()->with('error', 'Sorteio ja realizado.');
        }

        if ($exclusion->group_id !== $group->id) {
            abort(403, 'Acao invalida.');
        }

        $exclusion->delete();

        return back()->with('success', 'Restricao removida.');
    }

    public function updateWishlist(Request $request, Group $group)
    {
        Gate::authorize('view', $group);

        if ($group->is_drawn) {
            return back()->with('error', 'O sorteio ja foi realizado! Nao e possivel alterar o desejo.');
        }

        $request->validate(['wishlist' => 'nullable|string|max:1000']);

        $memberPivot = GroupMember::where('group_id', $group->id)
            ->where('user_id', Auth::id())
            ->first();

        if (! $memberPivot) {
            return back()->with('error', 'Voce nao participa deste grupo.');
        }

        $memberPivot->update(['wishlist' => $request->wishlist]);

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

        return redirect()->route('groups.show', $group)->with('success', 'Informacoes do grupo atualizadas com sucesso!');
    }

    public function destroy(Group $group)
    {
        Gate::authorize('delete', $group);

        $group->delete();

        return redirect()->route('dashboard')->with('success', 'Grupo excluido com sucesso!');
    }

    public function removeMember(Group $group, User $user)
    {
        Gate::authorize('removeMember', $group);

        if ($group->is_drawn) {
            return back()->with('error', 'Nao e possivel remover membros apos o sorteio.');
        }

        if ($user->id === $group->owner_id) {
            return back()->with('error', 'O administrador nao pode ser removido.');
        }

        $pivot = GroupMember::where('group_id', $group->id)
            ->where('user_id', $user->id)
            ->first();

        if ($pivot) {
            $pivot->delete();
            // SEGURANÇA/CACHE: Invalida os caches de membros imediatamente após
            // a remoção, evitando exibição de lista desatualizada por até 5s.
            Cache::forget("group_members_html_{$group->id}");
            Cache::forget("group_member_check_{$group->id}_{$user->id}");
        }

        return back()->with('success', "{$user->name} foi removido do grupo.");
    }

    public function membersList(Group $group)
    {
        $userId = Auth::id();

        $isMember = Cache::remember("group_member_check_{$group->id}_{$userId}", 30, function () use ($group, $userId) {
            return $group->members()->where('user_id', $userId)->exists();
        });

        if (! $isMember) {
            abort(403);
        }

        $html = Cache::remember("group_members_html_{$group->id}", 5, function () use ($group) {
            $group->load('members');

            return view('groups.partials.members-list', compact('group'))->render();
        });

        return $html;
    }

    public function membersStream(Group $group): StreamedResponse
    {
        Gate::authorize('view', $group);

        return response()->stream(function () use ($group) {
            $iterations = 0;

            while (! connection_aborted() && $iterations < 12) {
                $group->load('members');
                $html    = view('groups.partials.members-list', compact('group'))->render();
                $payload = [
                    'html'    => $html,
                    'count'   => $group->members->count(),
                    'options' => $group->members->map(fn ($member) => [
                        'id'   => $member->id,
                        'name' => $member->name,
                    ])->values(),
                ];

                echo "event: members\n";
                echo 'data: ' . json_encode($payload, JSON_UNESCAPED_UNICODE) . "\n\n";
                @ob_flush();
                @flush();

                $iterations++;
                sleep(5);
            }
        }, 200, [
            'Content-Type'      => 'text/event-stream',
            'Cache-Control'     => 'no-cache, no-store, must-revalidate',
            'Connection'        => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }
}
