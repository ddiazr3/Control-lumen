@component('mail::message')
# Aviso

{{ $msg }}

@component('mail::button', ['url' => $link, 'color' => 'blue' ])
    Ir
@endcomponent

Saludos,<br>
{{ config('app.name') }}
@endcomponent
