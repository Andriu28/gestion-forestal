<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Component;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rule;

class FormUser extends Component
{
    public $name = '';
    public $email = '';
    public $password = '';
    public $password_confirmation = '';
    public $showPasswordErrors = false;

    protected function rules()
    {
        $rules = [
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
            'password' => [
                'required',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised(),
                    
            ],
            'password_confirmation' => '',
            'role' => [ 
                'required',
                Rule::in(['basico', 'administrador'])
            ]
        ];

        // Solo agregar 'confirmed' cuando ambas contraseñas tienen valor
        if ($this->password && $this->password_confirmation) {
            $rules['password'][] = 'confirmed';
        }

        return $rules;
    }

    protected function validationAttributes()
    {
        return [
            'name' => 'nombre',
            'email' => 'correo electrónico',
            'password' => 'contraseña',
            'role' => 'rol',
        ];
    }

    protected function messages()
    {
        return [
            // Mensajes para el campo 'name'
            'name.required' => 'El nombre es obligatorio.',
            'name.regex' => 'El nombre solo puede contener letras, espacios y apóstrofes.',
            'name.min' => 'El nombre debe tener al menos 3 caracteres.',

            // Mensajes para el campo 'email'
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El formato del correo electrónico no es válido.',
            'email.unique' => 'Este correo electrónico ya está registrado.',
            
            // Mensajes para el campo 'password'
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.mixed' => 'La contraseña debe contener letras mayúsculas y minúsculas.',
            'password.numbers' => 'La contraseña debe contener al menos un número.',
            'password.symbols' => 'La contraseña debe contener al menos un símbolo.',
            'password.uncompromised' => 'Esta contraseña ha sido expuesta en una filtración de datos. Por favor, elige una diferente.',
            'password.confirmed' => 'La confirmación de la contraseña no coincide.',

            // Mensajes para el campo 'password_confirmation'
            'password_confirmation.required' => 'La confirmación de la contraseña es obligatoria.',

            // Mensaje para 'role' ← NUEVO
            'role.required' => 'Debes seleccionar un rol.',
            'role.in' => 'El rol seleccionado no es válido.',
        ];
    }
    
    public function updated($propertyName)
    {
        // Valida todos los campos, excepto los de contraseña, uno por uno.
        if (!in_array($propertyName, ['password', 'password_confirmation'])) {
            $this->validateOnly($propertyName);
        }
        
        // Si se actualiza cualquiera de los campos de contraseña,
        // valida ambos para que la regla 'confirmed' funcione correctamente.
        if (in_array($propertyName, ['password', 'password_confirmation'])) {
            $this->validate([
                'password' => $this->rules()['password'],
                'password_confirmation' => $this->rules()['password_confirmation']
            ], $this->messages(), $this->validationAttributes());
        }
    }

    public function store()
    {
        $validatedData = $this->validate();

        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'role' => $validatedData['role'],
        ]);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($user);

        $this->reset();

        return redirect()->route('admin.users.index')
            ->with('swal', [
                'icon' => 'success',
                'title' => 'Éxito',
                'text' => 'Usuario creado exitosamente.'
            ]);
    }

    public function update()
    {

        $validatedData = $this->validate();

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
        ];

        /* // Actualizar password solo si se proporciona
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        } */

        $user->update($data);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($user);

        // Usar with() en lugar de session()->flash()
        return redirect()->route('admin.users.index')
            ->with('swal', [
                'icon' => 'success',
                'title' => 'Éxito',
                'text' => 'Usuario actualizado exitosamente.'
            ]);
    }

    public function render()
    {
        return view('livewire.form-user');
    }
}