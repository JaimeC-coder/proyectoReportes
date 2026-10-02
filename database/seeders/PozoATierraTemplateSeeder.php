<?php

namespace Database\Seeders;

use App\Data\TemplateData;
use App\Models\Template;
use App\Models\User;
use App\Services\TemplateService;
use Illuminate\Database\Seeder;

/**
 * Plantilla basada en el documento "Informe Técnico Pozo a Tierra".
 */
class PozoATierraTemplateSeeder extends Seeder
{
    public const NAME = 'Mantenimiento de Pozo a Tierra';

    public static function sections(): array
    {
        return [
            [
                'title' => 'Información general',
                'description' => null,
                'type' => 'key_value',
                'content' => ['items' => [
                    ['label' => 'Nombre del Servicio', 'value' => 'Mantenimiento preventivo y correctivo de pozo a tierra.'],
                    ['label' => 'Fecha de Ejecución', 'value' => ''],
                    ['label' => 'Objetivo', 'value' => 'Reducir la resistencia eléctrica óhmica del pozo mediante tratamiento químico y mecánico para garantizar la seguridad de las instalaciones.'],
                ]],
            ],
            [
                'title' => 'Estado inicial',
                'description' => 'Al iniciar la jornada técnica en el punto, el pozo presentaba las siguientes condiciones:',
                'type' => 'richtext',
                'content' => ['html' => '<ul><li><p><strong>Sulfatación Severa:</strong> El conector de bronce tipo mordaza y la punta de cobre conductora se encontraban cubiertos por una densa capa de sulfato que elevaba la resistencia por falta de contacto limpio.</p></li><li><p><strong>Alta Resistencia Óhmica:</strong> Al realizar la medición de apertura con el telurómetro digital, la pantalla registró un valor de <strong>00.00 Ohmios (Ω)</strong> a las 00:00 horas.</p></li></ul>'],
            ],
            [
                'title' => 'Procedimiento ejecutado',
                'description' => null,
                'type' => 'richtext',
                'content' => ['html' => '<ol><li><p><strong>Limpieza Mecánica a Detalle:</strong> Se retiró el conector tipo mordaza y se procedió a un lijado profundo, eliminando impurezas aislantes.</p></li><li><p><strong>Excavación e Impregnación Química:</strong> Se descubrió el contorno de la barra de cobre y se aplicó la solución de aditivo químico para reconfigurar la resistividad del terreno.</p></li><li><p><strong>Tiempo de Absorción:</strong> Se dejó reposar el pozo durante 10 minutos para permitir la correcta percolación y humectación del sustrato.</p></li><li><p><strong>Reconexión y Ajuste:</strong> Se reinstaló el conector limpio y se aseguró el cable de tierra con el torque adecuado.</p></li><li><p><strong>Medición de Control Final:</strong> Se volvió a acoplar el telurómetro, arrojando una lectura final de <strong>00.00 Ohmios (Ω)</strong>.</p></li></ol>'],
            ],
            [
                'title' => 'Cuadro comparativo de lecturas',
                'description' => null,
                'type' => 'table',
                'content' => [
                    'columns' => ['Parámetro Técnico', 'Estado Inicial (Antes)', 'Estado Final (Después)', 'Resultado Obtenido'],
                    'rows' => [
                        ['Resistencia de Tierra', '', '', ''],
                        ['Estado del Conector', 'Sulfatado / Deficiente', 'Limpio y Reluciente', 'Conductividad Restablecida'],
                        ['Condición del Suelo', 'Seco / Alta resistividad', 'Tratado con aditivo', 'Humedad Estable y Activa'],
                    ],
                ],
            ],
            [
                'title' => 'Panel de evidencias fotográficas',
                'description' => null,
                'type' => 'gallery',
                'content' => ['columns' => 1, 'label' => 'Evidencia', 'items' => [
                    ['path' => null, 'title' => 'Medición Inicial', 'caption' => 'Apertura del pozo a tierra indicando el valor inicial en el telurómetro digital.'],
                    ['path' => null, 'title' => 'Mantenimiento Mecánico', 'caption' => 'Conector tipo mordaza de bronce limpio, pulido y libre de sulfatación tras el lijado técnico.'],
                    ['path' => null, 'title' => 'Aplicación de Insumos', 'caption' => 'Preparación y dosificación del compuesto químico para el acondicionamiento del terreno.'],
                    ['path' => null, 'title' => 'Medición de Conformidad', 'caption' => 'Lectura final aprobada en el instrumento tras la absorción del aditivo.'],
                ]],
            ],
            [
                'title' => 'Conclusiones y recomendaciones',
                'description' => null,
                'type' => 'richtext',
                'content' => ['html' => '<ul><li><p><strong>Conformidad:</strong> La intervención técnica logró reducir la resistencia, quedando por debajo del máximo normativo de 25 Ohmios exigido por el Código Nacional de Electricidad (CNE) del Perú. El pozo queda en condición <strong>Totalmente Operativo y Aprobado.</strong></p></li><li><p><strong>Frecuencia de Control:</strong> Se recomienda programar el próximo mantenimiento preventivo y dosificación química en un plazo de 12 meses.</p></li></ul>'],
            ],
        ];
    }

    public function run(): void
    {
        if (Template::where('name', self::NAME)->exists()) {
            return;
        }

        app(TemplateService::class)->save(TemplateData::fromArray([
            'name' => self::NAME,
            'description' => 'Informe técnico de mantenimiento preventivo y correctivo de sistema de puesta a tierra.',
            'default_title' => 'Informe técnico: mantenimiento de sistema de puesta a tierra',
            'is_active' => true,
            'sections' => self::sections(),
        ]), null, User::role('admin')->first());
    }
}
