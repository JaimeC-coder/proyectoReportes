<?php

namespace App\Models;

use App\Enums\SectionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TemplateSection extends Model
{
    protected $fillable = ['template_id', 'position', 'title', 'description', 'type', 'content'];

    protected function casts(): array
    {
        return [
            'type' => SectionType::class,
            'content' => 'array',
        ];
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }
}
