<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name'      => 'Administrator',
            'email'     => 'admin@example.com',
            'password'  => bcrypt('qgURQ3+<')
        ]);

        $permissions = Permission::all();

        $role = Role::find(1);
        $role->syncPermissions($permissions);

        $user = User::find(1);
        $user->assignRole($role->name);
    }
}
