<?php

namespace App\Livewire;

use App\Models\Producer;
use Livewire\Component;
use Livewire\Attributes\On;

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
            'lastname' => ['required', 'string', 'max:255', 'min:3'],
            'description' => ['required', 'string'],
            'is_active' => ['boolean'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'address' => ['nullable', 'string', 'max:500'],
        ];
    }


    #[On('locationUpdated')]
    public function locationUpdated($data)
    {
        $this->latitude = $data['latitude'];
        $this->longitude = $data['longitude'];
        $this->address = $data['address'];
    }

    public function store()
    {
        $validated = $this->validate();

        Producer::create([
            'name' => $validated['name'],
            'lastname' => $validated['lastname'],
            'description' => $validated['description'],
            'is_active' => $validated['is_active'],
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'address' => $this->address,
        ]);

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