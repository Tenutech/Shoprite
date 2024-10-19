<div class="row g-3">
                <!-- Average Proximity -->
                <div class="col-xl-6 col-md-6">
                    <div class="card card-animate">
                        <div class="card-header">
                            <div class="d-flex">
                                <h5 class="card-title mb-0 flex-grow-1">
                                    Average Proximity
                                </h5>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h2 class="mt-4 ff-primary fw-bold">
                                    <span id="averageDistanceApplicantsAppointed" class="counter-value"  data-target="{{ $averageDistanceApplicantsAppointed }}">
                                        0
                                    </span>km 
                                    </h2>
                                    <p class="mb-0 text-muted">
                                        Average distance for succesfull placements
                                    </p>
                                </div>
                            </div>
                        </div><!-- end card body -->
                    </div>
                </div> <!-- end col -->

                <!-- Average Score -->
                <div class="col-xl-6 col-md-6">
                    <div class="card card-animate">
                        <div class="card-header">
                            <div class="d-flex">
                                <h5 class="card-title mb-0 flex-grow-1">
                                    Average Score
                                </h5>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h2 class="mt-4 ff-primary fw-bold">
                                        <span id="averageScoreApplicantsAppointed" class="counter-value"  data-target="{{ $averageScoreApplicantsAppointed }}">
                                            0
                                        </span>
                                    </h2>
                                    <p class="mb-0 text-muted">
                                        Average score for succesfull placements
                                    </p>
                                </div>
                            </div>
                        </div><!-- end card body -->
                    </div>
                </div> <!-- end col -->
            </div> <!-- end row -->