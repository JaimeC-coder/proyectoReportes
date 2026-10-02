<?php

namespace App\Models;

use App\Enums\SectionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportSection extends Model
{
    protected $fillable = ['report_id', 'position', 'title', 'description', 'type', 'content'];

    protected function casts(): array
    {
        return [
            'type' => SectionType::class,
            'content' => 'array',
        ];
    }

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }
}
