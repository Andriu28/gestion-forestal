<?php

namespace App\Livewire;

use App\Models\Producer;
use App\Models\State;
use App\Models\Municipality;
use App\Models\Parish;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Computed;
use Illuminate\Validation\Rule;

class CreateProducer extends Component
{
    public $name = '';
    public $lastname = '';
    public $cedula_type = 'V';
    public $cedula = '';
    public $code = '';
    public $description = '';
    public $is_active = true;

    // Ubicación
    public $latitude = null;
    public $longitude = null;
    public $address = '';

    // Componentes de dirección
    public $parroquia = '';
    public $municipio = '';
    public $estado = '';

    // IDs maestros
    public $state_id = null;
    public $municipality_id = null;
    public $parish_id = null;

    /**
     * Límites por tipo de documento (usados por JS-less validation).
     */
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

    /**
     * Reglas de validación completas (usadas en store()).
     */
    protected function rules()
    {
        return [
            'name'        => ['required', 'string', 'min:3'],
            'lastname'    => ['required', 'string', 'max:255', 'min:3'],
            'cedula_type' => ['required', 'in:V,E,P,J,G'],
            'cedula'      => [
                'required',
                'string',
                $this->reglaFormatoCedula(),
                Rule::unique('producers')
                    ->where(fn ($q) => $q->where('cedula_type', $this->cedula_type)),
            ],
            'description' => ['required', 'string'],
            'is_active'   => ['boolean'],
            'latitude'    => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'   => ['nullable', 'numeric', 'between:-180,180'],
            'address'     => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Regla de formato puro (sin unique). Se reutiliza en updatedCedula.
     */
    protected function reglaFormatoCedula(): string
    {
        [$min, $max] = $this->cedulaLimits()[$this->cedula_type] ?? [5, 10];
        return 'regex:/^\d{' . $min . ',' . $max . '}$/';
    }

    protected function messages()
    {
        $limits = $this->cedulaLimits();
        [$min, $max] = $limits[$this->cedula_type] ?? [5, 10];

        return [
            'name.required'        => 'El nombre es obligatorio.',
            'name.min'             => 'El nombre debe tener al menos 3 caracteres.',
            'lastname.required'    => 'El apellido es obligatorio.',
            'lastname.min'         => 'El apellido debe tener al menos 3 caracteres.',
            'cedula.required'      => 'La cédula de identidad es obligatoria.',
            'cedula.regex'         => "La cédula debe contener entre {$min} y {$max} dígitos para el tipo {$this->cedula_type}.",
            'cedula.unique'        => 'Ya existe un productor con esa cédula para el tipo seleccionado.',
            'description.required' => 'La descripción es obligatoria.',
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
        ];
    }

    /**
     * Al escribir cédula:
     *   1. Limpiar a solo dígitos (defensa + respuesta al oninput del Blade).
     *   2. Validar solo formato (sin unique) para no golpear la BD en cada tecla.
     *   3. La regla unique se valida al hacer submit.
     */
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

    /**
     * Al cambiar el tipo, revalidar la cédula existente con los nuevos límites.
     */
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
    public function locationUpdated($data)
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

    public function store()
    {
        // Validación completa (incluye unique)
        $validated = $this->validate();

        // Defensa adicional por si llega algo raro
        $validated['cedula'] = preg_replace('/\D/', '', $validated['cedula']);

        $this->syncLocationIds();

        Producer::create([
            'name'            => $validated['name'],
            'lastname'        => $validated['lastname'],
            'cedula_type'     => $validated['cedula_type'],
            'cedula'          => $validated['cedula'],
            'code'            => $this->codePreview(),
            'description'     => $validated['description'],
            'is_active'       => $validated['is_active'],
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
            'text'  => 'Productor creado exitosamente.',
        ]);
    }

     /**
     * Vista previa del código (CSJ + tipo + cédula).
     * Se recalcula en cada render, que se dispara tras cada debounce
     * de cedula y cedula_type.
     */
    #[Computed]
    public function codePreview(): ?string
    {
        if (empty($this->cedula_type) || empty($this->cedula)) {
            return null;
        }

        $digits = preg_replace('/\D/', '', (string) $this->cedula);

        if ($digits === '') {
            return null;
        }

        return 'CSJ' . strtoupper($this->cedula_type) . $digits;
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
        return view('livewire.create-producer');
    }
}