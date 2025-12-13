<x-app-layout>
    <div class="mx-auto">
        <div class="bg-stone-100/90 dark:bg-custom-gray overflow-hidden shadow-sm sm:rounded-2xl shadow-soft p-4 md:p-6 lg:p-8">
            <h2 class="font-semibold text-xl text-gray-900 dark:text-gray-100 leading-tight mb-6">
                Editar productor
            </h2>
            @livewire('edit-producer', ['producer' => $producer])
        </div>
    </div>
</x-app-layout>