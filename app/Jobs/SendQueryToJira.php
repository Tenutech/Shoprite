<?php

namespace App\Jobs;

use App\Models\Query;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class SendQueryToJira implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected $query;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Query $query)
    {
        $this->query = $query;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $client = new Client();

         // Map severity to Jira priority levels
        $priorityMap = [
            'Low' => 'Low',
            'Medium' => 'Medium',
            'High' => 'High',
            'Critical' => 'Highest',
        ];
        
        $priority = $priorityMap[$this->query->severity] ?? 'Medium'; // Default to 'Medium' if severity is not mapped

        $issueData = [
            'fields' => [
                'project' => ['key' => 'SQ'],
                'summary' => $this->query->subject,
                'description' =>
                    "Body: " . strip_tags($this->query->body) . "\n\n" .
                    "User ID: {$this->query->user_id}\n" .
                    "Firstname: {$this->query->firstname}\n" .
                    "Lastname: {$this->query->lastname}\n" .
                    "Email: {$this->query->email}\n",
                'issuetype' => ['name' => 'Task'], // Use appropriate issue type
                'priority' => ['name' => $priority], // Set priority based on mapped severity
                'assignee' => null, // Set assignee as unassigned
            ],
        ];

        try {
            $response = $client->post(
                config('services.jira.host') . '/rest/api/2/issue',
                [
                    'auth' => [config('services.jira.user'), config('services.jira.token')],
                    'json' => $issueData,
                    'headers' => [
                        'Content-Type' => 'application/json',
                    ],
                ]
            );

            $issue = json_decode($response->getBody()->getContents(), true);

            $this->query->jira_issue_id = $issue['key'];
            $this->query->save();
        } catch (\Exception $e) {
            Log::error("Error creating Jira issue: " . $e->getMessage());
            // Rethrow the exception to indicate job failure
            throw $e;
        }
    }

    public function getQuery()
    {
        return $this->query;
    }
}
