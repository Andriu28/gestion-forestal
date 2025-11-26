<?php

namespace App\Livewire;

use App\Models\Producer;
use Livewire\Component;

class EditProducer extends Component
{
    public Producer $producer;
    public $name = '';
    public $lastname = '';
    public $description = '';
    public $is_active = false;

    public function mount(Producer $producer)
    {
        $this->producer = $producer;
        $this->name = $producer->name;
        $this->lastname = $producer->lastname;
        $this->description = $producer->description;
        $this->is_active = $producer->is_active;
    }
    
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

    public function update()
    {
        $validatedData = $this->validate();

        $this->producer->update($validatedData);

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