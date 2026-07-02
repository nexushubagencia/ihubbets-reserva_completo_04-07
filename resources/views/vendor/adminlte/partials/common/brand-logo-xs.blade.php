@inject('layoutHelper', 'JeroenNoten\LaravelAdminLte\Helpers\LayoutHelper')

@php( $dashboard_url = View::getSection('dashboard_url') ?? config('adminlte.dashboard_url', 'home') )

@if (config('adminlte.use_route_url', false))
    @php( $dashboard_url = $dashboard_url ? route($dashboard_url) : '' )
@else
    @php( $dashboard_url = $dashboard_url ? url($dashboard_url) : '' )
@endif

@php
    $siteId = env('ID_SITE', 1);
    $site = \App\Models\Site::find($siteId);
    $nomeBanca = $site ? $site->name : config('adminlte.logo', 'IHUB BETS');
    
    // Generate initials (2 letters)
    $words = explode(' ', trim(strip_tags($nomeBanca)));
    $initials = '';
    if (count($words) >= 2) {
        $initials = strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
    } else {
        $initials = strtoupper(substr(strip_tags($nomeBanca), 0, 2));
    }
@endphp

<a href="{{ $dashboard_url }}"
    @if($layoutHelper->isLayoutTopnavEnabled())
        class="navbar-brand {{ config('adminlte.classes_brand') }}"
    @else
        class="brand-link {{ config('adminlte.classes_brand') }}"
    @endif>

    {{-- Brand text (Full) --}}
    <span class="brand-text full-text">
        {!! $nomeBanca !!}
    </span>

    {{-- Brand text (Mini) --}}
    <span class="mini-text">
        {!! $initials !!}
    </span>

</a>
