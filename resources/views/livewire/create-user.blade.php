<div>
    <form wire:submit="store">
        @csrf

        <!-- Nombre -->
        <div class="mt-4">
            <x-input-label for="name" :value="__('Nombre')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" 
                wire:model.live.debounce.250ms="name" 
                autofocus
                oninput="if(this.value.length === 1) this.value = this.value.toUpperCase();" />
            <x-input-error :messages="$errors->first('name')" class="mt-2" />
        </div>

        <!-- Email -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" wire:model.live.debounce.250ms="email" />
            <x-input-error :messages="$errors->first('email')" class="mt-2" />
        </div>

        <!-- Rol -->
        <div class="mt-4">
            <x-input-label for="role" :value="__('Rol')" />
            <x-select-input
                name="role"
                id="role"
                wire:model.live="role"
                :options="[
                    'basico' => 'Básico',
                    'administrador' => 'Administrador'
                ]"
            />
            <x-input-error :messages="$errors->first('role')" class="mt-2" />
        </div>

        <!-- Botones -->
        <div class="flex items-center justify-end mt-4 space-x-4">
            <x-go-back-button />
            <x-primary-button class="ms-4">
                {{ __('Crear Usuario') }}
            </x-primary-button>
        </div>
    </form>
</div>