<?php

namespace App\Models;

use App\Enums\ReportStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Un informe nace como BORRADOR (editable, sin PDF ni correlativo) o directamente
 * FINALIZADO. Una vez finalizado es inmutable: sólo puede visualizarse,
 * descargarse o duplicarse (para crear uno nuevo a partir de él).
 */
class Report extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code', 'status', 'finalized_at', 'title', 'client_name', 'client_location', 'service_date',
        'template_id', 'user_id', 'duplicated_from_id', 'signature', 'client_signature', 'pdf_path',
    ];

    protected function casts(): array
    {
        return [
            'service_date' => 'date',
            'status' => ReportStatus::class,
            'finalized_at' => 'datetime',
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

    public function isDraft(): bool
    {
        return $this->status === ReportStatus::Draft;
    }

    public function isFinal(): bool
    {
        return $this->status === ReportStatus::Final;
    }

    /** Código visible: el correlativo o "Borrador" mientras no se finaliza. */
    public function label(): string
    {
        return $this->code ?? 'Borrador';
    }

    public function scopeFinal(Builder $query): Builder
    {
        return $query->where('status', ReportStatus::Final->value);
    }

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', ReportStatus::Draft->value);
    }

    public function pdfFileName(): string
    {
        return $this->code.'.pdf';
    }
}
