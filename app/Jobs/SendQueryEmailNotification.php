<?php

namespace App\Jobs;

use App\Models\Query;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendQueryEmailNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $query;

    /**
     * Create a new job instance.
     *
     * @param Query $query
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
        // Send the email
        Mail::send([], [], function ($message) {
            $recipientEmail = 'shoprite@caxsa1727338631.zendesk.com';
            $fromEmail = $this->query->email;

            $message->to($recipientEmail)
                ->cc(['shopritesupport@otbgroup.co.za', 'burgerhanno@gmail.com'])
                ->replyTo($fromEmail)
                ->subject("New Query Submitted: {$this->query->subject}")
                ->html("
                    <p>A new query has been submitted:</p>
                    
                    <p><strong>Jira Issue ID:</strong> {$this->query->jira_issue_id}</p>
                    <p><strong>Subject:</strong> {$this->query->subject}</p>
                    <p><strong>Body:</strong> {$this->query->body}</p>
                    <p><strong>Status:</strong> {$this->query->status}</p>
                    <p><strong>Category:</strong> " . optional($this->query->category)->name . "</p>
                    <p><strong>Severity:</strong> {$this->query->severity}</p>
                    <p><strong>Created at:</strong> {$this->query->created_at->format('Y/m/d H:i')}</p>
                    <p><strong>Updated at:</strong> {$this->query->updated_at->format('Y/m/d H:i')}</p>
                    
                    <p><strong>User Details:</strong></p>
                    <p><strong>ID:</strong> {$this->query->user_id}</p>
                    <p><strong>Name:</strong> {$this->query->firstname} {$this->query->lastname}</p>
                    <p><strong>Email:</strong> {$this->query->email}</p>
                    <p><strong>Phone:</strong> {$this->query->phone}</p>
                    
                    <p>Regards,<br>Orient E-Recruiter</p>
                ");
        });
    }
}
