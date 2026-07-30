<?php

namespace App\Livewire;

use App\Models\Producer;
use App\Models\State;
use App\Models\Municipality;
use App\Models\Parish;
use Livewire\Component;
use Livewire\Attributes\On;

class CreateProducer extends Component
{
    public $name = '';
    public $lastname = '';
    public $description = '';
    public $is_active = true;

    // Ubicación
    public $latitude = null;
    public $longitude = null;
    public $address = '';

    // Componentes de dirección (para mostrar)
    public $parroquia = '';
    public $municipio = '';
    public $estado = '';

    // IDs de las tablas maestras
    public $state_id = null;
    public $municipality_id = null;
    public $parish_id = null;

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

        // Extraer componentes
        $components = $data['components'] ?? [];
        $this->parroquia = $components['parroquia'] ?? '';
        $this->municipio = $components['municipio'] ?? '';
        $this->estado = $components['estado'] ?? '';

    }

    protected function syncLocationIds()
    {
        $this->state_id = null;
        $this->municipality_id = null;
        $this->parish_id = null;

        if (!empty($this->estado)) {
            $state = State::firstOrCreate(['name' => $this->estado]);
            $this->state_id = $state->id;

            if (!empty($this->municipio)) {
                $municipality = Municipality::firstOrCreate([
                    'name' => $this->municipio,
                    'state_id' => $state->id,
                ]);
                $this->municipality_id = $municipality->id;

                if (!empty($this->parroquia)) {
                    $parish = Parish::firstOrCreate([
                        'name' => $this->parroquia,
                        'municipality_id' => $municipality->id,
                    ]);
                    $this->parish_id = $parish->id;
                }
            }
        }
    }

    public function store()
    {
        $validated = $this->validate();

        // Asegurar IDs antes de guardar
        $this->syncLocationIds();

        Producer::create([
            'name' => $validated['name'],
            'lastname' => $validated['lastname'],
            'description' => $validated['description'],
            'is_active' => $validated['is_active'],
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'address' => $this->address,
            'state_id' => $this->state_id,
            'municipality_id' => $this->municipality_id,
            'parish_id' => $this->parish_id,
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