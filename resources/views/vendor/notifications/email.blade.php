<x-mail::message>

{{-- HEADER PERSONALIZADO --}}
<div style="text-align: center; padding: 20px 0 30px 0;">
    <img src="{{ Vite::asset('resources/img/user.jpg') }}" 
         alt="Gestión Forestal" 
         width="100" 
         style="border-radius: 50%; margin-bottom: 10px;">
   
            <img src="https://gestion-forestal-production.up.railway.app/build/assets/favicon-Cv1mLpvh.png" alt="Foto de perfil" class="w-10 h-10 rounded-full">
    <h1 style="color: #2E7D32; margin: 0;">🌳 Gestión Forestal</h1>
    <p style="color: #666; font-size: 14px;">Gestión forestal sostenible</p>
</div>

{{-- Greeting --}}
@if (! empty($greeting))
# {{ $greeting }}
@else
@if ($level === 'error')
# @lang('Whoops!')
@else
# @lang('Hello!')
@endif
@endif

{{-- Intro Lines --}}
@foreach ($introLines as $line)
{{ $line }}

@endforeach

{{-- Action Button --}}
@isset($actionText)
<?php
    $color = match ($level) {
        'success', 'error' => $level,
        default => 'primary',
    };
?>
<x-mail::button :url="$actionUrl" :color="$color">
{{ $actionText }}
</x-mail::button>
@endisset

{{-- Outro Lines --}}
@foreach ($outroLines as $line)
{{ $line }}

@endforeach

{{-- Salutation --}}
@if (! empty($salutation))
{{ $salutation }}
@else
@lang('Regards,')<br>
{{ config('app.name') }}
@endif

{{-- Subcopy --}}
@isset($actionText)
<x-slot:subcopy>
@lang(
    "If you're having trouble clicking the \":actionText\" button, copy and paste the URL below\n".
    'into your web browser:',
    [
        'actionText' => $actionText,
    ]
) <span class="break-all">[{{ $displayableActionUrl }}]({{ $actionUrl }})</span>
</x-slot:subcopy>
@endisset
</x-mail::message>
