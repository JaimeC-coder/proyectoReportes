<?php

namespace App\Livewire\Concerns;

use App\Enums\SectionType;
use App\Services\ImageService;
use App\Services\ReportService;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\WithFileUploads;

/**
 * Lógica compartida del constructor de secciones (plantillas e informes).
 *
 * $sections se guarda como arreglo asociativo [uid => sección] para que las
 * rutas de wire:model / entangle no cambien al reordenar.
 */
trait ManagesSections
{
    use WithFileUploads;

    /** @var array<string, array{uid:string,title:string,description:string,type:string,content:array}> */
    public array $sections = [];

    // Modal "Agregar sección"
    public bool $showAddModal = false;
    public ?string $newType = null;
    public string $newTitle = '';
    public string $newDescription = '';
    public ?string $insertAfter = null;

    // Subidas de archivos
    public $editorImage;
    public array $galleryUploads = [];

    /** Convierte una lista de secciones en el arreglo indexado por uid. */
    protected function keyedSections(array $list): array
    {
        return collect($list)->mapWithKeys(fn ($s) => [$s['uid'] => $s])->all();
    }

    /* ----------------------------------------------------------------
     |  Agregar / quitar / ordenar secciones
     * ---------------------------------------------------------------- */

    public function openAddModal(?string $after = null): void
    {
        $this->resetErrorBag(['newType', 'newTitle']);
        $this->newType = null;
        $this->newTitle = '';
        $this->newDescription = '';
        $this->insertAfter = $after;
        $this->showAddModal = true;
    }

    public function selectType(string $type): void
    {
        $this->newType = SectionType::tryFrom($type)?->value;
    }

    public function addSection(): void
    {
        $this->validate([
            'newType' => ['required', Rule::enum(SectionType::class)],
            'newTitle' => ['required', 'string', 'max:150'],
            'newDescription' => ['nullable', 'string', 'max:1000'],
        ], [
            'newType.required' => 'Selecciona el tipo de bloque.',
        ], ['newTitle' => 'subtítulo', 'newDescription' => 'descripción']);

        $type = SectionType::from($this->newType);
        $section = ReportService::draftSection($this->newTitle, $this->newDescription, $type->value, $type->emptyContent());

        $this->sections = $this->insertSection($section, $this->insertAfter);
        $this->showAddModal = false;
        $this->dispatch('toast', icon: 'success', title: 'Sección agregada');
        $this->dispatch('section-added', uid: $section['uid']);
    }

    protected function insertSection(array $section, ?string $after): array
    {
        if (! $after || ! isset($this->sections[$after])) {
            return $this->sections + [$section['uid'] => $section];
        }

        $result = [];
        foreach ($this->sections as $uid => $s) {
            $result[$uid] = $s;
            if ($uid === $after) {
                $result[$section['uid']] = $section;
            }
        }

        return $result;
    }

    public function removeSection(string $uid): void
    {
        unset($this->sections[$uid]);
        $this->dispatch('toast', icon: 'success', title: 'Sección eliminada');
    }

    public function duplicateSection(string $uid): void
    {
        if (! isset($this->sections[$uid])) {
            return;
        }
        $copy = $this->sections[$uid];
        $copy['uid'] = (string) Str::ulid();
        $copy['title'] .= ' (copia)';
        $this->sections = $this->insertSection($copy, $uid);
    }

    public function moveSection(string $uid, int $direction): void
    {
        $keys = array_keys($this->sections);
        $index = array_search($uid, $keys, true);
        $target = $index + $direction;

        if ($index === false || $target < 0 || $target >= count($keys)) {
            return;
        }

        [$keys[$index], $keys[$target]] = [$keys[$target], $keys[$index]];
        $this->sections = collect($keys)->mapWithKeys(fn ($k) => [$k => $this->sections[$k]])->all();
    }

    /* ----------------------------------------------------------------
     |  Campos clave-valor
     * ---------------------------------------------------------------- */

    public function addKvItem(string $uid): void
    {
        $this->sections[$uid]['content']['items'][] = ['label' => '', 'value' => ''];
    }

    public function removeKvItem(string $uid, int $i): void
    {
        $items = $this->sections[$uid]['content']['items'] ?? [];
        unset($items[$i]);
        $this->sections[$uid]['content']['items'] = array_values($items);
    }

