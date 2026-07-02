@php
    $site = \App\Models\Site::first();
    $logoPath = $site->logo_path ?? '';
    $logoUrl = null;
    if ($logoPath) {
        $logoUrl = str_starts_with($logoPath, 'http') ? $logoPath : asset($logoPath);
    }
@endphp
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if($logoUrl)
<img src="{{ $logoUrl }}" class="logo" alt="{{ $site->name ?? 'Logo' }}" style="height: 60px; width: auto;">
@else
{{ $site->name ?? config('app.name') }}
@endif
</a>
</td>
</tr>
