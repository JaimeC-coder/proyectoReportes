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

    /** Sólo se duplican informes finalizados. */
    public function duplicate(User $user, Report $report): bool
    {
        return $report->isFinal() && $this->view($user, $report) && $user->can(Permission::ReportsCreate->value);
    }

    /** Sólo el autor puede seguir editando, y únicamente mientras sea borrador. */
    public function update(User $user, Report $report): bool
    {
        return $report->isDraft() && $report->user_id === $user->id && $user->can(Permission::ReportsCreate->value);
    }

    /** El PDF existe sólo cuando el informe está finalizado. */
    public function download(User $user, Report $report): bool
    {
        return $report->isFinal() && $this->view($user, $report);
    }

    /** El autor puede descartar sus borradores; los finalizados sólo los elimina el administrador. */
    public function delete(User $user, Report $report): bool
    {
        return $user->can(Permission::ReportsDelete->value)
            || ($report->isDraft() && $report->user_id === $user->id);
    }

    /** Regenerar el PDF: administrador y sólo en informes finalizados. */
    public function regenerate(User $user, Report $report): bool
    {
        return $report->isFinal() && $user->can(Permission::ReportsDelete->value);
    }
}
