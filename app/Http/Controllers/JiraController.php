<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Query;

class JiraController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Jira Handel
    |--------------------------------------------------------------------------
    */

    public function handle(Request $request)
    {
        // Extract necessary data from the request
        $issueKey = $request->input('issue.key');
        $eventType = $request->header('X-Atlassian-Webhook-Identifier'); // Identify the event type
        $newStatus = $request->input('issue.fields.status.name');
        $comment = $request->input('comment.body');
        $commentId = $request->input('comment.id');
        $webhookEvent = $request->input('webhookEvent');

        // Convert Markdown to HTML with custom rules
        $commentHtml = $this->convertMarkdownToHtml($comment);

        // Map Jira statuses to application statuses
        $statusMap = [
            'Backlog' => 'Pending',
            'Done' => 'Complete',
            'In Progress' => 'In Progress'
        ];

        if ($query) {
            if ($newStatus && isset($statusMap[$newStatus])) {
                // Update the status of the query based on the new status from Jira
                $query->status = $statusMap[$newStatus];
            }

            // Handle comment events
            switch ($webhookEvent) {
                case 'comment_created':
                case 'comment_updated':
                    $query->answer = $commentHtml;
                    break;

                case 'comment_deleted':
                    $query->answer = null;
                    break;
            }

            $query->save();
        }

        return response()->json(['message' => 'Webhook received and processed'], 200);
    }

    /*
    |--------------------------------------------------------------------------
    | Jira Convert Markdown
    |--------------------------------------------------------------------------
    */

    private function convertMarkdownToHtml($markdown)
    {
        // Handle headers (commenting out since we want to handle ordered lists differently)
        // $html = preg_replace('/^###### (.*)$/m', '<h6>$1</h6>', $markdown);
        // $html = preg_replace('/^##### (.*)$/m', '<h5>$1</h5>', $html);
        // $html = preg_replace('/^#### (.*)$/m', '<h4>$1</h4>', $html);
        // $html = preg_replace('/^### (.*)$/m', '<h3>$1</h3>', $html);
        // $html = preg_replace('/^## (.*)$/m', '<h2>$1</h2>', $html);
        // $html = preg_replace('/^# (.*)$/m', '<h1>$1</h1>', $html);

        // Handle blockquotes
        $html = preg_replace('/^> (.*)$/m', '<blockquote>$1</blockquote>', $markdown);

        // Handle horizontal rules
        $html = preg_replace('/^\*\*\*\*$/m', '<hr>', $markdown);
        $html = preg_replace('/^-{3,}$/m', '<hr>', $html);
        $html = preg_replace('/^_{3,}$/m', '<hr>', $html);

        // Handle links
        $html = preg_replace('/\[(.*?)\]\((.*?)\)/', '<a href="$2">$1</a>', $markdown);

        // Handle images
        $html = preg_replace('/!\[(.*?)\]\((.*?)\)/', '<img src="$2" alt="$1">', $html);

        // Handle bold text (with asterisks)
        $html = preg_replace('/\*\*(.*?)\*\*/', '<b>$1</b>', $markdown);

        // Handle italic text (with asterisks)
        $html = preg_replace('/_(.*?)_/', '<i>$1</i>', $html);

        // Handle inline code
        $html = preg_replace('/`(.*?)`/', '<code>$1</code>', $html);

        // Handle ordered lists
        $html = preg_replace('/^# (.*)$/m', '<li>$1</li>', $html);
        $html = preg_replace('/<\/li>\s*<li>/', '</li><li>', $html);
        $html = preg_replace('/^(<li>.*<\/li>)+$/m', '<ol>$0</ol>', $html);

        // Handle unordered lists
        $html = preg_replace('/^\* (.*)$/m', '<li>$1</li>', $html);
        $html = preg_replace('/<\/li>\s*<li>/', '</li><li>', $html);
        $html = preg_replace('/^(<li>.*<\/li>)+$/m', '<ul>$0</ul>', $html);

        // Handle line breaks
        $html = preg_replace('/\n/', '<br>', $html);

        // Clean up any remaining empty lists
        $html = preg_replace('/<ul>\s*<\/ul>/', '', $html);
        $html = preg_replace('/<ol>\s*<\/ol>/', '', $html);

        return $html;
    }
}
