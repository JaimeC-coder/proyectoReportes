<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Report;
use App\Models\User;

class ReportPolicy
{
    public function view(User $user, Report $report): bool
    {
        return $report->user_id === $user->id || $user->can(Permission::ReportsViewAll->value);
    }

    public function duplicate(User $user, Report $report): bool
    {
        return $this->view($user, $report) && $user->can(Permission::ReportsCreate->value);
    }

    /** Los informes no se editan nunca (inmutables). */
    public function update(User $user, Report $report): bool
    {
        return false;
    }

    public function delete(User $user, Report $report): bool
    {
        return $user->can(Permission::ReportsDelete->value);
    }
}
