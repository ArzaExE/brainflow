<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectLabelRequest;
use App\Http\Requests\UpdateProjectLabelRequest;
use App\Models\Label;
use App\Models\Project;

class LabelController extends Controller
{
    public function store(StoreProjectLabelRequest $request, Project $project)
    {
        $validated = $request->validated();

        $label = $project->labels()->create([
            'name' => strtoupper($validated['name']),
            'color' => $validated['color'],
        ]);

        return response()->json($label);
    }

    public function update(UpdateProjectLabelRequest $request, Project $project, Label $label)
    {
        $validated = $request->validated();

        if ($label->is_system) {
            return response()->json(['message' => 'Le etichette di sistema non possono essere modificate.'], 422);
        }

        if (!$project->labels()->where('id', $label->id)->exists()) {
            return response()->json(['message' => 'L\'etichetta non appartiene al progetto.'], 422);
        }

        $label->update([
            'color' => $validated['color'],
        ]);

        return response()->json($label);

    }

    public function destroy(Project $project, Label $label)
    {
        if ($label->is_system) {
            return response()->json(['message' => 'Le etichette di sistema non possono essere modificate.'], 422);
        }

        if (!$project->labels()->where('id', $label->id)->exists()) {
            return response()->json(['message' => 'L\'etichetta non appartiene al progetto.'], 422);
        }

        $label->delete();
        return response()->json(['ok' => true]);
    }
}
