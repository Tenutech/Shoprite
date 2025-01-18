<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Exports\ApplicantsExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Mail;
use App\Mail\ReportReadyMail;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class GenerateApplicantsReportJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    // Variables to hold the job's input data
    public $authUser; // The authenticated user
    public $type; // The type of report (e.g., region, division, store, etc.)
    public $id; // The ID of the region, division, or store
    public $startDate; // The start date of the report range
    public $endDate; // The end date of the report range
    public $maxDistanceFromStore; // Maximum proximity distance for filtering applicants
    public $filters; // Additional filters for the report

    /**
     * Create a new job instance.
     *
     * @param object $authUser The authenticated user
     * @param string $type The type of report
     * @param int|null $id The ID of the region, division, or store
     * @param \Carbon\Carbon $startDate The start date for filtering
     * @param \Carbon\Carbon $endDate The end date for filtering
     * @param int $maxDistanceFromStore The max distance in kilometers
     * @param array $filters Additional filters
     */
    public function __construct($authUser, $type, $id, $startDate, $endDate, $maxDistanceFromStore, $filters)
    {
        $this->authUser = $authUser;
        $this->type = $type;
        $this->id = $id;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->maxDistanceFromStore = $maxDistanceFromStore;
        $this->filters = $filters;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        try {
            Log::info("Starting job: " . self::class);

            // Generate the file name
            $fileName = 'Applicants_Report_' . now()->format('Y_m_d_H_i_s') . '.csv';
            $filePath = "reports/{$fileName}";
            $zipFileName = 'Applicants_Report_' . now()->format('Y_m_d_H_i_s') . '.zip';
            $zipFilePath = "reports/{$zipFileName}";

            // Export the file and save it to storage
            Excel::store(
                new ApplicantsExport($this->type, $this->id, $this->startDate, $this->endDate, $this->maxDistanceFromStore, $this->filters),
                $filePath
            );

            // Compress the file into a ZIP archive
            try {
                $csvFullPath = storage_path("app/{$filePath}");
                $zipFullPath = storage_path("app/{$zipFilePath}");
            
                // Ensure the CSV file exists
                if (!file_exists($csvFullPath)) {
                    throw new \Exception("CSV file not found at path: {$csvFullPath}");
                }
            
                Log::info("Creating ZIP file at: {$zipFullPath}");
            
                $zip = new \ZipArchive();
            
                if ($zip->open($zipFullPath, \ZipArchive::CREATE) === true) {
                    $zip->addFile($csvFullPath, $fileName);
                    $zip->close();
            
                    Log::info("ZIP file created successfully: {$zipFullPath}");
                } else {
                    throw new \Exception("Failed to create ZIP file at: {$zipFullPath}");
                }
            
                // Verify ZIP file exists
                if (!file_exists($zipFullPath)) {
                    throw new \Exception("ZIP file not created at path: {$zipFullPath}");
                }
            } catch (\Exception $e) {
                Log::error("Error creating ZIP file: " . $e->getMessage());
            }


            // Clean up previous exports
            $this->deleteOldReports();

            // Send the email using the Mail facade
            Mail::send([], [], function ($message) use ($zipFilePath) {
                $message->to($this->authUser->email) // Recipient email
                    ->from('noreply@otbgroup.co.za', 'Shoprite - Job Opportunities') // Set the "from" address and name
                    ->subject('Your Applicants Report is Ready') // Email subject
                    ->html( // Use HTML for the body
                        "<p>Dear {$this->authUser->firstname},</p>
                        <p>Please see attached the applicants report.</p>
                        <p>Kind Regards,<br>Shoprite Job Opportunities</p>"
                    )
                    ->attach(storage_path("app/{$zipFilePath}")); // Attach the ZIP file
            });

            Log::info("Job completed successfully.");
        } catch (\Exception $e) {
            Log::error("Error in job: " . self::class . " - " . $e->getMessage());
            // Optionally, you can retry or handle the exception in another way
            throw $e; // Re-throw to mark the job as failed
        }
    }

    /**
     * Delete all previous reports in the reports directory, keeping only the latest one.
     */
    protected function deleteOldReports()
    {
        $reportsDirectory = storage_path('app/reports');

        // Get all files in the reports directory
        $files = File::files($reportsDirectory);

        // Sort files by modified time in descending order
        usort($files, function ($a, $b) {
            return $b->getMTime() - $a->getMTime();
        });

        // Remove all files except the latest one
        foreach (array_slice($files, 1) as $file) {
            File::delete($file->getPathname());
        }
    }
}
