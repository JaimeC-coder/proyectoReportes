<?php

namespace App\Repositories;

use App\Models\Template;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class TemplateRepository
{
    public function paginate(?string $search = null, int $perPage = 10): LengthAwarePaginator
    {
        return Template::query()
            ->withCount(['sections', 'reports'])
            ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->paginate($perPage);
    }

    public function active(): Collection
    {
        return Template::query()->active()->withCount('sections')->orderBy('name')->get();
    }
}
