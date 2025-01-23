<!-- Interview NoShow Modal -->
<div class="modal fade zoomIn" id="interviewNoShowModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="btn-close" id="noShowInterview-close" data-bs-dismiss="modal" aria-label="Close" id="btn-close"></button>
            </div>
            <div class="modal-body p-5 text-center">
                <lord-icon src="https://cdn.lordicon.com/xzybfbcm.json" trigger="loop" style="width:90px;height:90px"></lord-icon>
                <div class="mt-4 text-center">
                    <h4 class="fs-semibold">
                        You are about to mark this interview as a no show ?
                    </h4>
                    <p class="text-muted fs-14 mb-4 pt-1">
                        Marking the interview as a no show will record the candidate's absence and notify relevant parties. Please confirm if you wish to proceed with marking the candidate as a no show.
                    </p>                                   
                    <div class="hstack gap-2 justify-content-center remove">
                        <button class="btn btn-light" data-bs-dismiss="modal" id="noShowInterview-close">
                            <i class="ri-close-line me-1 align-middle"></i>
                            Close
                        </button>
                        <button class="btn btn-danger" id="noShow-interview" data-id={{ Crypt::encryptString($interviewId) }}>
                            Yes, No Show!
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!--end no show modal -->