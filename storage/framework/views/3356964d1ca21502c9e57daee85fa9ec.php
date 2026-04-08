<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="<?php echo e(route('dashboard')); ?>" class="brand-link">
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
                <a href="#" class="d-block"><?php echo e(auth()->user()->name ?? 'Admin'); ?></a>
            </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column nav-child-indent" data-widget="treeview" role="menu">

                
                <li class="nav-item">
                    <a href="<?php echo e(route('dashboard')); ?>" class="nav-link <?php echo e(request()->routeIs('dashboard') ? 'active' : ''); ?>">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>Dashboard</p>
                    </a>
                </li>

                
                <li class="nav-item">
                    <a href="<?php echo e(route('clients.index')); ?>" class="nav-link <?php echo e(request()->routeIs('clients.*') ? 'active' : ''); ?>">
                        <i class="nav-icon fas fa-building"></i>
                        <p>Clients</p>
                    </a>
                </li>

                
                <li class="nav-item">
                    <a href="<?php echo e(route('projects.index')); ?>" class="nav-link <?php echo e(request()->routeIs('projects.*') ? 'active' : ''); ?>">
                        <i class="nav-icon fas fa-globe"></i>
                        <p>Projects</p>
                    </a>
                </li>

                <li class="nav-header text-uppercase" style="font-size:.65rem; letter-spacing:.08em;">Import & Analysis</li>

                
                <li class="nav-item">
                    <a href="<?php echo e(route('imports.index')); ?>" class="nav-link <?php echo e(request()->routeIs('imports.*') ? 'active' : ''); ?>">
                        <i class="nav-icon fas fa-file-upload"></i>
                        <p>Import CSV</p>
                    </a>
                </li>

                
                <li class="nav-item">
                    <a href="<?php echo e(route('keywords.index')); ?>" class="nav-link <?php echo e(request()->routeIs('keywords.*') ? 'active' : ''); ?>">
                        <i class="nav-icon fas fa-search"></i>
                        <p>Keywords</p>
                    </a>
                </li>

                
                <li class="nav-item">
                    <a href="<?php echo e(route('competitors.index')); ?>" class="nav-link <?php echo e(request()->routeIs('competitors.*') ? 'active' : ''); ?>">
                        <i class="nav-icon fas fa-users"></i>
                        <p>Competitors</p>
                    </a>
                </li>

                <li class="nav-header text-uppercase" style="font-size:.65rem; letter-spacing:.08em;">Reports</li>

                
                <li class="nav-item">
                    <a href="<?php echo e(route('reports.index')); ?>" class="nav-link <?php echo e(request()->routeIs('reports.*') ? 'active' : ''); ?>">
                        <i class="nav-icon fas fa-file-pdf"></i>
                        <p>Báo cáo PDF</p>
                    </a>
                </li>

                
                <li class="nav-item">
                    <a href="<?php echo e(route('templates.index')); ?>" class="nav-link <?php echo e(request()->routeIs('templates.*') ? 'active' : ''); ?>">
                        <i class="nav-icon fas fa-palette"></i>
                        <p>Templates</p>
                    </a>
                </li>

            </ul>
        </nav>
    </div>
</aside>
<?php /**PATH C:\laragon\www\rankreport-pro\resources\views/layouts/partials/sidebar.blade.php ENDPATH**/ ?>