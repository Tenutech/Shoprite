<?php

namespace App\Jobs;

use App\Models\Applicant;
use App\Models\ApplicantTotalData;
use App\Models\ApplicantMonthlyData;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class UpdateApplicantData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $applicantId;
    protected $action;
    protected $status;

    /**
     * Create a new job instance.
     *
     * @param int $applicantId
     * @param string $action 'created', 'updated', etc.
     * @param string|null $status 'Application', 'Interviewed', 'Appointed', 'Rejected'
     */
    public function __construct($applicantId, $action, $status = null)
    {
        $this->applicantId = $applicantId;
        $this->action = $action;
        $this->status = $status;
    }

    /*
    |--------------------------------------------------------------------------
    | Execute The Job
    |--------------------------------------------------------------------------
    */

    public function handle(): void
    {
        $applicant = Applicant::find($this->applicantId);
        if (!$applicant) {
            Log::error("UpdateApplicantData: Applicant not found with ID {$this->applicantId}");
            return;
        }

        // If yearlyDataId was not provided, fetch or create it.
        $yearlyData = ApplicantTotalData::firstOrCreate(
            ['year' => Carbon::now()->year],
            ['total_applicants' => 0]
        );

        if ($this->status == 'Interviewed') {
            $yearlyData->increment('total_interviewd');
        } elseif ($this->status == 'Appointed') {
            $yearlyData->increment('total_appointed');

            // Calculate the difference in minutes and update total_time_to_appointed
            $timeToAppoint = $applicant->created_at->diffInMinutes($applicant->updated_at);
            $yearlyData->increment('total_time_to_appointed', $timeToAppoint);
        }

        if ($this->action === 'created') {
            $this->handleCreation($applicant, $yearlyData);
        } elseif ($this->action === 'updated') {
            if ($this->status) {
                $this->handleStatusUpdate($yearlyData->id, $this->status);
            } else {
                $this->handleUpdate($applicant, $yearlyData);
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Handle Creation
    |--------------------------------------------------------------------------
    */

    protected function handleCreation($applicant, $yearlyData)
    {
        $yearlyData->increment('total_applicants');
        $monthField = strtolower(Carbon::now()->format('M'));
        $yearlyData->increment($monthField);

        $this->updateMonthlyData($applicant, $yearlyData->id, true);
    }

    /*
    |--------------------------------------------------------------------------
    | Handle Update
    |--------------------------------------------------------------------------
    */

    protected function handleUpdate($applicant, $yearlyData)
    {
        $this->updateMonthlyData($applicant, $yearlyData->id, false);
    }

    /*
    |--------------------------------------------------------------------------
    | Update Category Count
    |--------------------------------------------------------------------------
    */

    protected function updateMonthlyData($applicant, $yearlyDataId, $isNew)
    {
        $attributes = [
            'Gender' => $applicant->gender_id,
            'Race' => $applicant->race_id,
            'Position' => $applicant->position_id,
            'Province' => optional($applicant->town)->province_id,
        ];

        foreach ($attributes as $type => $id) {
            if (!is_null($id)) {
                if ($isNew || $applicant->wasChanged(strtolower($type) . "_id")) {
                    $this->adjustMonthlyData($yearlyDataId, $type, $id, true);
                }
            }
        }
    }

    protected function adjustMonthlyData($yearlyDataId, $categoryType, $categoryId, $isIncrement)
    {
        $this->updateCategoryCount($yearlyDataId, $categoryType, $categoryId, $isIncrement);
    }

    protected function updateCategoryCount($yearlyDataId, $categoryType, $categoryId, $isIncrement)
    {
        $monthlyData = ApplicantMonthlyData::firstOrCreate([
            'applicant_total_data_id' => $yearlyDataId,
            'category_id' => $categoryId,
            'category_type' => $categoryType,
            'month' => Carbon::now()->format('M'),
        ], [
            'count' => 0 // Ensures a default count is set if a new record is created
        ]);

        if ($isIncrement) {
            $monthlyData->increment('count');
        } else {
            $monthlyData->decrement('count');
        }
    }

    protected function handleStatusUpdate($yearlyDataId, $categoryType)
    {
        $monthlyData = ApplicantMonthlyData::firstOrCreate([
            'applicant_total_data_id' => $yearlyDataId,
            'category_id' => null,
            'category_type' => $categoryType,
            'month' => Carbon::now()->format('M'),
        ], [
            'count' => 0 // Ensures a default count is set if a new record is created
        ]);

        $monthlyData->increment('count');
    }
}
