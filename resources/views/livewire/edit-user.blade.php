<div>
    <form wire:submit="update">
        @csrf

        <div class="mt-4">
            <x-input-label for="name" :value="__('Nombre')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" wire:model.live.debounce.250ms="name" autofocus />
            <x-input-error :messages="$errors->first('name')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" wire:model.live.debounce.250ms="email" />
            <x-input-error :messages="$errors->first('email')" class="mt-2" />
        </div>

        <div class="mt-4 text-gray-900 dark:text-gray-300">
            <x-input-label for="role" :value="__('Rol')" />
            <select id="role" class="block mt-1 w-full bg-gray-200  border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"" wire:model.live="role">
                <option value="basico" @if ($user->role === 'basico') selected @endif>{{ __('Básico') }}</option>
                <option value="administrador" @if ($user->role === 'administrador') selected @endif>{{ __('Administrador') }}</option>
            </select>
            <x-input-error :messages="$errors->first('role')" class="mt-2" />
        </div>
        
        <div class="flex items-center justify-end mt-4 space-x-4">
            <x-go-back-button />
            <x-primary-button class="ms-4">
                {{ __('Actualizar Usuario') }}
            </x-primary-button>
        </div>
    </form>
</div>