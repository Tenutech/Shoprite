<div class="row">
    <div class="col-xl-12">
        <div class="card crm-widget">
            <div class="card-body p-0">
                <div class="row row-cols-xxl-5 row-cols-md-3 row-cols-1 g-0">
                    <div class="col">
                        <div class="py-4 px-3">
                            <h5 class="text-muted text-uppercase fs-13">Time to Shortlist (days)<i
                                    class="ri-arrow-up-circle-line text-success fs-18 float-end align-middle"></i></h5>
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <i class="ri-space-ship-line display-6 text-muted"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h2 class="mb-0"><span class="counter-value" data-target="{{ $averageShortlistTime }}">{{ $averageShortlistTime }}</span>
                                    </h2>
                                </div>
                            </div>
                        </div>
                    </div><!-- end col -->
                    <div class="col">
                        <div class="mt-3 mt-md-0 py-4 px-3">
                            <h5 class="text-muted text-uppercase fs-13">Time to Hire (days)<i
                                    class="ri-arrow-up-circle-line text-success fs-18 float-end align-middle"></i></h5>
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <i class="ri-exchange-dollar-line display-6 text-muted"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h2 class="mb-0"><span class="counter-value"
                                            data-target="{{ $averageTimeToHire }}">{{ $averageTimeToHire }}</span></h2>
                                </div>
                            </div>
                        </div>
                    </div><!-- end col -->
                    <div class="col">
                        <div class="mt-3 mt-md-0 py-4 px-3">
                            <h5 class="text-muted text-uppercase fs-13">Adoption Rate <i
                                    class="ri-arrow-down-circle-line text-danger fs-18 float-end align-middle"></i></h5>
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <i class="ri-pulse-line display-6 text-muted"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h2 class="mb-0"><span class="counter-value"
                                            data-target="{{ $adoptionRate }}">{{ $adoptionRate }}</span>%</h2>
                                </div>
                            </div>
                        </div>
                    </div><!-- end col -->
                    <div class="col">
                        <div class="mt-3 mt-lg-0 py-4 px-3">
                            <h5 class="text-muted text-uppercase fs-13">App Completion Rate <i
                                    class="ri-arrow-up-circle-line text-success fs-18 float-end align-middle"></i></h5>
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <i class="ri-trophy-line display-6 text-muted"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h2 class="mb-0"><span class="counter-value"
                                            data-target="{{ $applicationCompletionRate }}">{{ $applicationCompletionRate }}</span>%</h2>
                                </div>
                            </div>
                        </div>
                    </div><!-- end col -->
                    <div class="col">
                        <div class="mt-3 mt-lg-0 py-4 px-3">
                            <h5 class="text-muted text-uppercase fs-13">Absorption Rate <i
                                    class="ri-arrow-down-circle-line text-danger fs-18 float-end align-middle"></i></h5>
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <i class="ri-service-line display-6 text-muted"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h2 class="mb-0">
                                        <span class="counter-value" data-target="{{ $previousYearData->total_applicants > 0 ? round($previousYearData->total_appointed / $previousYearData->total_applicants * 100) : 0 }}" id="absorptionRatePreviousYearValue">
                                            0
                                        </span>%
                                    </h2>
                                </div>
                            </div>
                        </div>
                    </div><!-- end col -->
                </div><!-- end row -->
            </div><!-- end card body -->
        </div><!-- end card -->
    </div><!-- end col -->
</div><!-- end row -->
