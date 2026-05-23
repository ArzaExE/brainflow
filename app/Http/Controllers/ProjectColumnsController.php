<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProjectColumnPositionRequest;
use App\Http\Requests\StoreProjectColumnRequest;
use App\Models\ColumnType;
use App\Models\Project;
use App\Models\ProjectColumn;

class ProjectColumnsController extends Controller
{
    public function store(StoreProjectColumnRequest $request, Project $project)
    {

        $validated = $request->validated();

        $column_type_id = intval($validated['column_type_id']);

        if ($project->columns()->where('column_type_id', $column_type_id)->exists()) {
            return response()->json(['errors' => 'Questo tipo di colonna è già presente nel progetto.'], 422);
        }

        $type = ColumnType::select('name', 'is_done')->where('id', $column_type_id)->first();

        $column = $project->columns()->create([
            'column_type_id' => $column_type_id,
            'name' => $type->name,
            'position' => ($project->columns()->max('position') ?? 0) + 1, // Prende la posizione dell'ultima colonna e la incrementa
            'is_done' => $type->is_done,
        ]);
        return response()->json($column->load('columnType')); // Restituisce la colonna con la relazione del tipo di colonna caricata
    }

    // Mostra solo le colonne non ancora usate
    public function availableTypes(Project $project)
    {
        $usedTypeIds = $project->columns()->select('column_type_id');
        $available = ColumnType::whereNotIn('id', $usedTypeIds)->get();
        return response()->json($available);
    }

    // Gestione dello spostamento delle task
    public function reorder(UpdateProjectColumnPositionRequest $request, Project $project)
    {
        $validated = $request->validated();

        foreach ($validated['columns'] as $position => $columnId) { // chiave = posizione, valore = ID colonna
            $project->columns()->where('id', $columnId)->update(['position' => $position]);
        }

        return response()->json(['ok' => true]);
    }

    public function destroy(Project $project, ProjectColumn $column)
    {
        if (!$project->columns()->where('id', $column->id)->exists()) {
            return response()->json(['message' => 'La colonna non appartiene al progetto.'], 422);
        }

        $column->delete();
        return response()->json(['ok' => true]);
    }

}
