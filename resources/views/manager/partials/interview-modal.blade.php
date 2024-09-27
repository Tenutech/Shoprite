<!--  Interview Modal -->
<div class="modal fade" id="interviewModal" tabindex="-1" role="dialog" aria-labelledby="interviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0">
            <div class="modal-header p-3 bg-secondary-subtle">
                <h5 class="modal-title" id="modal-title">
                    Schedule Interview
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true"></button>
            </div>
            <div class="modal-body p-4">
                <form id="formInterviewSchedule" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="UserID" name="user_id" value="{{ Crypt::encryptstring(Auth::id()) }}"/>
                    <input type="hidden" id="vacancyID" name="vacancy_id" value="{{ $vacancyId ? $vacancyId : '' }}"/>
                    <input type="hidden" name="applicant_id" value="{{ $applicantId }} ">
                    <div class="row">
                        <div class="col-12">
                            <div class="mb-3">
                                <label class="form-label" for="date">Interview Date</label>
                                <div class="input-group">
                                    <input type="text" class="form-control flatpickr-input active" id="date" name="date" placeholder="Select date" data-provider="flatpickr" data-date-format="d M, Y"  value="{{ date('d M Y') }}" readonly="readonly" required>
                                    <span class="input-group-text"><i class="ri-calendar-event-line"></i></span>
                                    <div class="invalid-feedback">
                                        Please select a date
                                    </div>
                                </div>
                            </div>
                        </div><!--end col-->
                        <div class="col-12" id="event-time">
                            <div class="row">
                                <div class="col-6">
                                    <div class="mb-3">
                                        <label class="form-label" for="startTime">Start Time</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control flatpickr-input active" data-provider="timepickr" id="startTime" name="start_time" data-time-hrs="true" id="timepicker-24hrs" readonly="readonly" required>
                                            <span class="input-group-text"><i class="ri-time-line"></i></span>
                                            <div class="invalid-feedback">
                                                Please select a start time
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="mb-3">
                                        <label class="form-label" for="endTime">End Time</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control flatpickr-input active" data-provider="timepickr" id="endTime" name="end_time" data-time-hrs="true" id="timepicker-24hrs" readonly="readonly" required>
                                            <span class="input-group-text"><i class="ri-time-line"></i></span>
                                            <div class="invalid-feedback">
                                                Please select a end time
                                            </div>
                                        </div>                                        
                                    </div>
                                </div>
                            </div>
                        </div><!--end col-->
                        <div class="col-12">
                            <div class="mb-3">
                                <label class="form-label" for="location">Location</label>
                                <div>
                                    <input type="text" class="form-control d-block" id="location" name="location" placeholder="Interview location" value="{{ $vacancy ? optional($vacancy->store)->address : '' }}" {{ $authUser->role_id >= 6 ? 'readonly' : '' }} required>
                                    <div class="invalid-feedback">
                                        Please enter a location
                                    </div>
                                </div>
                            </div>
                        </div><!--end col-->
                        <div class="col-12">
                            <div class="mb-3">
                                <label class="form-label" for="notes">Notes</label>
                                <textarea class="form-control d-block" id="notes" name="notes" placeholder="Enter additional notes" rows="3" spellcheck="true" {{ $authUser->role_id >= 6 ? 'readonly' : '' }}>Please bring your ID and a copy of your CV.</textarea>
                            </div>
                        </div><!--end col-->
                    </div><!--end row-->
                    <div class="hstack gap-2 justify-content-end">
                        <button type="submit" class="btn btn-secondary" id="btn-save-event">
                            Send Invite
                        </button>
                    </div>
                </form>
            </div>
        </div> <!-- end modal-content-->
    </div> <!-- end modal dialog-->
</div>