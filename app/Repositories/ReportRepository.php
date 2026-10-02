<?php

namespace App\Repositories;

use App\Enums\Permission;
use App\Models\Report;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class ReportRepository
{
    public function visibleTo(User $user): Builder
    {
        return Report::query()
            ->when(! $user->can(Permission::ReportsViewAll->value), fn (Builder $q) => $q->where('user_id', $user->id));
    }

    public function paginate(User $user, array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return $this->visibleTo($user)
            ->with(['user:id,name', 'template:id,name'])
            ->when($filters['search'] ?? null, function (Builder $q, string $search) {
                $q->where(fn (Builder $w) => $w
                    ->where('code', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%")
                    ->orWhere('client_name', 'like', "%{$search}%")
                    ->orWhere('client_location', 'like', "%{$search}%"));
            })
            ->when($filters['template_id'] ?? null, fn (Builder $q, $id) => $q->where('template_id', $id))
            ->when($filters['user_id'] ?? null, fn (Builder $q, $id) => $q->where('user_id', $id))
            ->when($filters['from'] ?? null, fn (Builder $q, $d) => $q->whereDate('service_date', '>=', $d))
            ->when($filters['to'] ?? null, fn (Builder $q, $d) => $q->whereDate('service_date', '<=', $d))
            ->latest('id')
            ->paginate($perPage);
    }

    /** Siguiente correlativo del año: INF-2026-0001 */
    public function nextCode(): string
    {
        $year = now()->year;
        $prefix = "INF-{$year}-";

        $last = Report::withTrashed()
            ->where('code', 'like', $prefix.'%')
            ->lockForUpdate()
            ->orderByDesc('code')
            ->value('code');

        $next = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    public function stats(User $user): array
    {
        $base = fn () => $this->visibleTo($user);

        return [
            'total' => $base()->count(),
            'month' => $base()->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->count(),
            'today' => $base()->whereDate('created_at', today())->count(),
        ];
    }
}
