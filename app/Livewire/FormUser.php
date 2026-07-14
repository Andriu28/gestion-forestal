<?php

namespace App\Livewire;

use App\Models\User;
use App\Mail\NewUserPasswordMail;
use Livewire\Component;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class FormUser extends Component
{
    public $name = '';
    public $email = '';
    public $role = 'basico';

    protected function rules()
    {
        return [
            'name' => [
                'required',
                'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s\']+$/',
                'min:3'
            ],
            'email' => [
                'required',
                'email',
                Rule::unique('users')->whereNull('deleted_at')
            ],
            'role' => [
                'required',
                Rule::in(['basico', 'administrador'])
            ]
        ];
    }

    protected function validationAttributes()
    {
        return [
            'name' => 'nombre',
            'email' => 'correo electrónico',
            'role' => 'rol',
        ];
    }

    protected function messages()
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'name.regex' => 'El nombre solo puede contener letras, espacios y apóstrofes.',
            'name.min' => 'El nombre debe tener al menos 3 caracteres.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El formato del correo electrónico no es válido.',
            'email.unique' => 'Este correo electrónico ya está registrado.',
            'role.required' => 'Debes seleccionar un rol.',
            'role.in' => 'El rol seleccionado no es válido.',
        ];
    }

    /**
     * Validación en tiempo real solo para campos que cambian
     */
    public function updated($propertyName)
    {
        // Definimos qué campos se validan en tiempo real y con qué reglas
        $rulesForProperty = [
            'name' => $this->rules()['name'],
            'email' => $this->rules()['email'],
            // 'role' no se valida en tiempo real porque es un select con opciones fijas
        ];

        if (array_key_exists($propertyName, $rulesForProperty)) {
            // Validamos solo la propiedad que cambió
            $this->validateOnly($propertyName, [$propertyName => $rulesForProperty[$propertyName]]);
        }
    }

    public function store()
    {
        $validatedData = $this->validate();

        $plainPassword = Str::password(
            length: 12,
            letters: true,
            numbers: true,
            symbols: true,
            spaces: false
        );

        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($plainPassword),
            'role' => $validatedData['role'],
        ]);

        Mail::to($user->email)->send(new NewUserPasswordMail($user->name, $plainPassword));

        $this->reset();

        return redirect()->route('admin.users.index')
            ->with('swal', [
                'icon' => 'success',
                'title' => 'Éxito',
                'text' => 'Usuario creado exitosamente. Se ha enviado un correo con la contraseña.'
            ]);
    }

    public function render()
    {
        return view('livewire.form-user');
    }
}