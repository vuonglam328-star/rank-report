<nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button">
                <i class="fas fa-bars"></i>
            </a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
            <a href="<?php echo e(route('dashboard')); ?>" class="nav-link">
                <i class="fas fa-chart-line mr-1"></i>RankReport Pro
            </a>
        </li>
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
        <!-- Quick import button -->
        <li class="nav-item">
            <a class="nav-link" href="<?php echo e(route('imports.create')); ?>" title="Import CSV mới">
                <i class="fas fa-upload"></i>
                <span class="d-none d-md-inline ml-1">Import CSV</span>
            </a>
        </li>

        <!-- User menu -->
        <li class="nav-item dropdown">
            <a class="nav-link" data-toggle="dropdown" href="#">
                <i class="far fa-user-circle fa-lg"></i>
                <span class="d-none d-md-inline ml-1"><?php echo e(auth()->user()->name ?? 'Admin'); ?></span>
            </a>
            <div class="dropdown-menu dropdown-menu-right shadow">
                <span class="dropdown-item-text">
                    <small class="text-muted"><?php echo e(auth()->user()->email ?? ''); ?></small>
                </span>
                <div class="dropdown-divider"></div>
                <a href="#" class="dropdown-item">
                    <i class="fas fa-cog mr-2"></i>Cài đặt
                </a>
                <div class="dropdown-divider"></div>
                <form method="POST" action="<?php echo e(route('logout')); ?>">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="dropdown-item text-danger">
                        <i class="fas fa-sign-out-alt mr-2"></i>Đăng xuất
                    </button>
                </form>
            </div>
        </li>
    </ul>
</nav>
<?php /**PATH C:\laragon\www\rankreport-pro\resources\views/layouts/partials/navbar.blade.php ENDPATH**/ ?>