<!-- Un-Actioned Shortlist Modal -->
<div class="modal fade zoomIn" id="unActionedShortlistModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-5 text-center">
                <lord-icon src="https://cdn.lordicon.com/usownftb.json" trigger="loop" style="width:90px; height:90px"></lord-icon>
                <div class="mt-4 text-center">
                    <h4 class="fs-semibold">
                        Un-Actioned Shortlist
                    </h4>
                    <p class="text-muted fs-14 mb-4 pt-1">
                        You have created a shortlist, but no interviews have been scheduled yet. Please review your shortlist and proceed with scheduling interviews to take the next step in your hiring process.
                    </p>                                   
                    <div class="hstack gap-2 justify-content-center remove">
                        <button class="btn btn-light" data-bs-dismiss="modal">
                            <i class="ri-close-line me-1 align-middle"></i>
                            Close
                        </button>
                        <a class="btn btn-primary" id="view-shortlist" href="{{ route('shortlist.index') }}?id={{ Crypt::encryptString($shortlist->vacancy_id) }}">
                            View Shortlist
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!--end un-actioned shortlist modal -->