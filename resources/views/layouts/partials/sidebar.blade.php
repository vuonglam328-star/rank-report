<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="{{ route('dashboard') }}" class="brand-link">
        <i class="fas fa-chart-line brand-image img-circle elevation-3" style="font-size:1.4rem; opacity:.8; padding:6px 8px;"></i>
        <span class="brand-text font-weight-bold">RankReport Pro</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <i class="fas fa-user-circle fa-2x text-secondary"></i>
            </div>
            <div class="info">
                <a href="#" class="d-block">{{ auth()->user()->name ?? 'Admin' }}</a>
            </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column nav-child-indent" data-widget="treeview" role="menu">

                {{-- Dashboard --}}
                <li class="nav-item">
                    <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>Dashboard</p>
                    </a>
                </li>

                {{-- Clients --}}
                <li class="nav-item">
                    <a href="{{ route('clients.index') }}" class="nav-link {{ request()->routeIs('clients.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-building"></i>
                        <p>Clients</p>
                    </a>
                </li>

                {{-- Projects --}}
                <li class="nav-item">
                    <a href="{{ route('projects.index') }}" class="nav-link {{ request()->routeIs('projects.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-globe"></i>
                        <p>Projects</p>
                    </a>
                </li>

                <li class="nav-header text-uppercase" style="font-size:.65rem; letter-spacing:.08em;">Import & Analysis</li>

                {{-- Imports --}}
                <li class="nav-item">
                    <a href="{{ route('imports.index') }}" class="nav-link {{ request()->routeIs('imports.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-file-upload"></i>
                        <p>Import CSV</p>
                    </a>
                </li>

                {{-- Keywords --}}
                <li class="nav-item">
                    <a href="{{ route('keywords.index') }}" class="nav-link {{ request()->routeIs('keywords.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-search"></i>
                        <p>Keywords</p>
                    </a>
                </li>

                {{-- Competitors --}}
                <li class="nav-item">
                    <a href="{{ route('competitors.index') }}" class="nav-link {{ request()->routeIs('competitors.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-users"></i>
                        <p>Competitors</p>
                    </a>
                </li>

                <li class="nav-header text-uppercase" style="font-size:.65rem; letter-spacing:.08em;">Reports</li>

                {{-- Reports --}}
                <li class="nav-item">
                    <a href="{{ route('reports.index') }}" class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-file-pdf"></i>
                        <p>Báo cáo PDF</p>
                    </a>
                </li>

                {{-- Templates --}}
                <li class="nav-item">
                    <a href="{{ route('templates.index') }}" class="nav-link {{ request()->routeIs('templates.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-palette"></i>
                        <p>Templates</p>
                    </a>
                </li>

            </ul>
        </nav>
    </div>
</aside>
