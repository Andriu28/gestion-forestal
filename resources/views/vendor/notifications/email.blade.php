<x-mail::message>

{{-- HEADER PERSONALIZADO --}}
<div style="text-align: center; padding: 20px 0 30px 0;">
    <div style="display: inline-flex; align-items: center; gap: 15px; max-width: 500px; text-align: left;">
        <img src="https://gestion-forestal-production.up.railway.app/build/assets/favicon-Cv1mLpvh.png" 
             alt="Logo Gestión Forestal" 
             style="width: 60px; height: 60px; border-radius: 18%; object-fit: cover; flex-shrink: 0;">
        <div style="margin-left: 15px;">
            <h1 style="color: #4b3215ff; margin: 0 0 1px 0; font-size: 30px; font-weight: bold;">
                 Gestión Forestal
            </h1>
            <p style="color: #666; margin: 0; font-size: 14px; line-height: 1.3;">
                Desarrollo Sostenible
            </p>
        </div>
    </div>
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
{{-- Action Button --}}
@isset($actionText)
<br>
<div style="text-align: center; margin: 30px 0;">
    <a href="{{ $actionUrl }}" 
       style="display: inline-block; padding: 10px 13px; background-color: #583c1bff; 
              color: white; text-decoration: none; border-radius: 6px; font-weight: bold;
              font-size: 16px; border: none; cursor: pointer;">
        {{ $actionText }}
    </a>
</div>
<br>
<br>
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
