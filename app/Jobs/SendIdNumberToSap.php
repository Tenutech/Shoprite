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
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $idNumber;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($idNumber)
    {
        $this->idNumber = $idNumber;
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
        $endpoint = config('services.saphr.endpoint');
        $username = config('services.saphr.username');
        $password = config('services.saphr.password');
        $contractId = config('services.saphr.contract_id');

        // Encode credentials for Basic Auth
        $authHeader = 'Basic ' . base64_encode("{$username}:{$password}");

        try {
            // Send the request to the SAPHR endpoint
            $response = $client->request('POST', $endpoint, [
                'headers' => [
                    'Authorization' => $authHeader,
                    'ContractID' => $contractId,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'id_number' => $this->idNumber
                ]
            ]);

            // Get the response body
            $responseBody = json_decode($response->getBody(), true);

            // Log the successful response
            Log::info('SAPHR API Response:', ['response' => $responseBody]);

        } catch (Exception $e) {
            // Log the error if something goes wrong
            Log::error('Error in SendIdNumberToSap Job: ' . $e->getMessage());
        }
    }
}
