<aside class="main-sidebar {{ config('adminlte.classes_sidebar', 'sidebar-dark-primary elevation-4') }}">

    {{-- Sidebar brand logo --}}
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
    <style>
        .brand-link {
            display: flex !important;
            justify-content: center !important;
            align-items: center !important;
            height: 57px;
            padding: 20px 0 !important;
            margin: 0 !important;
            background: var(--sidebar-bg) !important;
            border-bottom: 1px solid var(--sidebar-border) !important;
            text-align: center !important;
        }
        .brand-link .full-text {
            display: block !important;
            font-family: 'Inter', sans-serif !important;
            font-weight: 800 !important;
            color: var(--text-main) !important;
            font-size: 1.3rem !important;
            letter-spacing: 1px;
            text-align: center !important;
            width: 100% !important;
            margin: 0 auto !important;
        }
        .brand-link .mini-text {
            display: none !important;
        }
        
        body.sidebar-collapse .brand-link .full-text {
            display: none !important;
        }
        body.sidebar-collapse .brand-link .mini-text {
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
            font-family: 'Inter', sans-serif !important;
            font-weight: 800 !important;
            color: var(--text-main) !important;
            font-size: 1.2rem !important;
            text-align: center !important;
            width: 100%;
        }
    </style>


    <a href="{{ url(config('adminlte.dashboard_url', 'home')) }}" 
       class="brand-link {{ config('adminlte.classes_brand') }}">
         
        {{-- Brand text (Full) --}}
        <span class="brand-text full-text">
            {!! $nomeBanca !!}
        </span>

        {{-- Brand text (Mini) --}}
        <span class="mini-text">
            {!! $initials !!}
        </span>
    </a>

    {{-- Sidebar --}}
    <div class="sidebar">

        {{-- ═══ User Panel Premium ═══ --}}
        @auth
        @php
            $userName = Auth::user()->name ?? 'Admin';
            $userInitial = strtoupper(substr($userName, 0, 1));
            $sidebarAvatar = Auth::user()->avatar ? asset(Auth::user()->avatar) : null;
        @endphp
        <div style="padding: 14px 16px; border-bottom: 1px solid rgba(255,255,255,0.06); display: flex; align-items: center; gap: 12px;">
            <div style="width: 38px; height: 38px; border-radius: 50%; background: transparent; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1rem; color: #fff; flex-shrink: 0; overflow: hidden; position: relative;">
                @php
                    $finalAvatar = $sidebarAvatar ?: 'https://ui-avatars.com/api/?name='.urlencode($userName).'&background=3b82f6&color=fff&size=128';
                @endphp
                <img src="{{ $finalAvatar }}" class="avatar-preview-sync" alt="Avatar" style="width:100%;height:100%;object-fit:cover;">
            </div>
            {{-- Info --}}
            <div style="overflow: hidden; flex: 1; min-width: 0;">
                <div style="color: #fff; font-weight: 700; font-size: 0.88rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $userName }}</div>
                <a href="{{ url('admin/editar-perfil') }}" style="color: #64748b; font-size: 0.75rem; text-decoration: none; transition: color 0.15s;" onmouseover="this.style.color='#94a3b8'" onmouseout="this.style.color='#64748b'">
                    <i class="fas fa-edit" style="font-size: 0.65rem;"></i> Editar perfil
                </a>
            </div>
        </div>
        @endauth

        {{-- Sidebar menu --}}
        <nav class="pt-2">
            <ul class="nav nav-pills nav-sidebar flex-column {{ config('adminlte.classes_sidebar_nav', '') }}"
                data-widget="treeview" role="menu"
                @if(config('adminlte.sidebar_nav_animation_speed') != 300)
                    data-animation-speed="{{ config('adminlte.sidebar_nav_animation_speed') }}"
                @endif
                @if(!config('adminlte.sidebar_nav_accordion'))
                    data-accordion="false"
                @endif>
                {{-- Configured sidebar links --}}
                @each('adminlte::partials.sidebar.menu-item', $adminlte->menu('sidebar'), 'item')
            </ul>
        </nav>
    </div>

</aside>
