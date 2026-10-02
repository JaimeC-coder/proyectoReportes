{{-- Estilos compartidos por la vista previa y el PDF (compatibles con DomPDF: sin flex ni grid). --}}
@php $c = $settings['primary_color'] ?? '#1e3a8a'; @endphp
<style>
    .doc { font-family: 'DejaVu Sans', Arial, Helvetica, sans-serif; font-size: 10pt; line-height: 1.45; color: #1f2937; }
    .doc * { box-sizing: border-box; }
    .doc p { margin: 0 0 6px 0; }
    .doc ul { list-style: disc; } .doc ol { list-style: decimal; }
    .doc ul ul { list-style: circle; }
    .doc strong, .doc b { font-weight: bold; } .doc em, .doc i { font-style: italic; } .doc u { text-decoration: underline; } .doc s { text-decoration: line-through; }
    .doc img { display: inline; }
    .doc table { border-collapse: collapse; }
    .doc .doc-header { width: 100%; border-bottom: 2px solid {{ $c }}; margin-bottom: 14px; }
    .doc .doc-header td { vertical-align: middle; padding: 0 0 8px 0; }
    .doc .doc-logo { max-height: 60px; max-width: 170px; }
    .doc .doc-company { font-size: 12pt; font-weight: bold; color: {{ $c }}; }
    .doc .doc-company-meta { font-size: 8pt; color: #4b5563; }
    .doc .doc-code { font-size: 8.5pt; color: #374151; text-align: right; }
    .doc .doc-code strong { color: {{ $c }}; font-size: 10pt; }
    .doc .doc-title { text-align: center; font-size: 13.5pt; font-weight: bold; text-transform: uppercase; color: {{ $c }}; margin: 6px 0 14px; letter-spacing: .3px; }
    .doc .doc-client { width: 100%; margin-bottom: 16px; font-size: 9pt; }
    .doc .doc-client td { border: 1px solid #d1d5db; padding: 5px 8px; vertical-align: top; }
    .doc .doc-client .lbl { background: #f3f4f6; font-weight: bold; width: 22%; color: #374151; }
    .doc .doc-section { margin-bottom: 14px; }
    .doc .doc-section-title { font-size: 11pt; font-weight: bold; text-transform: uppercase; color: {{ $c }}; border-left: 4px solid {{ $c }}; padding: 2px 0 2px 8px; margin: 0 0 8px 0; }
    .doc .doc-section-desc { font-style: italic; color: #4b5563; margin: 0 0 6px 0; }
    .doc .doc-kv { margin: 0; padding: 0 0 0 16px; }
    .doc .doc-kv li { margin-bottom: 3px; }
    .doc .doc-rich ul, .doc .doc-rich ol { margin: 0 0 6px 0; padding-left: 20px; }
    .doc .doc-rich li { margin-bottom: 4px; }
    .doc .doc-rich li p { margin: 0; }
    .doc .doc-rich h1, .doc .doc-rich h2, .doc .doc-rich h3, .doc .doc-rich h4 { margin: 8px 0 4px; color: #111827; }
    .doc .doc-rich h2 { font-size: 11.5pt; } .doc .doc-rich h3 { font-size: 10.5pt; }
    .doc .doc-rich blockquote { border-left: 3px solid #d1d5db; margin: 6px 0; padding: 2px 10px; color: #4b5563; }
    .doc .doc-rich table { width: 100%; margin: 6px 0; }
    .doc .doc-rich td, .doc .doc-rich th { border: 1px solid #9ca3af; padding: 4px 6px; vertical-align: top; }
    .doc .doc-rich th { background: #f3f4f6; font-weight: bold; }
    .doc .doc-rich img { max-width: 70%; max-height: 300px; height: auto; margin: 4px 0; }
    .doc .doc-rich mark { background: #fef08a; padding: 0 2px; }
    .doc .doc-rich a { color: {{ $c }}; }
    .doc .doc-table { width: 100%; font-size: 9pt; }
    .doc .doc-table th { background: {{ $c }}; color: #fff; border: 1px solid {{ $c }}; padding: 6px; text-align: center; }
    .doc .doc-table td { border: 1px solid #9ca3af; padding: 5px 6px; text-align: center; vertical-align: middle; }
    .doc .doc-table td.first { font-weight: bold; text-align: left; background: #f9fafb; }
    .doc .doc-gallery { width: 100%; }
    .doc .doc-gallery td { vertical-align: top; padding: 0 6px 12px 6px; }
    .doc .doc-gallery-item { page-break-inside: avoid; }
    .doc .doc-gallery-text { margin-bottom: 6px; }
    .doc .doc-gallery-img { text-align: center; }
    .doc .doc-gallery-img img { display: inline-block; max-width: 100%; max-height: 330px; border: 1px solid #e5e7eb; }
    .doc .doc-gallery.cols-2 .doc-gallery-img img { max-height: 230px; }
    .doc .doc-placeholder { border: 1px dashed #9ca3af; color: #9ca3af; text-align: center; padding: 26px 8px; font-size: 8.5pt; }
    .doc .doc-signatures { width: 100%; margin-top: 40px; page-break-inside: avoid; }
    .doc .doc-signatures td { vertical-align: bottom; padding: 0 10px; }
    .doc .doc-signature { text-align: center; }
    .doc .doc-signature .sig-img { height: 72px; }
    .doc .doc-signature img { display: inline-block; max-height: 70px; max-width: 190px; }
    .doc .doc-signature .line { width: 220px; margin: 4px auto 3px; border-top: 1px solid #111827; }
    .doc .doc-signature .name { font-weight: bold; }
    .doc .doc-signature .pos { font-size: 8.5pt; color: #4b5563; }
    .doc .doc-signature .lbl { font-size: 7.5pt; color: #6b7280; text-transform: uppercase; letter-spacing: .4px; margin-top: 2px; }
    .doc .doc-empty { color: #9ca3af; font-style: italic; }
</style>
