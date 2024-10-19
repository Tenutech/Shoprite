<div class="row g-3">
                <div class="col-xl-12 col-md-12">
                    <div class="card card-animate">
                        <div class="card-header border-0 align-items-center d-flex">
                            <h4 class="card-title mb-0 flex-grow-1">Talent Pool</h4>
                        </div><!-- end card header -->

                        <div class="card-header p-0 border-0 bg-white bg-opacity-10">
                            <div class="row g-0 text-center">
                                <div class="col-6 col-sm-6">
                                    <div class="p-3 border border-dashed border-start-0">
                                        <h5 class="mb-1">
                                            <span id="talentPoolApplicantsValue" class="counter-value" data-target="{{ $talentPoolApplicantsValue }}">
                                                0
                                            </span>
                                        </h5>
                                        <p class="text-muted mb-0">
                                            Total Talent Pool
                                        </p>
                                    </div>
                                </div> <!--end col -->
                                <div class="col-6 col-sm-6">
                                    <div class="p-3 border border-dashed border-start-0">
                                        <h5 class="mb-1">
                                            <span id="applicantsAppointedValue" class="counter-value" data-target="{{ $applicantsAppointedValue }}">
                                                0
                                            </span>
                                        </h5>
                                        <p class="text-muted mb-0">
                                            Total Appointed
                                        </p>
                                    </div>
                                </div> <!--end col -->
                            </div>
                        </div><!-- end card header -->
            
                        <div class="card-body">
                            <div id="talent_pool_by_month" data-colors='["--vz-primary", "--vz-success"]' class="apex-charts" dir="ltr"></div>
                        </div> 
                        <!-- end card-body -->
                    </div> <!-- end card -->
                </div> <!-- end col -->
            </div> <!-- end row -->