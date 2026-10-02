<?php

namespace App\Data;

use Carbon\CarbonImmutable;

final readonly class ReportData
{
    /** @param array<int, SectionData> $sections */
    public function __construct(
        public string $title,
        public ?string $clientName,
        public ?string $clientLocation,
        public CarbonImmutable $serviceDate,
        public ?int $templateId,
        public ?int $duplicatedFromId,
        public array $sections,
        public ?array $clientSignature = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            title: trim($data['title']),
            clientName: filled($data['client_name'] ?? null) ? trim($data['client_name']) : null,
            clientLocation: filled($data['client_location'] ?? null) ? trim($data['client_location']) : null,
            serviceDate: CarbonImmutable::parse($data['service_date']),
            templateId: $data['template_id'] ?? null,
            duplicatedFromId: $data['duplicated_from_id'] ?? null,
            sections: array_map(fn ($s) => SectionData::fromArray($s), array_values($data['sections'] ?? [])),
            clientSignature: self::clientSignature($data['client_signature'] ?? null),
        );
    }

    /** Normaliza la firma del cliente; null si no tiene ningún dato. */
    public static function clientSignature(?array $signature): ?array
    {
        $clean = [
            'name' => filled($signature['name'] ?? null) ? trim($signature['name']) : null,
            'position' => filled($signature['position'] ?? null) ? trim($signature['position']) : null,
            'path' => filled($signature['path'] ?? null) ? (string) $signature['path'] : null,
        ];

        return array_filter($clean) ? $clean : null;
    }
}