    /* ----------------------------------------------------------------
     |  Tabla
     * ---------------------------------------------------------------- */

    public function addTableColumn(string $uid): void
    {
        $content = &$this->sections[$uid]['content'];
        if (count($content['columns'] ?? []) >= 8) {
            $this->dispatch('toast', icon: 'warning', title: 'Máximo 8 columnas');

            return;
        }
        $content['columns'][] = 'Columna '.(count($content['columns'] ?? []) + 1);
        foreach ($content['rows'] as &$row) {
            $row[] = '';
        }
    }

    public function removeTableColumn(string $uid, int $col): void
    {
        $content = &$this->sections[$uid]['content'];
        if (count($content['columns']) <= 1) {
            return;
        }
        array_splice($content['columns'], $col, 1);
        foreach ($content['rows'] as &$row) {
            array_splice($row, $col, 1);
        }
    }

    public function addTableRow(string $uid): void
    {
        $cols = count($this->sections[$uid]['content']['columns'] ?? []);
        $this->sections[$uid]['content']['rows'][] = array_fill(0, max($cols, 1), '');
    }

    public function removeTableRow(string $uid, int $row): void
    {
        $rows = $this->sections[$uid]['content']['rows'] ?? [];
        unset($rows[$row]);
        $this->sections[$uid]['content']['rows'] = array_values($rows);
    }

    /* ----------------------------------------------------------------
     |  Galería de imágenes
     * ---------------------------------------------------------------- */

    public function updatedGalleryUploads($value, $key): void
    {
        $uid = Str::before((string) $key, '.');
        $files = Arr::wrap($this->galleryUploads[$uid] ?? []);

        $this->validate([
            "galleryUploads.{$uid}" => ['array', 'max:20'],
            "galleryUploads.{$uid}.*" => ['image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ], [], ["galleryUploads.{$uid}.*" => 'imagen']);

        $service = app(ImageService::class);
        foreach ($files as $file) {
            $this->sections[$uid]['content']['items'][] = [
                'path' => $service->store($file, 'uploads/gallery'),
                'title' => '',
                'caption' => '',
            ];
        }

        unset($this->galleryUploads[$uid]);
        $this->dispatch('toast', icon: 'success', title: count($files) === 1 ? 'Imagen agregada' : count($files).' imágenes agregadas');
    }

    public function addGalleryItem(string $uid): void
    {
        $this->sections[$uid]['content']['items'][] = ['path' => null, 'title' => '', 'caption' => ''];
    }

    public function removeGalleryItem(string $uid, int $i): void
    {
        $items = $this->sections[$uid]['content']['items'] ?? [];
        unset($items[$i]);
        $this->sections[$uid]['content']['items'] = array_values($items);
    }

    public function removeGalleryImage(string $uid, int $i): void
    {
        $this->sections[$uid]['content']['items'][$i]['path'] = null;
    }

    public function moveGalleryItem(string $uid, int $i, int $direction): void
    {
        $items = $this->sections[$uid]['content']['items'] ?? [];
        $target = $i + $direction;
        if (! isset($items[$i], $items[$target])) {
            return;
        }
        [$items[$i], $items[$target]] = [$items[$target], $items[$i]];
        $this->sections[$uid]['content']['items'] = $items;
    }

    /** Imagen para una tarjeta concreta de la galería (reemplaza o completa). */
    public array $galleryItemUploads = [];

    public function updatedGalleryItemUploads($value, $key): void
    {
        [$uid, $i] = explode('.', (string) $key) + [null, null];
        $this->validate(["galleryItemUploads.{$uid}.{$i}" => ['image', 'mimes:jpg,jpeg,png,webp', 'max:10240']], [], ["galleryItemUploads.{$uid}.{$i}" => 'imagen']);

        $this->sections[$uid]['content']['items'][(int) $i]['path'] = app(ImageService::class)
            ->store($this->galleryItemUploads[$uid][$i], 'uploads/gallery');

        unset($this->galleryItemUploads[$uid]);
    }

    /* ----------------------------------------------------------------
     |  Imágenes dentro del editor Tiptap
     * ---------------------------------------------------------------- */

    public function storeEditorImage(): ?string
    {
        $this->validate(['editorImage' => ['required', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:10240']], [], ['editorImage' => 'imagen']);

        $path = app(ImageService::class)->store($this->editorImage, 'uploads/editor');
        $this->reset('editorImage');

        return '/storage/'.$path;
    }

    /* ----------------------------------------------------------------
     |  Validación
     * ---------------------------------------------------------------- */

    /** Reglas de validación según el tipo de cada sección. */
    protected function sectionRules(): array
    {
        $rules = ['sections' => ['array', 'min:1']];

        foreach ($this->sections as $uid => $section) {
            $p = "sections.{$uid}";
            $rules["{$p}.title"] = ['required', 'string', 'max:150'];
            $rules["{$p}.description"] = ['nullable', 'string', 'max:1000'];
            $rules["{$p}.type"] = ['required', Rule::enum(SectionType::class)];

            $rules += match (SectionType::tryFrom($section['type'] ?? '')) {
                SectionType::RichText => ["{$p}.content.html" => ['nullable', 'string', 'max:200000']],
                SectionType::KeyValue => [
                    "{$p}.content.items" => ['array'],
                    "{$p}.content.items.*.label" => ['nullable', 'string', 'max:150'],
                    "{$p}.content.items.*.value" => ['nullable', 'string', 'max:2000'],
                ],
                SectionType::Table => [
                    "{$p}.content.columns" => ['required', 'array', 'min:1', 'max:8'],
                    "{$p}.content.columns.*" => ['nullable', 'string', 'max:100'],
                    "{$p}.content.rows" => ['array'],
                    "{$p}.content.rows.*.*" => ['nullable', 'string', 'max:500'],
                ],
                SectionType::Gallery => [
                    "{$p}.content.columns" => ['required', Rule::in([1, 2, '1', '2'])],
                    "{$p}.content.label" => ['nullable', 'string', 'max:40'],
                    "{$p}.content.items" => ['array'],
                    "{$p}.content.items.*.title" => ['nullable', 'string', 'max:150'],
                    "{$p}.content.items.*.caption" => ['nullable', 'string', 'max:1000'],
                ],
                default => [],
            };
        }

        return $rules;
    }

    /** Nombres legibles ("Sección 2: subtítulo") para los mensajes de error. */
    protected function sectionAttributes(): array
    {
        $attributes = [];
        $n = 0;
        foreach ($this->sections as $uid => $section) {
            $n++;
            $attributes["sections.{$uid}.title"] = "subtítulo de la sección {$n}";
            $attributes["sections.{$uid}.description"] = "descripción de la sección {$n}";
            $attributes["sections.{$uid}.content.html"] = "contenido de la sección {$n}";
            $attributes["sections.{$uid}.content.columns"] = "columnas de la sección {$n}";
        }

        return $attributes;
    }

    protected function sectionMessages(): array
    {
        return [
            'sections.min' => 'Agrega al menos una sección.',
        ];
    }

    /** Errores extra que no se expresan bien con reglas (p. ej. imágenes faltantes). */
    protected function sectionBusinessErrors(bool $requireImages): array
    {
        $errors = [];
        $n = 0;
        foreach ($this->sections as $uid => $section) {
            $n++;
            if ($requireImages && $section['type'] === SectionType::Gallery->value) {
                foreach ($section['content']['items'] ?? [] as $i => $item) {
                    if (empty($item['path'])) {
                        $errors["sections.{$uid}.content.items.{$i}.path"] = "Sección {$n}: la tarjeta ".($i + 1).' de la galería no tiene imagen.';
                    }
                }
            }
            if ($section['type'] === SectionType::KeyValue->value) {
                foreach ($section['content']['items'] ?? [] as $i => $item) {
                    if (trim((string) ($item['value'] ?? '')) !== '' && trim((string) ($item['label'] ?? '')) === '') {
                        $errors["sections.{$uid}.content.items.{$i}.label"] = "Sección {$n}: falta la etiqueta del campo ".($i + 1).'.';
                    }
                }
            }
        }

        return $errors;
    }

    /** Ejecuta la validación y, si falla, muestra un resumen con SweetAlert2. */
    protected function validateWithAlert(callable $callback): void
    {
        try {
            $callback();
        } catch (ValidationException $e) {
            $this->dispatch('validation-failed', messages: array_values(array_unique(Arr::flatten($e->errors()))));

            throw $e;
        }
    }

    public function sectionTypes(): array
    {
        return SectionType::cases();
    }
}
