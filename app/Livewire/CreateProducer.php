<?php

namespace App\Livewire;

use App\Models\Producer;
use Livewire\Component;

class CreateProducer extends Component
{
    public $name = '';
    public $lastname = '';
    public $description = '';
    public $is_active = true;

    protected function rules()
    {
        return [
            'name' => ['required', 'string', 'min:3'],
            'lastname' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ];
    }

    protected function validationAttributes()
    {
        return [
            'name' => 'nombre',
            'lastname' => 'apellido',
            'description' => 'descripción',
            'is_active' => 'productor activo'
        ];
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    public function store()
    {
        $validatedData = $this->validate();

        Producer::create($validatedData);
        
        return redirect()->route('producers.index')->with('swal', [
            'icon' => 'success',
            'title' => 'Éxito',
            'text' => 'Productor creado exitosamente.'
        ]);
    }

    public function render()
    {
        return view('livewire.create-producer');
    }
}