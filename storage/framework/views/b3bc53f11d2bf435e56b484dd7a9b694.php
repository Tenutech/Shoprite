<?php $__env->startSection('title'); ?> <?php echo app('translator')->get('translation.tabs'); ?> <?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
    <?php $__env->startComponent('components.breadcrumb'); ?>
        <?php $__env->slot('li_1'); ?> Pages <?php $__env->endSlot(); ?>
        <?php $__env->slot('title'); ?> Settings <?php $__env->endSlot(); ?>
    <?php echo $__env->renderComponent(); ?>

    <div class="row">
        <div class="col-xxl-12">
            <div class="card">
                <div class="card-body">
                    <!-- Nav tabs -->
                    <ul class="nav nav-tabs nav-justified nav-border-top nav-border-top-primary mb-3" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#nav-border-justified-settings" role="tab" aria-selected="false">
                                <i class="ri-settings-5-line align-middle me-1"></i> Settings
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#nav-border-justified-reminders" role="tab" aria-selected="false">
                                <i class="ri-notification-3-line me-1 align-middle"></i> Reminders
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content">

                        <!-------------------------------------------------------------------------------------
                            Settings
                        -------------------------------------------------------------------------------------->

                        <div class="tab-pane active" id="nav-border-justified-settings" role="tabpanel">
                            <form id="formSettings" enctype="multipart/form-data">
                                <?php echo csrf_field(); ?>                                    
                                <div class="row">
                                    <h5 class="card-title text-decoration-underline mb-3">
                                        Application Settings:
                                    </h5>
                                    <?php
                                        $vacancyPostingDuration = $settings->firstWhere('key', 'vacancy_posting_duration');
                                        $shortlistExpiry = $settings->firstWhere('key', 'shortlist_expiry');
                                        $sessionTimeout = $settings->firstWhere('key', 'session_timeout');
                                    ?>
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="vacancyPostingDuration" class="form-label">
                                                Vacancy Posting Duration <span class="text-danger">*</span>
                                            </label>
                                            <?php if($vacancyPostingDuration && $vacancyPostingDuration->description): ?>
                                                <p class="text-muted">
                                                    <?php echo e($vacancyPostingDuration->description); ?>

                                                </p>
                                            <?php endif; ?>
                                            <input type="number" class="form-control <?php $__errorArgs = ['vacancy_posting_duration'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" name="vacancy_posting_duration" id="vacancyPostingDuration" placeholder="Enter the amount of days" value="<?php echo e($vacancyPostingDuration ? ($vacancyPostingDuration->value ? $vacancyPostingDuration->value : 1) : 1); ?>" min="1" required/>
                                            <?php $__errorArgs = ['vacancy_posting_duration'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                <span class="invalid-feedback" role="alert">
                                                    <strong><?php echo e($message); ?></strong>
                                                </span>
                                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                            <div class="invalid-feedback">
                                                Please enter the amount of days
                                            </div>
                                        </div>
                                    </div>
                                    <!--end col-->

                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="shortlistExpiry" class="form-label">
                                                Shortlist Expiry <span class="text-danger">*</span>
                                            </label>
                                            <?php if($shortlistExpiry && $shortlistExpiry->description): ?>
                                                <p class="text-muted">
                                                    <?php echo e($shortlistExpiry->description); ?>

                                                </p>
                                            <?php endif; ?>
                                            <input type="number" class="form-control <?php $__errorArgs = ['shortlist_expiry'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" name="shortlist_expiry" id="shortlistExpiry" placeholder="Enter the amount of days" value="<?php echo e($shortlistExpiry ? ($shortlistExpiry->value ? $shortlistExpiry->value : 1) : 1); ?>" min="1" required/>
                                            <?php $__errorArgs = ['shortlist_expiry'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                <span class="invalid-feedback" role="alert">
                                                    <strong><?php echo e($message); ?></strong>
                                                </span>
                                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                            <div class="invalid-feedback">
                                                Please enter the amount of days
                                            </div>
                                        </div>
                                    </div>
                                    <!--end col-->

                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="sessionTimeout" class="form-label">
                                                Session Timeout <span class="text-danger">*</span>
                                            </label>
                                            <?php if($sessionTimeout && $sessionTimeout->description): ?>
                                                <p class="text-muted">
                                                    <?php echo e($sessionTimeout->description); ?>

                                                </p>
                                            <?php endif; ?>
                                            <input type="number" class="form-control <?php $__errorArgs = ['session_timeout'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" name="session_timeout" id="sessionTimeout" placeholder="Enter the amount of minutes" value="<?php echo e($sessionTimeout ? ($sessionTimeout->value ? $sessionTimeout->value : 1) : 1); ?>" min="1" required/>
                                            <?php $__errorArgs = ['session_timeout'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                <span class="invalid-feedback" role="alert">
                                                    <strong><?php echo e($message); ?></strong>
                                                </span>
                                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                            <div class="invalid-feedback">
                                                Please enter the amount of days
                                            </div>
                                        </div>
                                    </div>
                                    <!--end col-->
                                    
                                    <div class="col-lg-12">
                                        <div class="hstack gap-2 justify-content-end">
                                            <button type="submit" class="btn btn-primary">
                                                Save Settings
                                            </button>
                                        </div>
                                    </div>
                                    <!--end col-->
                                </div>
                                <!--end row-->
                            </form>
                        </div>

                        <!-------------------------------------------------------------------------------------
                            Reminders
                        -------------------------------------------------------------------------------------->

                        <div class="tab-pane" id="nav-border-justified-reminders" role="tabpanel">
                            <form id="formReminders" action="post" enctype="multipart/form-data">
                                <?php echo csrf_field(); ?>
                                <div class="mb-3">
                                    <h5 class="card-title text-decoration-underline mb-3">
                                        Application Reminders:
                                    </h5>
                                    <ul class="list-unstyled mb-0">
                                        <?php
                                            $vacancyCreatedNoShortlistChecked = $reminders->firstWhere('type', 'vacancy_created_no_shortlist')?->is_active === 1;
                                            $shortlistCreatedNoInterviewChecked = $reminders->firstWhere('type', 'shortlist_created_no_interview')?->is_active === 1;
                                            $interviewScheduledNoVacancyFilledChecked = $reminders->firstWhere('type', 'interview_scheduled_no_vacancy_filled')?->is_active === 1;
                                        ?>
                                        <li class="d-flex">
                                            <div class="flex-grow-1">
                                                <label for="notifyVacancyCreatedNoShortlist" class="form-check-label fs-15">
                                                    Notify If No Shortlist After Vacancy Creation
                                                </label>
                                                <p class="text-muted">
                                                    Receive a reminder if no shortlist has been created within 7 days after a vacancy is posted. This helps ensure timely progression in the recruitment process.
                                                </p>
                                            </div>
                                            <div class="flex-shrink-0">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" role="switch" id="vacancyCreatedNoShortlist" name="vacancy_created_no_shortlist" <?php echo e($vacancyCreatedNoShortlistChecked ? 'checked' : ''); ?>/>
                                                </div>
                                            </div>
                                        </li>
                                        <li class="d-flex">
                                            <div class="flex-grow-1">
                                                <label for="notifyShortlistCreatedNoInterview" class="form-check-label fs-15">
                                                    Notify If No Interview After Shortlist
                                                </label>
                                                <p class="text-muted">
                                                    Get alerted if no interview has been scheduled within 7 days after creating a shortlist. Keeps the recruitment process on track by encouraging prompt scheduling.
                                                </p>
                                            </div>
                                            <div class="flex-shrink-0">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" role="switch" id="shortlistCreatedNoInterview" name="shortlist_created_no_interview" <?php echo e($shortlistCreatedNoInterviewChecked ? 'checked' : ''); ?>/>
                                                </div>
                                            </div>
                                        </li>
                                        <li class="d-flex">
                                            <div class="flex-grow-1">
                                                <label for="notifyInterviewScheduledNoVacancyFilled" class="form-check-label fs-15">
                                                    Notify If No Appointment After Interview
                                                </label>
                                                <p class="text-muted">
                                                    Receive notifications if a vacancy remains unfilled 7 days after conducting interviews. This prompt aims to accelerate decision-making for candidate selection.
                                                </p>
                                            </div>
                                            <div class="flex-shrink-0">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" role="switch" id="interviewScheduledNoVacancyFilled" name="interview_scheduled_no_vacancy_filled" <?php echo e($interviewScheduledNoVacancyFilledChecked ? 'checked' : ''); ?>/>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>                                    
                                </div>
                                <div class="col-lg-12">
                                    <div class="hstack gap-2 justify-content-end">
                                        <button type="submit" class="btn btn-primary">
                                            Save Settings
                                        </button>
                                    </div>
                                </div>
                                <!--end col-->
                            </form>  
                        </div>
                    </div>
                </div><!-- end card-body -->
            </div>
        </div><!--end col-->
    </div><!--end row-->

<?php $__env->stopSection(); ?>
<?php $__env->startSection('script'); ?>
    <script src="<?php echo e(URL::asset('build/libs/prismjs/prism.js')); ?>"></script>
    <script src="<?php echo e(URL::asset('build/js/pages/settings.init.js')); ?>"></script>
    <script src="<?php echo e(URL::asset('build/libs/sweetalert2/sweetalert2.min.js')); ?>"></script>
    <script src="<?php echo e(URL::asset('build/js/app.js')); ?>"></script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\Recruitment\resources\views/admin/settings.blade.php ENDPATH**/ ?>