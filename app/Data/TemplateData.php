<?php

namespace App\Data;

final readonly class TemplateData
{
    /** @param array<int, SectionData> $sections */
    public function __construct(
        public string $name,
        public ?string $description,
        public ?string $defaultTitle,
        public bool $isActive,
        public array $sections,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: trim($data['name']),
            description: filled($data['description'] ?? null) ? trim($data['description']) : null,
            defaultTitle: filled($data['default_title'] ?? null) ? trim($data['default_title']) : null,
            isActive: (bool) ($data['is_active'] ?? true),
            sections: array_map(fn ($s) => SectionData::fromArray($s), array_values($data['sections'] ?? [])),
        );
    }
}
