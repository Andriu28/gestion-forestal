<x-app-layout>
    <div class="">
        <div class="mx-auto">
            <div class="bg-stone-100/90 dark:bg-custom-gray overflow-hidden shadow-sm rounded-2xl">
                <div class="p-6">
                    <h2 class="font-semibold text-xl text-gray-900 dark:text-gray-100 leading-tight md:mb-6">
                        {{ __('Crear productor') }}
                    </h2>
                    @livewire('create-producer')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>