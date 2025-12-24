<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="">
        <div class="max-w-7xl mx-auto  space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <!-- Tarjeta Información del Perfil -->
                <div class="p-4 sm:p-8 bg-stone-100/90 dark:bg-custom-gray rounded-2xl shadow-soft p-4 md:p-6 lg:p-8 ">
                    <div class="max-w-xl">
                        @include('profile.partials.update-profile-information-form')
                    </div>
                </div>

                <!-- Tarjeta Contraseña -->
                <div class="p-4 sm:p-8 bg-stone-100/90 dark:bg-custom-gray rounded-2xl shadow-soft p-4 md:p-6 lg:p-8 ">
                    <div class="max-w-xl">
                        @include('profile.partials.update-password-form')
                    </div>
                </div>
            </div>

            <!-- Tarjeta Eliminar Cuenta -->
            <div class="p-4 sm:p-8 bg-stone-100/90 dark:bg-custom-gray shadow rounded-2xl shadow-soft p-4 md:p-6 lg:p-8 ">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
