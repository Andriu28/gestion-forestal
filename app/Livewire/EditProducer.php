<?php

namespace App\Livewire;

use App\Models\Producer;
use Livewire\Component;
use Livewire\Attributes\On;

class EditProducer extends Component
{
    public Producer $producer;
    public $name = '';
    public $lastname = '';
    public $description = '';
    public $is_active = false;

    // Propiedades para la ubicación
    public $latitude = null;
    public $longitude = null;
    public $address = '';

    public function mount(Producer $producer)
    {
        $this->producer = $producer;
        $this->name = $producer->name;
        $this->lastname = $producer->lastname;
        $this->description = $producer->description;
        $this->is_active = $producer->is_active;
        
        // Cargar ubicación existente del productor
        $this->latitude = $producer->latitude;
        $this->longitude = $producer->longitude;
        $this->address = $producer->address ?? '';
    }

    protected function rules()
    {
        return [
            'name' => ['required', 'string', 'min:3'],
            'lastname' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
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
            'latitude' => 'latitud',
            'longitude' => 'longitud',
            'address' => 'dirección',
        ];
    }

    // Escuchar el evento del componente hijo
    #[On('locationUpdated')]
    public function updateLocation($data)
    {
        $this->latitude = $data['latitude'];
        $this->longitude = $data['longitude'];
        $this->address = $data['address'];
    }

    public function update()
    {
        $validatedData = $this->validate();

        $this->producer->update([
            'name' => $validatedData['name'],
            'lastname' => $validatedData['lastname'],
            'description' => $validatedData['description'],
            'is_active' => $validatedData['is_active'],
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'address' => $this->address,
        ]);

        return redirect()->route('producers.index')->with('swal', [
            'icon' => 'success',
            'title' => 'Éxito',
            'text' => 'Productor actualizado exitosamente.'
        ]);
    }

    public function render()
    {
        return view('livewire.edit-producer');
    }
}