<?php

namespace App\Services;

use App\Data\TemplateData;
use App\Models\Template;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TemplateService
{
    public function save(TemplateData $data, ?Template $template = null, ?User $author = null): Template
    {
        return DB::transaction(function () use ($data, $template, $author) {
            $template ??= new Template(['created_by' => $author?->id]);
            $template->fill([
                'name' => $data->name,
                'description' => $data->description,
                'default_title' => $data->defaultTitle,
                'is_active' => $data->isActive,
            ])->save();

            // Las secciones se reemplazan completas (los informes ya creados no se ven afectados).
            $template->sections()->delete();
            foreach ($data->sections as $i => $section) {
                $template->sections()->create($section->toArray($i + 1));
            }

            return $template->fresh('sections');
        });
    }

    public function duplicate(Template $template, User $author): Template
    {
        return DB::transaction(function () use ($template, $author) {
            $copy = $template->replicate(['created_by']);
            $copy->name = $template->name.' (copia)';
            $copy->created_by = $author->id;
            $copy->save();

            foreach ($template->sections as $section) {
                $copy->sections()->create($section->only(['position', 'title', 'description', 'type', 'content']));
            }

            return $copy;
        });
    }

    public function draft(?Template $template): array
    {
        if (! $template) {
            return [
                'form' => ['name' => '', 'description' => '', 'default_title' => '', 'is_active' => true],
                'sections' => [],
            ];
        }

        $template->loadMissing('sections');

        return [
            'form' => [
                'name' => $template->name,
                'description' => $template->description ?? '',
                'default_title' => $template->default_title ?? '',
                'is_active' => $template->is_active,
            ],
            'sections' => $template->sections->map(fn ($s) => ReportService::draftSection($s->title, $s->description, $s->type->value, $s->content ?? []))->all(),
        ];
    }
}
