{{--
    Documento del informe. Se usa en:
      - la vista previa en vivo del editor ($pdf = false)
      - el PDF generado con DomPDF ($pdf = true)
    Recibe: $doc (array), $settings (array), $pdf (bool), $logoSrc (?string)
--}}
@php
    use App\Support\DocumentAsset;
    $pdf = $pdf ?? false;
    $sections = $doc['sections'] ?? [];
    $date = $doc['service_date'] ?? null;
    $dateText = $date ? $date->locale('es')->translatedFormat('d \d\e F \d\e Y') : '—';
    $signature = $doc['signature'] ?? null;
@endphp
<div class="doc">
    @if ($settings['show_company'] || $settings['show_code'])
        <table class="doc-header">
            <tr>
                @if ($settings['show_company'])
                    @if ($logoSrc)
                        <td style="width: 1%; padding-right: 12px;"><img class="doc-logo" src="{{ $logoSrc }}" alt="Logo"></td>
                    @endif
                    <td>
                        <div class="doc-company">{{ $settings['company_name'] }}</div>
                        <div class="doc-company-meta">
                            @if (filled($settings['company_ruc'])) RUC: {{ $settings['company_ruc'] }} @endif
                            @if (filled($settings['company_address'])) · {{ $settings['company_address'] }} @endif
                            @if (filled($settings['company_phone'])) <br>Tel.: {{ $settings['company_phone'] }} @endif
                            @if (filled($settings['company_email'])) · {{ $settings['company_email'] }} @endif
                        </div>
                    </td>
                @endif
                @if ($settings['show_code'])
                    <td class="doc-code" style="width: 34%;">
                        <strong>{{ $doc['code'] ?? 'INF-XXXX-XXXX' }}</strong><br>{{ $dateText }}
                    </td>
                @endif
            </tr>
        </table>
    @endif

    <div class="doc-title">{{ filled($doc['title'] ?? null) ? $doc['title'] : 'Título del informe' }}</div>

    @if ($settings['show_client'])
        <table class="doc-client">
            <tr>
                <td class="lbl">{{ $settings['client_label'] }}</td>
                <td>{{ $doc['client_name'] ?: '—' }}</td>
                <td class="lbl">Fecha</td>
                <td style="width: 22%;">{{ $dateText }}</td>
            </tr>
            <tr>
                <td class="lbl">{{ $settings['location_label'] }}</td>
                <td colspan="3">{{ $doc['client_location'] ?: '—' }}</td>
            </tr>
        </table>
    @endif

    @forelse ($sections as $index => $section)
        @php $content = $section['content'] ?? []; @endphp
        <div class="doc-section">
            <div class="doc-section-title">{{ $index + 1 }}. {{ filled($section['title']) ? $section['title'] : 'Sección sin título' }}</div>

            @if (filled($section['description'] ?? null))
                <p class="doc-section-desc">{{ $section['description'] }}</p>
            @endif

            @switch($section['type'])
                @case('richtext')
                    @if (filled($content['html'] ?? null))
                        <div class="doc-rich">{!! DocumentAsset::html($content['html'], $pdf) !!}</div>
                    @elseif (! $pdf)
                        <p class="doc-empty">Escribe el contenido de esta sección…</p>
                    @endif
                    @break

                @case('key_value')
                    <ul class="doc-kv">
                        @foreach ($content['items'] ?? [] as $item)
                            <li>@if (filled($item['label']))<strong>{{ $item['label'] }}:</strong>@endif {{ $item['value'] }}</li>
                        @endforeach
                    </ul>
                    @break

                @case('table')
                    @if (count($content['columns'] ?? []))
                        <table class="doc-table">
                            <thead>
                                <tr>
                                    @foreach ($content['columns'] as $col)
                                        <th>{{ $col }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($content['rows'] ?? [] as $row)
                                    <tr>
                                        @foreach ($row as $ci => $cell)
                                            <td class="{{ $ci === 0 ? 'first' : '' }}">{{ $cell }}</td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                    @break

                @case('gallery')
                    @php
                        $cols = (int) ($content['columns'] ?? 1);
                        $label = $content['label'] ?? 'Evidencia';
                        $items = $content['items'] ?? [];
                        $chunks = array_chunk($items, $cols, true);
                    @endphp
                    <table class="doc-gallery cols-{{ $cols }}">
                        @foreach ($chunks as $chunk)
                            <tr>
                                @foreach ($chunk as $i => $item)
                                    @php $src = DocumentAsset::src($item['path'] ?? null, $pdf); @endphp
                                    <td style="width: {{ 100 / $cols }}%;">
                                        <div class="doc-gallery-item">
                                            <div class="doc-gallery-text">
                                                &bull; <strong>{{ trim($label.' '.($i + 1)) }}@if (filled($item['title'])) - {{ $item['title'] }}@endif:</strong>
                                                {{ $item['caption'] }}
                                            </div>
                                            <div class="doc-gallery-img">
                                                @if ($src)
                                                    <img src="{{ $src }}" alt="{{ $item['title'] }}">
                                                @else
                                                    <div class="doc-placeholder">Sin imagen</div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                @endforeach
                                @for ($k = count($chunk); $k < $cols; $k++)
                                    <td></td>
                                @endfor
                            </tr>
                        @endforeach
                    </table>
                    @break
            @endswitch
        </div>
    @empty
        @unless ($pdf)
            <p class="doc-empty" style="text-align:center; padding: 30px 0;">Agrega secciones para ver aquí la vista previa del informe.</p>
        @endunless
    @endforelse

    @php
        // Firmas: técnico (izquierda) y cliente (derecha), en la misma fila.
        $signatures = [];
        if (($settings['show_signature'] ?? true) && $signature) {
            $signatures[] = [
                'label' => $settings['technician_signature_label'] ?? 'Técnico responsable',
                'name' => $signature['name'] ?? '',
                'position' => $signature['position'] ?? null,
                'src' => DocumentAsset::src($signature['path'] ?? null, $pdf),
            ];
        }
        if ($settings['show_client_signature'] ?? true) {
            $clientSignature = $doc['client_signature'] ?? [];
            $signatures[] = [
                'label' => $settings['client_signature_label'] ?? 'Conformidad del cliente',
                'name' => ($clientSignature['name'] ?? null) ?: ($doc['client_name'] ?? ''),
                'position' => $clientSignature['position'] ?? null,
                'src' => DocumentAsset::src($clientSignature['path'] ?? null, $pdf),
            ];
        }
    @endphp
    @if (count($signatures))
        <table class="doc-signatures">
            <tr>
                @foreach ($signatures as $sig)
                    <td style="width: {{ 100 / count($signatures) }}%;">
                        <div class="doc-signature">
                            <div class="sig-img">
                                @if ($sig['src'])
                                    <img src="{{ $sig['src'] }}" alt="Firma">
                                @endif
                            </div>
                            <div class="line"></div>
                            <div class="name">{{ $sig['name'] ?: ' ' }}</div>
                            @if (filled($sig['position']))
                                <div class="pos">{{ $sig['position'] }}</div>
                            @endif
                            @if (filled($sig['label']))
                                <div class="lbl">{{ $sig['label'] }}</div>
                            @endif
                        </div>
                    </td>
                @endforeach
            </tr>
        </table>
    @endif
</div>
