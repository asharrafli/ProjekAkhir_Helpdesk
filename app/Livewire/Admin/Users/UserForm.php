<?php

namespace App\Livewire\Admin\Users;

use Livewire\Component;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;

class UserForm extends Component
{
    public $user;
    public $isEdit = false;
    
    public $name = '';
    public $email = '';
    public $phone = '';
    public $password = '';
    public $password_confirmation = '';
    public $status = 'active';
    public $is_admin = false;
    public $selectedRoles = [];

    public function mount($user = null)
    {
        $this->user = $user;
        $this->isEdit = !is_null($user);

        if ($this->isEdit) {
            $this->name = $user->name;
            $this->email = $user->email;
            $this->phone = $user->phone;
            $this->status = $user->status;
            $this->is_admin = $user->is_admin;
            $this->selectedRoles = $user->roles->pluck('name')->toArray();
        }
    }

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($this->user->id ?? null),
            ],
            'phone' => 'nullable|string|max:20',
            'password' => $this->isEdit ? 'nullable|string|min:8|confirmed' : 'required|string|min:8|confirmed',
            'status' => 'required|in:active,inactive,suspended',
            'is_admin' => 'boolean',
            'selectedRoles' => 'array',
            'selectedRoles.*' => 'exists:roles,name',
        ];
    }

    public function save()
    {
        $this->validate();

        try {
            if ($this->isEdit) {
                $this->user->update([
                    'name' => $this->name,
                    'email' => $this->email,
                    'phone' => $this->phone,
                    'status' => $this->status,
                    'is_admin' => $this->is_admin,
                ]);

                if ($this->password) {
                    $this->user->update(['password' => Hash::make($this->password)]);
                }
            } else {
                $this->user = User::create([
                    'name' => $this->name,
                    'email' => $this->email,
                    'phone' => $this->phone,
                    'password' => Hash::make($this->password),
                    'status' => $this->status,
                    'is_admin' => $this->is_admin,
                ]);
            }

            // Sync roles
            $this->user->syncRoles($this->selectedRoles);

            session()->flash('success', $this->isEdit ? 'User updated successfully.' : 'User created successfully.');
            return redirect()->route('admin.users.index');

        } catch (\Exception $e) {
            session()->flash('error', 'An error occurred while saving the user: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $roles = Role::all();
        return view('livewire.admin.users.user-form', compact('roles'));
    }
}