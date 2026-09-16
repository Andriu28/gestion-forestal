<?php

namespace App\Livewire;

use App\Models\Producer;
use App\Models\State;
use App\Models\Municipality;
use App\Models\Parish;
use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Validation\Rule;

class EditProducer extends Component
{
    public Producer $producer;
    public $name = '';
    public $lastname = '';
    public $cedula_type = 'V';
    public $cedula = '';
    public $description = '';
    public $is_active = false;

    public $latitude = null;
    public $longitude = null;
    public $address = '';

    public $parroquia = '';
    public $municipio = '';
    public $estado = '';

    public $state_id = null;
    public $municipality_id = null;
    public $parish_id = null;

    public function mount(Producer $producer)
    {
        $this->producer    = $producer;
        $this->name        = $producer->name;
        $this->lastname    = $producer->lastname;
        $this->cedula_type = $producer->cedula_type ?? 'V';
        $this->cedula      = $producer->cedula;
        $this->description = $producer->description;
        $this->is_active   = $producer->is_active;

        $this->latitude    = $producer->latitude;
        $this->longitude   = $producer->longitude;
        $this->address     = $producer->address ?? '';

        if ($producer->state) {
            $this->state_id = $producer->state_id;
            $this->estado   = $producer->state->name;
        }
        if ($producer->municipality) {
            $this->municipality_id = $producer->municipality_id;
            $this->municipio       = $producer->municipality->name;
        }
        if ($producer->parish) {
            $this->parish_id = $producer->parish_id;
            $this->parroquia = $producer->parish->name;
        }
    }

    protected function cedulaLimits(): array
    {
        return [
            'V' => [5, 8],
            'E' => [5, 8],
            'G' => [5, 8],
            'P' => [6, 10],
            'J' => [8, 10],
        ];
    }

    protected function reglaFormatoCedula(): string
    {
        [$min, $max] = $this->cedulaLimits()[$this->cedula_type] ?? [5, 10];
        return 'regex:/^\d{' . $min . ',' . $max . '}$/';
    }

    protected function rules()
    {
        return [
            'name'        => ['required', 'string', 'min:3'],
            'lastname'    => ['nullable', 'string', 'min:3'],
            'cedula_type' => ['required', 'in:V,E,P,J,G'],
            'cedula'      => [
                'required',
                'string',
                $this->reglaFormatoCedula(),
                Rule::unique('producers')
                    ->where(fn ($q) => $q->where('cedula_type', $this->cedula_type))
                    ->ignore($this->producer->id),
            ],
            'description' => ['nullable', 'string'],
            'is_active'   => ['boolean'],
            'latitude'    => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'   => ['nullable', 'numeric', 'between:-180,180'],
            'address'     => ['nullable', 'string', 'max:500'],
        ];
    }

    protected function messages()
    {
        $limits = $this->cedulaLimits();
        [$min, $max] = $limits[$this->cedula_type] ?? [5, 10];

        return [
            'name.required'     => 'El nombre es obligatorio.',
            'name.min'          => 'El nombre debe tener al menos 3 caracteres.',
            'lastname.required' => 'El apellido es obligatorio.',
            'lastname.min'      => 'El apellido debe tener al menos 3 caracteres.',
            'cedula.required'   => 'La cédula es obligatoria.',
            'cedula.regex'      => "La cédula debe contener entre {$min} y {$max} dígitos para el tipo {$this->cedula_type}.",
            'cedula.unique'     => 'Ya existe un productor con esa cédula para el tipo seleccionado.',
        ];
    }

    protected function validationAttributes()
    {
        return [
            'name'        => 'nombre',
            'lastname'    => 'apellido',
            'cedula'      => 'cédula',
            'cedula_type' => 'tipo de cédula',
            'description' => 'descripción',
            'is_active'   => 'productor activo',
            'latitude'    => 'latitud',
            'longitude'   => 'longitud',
            'address'     => 'dirección',
        ];
    }

    public function updatedCedula($value)
    {
        $cleaned = preg_replace('/\D/', '', (string) $value);
        $cleaned = substr($cleaned, 0, 10);

        if ($cleaned !== $value) {
            $this->cedula = $cleaned;
        }

        $this->validateOnly('cedula', [
            'cedula' => ['required', 'string', $this->reglaFormatoCedula()],
        ]);
    }

    public function updatedCedulaType()
    {
        $this->resetValidation('cedula');

        if (!empty($this->cedula)) {
            $this->validateOnly('cedula', [
                'cedula' => ['required', 'string', $this->reglaFormatoCedula()],
            ]);
        }
    }

    #[On('locationUpdated')]
    public function updateLocation($data)
    {
        $this->latitude  = $data['latitude'];
        $this->longitude = $data['longitude'];
        $this->address   = $data['address'];

        $components      = $data['components'] ?? [];
        $this->parroquia = $components['parroquia'] ?? '';
        $this->municipio = $components['municipio'] ?? '';
        $this->estado    = $components['estado']    ?? '';
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
                    'name'     => $this->municipio,
                    'state_id' => $state->id,
                ]);
                $this->municipality_id = $municipality->id;

                if (!empty($this->parroquia)) {
                    $parish = Parish::firstOrCreate([
                        'name'            => $this->parroquia,
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

        $validatedData['cedula'] = preg_replace('/\D/', '', $validatedData['cedula']);

        $this->syncLocationIds();

        $this->producer->update([
            'name'            => $validatedData['name'],
            'lastname'        => $validatedData['lastname'],
            'cedula_type'     => $validatedData['cedula_type'],
            'cedula'          => $validatedData['cedula'],
            'description'     => $validatedData['description'],
            'is_active'       => $validatedData['is_active'],
            'latitude'        => $this->latitude,
            'longitude'       => $this->longitude,
            'address'         => $this->address,
            'state_id'        => $this->state_id,
            'municipality_id' => $this->municipality_id,
            'parish_id'       => $this->parish_id,
        ]);

        return redirect()->route('producers.index')->with('swal', [
            'icon'  => 'success',
            'title' => 'Éxito',
            'text'  => 'Productor actualizado exitosamente.',
        ]);
    }

    public function updatedName()
    {
        $this->validateOnly('name');
    }

    public function updatedLastname()
    {
        $this->validateOnly('lastname');
    }

    public function updatedDescription()
    {
        $this->validateOnly('description');
    }

    public function render()
    {
        return view('livewire.edit-producer');
    }
}