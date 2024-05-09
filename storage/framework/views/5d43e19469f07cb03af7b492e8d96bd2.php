<!doctype html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>" data-topbar="light">

    <head>
    <meta charset="utf-8" />
    <title>Orient - Where Potential Meets Opportunity</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Where Potential Meets Opportunity" name="description" />
    <meta content="Orient" name="author" />
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <!-- Open Graph Tags -->
    <meta property="og:title" content="Orient">
    <meta property="og:description" content="Where Potential Meets Opportunity">
    <meta property="og:image" content="<?php echo e(URL::asset('build/images/logo.png')); ?>">

    <!-- App favicon -->
    <link rel="shortcut icon" href="<?php echo e(URL::asset('build/images/favicon.ico')); ?>">
        <?php echo $__env->make('layouts.head-css', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
  </head>

    <?php echo $__env->yieldContent('body'); ?>

    <?php echo $__env->yieldContent('content'); ?>

    <?php echo $__env->make('layouts.vendor-scripts', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    </body>
</html>
<?php /**PATH /Users/shadley/Development/Sourcecode/tenubah/Orient/resources/views/layouts/master-without-nav.blade.php ENDPATH**/ ?>