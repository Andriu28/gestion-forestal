<?php

namespace App\Livewire\Components;

use Livewire\Component;

class LocationPicker extends Component
{
    public $latitude = null;
    public $longitude = null;
    public $address = '';
    public $mapId = 'map';

    public $showCoordinates = true;
    public $showAddress = true;
    public $showLocateButton = true;
    public $initialZoom = 13;
    public $initialCenterLon = -63.2535;
    public $initialCenterLat = 10.6694;
    public $placeholder = 'Haz clic en el mapa para seleccionar una ubicación';

    // Componentes de dirección (para mostrar en el componente)
    public $parroquia = '';
    public $municipio = '';
    public $estado = '';

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

    public function setLocation($latitude, $longitude, $address, $components = null)
    {
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->address = $address;

        if ($components) {
            $this->parroquia = $components['parroquia'] ?? '';
            $this->municipio = $components['municipio'] ?? '';
            $this->estado = $components['estado'] ?? '';
        }

        $this->dispatch('locationUpdated', [
            'latitude' => $latitude,
            'longitude' => $longitude,
            'address' => $address,
            'components' => $components ?? [],
        ]);
    }

    public function render()
    {
        return view('livewire.components.location-picker');
    }
}