
<button {{ $attributes->merge(['type' => 'submit', 'class' => 'px-5 py-2.5
 bg-gradient-to-br from-[#6B4226] to-[#8F5A34]
 dark:from-[#4E301B] dark:to-[#6B4226]
 hover:from-[#4E301B] hover:to-[#6B4226]
 dark:hover:from-[#3A2314] dark:hover:to-[#4E301B]
 active:from-[#3A2314] active:to-[#4E301B]
 text-white rounded-lg font-medium
 shadow-sm hover:shadow-md
 transition-all duration-200
 focus:outline-none focus:ring-2 focus:ring-[#6B4226]/70
 focus:ring-offset-2 dark:focus:ring-offset-gray-800']) }}>
    {{ $slot }}
</button