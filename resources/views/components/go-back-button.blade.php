@props(['route' => null])


    <a href="{{ $route ?? 'javascript:history.back()' }}" 
       class="px-5 py-2.5 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 font-medium">
        Regresar
    </a>

    