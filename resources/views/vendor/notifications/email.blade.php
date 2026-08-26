<x-mail::message>

# 🚀 Virtual Data Room

@if (! empty($greeting))
{{ $greeting }}
@else
Hello!
@endif

@if (! empty($introLines))
@foreach ($introLines as $line)
{{ $line }}

@endforeach
@endif

@if (isset($actionText))
<x-mail::button :url="$actionUrl">
{{ $actionText }}
</x-mail::button>
@endif

@if (! empty($outroLines))
@foreach ($outroLines as $line)
{{ $line }}

@endforeach
@endif

Thanks,<br>
{{ config('app.name') }}

</x-mail::message>