<?php

namespace App\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client as GuzzleClient;

class SendIdNumberToSap implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected $idNumber;
    protected $applicant;

    /**
     * Number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $backoff = 10; // Retry every 10 seconds

    /**
     * Create a new job instance.
     *
     * @param string $idNumber
     * @param Applicant $applicant
     * @return void
     */
    public function __construct($idNumber, $applicant)
    {
        $this->idNumber = $idNumber;
        $this->applicant = $applicant;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Create Guzzle client for HTTP request
        $client = new GuzzleClient();

        // SAP endpoint and credentials from config
        $endpoint = config('services.sap.endpoint');
        $username = config('services.sap.username');
        $password = config('services.sap.password');
        $contractId = config('services.sap.contract_id');

        // Encode credentials for Basic Auth
        $authHeader = 'Basic ' . base64_encode("{$username}:{$password}");

        // Prepare the full URL by adding IDNumber as a query parameter
        $url = "{$endpoint}(IDNumber='{$this->idNumber}',EmployeeNumber='')?\$format=json";

        try {
            // Send the GET request to the SAPHR endpoint
            $response = $client->request('GET', $url, [
                'headers' => [
                    'Authorization' => $authHeader,
                    'ContractID' => $contractId,
                    'Content-Type' => 'application/json',
                ]
            ]);

            // Get the response body
            $responseBody = json_decode($response->getBody(), true);

            // Check if response contains a status
            if (isset($responseBody['d']['Status'])) {
                $status = $responseBody['d']['Status'];

                // Update the applicant's employment based on the status
                if ($status === 'A' || $status === 'B' || $status === 'P') {
                    $this->applicant->employment = $status;
                }
            } elseif (empty($responseBody['d'])) {
                // If the array is empty, set employment to 'N'
                $this->applicant->employment = 'N';
            } else {
                // If there is no status, handle it as an unknown issue
                $this->applicant->employment = 'N';
            }

            // Save the applicant changes
            $this->applicant->save();
        } catch (Exception $e) {
            // Log the error if something goes wrong
            Log::error('Error in SendIdNumberToSap Job: ' . $e->getMessage());

            // Re-throw the exception to trigger retry mechanism
            throw $e;
        }
    }
}
