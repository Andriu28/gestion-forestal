<?php

    namespace App\Livewire;

    use App\Models\User;
    use Livewire\Component;
    use Illuminate\Support\Facades\Hash;
    use Illuminate\Validation\Rules\Password;
    use Illuminate\Validation\Rule;

class EditUser extends Component
{
    // Propiedad para almacenar el usuario que se va a editar
    public User $user;

    // Propiedades para los campos del formulario
    public $name = '';
    public $email = '';
    public $password = '';
    public $password_confirmation = '';

    // Método de inicialización
    public function mount(User $user)
    {
        $this->user = $user;
        $this->name = $user->name;
        $this->email = $user->email;
    }

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
                // La regla unique ahora ignora el email del usuario actual
                Rule::unique('users')->ignore($this->user->id)->whereNull('deleted_at')
            ],
            'password' => [
                'nullable', // La contraseña es opcional al editar
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised(),
                'confirmed'
            ],
            'password_confirmation' => 'nullable' // La confirmación es opcional
        ];
    }

    protected function validationAttributes()
    {
        return [
            'name' => 'nombre',
            'email' => 'correo electrónico',
            'password' => 'contraseña',
        ];
    }

    public function updated($propertyName)
    {
        // Validar las contraseñas juntas si se modifica alguna
        if (in_array($propertyName, ['password', 'password_confirmation'])) {
            $this->validate([
                'password' => $this->rules()['password'],
                'password_confirmation' => $this->rules()['password_confirmation']
            ]);
        } else {
            $this->validateOnly($propertyName);
        }
    }

    public function update()
    {
        $validatedData = $this->validate();
    
        // Actualizar el nombre y el correo electrónico
        $this->user->name = $validatedData['name'];
        $this->user->email = $validatedData['email'];
    
        // Si el campo de contraseña no está vacío, actualizarla
        if (!empty($validatedData['password'])) {
            $this->user->password = Hash::make($validatedData['password']);
        }
    
        $this->user->save();
    
        // Registrar la actividad y redirigir
        activity()
            ->causedBy(auth()->user())
            ->performedOn($this->user);
    
        return redirect()->route('admin.users.index')
            ->with('swal', [
                'icon' => 'success',
                'title' => 'Éxito',
                'text' => 'Usuario actualizado exitosamente.'
            ]);
    }

    public function render()
    {
        return view('livewire.edit-user');
    }
}
