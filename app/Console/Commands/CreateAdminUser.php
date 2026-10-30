<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateAdminUser extends Command
{
    protected $signature = 'savoury:create-admin
        {--name= : Admin display name}
        {--email= : Admin email address}
        {--password= : Admin password (omit to be prompted securely)}';

    protected $description = 'Create an active administrator without hard-coding credentials in seeders';

    public function handle(): int
    {
        $name = $this->option('name') ?: $this->ask('Name', 'Savoury Admin');
        $email = $this->option('email') ?: $this->ask('Email');
        $password = $this->option('password') ?: $this->secret('Password');

        $validator = Validator::make([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ], [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:12'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $adminRole = Role::query()->where('slug', 'admin')->first();

        if ($adminRole === null) {
            $this->error('Admin role missing. Run php artisan db:seed --class=RolePermissionSeeder first.');

            return self::FAILURE;
        }

        $user = User::query()->create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'is_active' => true,
        ]);

        $user->roles()->sync([$adminRole->id]);

        $this->info("Admin created: {$user->email}");

        return self::SUCCESS;
    }
}
