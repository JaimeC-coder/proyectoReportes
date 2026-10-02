<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;

/**
 * Limpia el HTML generado por el editor (Tiptap) dejando sólo etiquetas y
 * atributos seguros. Evita XSS en la vista previa, el detalle y el PDF.
 */
class HtmlSanitizer
{
    /** @var array<string, array<int, string>> etiqueta => atributos permitidos */
    private const ALLOWED = [
        'p' => ['style'], 'br' => [], 'strong' => [], 'b' => [], 'em' => [], 'i' => [], 'u' => [], 's' => [],
        'span' => ['style'], 'mark' => ['style', 'data-color'], 'a' => ['href', 'target', 'rel'],
        'h1' => ['style'], 'h2' => ['style'], 'h3' => ['style'], 'h4' => ['style'],
        'ul' => [], 'ol' => ['start'], 'li' => [], 'blockquote' => [], 'hr' => [], 'code' => [], 'pre' => [],
        'table' => ['style'], 'colgroup' => [], 'col' => ['style'], 'tbody' => [], 'thead' => [], 'tr' => [],
        'th' => ['colspan', 'rowspan', 'style', 'colwidth'], 'td' => ['colspan', 'rowspan', 'style', 'colwidth'],
        'img' => ['src', 'alt', 'title', 'width', 'height', 'style'],
    ];

    private const ALLOWED_CSS = ['color', 'background-color', 'text-align', 'min-width', 'width'];

    public static function clean(?string $html): string
    {
        $html = trim((string) $html);
        if ($html === '' || $html === '<p></p>') {
            return '';
        }

        $doc = new DOMDocument('1.0', 'UTF-8');
        libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="UTF-8"><div id="__root">'.$html.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $root = $doc->getElementById('__root') ?? $doc->documentElement;
        self::walk($root);

        $out = '';
        foreach (iterator_to_array($root->childNodes) as $child) {
            $out .= $doc->saveHTML($child);
        }

        return $out;
    }

    private static function walk(DOMNode $node): void
    {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if ($child instanceof DOMElement) {
                $tag = strtolower($child->tagName);

                if (! array_key_exists($tag, self::ALLOWED)) {
                    if (in_array($tag, ['script', 'style', 'iframe', 'object', 'embed', 'form', 'input', 'button', 'svg', 'math'], true)) {
                        $node->removeChild($child);

                        continue;
                    }
                    // Etiqueta desconocida: se conserva su contenido.
                    self::walk($child);
                    while ($child->firstChild) {
                        $node->insertBefore($child->firstChild, $child);
                    }
                    $node->removeChild($child);

                    continue;
                }

                foreach (iterator_to_array($child->attributes) as $attr) {
                    $name = strtolower($attr->name);
                    if (! in_array($name, self::ALLOWED[$tag], true)) {
                        $child->removeAttribute($attr->name);

                        continue;
                    }
                    if (in_array($name, ['href', 'src'], true) && ! self::safeUrl($attr->value, $name)) {
                        $child->removeAttribute($attr->name);
                    }
                    if ($name === 'style') {
                        $style = self::cleanStyle($attr->value);
                        $style === '' ? $child->removeAttribute('style') : $child->setAttribute('style', $style);
                    }
                }

                if ($tag === 'a') {
                    $child->setAttribute('rel', 'noopener noreferrer');
                }

                self::walk($child);
            } elseif ($child->nodeType === XML_COMMENT_NODE) {
                $node->removeChild($child);
            }
        }
    }

    private static function safeUrl(string $url, string $attr): bool
    {
        $url = trim($url);
        if ($attr === 'src') {
            // Sólo imágenes subidas al propio sistema.
            return str_starts_with($url, '/storage/') || str_contains($url, '/storage/uploads/');
        }

        return (bool) preg_match('#^(https?://|mailto:|/|\#)#i', $url);
    }

    private static function cleanStyle(string $style): string
    {
        $clean = [];
        foreach (explode(';', $style) as $rule) {
            [$prop, $value] = array_pad(array_map('trim', explode(':', $rule, 2)), 2, '');
            $prop = strtolower($prop);
            if ($prop === '' || $value === '' || ! in_array($prop, self::ALLOWED_CSS, true)) {
                continue;
            }
            if (preg_match('/url\s*\(|expression|javascript:|[<>{}]/i', $value)) {
                continue;
            }
            $clean[] = $prop.': '.$value;
        }

        return implode('; ', $clean);
    }
}
