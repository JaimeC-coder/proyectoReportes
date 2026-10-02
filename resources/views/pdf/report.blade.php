<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $doc['code'] }} - {{ $doc['title'] }}</title>
    <style>
        @page { margin: 1.6cm 1.6cm 2cm 1.6cm; }
        body { margin: 0; }
        .pdf-footer { position: fixed; bottom: -1.3cm; left: 0; right: 0; height: 1cm; font-family: 'DejaVu Sans', sans-serif; font-size: 7.5pt; color: #6b7280; border-top: 1px solid #e5e7eb; padding-top: 4px; }
    </style>
    @include('documents.styles', ['settings' => $settings])
</head>
<body>
    <div class="pdf-footer">
        {{ $settings['footer_text'] }}@if ($settings['show_code']) · {{ $doc['code'] }}@endif
    </div>
    @include('documents.report', ['doc' => $doc, 'settings' => $settings, 'pdf' => true, 'logoSrc' => $logoPath])
</body>
</html>
