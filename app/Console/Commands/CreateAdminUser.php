<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Exception;

class CreateAdminUser extends Command
{
    protected $signature = 'create:admin';
    protected $description = 'Crea el primer usuario administrador con validaciones de seguridad';

    public function handle()
    {
        // 1. Definir los datos a registrar
        $data = [
            'name' => 'Admin',
            'email' => 'admin@gmail.com',
            'password' => '12345678',
            'role' => 'administrador',
        ];

        // 2. Aplicar validaciones (Iguales a las de un formulario)
        $validator = Validator::make($data, [
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'role'  => 'required|string'
        ]);

        // 3. Si la validación falla, mostrar errores específicos en rojo
        if ($validator->fails()) {
            $this->error('Error: No se pudo crear el administrador.');
            
            foreach ($validator->errors()->all() as $error) {
                $this->line("- $error");
            }
            return Command::FAILURE;
        }

        try {
            // 4. Intentar crear el usuario
            User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'email_verified_at' => now(),
                'password' => Hash::make($data['password']),
                'role' => $data['role'],
            ]);

            $this->info("¡Éxito! El usuario administrador ({$data['email']}) ha sido creado correctamente.");
            return Command::SUCCESS;

        } catch (Exception $e) {
            // 5. Capturar errores inesperados (ej. problemas de conexión a la BD)
            $this->error("Ocurrió un error inesperado en la base de datos:");
            $this->error($e->getMessage());
            return Command::FAILURE;
        }
    }
}
