<?php

namespace App\Livewire;

use App\Models\Template;
use App\Models\User;
use App\Repositories\ReportRepository;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Dashboard')]
class Dashboard extends Component
{
    public function render(ReportRepository $reports)
    {
        $user = auth()->user();

        return view('livewire.dashboard', [
            'stats' => $reports->stats($user),
            'templatesCount' => Template::active()->count(),
            'usersCount' => $user->can('users.manage') ? User::count() : null,
            'recent' => $reports->visibleTo($user)->with(['user:id,name', 'template:id,name'])->latest('updated_at')->latest('id')->limit(6)->get(),
        ]);
    }
}
