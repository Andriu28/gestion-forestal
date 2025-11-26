<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateAdminUser extends Command
{
    protected $signature = 'create:admin';
    protected $description = 'crea el primer usuario administrador para el sistema';

    public function handle()
    {

        User::create([
            'name' => 'Admin',
            'email' => 'admin@gmail.com',
            'email_verified_at' => now(), 
            'password' => Hash::make('12345678'),
            'role' => 'administrador',
        ]);

        $this->info("¡Usuario administrador creado con éxito!");
        return Command::SUCCESS;
    }
}
