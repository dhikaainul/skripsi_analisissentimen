<aside id="sidebar" class="sidebar">

    <ul class="sidebar-nav" id="sidebar-nav">

        <li class="nav-item">
            <a class="nav-link {{ Request::path() === 'dashboard' ? '' : 'collapsed' }}" href="{{route('dashboard')}}">
                <i class="bi bi-grid"></i>
                <span>Dashboard</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ Request::path() === 'dataset' ? '' : 'collapsed' }}" href="{{route('dataset')}}">
                <i class="bi bi-layout-text-window-reverse"></i>
                <span>Import Dataset</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ Request::path() === 'preprocessing' ? '' : 'collapsed' }}" href="{{route('preprocessing')}}">
                <i class="bi bi-journal-text"></i>
                <span>Preprocessing Data</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ Request::path() === 'klasifikasi' ? '' : 'collapsed' }}" href="{{route('klasifikasi')}}">
                <i class="bi bi-card-list"></i>
                <span>Klasifikasi Data</span>
            </a>
        </li>
        <li class="nav-item">
        @if(Request::path() === 'dashboardperbulan')
        <a class="nav-link" data-bs-target="#components-nav" data-bs-toggle="collapse" href="#">
            @elseif(Request::path() === 'datasetperbulan')
            <a class="nav-link" data-bs-target="#components-nav" data-bs-toggle="collapse" href="#">
            @elseif(Request::path() === 'preprocessingperbulan')
            <a class="nav-link" data-bs-target="#components-nav" data-bs-toggle="collapse" href="#">
            @elseif(Request::path() === 'klasifikasiperbulan')
            <a class="nav-link" data-bs-target="#components-nav" data-bs-toggle="collapse" href="#">
            @else
            <a class="nav-link collapsed" data-bs-target="#components-nav" data-bs-toggle="collapse" href="#">
            @endif
                <i class="bi bi-menu-button-wide"></i><span>Klasifikasi Per Bulan</span><i class="bi bi-chevron-down ms-auto"></i>
            </a>
            @if(Request::path() === 'dashboardperbulan')
            <ul id="components-nav" class="nav-content collapse show" data-bs-parent="#sidebar-nav">
            @elseif(Request::path() === 'datasetperbulan')
            <ul id="components-nav" class="nav-content collapse show" data-bs-parent="#sidebar-nav">
            @elseif(Request::path() === 'preprocessingperbulan')
            <ul id="components-nav" class="nav-content collapse show" data-bs-parent="#sidebar-nav">
            @elseif(Request::path() === 'klasifikasiperbulan')
            <ul id="components-nav" class="nav-content collapse show" data-bs-parent="#sidebar-nav">
            @else
            <ul id="components-nav" class="nav-content collapse" data-bs-parent="#sidebar-nav">
            @endif
            <!-- <ul id="components-nav" class="nav-content collapse {{ Request::path() === 'dashboardperbulan' ? 'show' : '' }}" data-bs-parent="#sidebar-nav"> -->
                <li>
                    <a href="{{ route('dashboardperbulan') }}" class="{{ Request::path() === 'dashboardperbulan' ? 'active' : '' }}">
                        <i class="bi bi-circle"></i><span>Dashboard Per Bulan</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('datasetperbulan') }}" class="{{ Request::path() === 'datasetperbulan' ? 'active' : '' }}">
                        <i class="bi bi-circle"></i><span>Import Data</span>
                    </a>
                </li>
                <li>
                    <a href="{{route('preprocessingperbulan')}}" class="{{ Request::path() === 'preprocessingperbulan' ? 'active' : '' }}">
                        <i class="bi bi-circle"></i><span>Preprocessing Data</span>
                    </a>
                </li>
                <li>
                    <a href="{{route('klasifikasiperbulan')}}" class="{{ Request::path() === 'klasifikasiperbulan' ? 'active' : '' }}">
                        <i class="bi bi-circle"></i><span>Klasifikasi Data</span>
                    </a>
                </li>
            </ul>
        </li><!-- End Components Nav -->
    </ul>

</aside>