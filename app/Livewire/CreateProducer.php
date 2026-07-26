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
    public $latitude = null;
    public $longitude = null;
    public $address = '';

    protected function rules()
    {
        return [
            'name' => ['required', 'string', 'min:3'],
            'lastname' => ['required', 'string', 'max:255','min:3'],
            'description' => ['required', 'string'],
            'is_active' => ['boolean'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'address' => ['nullable', 'string', 'max:500'],
        ];
    }

    protected function validationAttributes()
    {
        return [
            'name' => 'nombre',
            'lastname' => 'apellido',
            'description' => 'descripción',
            'is_active' => 'productor activo',
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'address' => $this->address,
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