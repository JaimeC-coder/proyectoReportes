<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

/**
 * Ajustes generales del sistema (encabezado, pie y opciones del PDF).
 */
class SettingService
{
    private const CACHE_KEY = 'app_settings';

    public const DEFAULTS = [
        'company_name' => 'Mi Empresa S.A.C.',
        'company_ruc' => '',
        'company_address' => '',
        'company_phone' => '',
        'company_email' => '',
        'company_logo' => null,
        'primary_color' => '#1e3a8a',
        'footer_text' => 'Documento generado por el Sistema de Informes Técnicos',
        'client_label' => 'Cliente',
        'location_label' => 'Ubicación / Sede',
        'show_company' => true,
        'show_client' => true,
        'show_signature' => true,
        'show_client_signature' => true,
        'technician_signature_label' => 'Técnico responsable',
        'client_signature_label' => 'Conformidad del cliente',
        'show_code' => false,
        'show_page_numbers' => true,
    ];

    public function all(): array
    {
        $stored = Cache::rememberForever(self::CACHE_KEY, function () {
            if (! Schema::hasTable('settings')) {
                return [];
            }

            return Setting::query()->pluck('value', 'key')->all();
        });

        return array_merge(self::DEFAULTS, array_intersect_key($stored, self::DEFAULTS));
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->all()[$key] ?? $default;
    }

    public function update(array $values): void
    {
        foreach (array_intersect_key($values, self::DEFAULTS) as $key => $value) {
            Setting::query()->updateOrCreate(['key' => $key], ['value' => $value]);
        }

        Cache::forget(self::CACHE_KEY);
    }

    public function logoUrl(): ?string
    {
        $logo = $this->get('company_logo');

        return $logo ? Storage::disk('public')->url($logo) : null;
    }
}
