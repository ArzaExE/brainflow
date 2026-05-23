<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectMemberRequest;
use App\Http\Requests\UpdateProjectMemberRequest;
use App\Models\Project;
use App\Models\ProjectUser;
use App\Models\User;

class MemberController extends Controller
{
    public function store(StoreProjectMemberRequest $request, Project $project)
    {
        $validated = $request->validated();
        $user = User::where('id', $validated['user_id'])->first();

        // Controlla se l'utente del progetto associato alla tabella pivot già esista
        if ($project->members()->wherePivot('user_id', $user->id)->exists()) {
            return response()->json(
                ['errors' => ['user_id' => ['Questo utente è già membro del progetto.']]],
                422
            );
        }

        $member = ProjectUser::withTrashed() //withTrashed serve per non escludere automaticamente i membri eliminati con il soft delete
            ->where('project_id', $project->id)
            ->where('user_id', $user->id)
            ->whereNotNull('deleted_at')
            ->first();

        if ($member) {
            $member->restore(); // Riaggiunge l'utente che era già stato assegnato al progetto
            $member->update([
                'role' => $validated['role'],
            ]);

        } else{
            $project->members()->attach($validated['user_id'], [ // Si usa attach e non create perchè la relazione è belongsToMany
                'role' => $validated['role'],
            ]);
        }

        $member = $project->members()
            ->wherePivot('user_id', $user->id)
            ->first();

        $emailSent = true;
        try {
            $user->sendInviteNotification($project, auth()->user());
        } catch (\Throwable $th) {
            $emailSent = false;
            report($th); // logga l'errore
        }

        return response()->json([
            'member'     => $member,
            'email_sent' => $emailSent,
        ], 201);
    }

    public function update(UpdateProjectMemberRequest $request, Project $project, User $user)
    {
        $validated = $request->validated();

        if ($validated['role'] !== 'pm') {
            $checkPm = $project->members()
                ->wherePivot('role', 'pm')
                ->whereNotIn('user_id', [$user->id])
                ->get();
            if ($checkPm->isEmpty()) {
                return response()->json(['message' => 'Non puoi cambiare il ruolo all\'ultimo utente PM.'], 422);
            }
        }

        // Aggiorna la tabella pivot con il nuovo ruolo
        $project->members()->updateExistingPivot($user->id, ['role' => $validated['role']]);
        return response()->json(['ok' => true]);
    }

    public function destroy(Project $project, User $user)
    {
        if (!auth()->user()->isAdmin() && $user->id === auth()->id()){
            return response()->json(['message' => 'Non puoi eliminare te stesso.'], 422);
        }

        if ($project->members()->count() === 1){
            return response()->json(['message' => 'Il progetto deve avere almeno un utente.'], 422);
        }

        $assignedTitles = $project->tasks() // Seleziona il titolo per ogni task assegnata all'utente
            ->whereHas('assignees', fn ($q) => $q->where('users.id', $user->id))
            ->select('title')->get();

        if ($assignedTitles->isNotEmpty()) {
            $shown = $assignedTitles->take(5);
            $extra = $assignedTitles->count() - $shown->count();

            $msg = '';

            foreach ($shown as $title){
                $msg .= $title->title . ', ';
            }
            $msg = rtrim($msg, ', ');

            if ($extra > 0) {
                $msg .= " e altre {$extra}";
            }

            return response()->json([
                'message' => 'Impossibile rimuovere il membro: è ancora assegnato a '
                    . $assignedTitles->count() . ' task ('
                    . $msg . '). '
                    . 'Riassegna o rimuovi quelle task prima di procedere.',
            ], 422);
        }

        $project->members()->detach($user); // rimuove l'utente dal progetto, si usa detach per la relazione belongsToMany

        return response()->json(['ok' => true]);
    }

    // Collezione di utenti non assegnati a un progetto
    public function availableUsers(Project $project){
        $projectUsersIds = $project->members()->select('user_id');
        $available = User::whereNotIn('id', $projectUsersIds)->get(['id', 'name', 'email']);
        return response()->json($available);
    }
}
