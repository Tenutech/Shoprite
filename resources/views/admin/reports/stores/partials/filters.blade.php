<!-------------------------------------------------------------------------------------
    Off Canvas
-------------------------------------------------------------------------------------->

<div class="offcanvas offcanvas-end border-0" tabindex="-1" id="filters-canvas">
    <form id="formFilters" enctype="multipart/form-data">
        @csrf
        <div class="offcanvas-body profile-offcanvas d-flex flex-column p-0" style="height: 100vh;">
            <!-- Main content that can scroll if necessary -->
            <div class="flex-grow-1">
                <div class="team-cover">
                    <img src="{{ URL::asset('build/icons/auth-two-bg.jpg') }}" alt="" class="img-fluid" />
                </div>
                <div class="p-5"></div>
                <div class="p-3 mt-4 text-center">
                    <div class="mt-3">
                        <h5 class="fs-15 profile-name">
                            Applicant Filters
                        </h5>
                    </div>
                </div>
                <div class="row g-0 p-3">
                    <!-- Date -->
                    <div class="col-12">
                        <div class="mb-3">
                            <label for="date_range" class="form-label">
                                Date Range
                            </label>
                            <div class="input-group">
                                <input type="text" id="date_range" name="date_range" class="form-control border-0 dash-filter-picker shadow" required>
                                <div class="input-group-text bg-secondary border-secondary text-white">
                                    <i class="ri-calendar-2-line"></i>
                                </div>
                                <div class="invalid-feedback">
                                    Please select a date range!
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- end col -->

                    <!-- Provinces -->
                    <div class="col-12">
                        <div class="mb-3">
                            <label for="province" class="form-label">
                                Provinces
                            </label>
                            <select class="form-control" id="province_id" name="province_id" data-choices data-choices-search-false>
                                <option value="" selected>Select Province</option>
                                @foreach ($provinces as $province)
                                    <option value="{{ $province->id }}">
                                        {{ $province->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">
                                Please select a province!
                            </div>
                        </div>
                    </div>
                    <!-- end col -->

                    <!-- Region -->
                    <div class="col-12">
                        <div class="mb-3">
                            <label for="region" class="form-label">
                                Regions
                            </label>
                            <select class="form-control" id="region_id" name="region_id" data-choices data-choices-search-false>
                                <option value="" selected>Select Region</option>
                                @foreach ($regions as $region)
                                    <option value="{{ $region->id }}">
                                        {{ $region->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">
                                Please select a region!
                            </div>
                        </div>
                    </div>
                    <!-- end col -->

                    <!-- Division -->
                    <div class="col-12">
                        <div class="mb-3">
                            <label for="division" class="form-label">
                                Divisions
                            </label>
                            <select class="form-control" id="division_id" name="division_id" data-choices data-choices-search-false>
                                <option value="" selected>Select Division</option>
                                @foreach ($divisions as $division)
                                    <option value="{{ $division->id }}">
                                        {{ $division->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">
                                Please select a division!
                            </div>
                        </div>
                    </div>
                    <!-- end col -->

                    <!-- Town -->
                    <div class="col-12">
                        <div class="mb-3">
                            <label for="town" class="form-label">
                                Town
                            </label>
                            <select class="form-control" id="town_id" name="town_id" data-choices data-choices-search-false>
                                <option value="" selected>Select Town</option>
                                @foreach ($towns as $town)
                                    <option value="{{ $town->id }}">
                                        {{ $town->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">
                                Please select a town!
                            </div>
                        </div>
                    </div>
                    <!-- end col -->

                    <!-- Store -->
                    <div class="col-12">
                        <div class="mb-3">
                            <label for="store" class="form-label">
                                Store
                            </label>
                            <select class="form-control" id="store" name="store_id" data-choices data-choices-search-true>
                                <option value="" selected>Select Store</option>
                                @foreach ($stores as $store)
                                    <option value="{{ $store->id }}">
                                        {{ optional($store->brand)->name ?? '' }} ({{ $store->name }})
                                    </option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">
                                Please select a store!
                            </div>
                        </div>
                    </div>
                    <!-- end col-->
                </div>
                <div style="height: 100px;"></div>
            </div>
            <!-- end main content -->
            
            <!-- Sticky footer at the bottom -->
            <div class="offcanvas-footer p-3 hstack gap-3 text-center position-absolute w-100 bg-white" style="bottom: 0;">
                <button class="btn btn-light btn-label w-100" id="clearFilters">
                    <i class="ri-delete-bin-fill label-icon align-middle fs-16 me-2"></i> 
                    Clear Filters
                </button>

                <button type="submit" class="btn btn-secondary btn-label w-100" id="filter">
                    <i class="ri-equalizer-fill label-icon align-middle fs-16 me-2"></i> 
                    Filter
                </button>                        
            </div>
        </div>
    </form>
</div>
<!-- end offcanvas-->  