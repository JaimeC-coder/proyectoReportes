<?php

namespace App\Data;

use App\Enums\SectionType;
use App\Support\HtmlSanitizer;

/**
 * DTO de una sección (bloque) de plantilla o informe.
 */
final readonly class SectionData
{
    public function __construct(
        public string $title,
        public ?string $description,
        public SectionType $type,
        public array $content,
    ) {}

    public static function fromArray(array $data): self
    {
        $type = $data['type'] instanceof SectionType ? $data['type'] : SectionType::from($data['type']);

        return new self(
            title: trim((string) ($data['title'] ?? '')),
            description: filled($data['description'] ?? null) ? trim((string) $data['description']) : null,
            type: $type,
            content: self::normalizeContent($type, (array) ($data['content'] ?? [])),
        );
    }

    /** Normaliza y limpia el contenido según el tipo de bloque. */
    public static function normalizeContent(SectionType $type, array $content): array
    {
        $text = fn ($v) => trim((string) ($v ?? ''));

        return match ($type) {
            SectionType::RichText => ['html' => HtmlSanitizer::clean($content['html'] ?? '')],
            SectionType::KeyValue => [
                'items' => collect($content['items'] ?? [])
                    ->map(fn ($i) => ['label' => $text($i['label'] ?? ''), 'value' => $text($i['value'] ?? '')])
                    ->filter(fn ($i) => $i['label'] !== '' || $i['value'] !== '')
                    ->values()->all(),
            ],
            SectionType::Table => (function () use ($content, $text) {
                $columns = array_map($text, array_values($content['columns'] ?? []));
                $rows = collect($content['rows'] ?? [])
                    ->map(fn ($row) => array_map($text, array_pad(array_slice(array_values((array) $row), 0, count($columns)), count($columns), '')))
                    ->filter(fn ($row) => implode('', $row) !== '')
                    ->values()->all();

                return ['columns' => $columns, 'rows' => $rows];
            })(),
            SectionType::Gallery => [
                'columns' => in_array((int) ($content['columns'] ?? 1), [1, 2], true) ? (int) $content['columns'] : 1,
                'label' => $text($content['label'] ?? 'Evidencia'),
                'items' => collect($content['items'] ?? [])
                    ->map(fn ($i) => [
                        'path' => filled($i['path'] ?? null) ? (string) $i['path'] : null,
                        'title' => $text($i['title'] ?? ''),
                        'caption' => $text($i['caption'] ?? ''),
                    ])
                    ->filter(fn ($i) => $i['path'] || $i['title'] !== '' || $i['caption'] !== '')
                    ->values()->all(),
            ],
        };
    }

    public function toArray(int $position): array
    {
        return [
            'position' => $position,
            'title' => $this->title,
            'description' => $this->description,
            'type' => $this->type->value,
            'content' => $this->content,
        ];
    }
}
