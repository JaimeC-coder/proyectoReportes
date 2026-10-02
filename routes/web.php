<?php

use App\Http\Controllers\ReportPdfController;
use App\Livewire\Dashboard;
use App\Livewire\Reports;
use App\Livewire\Settings;
use App\Livewire\Templates;
use App\Livewire\Users;
use Illuminate\Support\Facades\Route;

// No hay páginas públicas: la raíz lleva al dashboard (o al login si no hay sesión).
Route::redirect('/', '/dashboard');

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'active'])->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');

    // Informes
    Route::get('/informes', Reports\Index::class)->name('reports.index');
    Route::get('/informes/nuevo', Reports\Create::class)->middleware('permission:reports.create')->name('reports.create');
    Route::get('/informes/{report}', Reports\Show::class)->name('reports.show');
    Route::get('/informes/{report}/pdf', [ReportPdfController::class, 'show'])->name('reports.pdf');
    Route::get('/informes/{report}/descargar', [ReportPdfController::class, 'download'])->name('reports.download');

    // Administración
    Route::middleware('permission:templates.manage')->group(function () {
        Route::get('/plantillas', Templates\Index::class)->name('templates.index');
        Route::get('/plantillas/nueva', Templates\Editor::class)->name('templates.create');
        Route::get('/plantillas/{template}/editar', Templates\Editor::class)->name('templates.edit');
    });

    Route::get('/usuarios', Users\Index::class)->middleware('permission:users.manage')->name('users.index');
    Route::get('/ajustes', Settings\Edit::class)->middleware('permission:settings.manage')->name('settings.edit');
});
