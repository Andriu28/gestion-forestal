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
    public $cedula_type = 'V'; 
    public $cedula = '';
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
            'cedula_type' => ['required', 'in:V,E,P,J,G'],
            'cedula'      => [
                'required',
                'string',
                'regex:/^[0-9]{5,10}$/',
                \Illuminate\Validation\Rule::unique('producers')
                    ->where(fn ($q) => $q->where('cedula_type', $this->cedula_type)),
            ],
            'description' => ['required', 'string'],
            'is_active' => ['boolean'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'address' => ['nullable', 'string', 'max:500'],
        ];
    }

    protected function messages()
    {
        return [
            'cedula.regex'  => 'La cédula debe contener entre 5 y 10 dígitos (sin puntos ni guiones).',
            'cedula.unique' => 'Ya existe un productor con esa cédula.',
        ];
    }

    // Limpieza automática al escribir
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

        $validated['cedula'] = preg_replace('/\D/', '', $validated['cedula']);

        // Asegurar IDs antes de guardar
        $this->syncLocationIds();

        Producer::create([
            'name' => $validated['name'],
            'lastname' => $validated['lastname'],
            'cedula_type' => $validated['cedula_type'],
            'cedula'      => $this->cedula,
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