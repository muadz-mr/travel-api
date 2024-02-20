<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

use function Laravel\Prompts\password;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

class CreateUserCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $user['name'] = text(
            label: "Name for the new user",
            required: "User's name is required."
        );
        $user['email'] = text(
            label: "Email for the new user",
            required: "User's email is required."
        );
        $user['password'] = password(
            label: 'Password for the new user',
            required: 'Password is required.'
        );

        $roles = Role::select('id', 'name')->get();
        if (count($roles) == 0) {
            $this->error('Roles do not exists.');
            return -1;
        }
        $defaultRoleIndex = null;
        $roleNames = $roles->map(function (mixed $item, int $key) use (&$defaultRoleIndex) {
            if ($item->name == 'editor') $defaultRoleIndex = $key;
            return $item->name;
        });
        if ($defaultRoleIndex == null) {
            $this->error("Default role 'editor' does not exist.");
            return -1;
        }

        $roleName = select(
            label: "Role of the new user",
            options: $roleNames,
            default: $defaultRoleIndex,
        );

        $roleId = $roles->where('name', $roleName)->pluck('id')[0];

        $validator = Validator::make($user, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users'],
            'password' => ['required', Password::defaults()]
        ]);
        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return -1;
        }

        DB::transaction(function () use ($user, $roleId) {
            $user['password'] = Hash::make($user['password']);
            $newUser = User::create($user);
            $newUser->roles()->attach($roleId);
        });

        $this->info("User {$user['email']} created successfully.");
        return 0;
    }
}
