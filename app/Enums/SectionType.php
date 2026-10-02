<?php

namespace App\Enums;

enum SectionType: string
{
    case RichText = 'richtext';
    case KeyValue = 'key_value';
    case Table = 'table';
    case Gallery = 'gallery';

    public function label(): string
    {
        return match ($this) {
            self::RichText => 'Texto enriquecido',
            self::KeyValue => 'Campos clave-valor',
            self::Table => 'Tabla editable',
            self::Gallery => 'Galería de imágenes',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::RichText => 'Párrafos, listas, negritas, colores, tablas e imágenes dentro del texto.',
            self::KeyValue => 'Pares etiqueta / valor, ideal para información general.',
            self::Table => 'Cuadro con columnas y filas editables (p. ej. comparativo antes / después).',
            self::Gallery => 'Evidencias fotográficas con título y descripción, en 1 o 2 columnas.',
        };
    }

    /** Heroicon name (WireUI) */
    public function icon(): string
    {
        return match ($this) {
            self::RichText => 'document-text',
            self::KeyValue => 'list-bullet',
            self::Table => 'table-cells',
            self::Gallery => 'photo',
        };
    }

    /** Contenido inicial vacío de cada tipo de bloque. */
    public function emptyContent(): array
    {
        return match ($this) {
            self::RichText => ['html' => ''],
            self::KeyValue => ['items' => [['label' => '', 'value' => '']]],
            self::Table => [
                'columns' => ['Parámetro', 'Antes', 'Después'],
                'rows' => [['', '', '']],
            ],
            self::Gallery => ['columns' => 1, 'label' => 'Evidencia', 'items' => []],
        };
    }
}
