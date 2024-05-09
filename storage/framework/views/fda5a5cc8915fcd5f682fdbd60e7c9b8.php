<?php $__env->startSection('title'); ?> Vacancies <?php $__env->stopSection(); ?>
<?php $__env->startSection('css'); ?>
<link href="<?php echo e(URL::asset('build/css/custom.css')); ?>" rel="stylesheet" type="text/css" />
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
<?php $__env->startComponent('components.breadcrumb'); ?>
<?php $__env->slot('li_1'); ?> Job <?php $__env->endSlot(); ?>
<?php $__env->slot('title'); ?> Vacancies <?php $__env->endSlot(); ?>
<?php echo $__env->renderComponent(); ?>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body bg-light-subtle">
                <div class="row g-3">
                    <div class="col-xxl-2 col-sm-12">
                        <div class="search-box">
                            <input type="text" class="form-control search bg-light border-light" id="searchJob" autocomplete="off" placeholder="Search for job opportunities...">
                            <i class="ri-search-line search-icon"></i>
                        </div>
                    </div>
                    <!--end col-->

                    <div class="col-xxl-2 col-sm-4">
                        <div class="input-light">
                            <select class="form-control" name="position" id="positionFilter">
                                <option value="all" selected>Select Position</option>
                                <?php $__currentLoopData = $positions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $position): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($position->id); ?>" <?php echo e($selectedPositionId == $position->id ? 'selected' : ''); ?>><?php echo e($position->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                    </div>
                    <!--end col-->

                    <div class="col-xxl-2 col-sm-4">
                        <div class="input-light">
                            <select class="form-control" name="type" id="typeFilter">
                                <option value="all" selected>Select Type</option>
                                <?php $__currentLoopData = $types; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($type->id); ?>"><?php echo e($type->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                    </div>
                    <!--end col-->

                    <div class="col-xxl-2 col-sm-4">
                        <div class="input-light">
                            <select class="form-control" name="store" id="storeFilter">
                                <option value="all" selected>Select Store</option>
                                <?php $__currentLoopData = $brands; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $brand): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($brand->id); ?>"><?php echo e($brand->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                    </div>
                    <!--end col-->

                    <div class="col-xxl-2 col-sm-4">
                        <div class="input-light">
                            <select class="form-control" name="town" id="townFilter">
                                <option value="all" selected>Select Town</option>
                                <?php $__currentLoopData = $towns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $town): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($town->id); ?>"><?php echo e($town->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                    </div>
                    <!--end col-->

                    <div class="col-xxl-2 col-sm-4 d-flex">
                        <div class="d-flex flex-wrap gap-2">
                            <button type="button" class="btn btn-primary waves-effect waves-light" id="filterData" onclick="filterData();">
                                <i class="ri-equalizer-fill me-1 align-bottom"></i>
                                Filter
                            </button>
                            <button type="button" class="btn btn-danger waves-effect waves-light" id="removeFilters" onclick="resetFilters();">
                                <i class="ri-close-line"></i>
                            </button>
                        </div>
                    </div>
                    <!--end col-->
                </div>
                <!--end row-->
            </div>
        </div>
    </div>
</div>
<!-- end row -->

<div class="row">
    <div class="col-lg-12">
        <div class="d-flex align-items-center mb-4">
            <div class="flex-grow-1">
                <p class="text-muted fs-14 mb-0">Result: <span id="total-result"></span></p>
            </div>
            <div class="flex-shrink-0">
                <div class="dropdown">
                    <a class="text-muted fs-14 dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-bs-toggle="dropdown" aria-expanded="false">
                        View All
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                        <li><a class="dropdown-item" href="#">Action</a></li>
                        <li><a class="dropdown-item" href="#">Another action</a></li>
                        <li><a class="dropdown-item" href="#">Something else here</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- end row -->

<div class="row" id="job-list">
</div>
<!-- end row -->

<div class="row g-0 justify-content-end mb-4 mt-4" id="pagination-element">
    <!-- end col -->
    <div class="col-sm-6">
        <div class="pagination-block pagination pagination-separated justify-content-center justify-content-sm-end mb-sm-0">
            <div class="page-item">
                <a href="javascript:void(0);" class="page-link" id="page-prev">Previous</a>
            </div>
            <span id="page-num" class="pagination"></span>
            <div class="page-item">
                <a href="javascript:void(0);" class="page-link" id="page-next">Next</a>
            </div>
        </div>
    </div><!-- end col -->
</div>
<!-- end row -->

<!-------------------------------------------------------------------------------------
    Modals
-------------------------------------------------------------------------------------->

<div class="modal fade zoomIn" id="applyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="btn-close" id="apply-close" data-bs-dismiss="modal" aria-label="Close" id="btn-close"></button>
            </div>
            <div class="modal-body p-5 text-center">
                <lord-icon src="https://cdn.lordicon.com/zpxybbhl.json" trigger="loop" colors="primary:#405189,secondary:#f06548" style="width:120px;height:120px"></lord-icon>
                <div class="mt-4 text-center">
                    <h4 class="fs-semibold">You are about to apply for this vacancy !</h4>
                    <p class="text-muted fs-14 mb-4 pt-1">Send application request ?</p>
                    <div class="hstack gap-2 justify-content-center remove">
                        <button class="btn btn-danger" data-bs-dismiss="modal" id="apply-close">
                            <i class="ri-close-line me-1 align-middle"></i>
                            Cancel
                        </button>
                        <button class="btn btn-primary vacancy-apply" id="apply" data-bs-id="">
                            Send Request !
                        </button>
                        <div class="spinner-border text-primary d-none" role="status" id="loading-apply">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!--end apply modal -->

<?php $__env->stopSection(); ?>
<?php $__env->startSection('script'); ?>
<!-- sweet alert -->
<script src="<?php echo e(URL::asset('build/libs/sweetalert2/sweetalert2.min.js')); ?>"></script>

<!-- job-candidate-grid js -->
<script src="<?php echo e(URL::asset('build/js/pages/vacancies.init.js')); ?>"></script>
<script src="<?php echo e(URL::asset('build/js/pages/vacancy-save.init.js')); ?>"></script>
<script src="<?php echo e(URL::asset('build/js/pages/vacancy-apply.init.js')); ?>"></script>

<!-- App js -->
<script src="<?php echo e(URL::asset('build/js/app.js')); ?>"></script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/shadley/Development/Sourcecode/tenubah/Orient/resources/views/vacancies.blade.php ENDPATH**/ ?>