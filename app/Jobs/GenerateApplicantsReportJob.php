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
            // Generate file names and paths
            $timestamp = now()->format('Y_m_d_H_i_s');
            $fileName = "Applicants_Report_{$timestamp}.csv";
            $zipFileName = "Applicants_Report_{$timestamp}.zip";
        
            $filePath = "reports/{$fileName}";
            $zipFilePath = "reports/{$zipFileName}";
        
            $csvFullPath = storage_path("app/{$filePath}");
            $zipFullPath = storage_path("app/{$zipFilePath}");
        
            // Export the CSV file and save it to storage
            Excel::store(
                new ApplicantsExport(
                    $this->type,
                    $this->id,
                    $this->startDate,
                    $this->endDate,
                    $this->maxDistanceFromStore,
                    $this->filters
                ),
                $filePath
            );
        
            // Compress the CSV file into a ZIP archive
            $this->createZipFile($csvFullPath, $zipFullPath, $fileName);
        
            // Clean up old reports
            $this->deleteOldReports();
        
            // Send the email with the ZIP file attached
            $this->sendSuccessEmail($zipFullPath);
        } catch (\Exception $e) {
            Log::error("Error in GenerateApplicantsReportJob: {$e->getMessage()}");
        
            // Send the error notification email
            $this->sendErrorEmail($e);
        
            // Re-throw the exception to mark the job as failed
            throw $e;
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

    /**
     * Create a ZIP file from the given CSV file.
     *
     * @param string $csvFullPath Path to the CSV file.
     * @param string $zipFullPath Path to save the ZIP file.
     * @param string $fileName The name of the file inside the ZIP archive.
     * @throws \Exception If ZIP creation fails.
     */
    protected function createZipFile(string $csvFullPath, string $zipFullPath, string $fileName)
    {
        if (!file_exists($csvFullPath)) {
            throw new \Exception("CSV file not found at path: {$csvFullPath}");
        }

        $zip = new \ZipArchive();

        if ($zip->open($zipFullPath, \ZipArchive::CREATE) === true) {
            $zip->addFile($csvFullPath, $fileName);
            $zip->close();
        } else {
            throw new \Exception("Failed to create ZIP file at: {$zipFullPath}");
        }

        if (!file_exists($zipFullPath)) {
            throw new \Exception("ZIP file was not created at path: {$zipFullPath}");
        }
    }

    /**
     * Send an email with the ZIP file attached.
     *
     * @param string $zipFullPath Path to the ZIP file.
     */
    protected function sendSuccessEmail(string $zipFullPath)
    {
        Mail::send([], [], function ($message) use ($zipFullPath) {
            $message->to($this->authUser->email)
                ->from('noreply@otbgroup.co.za', 'Shoprite - Job Opportunities')
                ->subject('Your Applicants Report is Ready')
                ->html(
                    "<p>Dear {$this->authUser->firstname},</p>
                    <p>Please see attached the applicants report.</p>
                    <p>Kind Regards,<br>Shoprite Job Opportunities</p>"
                )
                ->attach($zipFullPath);
        });
    }

    /**
     * Send an error notification email to the user.
     *
     * @param \Exception $e The exception that occurred.
     */
    protected function sendErrorEmail(\Exception $e)
    {
        Mail::send([], [], function ($message) use ($e) {
            $message->to($this->authUser->email)
                ->from('noreply@otbgroup.co.za', 'Shoprite - Job Opportunities')
                ->subject('Error Generating Your Applicants Report')
                ->html(
                    "<p>Dear {$this->authUser->firstname},</p>
                    <p>There was an error creating your applicants export: <strong>{$e->getMessage()}</strong></p>
                    <p>Please contact the support team for assistance.</p>
                    <p>Kind Regards,<br>Shoprite Job Opportunities</p>"
                );
        });
    }
}
