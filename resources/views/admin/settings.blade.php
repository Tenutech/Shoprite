@extends('layouts.master')
@section('title') @lang('translation.tabs') @endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1') Pages @endslot
        @slot('title') Settings @endslot
    @endcomponent

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
                                @csrf                                    
                                <div class="row">
                                    <h5 class="card-title text-decoration-underline mb-3">
                                        Application Settings:
                                    </h5>
                                    @php
                                        $vacancyPostingDuration = $settings->firstWhere('key', 'vacancy_posting_duration');
                                        $shortlistExpiry = $settings->firstWhere('key', 'shortlist_expiry');                                        
                                        $minShortlistNumber = $settings->firstWhere('key', 'min_shorlist_number');
                                        $maxShortlistNumber = $settings->firstWhere('key', 'max_shorlist_number');
                                        $maxDistanceFromStore = $settings->firstWhere('key', 'max_distance_from_store');
                                        $sessionTimeout = $settings->firstWhere('key', 'session_timeout');
                                        $vacancyPostingDurationNoAppointment = $settings->firstWhere('key', 'vacancy_posting_duration_no_appointment');
                                        $autoPlacedBackInTalentPoolFixedTerm = $settings->firstWhere('key', 'auto_placed_back_in_talent_pool_fixed_term');
                                        $autoPlacedBackInTalentPoolPeakSeason = $settings->firstWhere('key', 'auto_placed_back_in_talent_pool_peak_season');
                                        $autoPlacedBackInTalentPoolYes = $settings->firstWhere('key', 'auto_placed_back_in_talent_pool_yes');
                                        $autoPlacedBackInTalentPoolRRP = $settings->firstWhere('key', 'auto_placed_back_in_talent_pool_rrp');
                                    @endphp
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="vacancyPostingDuration" class="form-label">
                                                Vacancy Posting Duration <span class="text-danger">*</span>
                                            </label>
                                            @if ($vacancyPostingDuration && $vacancyPostingDuration->description)
                                                <p class="text-muted">
                                                    {{ $vacancyPostingDuration->description }}
                                                </p>
                                            @endif
                                            <input type="number" class="form-control @error('vacancy_posting_duration') is-invalid @enderror" name="vacancy_posting_duration" id="vacancyPostingDuration" placeholder="Enter the amount of days" value="{{ $vacancyPostingDuration ? ($vacancyPostingDuration->value ? $vacancyPostingDuration->value : 1) : 1 }}" min="1" required/>
                                            @error('vacancy_posting_duration')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
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
                                            @if ($shortlistExpiry && $shortlistExpiry->description)
                                                <p class="text-muted">
                                                    {{ $shortlistExpiry->description }}
                                                </p>
                                            @endif
                                            <input type="number" class="form-control @error('shortlist_expiry') is-invalid @enderror" name="shortlist_expiry" id="shortlistExpiry" placeholder="Enter the amount of days" value="{{ $shortlistExpiry ? ($shortlistExpiry->value ? $shortlistExpiry->value : 1) : 1 }}" min="1" required/>
                                            @error('shortlist_expiry')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                            <div class="invalid-feedback">
                                                Please enter the amount of days
                                            </div>
                                        </div>
                                    </div>
                                    <!--end col-->

                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="minShortlistNumber" class="form-label">
                                                Minimum Shortlist Number <span class="text-danger">*</span>
                                            </label>
                                            @if ($minShortlistNumber && $minShortlistNumber->description)
                                                <p class="text-muted">
                                                    {{ $minShortlistNumber->description }}
                                                </p>
                                            @endif
                                            <input type="number" class="form-control @error('min_shortlist_number') is-invalid @enderror" name="min_shortlist_number" id="minShortlistNumber" placeholder="Enter the least amount of candidates on a shortlist" value="{{ $minShortlistNumber ? ($minShortlistNumber->value ? $minShortlistNumber->value : 5) : 5 }}" min="1" required/>
                                            @error('min_shortlist_number')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                            <div class="invalid-feedback">
                                                Please enter the minimum shortlist number
                                            </div>
                                        </div>
                                    </div>
                                    <!--end col-->

                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="maxShortlistNumber" class="form-label">
                                                Max Shortlist Number <span class="text-danger">*</span>
                                            </label>
                                            @if ($maxShortlistNumber && $maxShortlistNumber->description)
                                                <p class="text-muted">
                                                    {{ $maxShortlistNumber->description }}
                                                </p>
                                            @endif
                                            <input type="number" class="form-control @error('max_shortlist_number') is-invalid @enderror" name="max_shortlist_number" id="maxShortlistNumber" placeholder="Enter the maximum of candidates on a shortlist" value="{{ $maxShortlistNumber ? ($maxShortlistNumber->value ? $maxShortlistNumber->value : 20) : 20 }}" max="100" required/>
                                            @error('max_shortlist_number')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                            <div class="invalid-feedback">
                                                Please enter the maximum shortlist number
                                            </div>
                                        </div>
                                    </div>
                                    <!--end col-->

                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="maxDistanceFromStore" class="form-label">
                                                Max Distance From Store<span class="text-danger">*</span>
                                            </label>
                                            @if ($maxDistanceFromStore && $maxDistanceFromStore->description)
                                                <p class="text-muted">
                                                    {{ $maxDistanceFromStore->description }}
                                                </p>
                                            @endif
                                            <input type="number" class="form-control @error('max_distance_from_store') is-invalid @enderror" name="max_distance_from_store" id="maxDistanceFromStore" placeholder="Enter the maximum distance in km from store" value="{{ $maxDistanceFromStore ? ($maxDistanceFromStore->value ? $maxDistanceFromStore->value : 50) : 50 }}" max="100" required/>
                                            @error('max_distance_from_store')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                            <div class="invalid-feedback">
                                                Please enter the max distance from store
                                            </div>
                                        </div>
                                    </div>
                                    <!--end col-->

                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="sessionTimeout" class="form-label">
                                                Session Timeout <span class="text-danger">*</span>
                                            </label>
                                            @if ($sessionTimeout && $sessionTimeout->description)
                                                <p class="text-muted">
                                                    {{ $sessionTimeout->description }}
                                                </p>
                                            @endif
                                            <input type="number" class="form-control @error('session_timeout') is-invalid @enderror" name="session_timeout" id="sessionTimeout" placeholder="Enter the amount of minutes" value="{{ $sessionTimeout ? ($sessionTimeout->value ? $sessionTimeout->value : 1) : 1 }}" min="1" required/>
                                            @error('session_timeout')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                            <div class="invalid-feedback">
                                                Please enter the amount of days
                                            </div>
                                        </div>
                                    </div>
                                    <!--end col-->

                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="vacancyPostingDurationNoAppointment" class="form-label">
                                                Vacancy Posting Duration (No Appointment) <span class="text-danger">*</span>
                                            </label>
                                            @if ($vacancyPostingDurationNoAppointment && $vacancyPostingDurationNoAppointment->description)
                                                <p class="text-muted">
                                                    {{ $vacancyPostingDurationNoAppointment->description }}
                                                </p>
                                            @endif
                                            <input type="number" class="form-control @error('vacancy_posting_duration_no_appointment') is-invalid @enderror" name="vacancy_posting_duration_no_appointment" id="vacancyPostingDurationNoAppointment" placeholder="Enter the amount of days" value="{{ $vacancyPostingDurationNoAppointment ? ($vacancyPostingDurationNoAppointment->value ? $vacancyPostingDurationNoAppointment->value : 1) : 1 }}" min="1" required/>
                                            @error('vacancy_posting_duration_no_appointment')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                            <div class="invalid-feedback">
                                                Please enter the amount of days
                                            </div>
                                        </div>
                                    </div>
                                    <!--end col-->

                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="vacancyPostingDuration" class="form-label">
                                                Auto Placed Back In Talent Pool: Fixed Term <span class="text-danger">*</span>
                                            </label>
                                            @if ($autoPlacedBackInTalentPoolFixedTerm && $autoPlacedBackInTalentPoolFixedTerm->description)
                                                <p class="text-muted">
                                                    {{ $autoPlacedBackInTalentPoolFixedTerm->description }}
                                                </p>
                                            @endif
                                            <input type="number" class="form-control @error('auto_placed_back_in_talent_pool_fixed_term') is-invalid @enderror" name="auto_placed_back_in_talent_pool_fixed_term" id="autoPlacedBackInTalentPoolFixedTerm" placeholder="Enter the amount of days" value="{{ $autoPlacedBackInTalentPoolFixedTerm ? ($autoPlacedBackInTalentPoolFixedTerm->value ? $autoPlacedBackInTalentPoolFixedTerm->value : 1) : 1 }}" min="1" required/>
                                            @error('auto_placed_back_in_talent_pool_fixed_term')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                            <div class="invalid-feedback">
                                                Please enter the amount of days
                                            </div>
                                        </div>
                                    </div>
                                    <!--end col-->

                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="autoPlacedBackInTalentPoolPeakSeason" class="form-label">
                                                Auto Placed Back In Talent Pool: Peak Season <span class="text-danger">*</span>
                                            </label>
                                            @if ($autoPlacedBackInTalentPoolPeakSeason && $autoPlacedBackInTalentPoolPeakSeason->description)
                                                <p class="text-muted">
                                                    {{ $autoPlacedBackInTalentPoolPeakSeason->description }}
                                                </p>
                                            @endif
                                            <input type="number" class="form-control @error('auto_placed_back_in_talent_pool_peak_season') is-invalid @enderror" name="auto_placed_back_in_talent_pool_peak_season" id="autoPlacedBackInTalentPoolPeakSeason" placeholder="Enter the amount of days" value="{{ $autoPlacedBackInTalentPoolPeakSeason ? ($autoPlacedBackInTalentPoolPeakSeason->value ? $autoPlacedBackInTalentPoolPeakSeason->value : 1) : 1 }}" min="1" required/>
                                            @error('auto_placed_back_in_talent_pool_peak_season')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                            <div class="invalid-feedback">
                                                Please enter the amount of days
                                            </div>
                                        </div>
                                    </div>
                                    <!--end col-->

                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="autoPlacedBackInTalentPoolYes" class="form-label">
                                                Auto Placed Back In Talent Pool: Yes <span class="text-danger">*</span>
                                            </label>
                                            @if ($autoPlacedBackInTalentPoolYes && $autoPlacedBackInTalentPoolYes->description)
                                                <p class="text-muted">
                                                    {{ $autoPlacedBackInTalentPoolYes->description }}
                                                </p>
                                            @endif
                                            <input type="number" class="form-control @error('auto_placed_back_in_talent_pool_yes') is-invalid @enderror" name="auto_placed_back_in_talent_pool_yes" id="autoPlacedBackInTalentPoolYes" placeholder="Enter the amount of days" value="{{ $autoPlacedBackInTalentPoolYes ? ($autoPlacedBackInTalentPoolYes->value ? $autoPlacedBackInTalentPoolYes->value : 1) : 1 }}" min="1" required/>
                                            @error('auto_placed_back_in_talent_pool_yes')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                            <div class="invalid-feedback">
                                                Please enter the amount of days
                                            </div>
                                        </div>
                                    </div>
                                    <!--end col-->

                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="autoPlacedBackInTalentPoolRRP" class="form-label">
                                                Auto Placed Back In Talent Pool: RRP <span class="text-danger">*</span>
                                            </label>
                                            @if ($autoPlacedBackInTalentPoolRRP && $autoPlacedBackInTalentPoolRRP->description)
                                                <p class="text-muted">
                                                    {{ $autoPlacedBackInTalentPoolRRP->description }}
                                                </p>
                                            @endif
                                            <input type="number" class="form-control @error('auto_placed_back_in_talent_pool_rrp') is-invalid @enderror" name="auto_placed_back_in_talent_pool_rrp" id="vacancyPostingDurationNoAppointment" placeholder="Enter the amount of days" value="{{ $autoPlacedBackInTalentPoolRRP ? ($autoPlacedBackInTalentPoolRRP->value ? $autoPlacedBackInTalentPoolRRP->value : 1) : 1 }}" min="1" required/>
                                            @error('auto_placed_back_in_talent_pool_rrp')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
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
                                @csrf
                                <div class="mb-3">
                                    <h5 class="card-title text-decoration-underline mb-3">
                                        Application Reminders:
                                    </h5>
                                    <ul class="list-unstyled mb-0">
                                        @php
                                            $vacancyCreatedNoShortlistChecked = $reminders->firstWhere('type', 'vacancy_created_no_shortlist')?->is_active === 1;
                                            $shortlistCreatedNoInterviewChecked = $reminders->firstWhere('type', 'shortlist_created_no_interview')?->is_active === 1;
                                            $interviewScheduledNoVacancyFilledChecked = $reminders->firstWhere('type', 'interview_scheduled_no_vacancy_filled')?->is_active === 1;
                                        @endphp
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
                                                    <input class="form-check-input" type="checkbox" role="switch" id="vacancyCreatedNoShortlist" name="vacancy_created_no_shortlist" {{ $vacancyCreatedNoShortlistChecked ? 'checked' : '' }}/>
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
                                                    <input class="form-check-input" type="checkbox" role="switch" id="shortlistCreatedNoInterview" name="shortlist_created_no_interview" {{ $shortlistCreatedNoInterviewChecked ? 'checked' : '' }}/>
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
                                                    <input class="form-check-input" type="checkbox" role="switch" id="interviewScheduledNoVacancyFilled" name="interview_scheduled_no_vacancy_filled" {{ $interviewScheduledNoVacancyFilledChecked ? 'checked' : '' }}/>
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

@endsection
@section('script')
    <script src="{{ URL::asset('build/libs/prismjs/prism.js') }}"></script>
    <script src="{{ URL::asset('build/js/pages/settings.init.js') }}"></script>
    <script src="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.js') }}"></script>
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection
