<?php

namespace App\Livewire;

use App\Models\Producer;
use App\Models\State;
use App\Models\Municipality;
use App\Models\Parish;
use Livewire\Component;
use Livewire\Attributes\On;

class EditProducer extends Component
{
    public Producer $producer;
    public $name = '';
    public $lastname = '';
    public $cedula_type = 'V';
    public $cedula = '';

    public $description = '';
    public $is_active = false;

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

    public function mount(Producer $producer)
    {
        $this->producer = $producer;
        $this->name = $producer->name;
        $this->lastname = $producer->lastname;
        $this->cedula_type = $producer->cedula_type ?? 'V';
        $this->cedula      = $producer->cedula;
        $this->description = $producer->description;
        $this->is_active = $producer->is_active;

        // Cargar ubicación existente
        $this->latitude = $producer->latitude;
        $this->longitude = $producer->longitude;
        $this->address = $producer->address ?? '';

        // Cargar nombres desde las relaciones
        if ($producer->state) {
            $this->state_id = $producer->state_id;
            $this->estado = $producer->state->name;
        }
        if ($producer->municipality) {
            $this->municipality_id = $producer->municipality_id;
            $this->municipio = $producer->municipality->name;
        }
        if ($producer->parish) {
            $this->parish_id = $producer->parish_id;
            $this->parroquia = $producer->parish->name;
        }
    }

    protected function rules()
    {
        return [
            'name' => ['required', 'string', 'min:3'],
            'lastname' => ['nullable', 'string', 'max:255'],
            'cedula_type' => ['required', 'in:V,E,P,J,G'],
            'cedula'      => [
                'required',
                'string',
                'regex:/^[0-9]{5,10}$/',
                \Illuminate\Validation\Rule::unique('producers')
                    ->where(fn ($q) => $q->where('cedula_type', $this->cedula_type))
                    ->ignore($this->producer->id),
            ],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'address' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function updatedCedula($value)
    {
        $this->cedula = $this->cleanCedula($value);
    }

    private function cleanCedula($value)
    {
        // Eliminar todo excepto letras y números
        $cleaned = preg_replace('/[^a-zA-Z0-9]/', '', $value);
        // Convertir a mayúsculas
        $cleaned = strtoupper($cleaned);
        // Asegurar que el primer carácter sea una letra válida y el resto dígitos
        if (preg_match('/^([VEPJG])(\d+)$/', $cleaned, $matches)) {
            return $matches[1] . $matches[2];
        }
        // Si no cumple, devolver el valor limpio (la validación fallará después)
        return $cleaned;
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

    #[On('locationUpdated')]
    public function updateLocation($data)
    {
        $this->latitude = $data['latitude'];
        $this->longitude = $data['longitude'];
        $this->address = $data['address'];

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

    public function update()
    {
        $validatedData = $this->validate();

        $this->syncLocationIds();

        $this->producer->update([
            'name' => $validatedData['name'],
            'lastname' => $validatedData['lastname'],
            'cedula_type' => $validatedData['cedula_type'],
            'cedula'      => $validatedData['cedula'],
            'description' => $validatedData['description'],
            'is_active' => $validatedData['is_active'],
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
            'text' => 'Productor actualizado exitosamente.'
        ]);
    }

    public function render()
    {
        return view('livewire.edit-producer');
    }
}