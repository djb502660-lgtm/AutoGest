<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\VehicleModelTemplate;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VehicleModelTemplateController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', VehicleModelTemplate::class);

        $search = $request->string('search')->trim();
        $type = $request->string('type')->toString();
        $state = $request->string('state')->toString();

        $templates = VehicleModelTemplate::query()
            ->when($search->isNotEmpty(), function ($q) use ($search) {
                $q->where(function ($query) use ($search) {
                    $query->where('brand', 'like', "%{$search}%")
                        ->orWhere('model', 'like', "%{$search}%")
                        ->orWhere('title', 'like', "%{$search}%");
                });
            })
            ->when($type !== '', fn ($q) => $q->where('maintenance_type', $type))
            ->when($state !== '', fn ($q) => $q->where('is_active', $state === 'active'))
            ->orderBy('brand')
            ->orderBy('model')
            ->orderBy('sort_order')
            ->paginate(12)
            ->withQueryString();

        return view('admin.model-templates.index', compact('templates', 'search', 'type', 'state'));
    }

    public function create()
    {
        $this->authorize('create', VehicleModelTemplate::class);

        return view('admin.model-templates.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', VehicleModelTemplate::class);

        $template = VehicleModelTemplate::create($this->validateTemplate($request));

        ActivityLog::record(
            'model_template.created',
            "Plantilla {$template->brand} {$template->model} — {$template->title} registrada.",
            $template,
        );

        return redirect()
            ->route('model-templates.index')
            ->with('success', 'Plantilla de mantenimiento registrada.');
    }

    public function edit(VehicleModelTemplate $model_template)
    {
        $this->authorize('update', $model_template);

        return view('admin.model-templates.edit', ['template' => $model_template]);
    }

    public function update(Request $request, VehicleModelTemplate $model_template)
    {
        $this->authorize('update', $model_template);

        $model_template->update($this->validateTemplate($request));

        ActivityLog::record(
            'model_template.updated',
            "Plantilla {$model_template->brand} {$model_template->model} actualizada.",
            $model_template,
        );

        return redirect()
            ->route('model-templates.index')
            ->with('success', 'Plantilla actualizada correctamente.');
    }

    public function destroy(VehicleModelTemplate $model_template)
    {
        $this->authorize('delete', $model_template);

        $label = "{$model_template->brand} {$model_template->model} — {$model_template->title}";
        $model_template->delete();

        ActivityLog::record('model_template.deleted', "Plantilla eliminada: {$label}.");

        return redirect()
            ->route('model-templates.index')
            ->with('success', 'Plantilla eliminada.');
    }

    private function validateTemplate(Request $request): array
    {
        return $request->validate([
            'brand' => ['required', 'string', 'max:80'],
            'model' => ['required', 'string', 'max:80'],
            'maintenance_type' => ['required', Rule::in(['preventivo', 'correctivo'])],
            'title' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
            'interval_km' => ['nullable', 'integer', 'min:1000', 'max:100000'],
            'interval_months' => ['nullable', 'integer', 'min:1', 'max:60'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
        ]) + [
            'is_active' => $request->boolean('is_active'),
            'sort_order' => (int) $request->input('sort_order', 0),
        ];
    }
}
