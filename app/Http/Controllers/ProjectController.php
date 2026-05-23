<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Project;
use App\Models\User;

class ProjectController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $loadProjects = $this->loadProjects($user);
        $canEdit = $this->resolvePermissions($loadProjects['isAdmin']);

        return view('projects.index', [
            'canCreateProject' => $canEdit['canCreateProject'], // Controllo sul ruolo utente per la modifica da frontend
            'projects'         => $loadProjects['projects'],
            'archivedProjects' => $loadProjects['archivedProjects'],
        ]);
    }

    public function show(Project $project)
    {
        $user = auth()->user();
        $isArchived = $project->archived_at !== null; // assegna isArchived solo se è diverso da null

        $loadProjects = $this->loadProjects($user);
        $canEdit = $this->resolvePermissions($loadProjects['isAdmin'], $project, $user);

        $canManageProject = $canEdit['isPm'];

        if ($isArchived) {
            $canEdit['isPm']    = false;
            $canEdit['canEdit'] = false;
        }

        $columns     = $project->columns;
        $tasks   = $project->tasks()->with(['assignees', 'labels', 'subtasks'])->get();
        $labels  = $project->labels;

        $members = $project->members()->get()->map(function ($m) { // Itera il risultato della collection
            $arr = $m->toArray(); // Trasforma il singolo user in un array
            $arr['pivot_role'] = $m->pivot->role; // Aggiunge il campo pivot_role con il ruolo del membro
            return $arr;
        });

        $activity = $project->activityLogs()->with('user')->get()->map(function ($log) { // Itera i log e gli user per ogni log (relazione)
            $arr = $log->toArray();
            $arr['user_name'] = $log->user?->name;
            return $arr;
        });

        return view('projects.show', [
            'project'          => $project,
            'columns'          => $columns,
            'tasks'            => $tasks,
            'isPm'             => (bool)$canEdit['isPm'], // Controllo sul ruolo utente PM
            'canEdit'          => $canEdit['canEdit'], // controllo ulteriore se il ruolo di sistema è admin
            'isArchived'       => $isArchived,
            'members'          => $members,
            'labels'           => $labels,
            'activity'         => $activity,
            'canManageProject' => $canManageProject,
            // Variabili per la sidebar
            'archivedProjects' => $loadProjects['archivedProjects'],
            'projects'  => $loadProjects['projects'],
            'currentProject'   => $project,
        ]);
    }

    public function store(StoreProjectRequest $request)
    {
        $project = Project::create($request->validated());
        $project->members()->attach($request->user()->id, ['role' => 'pm']); // Imposta l'utente che crea il progetto come PM
        $project->labels()->createMany($this->systemLabels());  // genera le etichette di sisteman, createMany itera ogni elemento dell'array

        return redirect()->route('projects.index')->with('success', 'Progetto creato con successo!');
    }

    public function update(UpdateProjectRequest $request, Project $project)
    {
        if ($project->archived_at) {
            return back()->with('error', 'Progetti archiviati non possono essere modificati.');
        }

        $project->update($request->validated());

        return redirect()->route('projects.show', $project)->with('success', 'Progetto aggiornato con successo!');
    }

    public function archive(Project $project)
    {
        $project->archived_at = date('Y-m-d H:i:s');
        $project->save();
        return redirect()->route('projects.show', $project)->with('success', 'Progetto archiviato con successo!.');
    }

    public function unarchive(Project $project)
    {
        $project->archived_at = null;
        $project->save();
        return redirect()->route('projects.show', $project)->with('success', 'Progetto ripristinato con successo!.');
    }

    public function destroy(Project $project)
    {
        $project->delete();
        return redirect()->route('projects.index')->with('success', 'Progetto eliminato con successo!');
    }

    /**
     * Funzioni helper interni (privati)
     */

    private function loadProjects(User $user){
        if ($user->isAdmin()){
            $projects = Project::active()->get();
            $archivedProjects = Project::archived()->get();
            $isAdmin = true;
        }else{
            $projects = $user->projects()
                ->active()
                ->whereNull('deleted_at') // Mostra i progetti per gli utenti non eliminati
                ->orderBy('updated_at')
                ->get();
            $archivedProjects = $user->projects()
                ->archived()
                ->whereNull('deleted_at')
                ->orderBy('updated_at')
                ->get();
            $isAdmin = false;
        }

        return [
            'projects' => $projects,
            'archivedProjects' => $archivedProjects,
            'isAdmin' => $isAdmin
        ];
    }

    private function resolvePermissions(bool $isAdmin, Project $project=null, User $user=null){
        if ($isAdmin){
            $canCreateProject = true;
            $isPm = true;
            $canEdit = true;
        } else{
            $canCreateProject = false;
            if ($project !== null && $user !== null){
                $role = $project->members()
                    ->where('user_id', $user->id)
                    ->first()
                    ?->pivot->role; // Controlla dalla tabella pivot con role se esiste effettivamente il ruolo

                if ($role === null) {
                    abort(403, 'Non sei membro di questo progetto.');
                }

                $isPm = $role === 'pm';
                $canEdit = in_array($role, ['pm', 'developer'], true);
            }
        }

        return[
            'canCreateProject' => $canCreateProject,
            'isPm' => $isPm ?? null,
            'canEdit' => $canEdit ?? null,
        ];
    }

    private function systemLabels(){
        return [
            ['name' => 'DIAGRAMMA',    'color' => '#6366f1', 'is_system' => true],
            ['name' => 'ANALISI',      'color' => '#8b5cf6', 'is_system' => true],
            ['name' => 'BACKEND',      'color' => '#3b82f6', 'is_system' => true],
            ['name' => 'FRONTEND',     'color' => '#ec4899', 'is_system' => true],
            ['name' => 'DOCUMENTAZIONE','color' => '#f97316', 'is_system' => true],
            ['name' => 'TEST',         'color' => '#22c55e', 'is_system' => true],
        ];
    }
}
