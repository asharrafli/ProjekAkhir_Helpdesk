<?php

namespace App\Livewire\Admin\Users;

use Livewire\Component;
use App\Models\User;

class SimpleUserIndex extends Component
{
    public function render()
    {
        $users = User::all();
        
        return view('livewire.admin.users.simple-user-index', compact('users'));
    }
}