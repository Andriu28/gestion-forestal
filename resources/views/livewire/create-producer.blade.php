<div>
    <form wire:submit="store">
        @csrf

        <div>
            <x-input-label for="name" :value="__('Nombre del productor *')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" wire:model.live.debounce.250ms="name" autofocus />
            <x-input-error :messages="$errors->first('name')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="lastname" :value="__('Apellido')" />
            <x-text-input id="lastname" class="block mt-1 w-full" type="text" wire:model.live.debounce.250ms="lastname" />
            <x-input-error :messages="$errors->first('lastname')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="description" :value="__('Descripción')" />
            <textarea id="description" wire:model.live="description" rows="3" class="block mt-1 w-full rounded-md border-gray-300 dark:bg-custom-gray dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500" placeholder="Descripción del productor..."></textarea>
            <x-input-error :messages="$errors->first('description')" class="mt-2" />
        </div>

        <div class="mt-4 flex items-center">
            <input type="checkbox" id="is_active" wire:model="is_active" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
            <label for="is_active" class="ml-2 block text-sm text-gray-900 dark:text-gray-100">Productor activo</label>
        </div>

        <div class="flex items-center justify-end mt-6 space-x-4">
            <x-go-back-button />
            <x-primary-button>
                {{ __('Guardar productor') }}
            </x-primary-button>
        </div>
    </form>
</div>