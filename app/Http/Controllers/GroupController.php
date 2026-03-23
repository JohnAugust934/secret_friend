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
        Gate::authorize('view', $group);

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

        if (! $group->members()->where('user_id', Auth::id())->exists()) {
            $group->members()->attach(Auth::id(), ['wishlist' => $request->wishlist]);
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

                if ($lockedGroup->is_drawn) {
                    return false;
                }

                if ($lockedGroup->members()->count() < 3) {
                    throw new \RuntimeException('E preciso ter pelo menos 3 participantes para usar restricoes com seguranca.');
                }

                $drawService->draw($lockedGroup);

                return true;
            });

            if (! $drawExecuted) {
                return back()->with('error', 'O sorteio ja foi realizado!');
            }
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        $pairings = Pairing::where('group_id', $group->id)->with(['santa', 'giftee'])->get();
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

        if ($mailErrors > 0) {
            return back()->with(
                'warning',
                'Sorteio concluido com sucesso, mas tivemos falha ao enfileirar '.$mailErrors.' notificacao(oes).'
            );
        }

        return back()->with('success', 'Sorteio realizado! Os e-mails estao sendo enviados em segundo plano.');
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
                'group_id' => $group->id,
                'user_id' => $request->user_id,
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
                $html = view('groups.partials.members-list', compact('group'))->render();
                $payload = [
                    'html' => $html,
                    'count' => $group->members->count(),
                    'options' => $group->members->map(fn ($member) => [
                        'id' => $member->id,
                        'name' => $member->name,
                    ])->values(),
                ];

                echo "event: members\n";
                echo 'data: '.json_encode($payload, JSON_UNESCAPED_UNICODE)."\n\n";
                @ob_flush();
                @flush();

                $iterations++;
                sleep(5);
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }
}
