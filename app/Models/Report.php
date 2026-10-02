<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Un informe es inmutable: una vez creado sólo puede visualizarse,
 * descargarse o duplicarse (para crear uno nuevo a partir de él).
 */
class Report extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code', 'title', 'client_name', 'client_location', 'service_date',
        'template_id', 'user_id', 'duplicated_from_id', 'signature', 'client_signature', 'pdf_path',
    ];

    protected function casts(): array
    {
        return [
            'service_date' => 'date',
            'signature' => 'array',
            'client_signature' => 'array',
        ];
    }

    public function sections(): HasMany
    {
        return $this->hasMany(ReportSection::class)->orderBy('position');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class)->withTrashed();
    }

    public function duplicatedFrom(): BelongsTo
    {
        return $this->belongsTo(self::class, 'duplicated_from_id');
    }

    public function pdfFileName(): string
    {
        return $this->code.'.pdf';
    }
}
