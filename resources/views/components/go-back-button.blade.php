@props(['route' => null])

<div>
    <a href="{{ $route ?? 'javascript:history.back()' }}" 
       class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-md transition duration-300 ease-in-out">
        Regresar
    </a>
</div>