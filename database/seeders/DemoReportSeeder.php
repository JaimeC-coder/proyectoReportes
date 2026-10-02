<?php

namespace Database\Seeders;

use App\Data\ReportData;
use App\Models\Report;
use App\Models\Template;
use App\Models\User;
use App\Services\ReportService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Informe de ejemplo idéntico al documento de referencia (con sus fotos).
 */
class DemoReportSeeder extends Seeder
{
    public function run(): void
    {
        if (Report::withTrashed()->exists()) {
            return;
        }

        $template = Template::where('name', PozoATierraTemplateSeeder::NAME)->first();
        $author = User::where('email', 'tecnico@informes.test')->first();

        $paths = [];
        foreach (range(1, 5) as $i) {
            $path = "uploads/demo/evidencia-{$i}.jpg";
            Storage::disk('public')->put($path, file_get_contents(database_path("seeders/assets/evidencia-{$i}.jpg")));
            $paths[$i] = $path;
        }

        $sections = PozoATierraTemplateSeeder::sections();
        $sections[0]['content']['items'][1]['value'] = '04 de septiembre de 2026.';
        $sections[1]['content']['html'] = str_replace(
            ['telurómetro digital,', '00.00 Ohmios (Ω)</strong> a las 00:00'],
            ['telurómetro digital DUOYI DY4100A,', '20.00 Ohmios (Ω)</strong> a las 14:51'],
            $sections[1]['content']['html']
        );
        $sections[2]['content']['html'] = str_replace(
            ['aditivo químico para', 'Se volvió a acoplar', '00.00 Ohmios (Ω)</strong>.'],
            ['aditivo químico THOR GEL (Gel Azul + Blanco) para', 'A las 16:18 horas se volvió a acoplar', '12.32 Ohmios (Ω)</strong> con indicador de estado "OK".'],
            $sections[2]['content']['html']
        );
        $sections[3]['content']['rows'][0] = ['Resistencia de Tierra', '20.00 Ohmios (Ω)', '12.32 Ohmios (Ω)', 'Reducción del 38.4% (Aprobado)'];
        $sections[3]['content']['rows'][2][2] = 'Tratado con Thor Gel';
        $items = &$sections[4]['content']['items'];
        $items[0]['path'] = $paths[1];
        $items[0]['caption'] = 'Apertura del pozo a tierra indicando un valor crítico inicial de 20.00 Ω en el telurómetro digital.';
        $items[1]['path'] = $paths[2];
        $items[2]['path'] = $paths[3];
        $items[2]['caption'] = 'Preparación y dosificación del compuesto químico Thor Gel (Gel Azul y Gel Blanco) para el acondicionamiento del terreno.';
        $items[3]['path'] = $paths[4];
        $items[3]['caption'] = "Lectura final aprobada de 12.32 Ω con estado 'OK' en el instrumento tras la absorción del gel.";
        $items[] = ['path' => $paths[5], 'title' => 'Medición de Conformidad (detalle)', 'caption' => 'Detalle del telurómetro con la lectura final, garantizando la operatividad del pozo.'];
        unset($items);
        $sections[5]['content']['html'] = '<ul><li><p><strong>Conformidad de Pago:</strong> La intervención técnica logró reducir la resistencia en un 38.4%, estabilizándose en 12.32 Ω. Al estar por debajo del máximo normativo de 25 Ohmios exigido por el Código Nacional de Electricidad (CNE) del Perú, el pozo queda en condición <strong>Totalmente Operativo y Aprobado.</strong> Las evidencias fotográficas adjuntas sustentan la ejecución del servicio, por lo que se da la conformidad técnica para el abono correspondiente.</p></li><li><p><strong>Frecuencia de Control:</strong> Debido a las condiciones climáticas y la alta concentración salina en la zona de San Juan de Marcona, se recomienda programar de manera estricta el próximo mantenimiento preventivo y dosificación química en septiembre de 2027.</p></li><li><p><strong>Recomendación:</strong> Cambiar de lugar el pozo a tierra ubicado en el ingreso del baño es una acción correctiva prioritaria y estrictamente necesaria. <span style="color: #dc2626">(alto riesgo eléctrico y operativo)</span></p></li></ul>';

        app(ReportService::class)->create(ReportData::fromArray([
            'title' => 'Informe técnico: mantenimiento de sistema de puesta a tierra',
            'client_name' => 'Cliente de ejemplo',
            'client_location' => 'San Juan de Marcona, Ica',
            'client_signature' => ['name' => 'Supervisor de ejemplo', 'position' => 'Jefe de planta'],
            'service_date' => '2026-09-04',
            'template_id' => $template?->id,
            'sections' => $sections,
        ]), $author);
    }
}
