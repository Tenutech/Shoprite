<!-- Vacancy Delete Warning Modal -->
<div class="modal fade zoomIn" id="vacancyDeleteWarningModal-{{ $vacancy->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-5 text-center">
                <lord-icon src="https://cdn.lordicon.com/usownftb.json" trigger="loop" style="width:90px; height:90px"></lord-icon>
                <div class="mt-4 text-center">
                    <h4 class="fs-semibold">
                        Vacancy Delete Warning
                    </h4>
                    <p class="text-muted fs-14 mb-4 pt-1">
                        <b>{{ $vacancy->id }}. {{ $vacancy->position->name }}: ({{ $vacancy->store->brand->name }} - {{ $vacancy->store->name }})</b> has not been filled. 
                        This vacancy will be deleted
                        @if(isset($vacancy->days_until_deletion))
                            @if($vacancy->days_until_deletion == 0)
                                today.
                            @else
                                in {{ $vacancy->days_until_deletion }} 
                                {{ $vacancy->days_until_deletion == 1 ? 'day' : 'days' }}.
                            @endif
                        @else
                            soon.
                        @endif
                        Please review your shortlist and proceed with scheduling interviews to take the next step in your hiring process.
                    </p>                    
                    <div class="hstack gap-2 justify-content-center remove">
                        <button class="btn btn-light" data-bs-dismiss="modal">
                            <i class="ri-close-line me-1 align-middle"></i>
                            Close
                        </button>
                        <a class="btn btn-primary" id="view-vacancy-{{ $vacancy->id }}" href="{{ route('shortlist.index') }}?id={{ Crypt::encryptString($vacancy->id) }}">
                            View Vacancy
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!--end vacancy delete warning modal -->