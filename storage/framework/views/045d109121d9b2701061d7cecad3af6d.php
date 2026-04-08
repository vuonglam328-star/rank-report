<?php if(session('success')): ?>
<div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
    <i class="fas fa-check-circle mr-2"></i><?php echo e(session('success')); ?>

    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
</div>
<?php endif; ?>

<?php if(session('error') || $errors->has('import') || $errors->has('generate') || $errors->has('delete')): ?>
<div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
    <i class="fas fa-exclamation-circle mr-2"></i>
    <?php if(session('error')): ?>
        <?php echo e(session('error')); ?>

    <?php endif; ?>
    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div><?php echo e($error); ?></div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
</div>
<?php endif; ?>

<?php if(session('warning')): ?>
<div class="alert alert-warning alert-dismissible fade show shadow-sm" role="alert">
    <i class="fas fa-exclamation-triangle mr-2"></i><?php echo e(session('warning')); ?>

    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
</div>
<?php endif; ?>
<?php /**PATH C:\laragon\www\rankreport-pro\resources\views/layouts/partials/alerts.blade.php ENDPATH**/ ?>