<?php

namespace App\Livewire\Components;

use Livewire\Component;

class LocationPicker extends Component
{
    // Propiedades sincronizadas con la vista
    public $latitude = null;
    public $longitude = null;
    public $address = '';
    public $mapId = 'map';

    // Configuración
    public $showCoordinates = true;
    public $showAddress = true;
    public $showLocateButton = true;
    public $initialZoom = 13;
    public $initialCenterLon = -63.2535;
    public $initialCenterLat = 10.6694;
    public $placeholder = 'Haz clic en el mapa para seleccionar una ubicación';

    protected function rules()
    {
        return [
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'address' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function mount(
        $latitude = null,
        $longitude = null,
        $address = '',
        $mapId = 'map',
        $showCoordinates = true,
        $showAddress = true,
        $showLocateButton = true,
        $initialZoom = 13,
        $initialCenterLon = -63.2535,
        $initialCenterLat = 10.6694,
        $placeholder = 'Haz clic en el mapa para seleccionar una ubicación'
    ) {
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->address = $address;
        $this->mapId = $mapId;
        $this->showCoordinates = $showCoordinates;
        $this->showAddress = $showAddress;
        $this->showLocateButton = $showLocateButton;
        $this->initialZoom = $initialZoom;
        $this->initialCenterLon = $initialCenterLon;
        $this->initialCenterLat = $initialCenterLat;
        $this->placeholder = $placeholder;
    }

    // Este método es llamado desde JavaScript para actualizar los datos
    public function setLocation($latitude, $longitude, $address)
    {
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->address = $address;

        // Livewire 3: dispatch envía el evento hacia arriba (padre)
        $this->dispatch('locationUpdated', [
            'latitude' => $latitude,
            'longitude' => $longitude,
            'address' => $address,
        ]);
    }

    public function render()
    {
        return view('livewire.components.location-picker');
    }
}