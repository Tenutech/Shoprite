<?php

namespace App\Services;

use DateTime;
use Exception;
use Carbon\Carbon;
use App\Models\Chat;
use App\Models\State;
use App\Models\Document;
use App\Models\Language;
use App\Models\Applicant;
use App\Models\Notification;
use App\Models\ChatTemplate;
use App\Models\ScoreWeighting;
use App\Models\ChatTotalData;
use App\Models\ChatMonthlyData;
use App\Jobs\SendIdNumberToSap;
use App\Jobs\ProcessUserIdNumber;
use App\Jobs\LogChatMessageJob;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

use Twilio\Rest\Client;
use App\Services\GoogleMapsService;
use Illuminate\Support\Facades\File;
use GuzzleHttp\Client as GuzzleClient;

class ChatService
{
    /*
    |--------------------------------------------------------------------------
    | Incoming Messages
    |--------------------------------------------------------------------------
    */

    /**
    * Handle the incoming message from the applicant.
    *
    * @param  array  $data
    * @return void
    */
    public function handleIncomingMessage($data)
    {
        try {
            // Check if the expected structure exists
            if (!isset($data['entry'][0]['changes'][0]['value']['messages'][0])) {
                //Log::error('Invalid message data received', $data);
                return; // Early exit if essential data is missing
            }

            // Extract phone number ID from the webhook data
            $phoneNumberId = $data['entry'][0]['changes'][0]['value']['metadata']['phone_number_id'] ?? null;

            // Get the configured phone number ID from the environment
            $configuredPhoneNumberId = config('services.meta.phone');

            // Check if the phone number ID matches the configured phone number ID for this environment
            if ($phoneNumberId !== $configuredPhoneNumberId) {
                return;
            }

            // Extract message details from a deeply nested structure
            $messageData = $data['entry'][0]['changes'][0]['value']['messages'][0];
            $phone = '+' . $messageData['from'];
            $from = '+' . $messageData['from'];

            // Determine message type and extract content
            $body = null;
            if (isset($messageData['text']['body'])) {
                $body = $messageData['text']['body'];
            } elseif (isset($messageData['button']['text'])) {
                $body = $messageData['button']['text'];
            } elseif (isset($messageData['interactive']['list_reply']['id'])) {
                $body = $messageData['interactive']['list_reply']['id'];
            }

            $latitude = $messageData['location']['latitude'] ?? null;
            $longitude = $messageData['location']['longitude'] ?? null;
            $mediaUrl = $messageData['media'][0]['url'] ?? null;
            $mediaId = $messageData['image']['id'] ?? null;

            // Fetch existing applicant or create a new one
            $applicant = $this->getOrCreateApplicant($phone);

            // Check if appliacnt already sent a picture
            if ($mediaId && $applicant->avatar && $applicant->state_id != 11) {
                return; // Exit the function early since more than one image was provided
            }

            // Extract message ID from the webhook data
            $messageId = $messageData['id'] ?? null;

            // Log the received message
            $this->logMessage($applicant->id, $body, 1, $messageId, 'Received');

            // Process the applicant's current state and check if a checkpoint was triggered
            $checkpointTriggered = $this->processApplicantState($applicant, $body);

            // Process the state-specific actions
            $this->processStateActions($applicant, $body, $latitude, $longitude, $mediaId);

            // If a checkpoint was triggered, reset the checkpoint status to 'No'
            if ($checkpointTriggered) {
                $applicant->update(['checkpoint' => 'No']);
            }
        } catch (Exception $e) {
            Log::error("Error in handleIncomingMessage: {$e->getMessage()}");
            throw new Exception('There was an error processing the incoming message. Please try again later.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Get or Create Applicant
    |--------------------------------------------------------------------------
    */

    /**
    * Fetch an existing applicant based on the phone number or create a new one.
    *
    * @param  string  $phone
    * @return \App\Models\Applicant
    */
    protected function getOrCreateApplicant($phone)
    {
        try {
            // Query for the applicant based on the phone number and preload certain related data
            $applicant = Applicant::with([
                'user',
                'gender',
                'race',
                'role',
                'interviews.vacancy.position'
            ])
            ->where('phone', $phone)
            ->first();

            // If the applicant doesn't exist, create a new entry
            if (!$applicant) {
                $applicant = new Applicant([
                    'phone' => $phone,
                    'role_id' => 8,
                    'applicant_type_id' => 2,
                    'application_type' => 'WhatsApp',
                    'state_id' => 1,
                ]);
                $applicant->save();
            }

            return $applicant;
        } catch (Exception $e) {
            Log::error("Error in getOrCreateApplicant: {$e->getMessage()}");
            throw new Exception('There was an error retrieving or creating the applicant. Please try again later.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Log Messages
    |--------------------------------------------------------------------------
    */

    /**
    * Log a message associated with an applicant.
    *
    * @param  int    $applicantID
    * @param  string $message
    * @param  int    $type
    * @return void
    */
    protected function logMessage($applicantID, $message = null, $type = null, $messageId = null, $status = null, $template = null)
    {
        LogChatMessageJob::dispatch($applicantID, $message, $type, $messageId, $status, $template)->onQueue('chats');
    }

    /*
    |--------------------------------------------------------------------------
    | Applicant State
    |--------------------------------------------------------------------------
    */

    /**
    * Process and potentially update the state of the applicant based on the message content and timing.
    *
    * @param  \App\Models\Applicant $applicant
    * @param  string                $body
    * @return void
    */
    protected function processApplicantState($applicant, $body)
    {
        try {
            $client = new GuzzleClient();
            $to = $applicant->phone;
            $from = config('services.meta.phone');
            $token = config('services.meta.token');

            // Calculate the time since the last update of the applicant
            $timeDifference = now()->diffInMinutes($applicant->updated_at);

            $checkpointTriggered = false; // flag to track checkpoint status

            // Get the 'complete' state ID
            $completeStateID = State::where('code', 'complete')->value('id');

            // If the elapsed time exceeds the delay or if the applicant's state has a '_checkpoint' suffix,
            // update the state of the applicant
            if ($applicant->state_id > 2 && $applicant->state_id < $completeStateID && ($timeDifference > 15 || $applicant->checkpoint == 'Yes')) {
                // Set applicant checkpoint to 'Yes'
                $applicant->update(['checkpoint' => 'Yes']);
                $checkpointTriggered = true;

                // Get the checkpoint message
                $checkpointMessage = $this->fetchStateMessages('checkpoint');
                $this->sendAndLogMessages($applicant, $checkpointMessage, $client, $to, $from, $token);

                // Get the message of the state_id
                $stateID = $applicant->state_id;
                $state = State::where('id', $stateID)->value('code');

                if ($state == 'literacy' || $state == 'numeracy'  || $state == 'situational') {
                    // Determine the correct question pool based on the state
                    if ($state == 'literacy') {
                        $questionPool = 'literacy_question_pool';
                    } elseif ($state == 'numeracy') {
                        $questionPool = 'numeracy_question_pool';
                    } elseif ($state == 'situational') {
                        $questionPool = 'situational_question_pool';
                    }
                    $sortOrderPool = explode(',', $applicant->{$questionPool});
                    $currentQuestionSortOrder = $sortOrderPool[0];

                    $currentQuestion = ChatTemplate::where('state_id', $stateID)
                                                   ->where('sort', $currentQuestionSortOrder)
                                                   ->first();

                    if ($currentQuestion) {
                        $currentQuestionText = $currentQuestion->message;
                        $this->sendAndLogMessages($applicant, [$currentQuestionText], $client, $to, $from, $token);
                    }
                } else {
                    $messages = $this->fetchStateMessages($state);
                    $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
                }
            }
            return $checkpointTriggered;
        } catch (Exception $e) {
            Log::error("Error in processApplicantState: {$e->getMessage()}");
            throw new Exception('There was an error processing the applicant state. Please try again later.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Process State Actions
    |--------------------------------------------------------------------------
    */

    /**
    * Process actions based on the current state of the applicant.
    *
    * @param  \App\Models\Applicant $applicant
    * @param  string $body
    * @return void
    */
    public function processStateActions($applicant, $body, $latitude, $longitude, $mediaId)
    {
        $client = new GuzzleClient();
        $to = $applicant->phone;
        $from = config('services.meta.phone');
        $token = config('services.meta.token');

        // If checkpoint is set to 'Yes', do not process specific state actions
        if ($applicant->checkpoint == 'Yes') {
            return;  // Early return
        }

        switch ($applicant->state->code) {
            case 'welcome':
                $this->handleWelcomeState($applicant, $client, $to, $from, $token);
                break;

            case 'introduction':
                $this->handleIntroductionState($applicant, $body, $client, $to, $from, $token);
                break;

            case 'employment_journey':
                $this->handleEmploymentJourneyState($applicant, $body, $client, $to, $from, $token);
                break;

            case 'id_number':
                $this->handleIdNumberState($applicant, $body, $client, $to, $from, $token);
                break;

            case 'first_name':
                $this->handleFirstNameState($applicant, $body, $client, $to, $from, $token);
                break;

            case 'last_name':
                $this->handleLastNameState($applicant, $body, $client, $to, $from, $token);
                break;

            case 'race':
                $this->handleRaceState($applicant, $body, $client, $to, $from, $token);
                break;

            case 'avatar_upload':
                $this->handleAvatarUploadState($applicant, $body, $client, $to, $from, $token);
                break;

            case 'avatar':
                $this->handleAvatarState($applicant, $body, $client, $to, $from, $token, $mediaId);
                break;

            case 'terms_conditions':
                $this->handleTermsConditionsState($applicant, $body, $client, $to, $from, $token);
                break;

            case 'additional_contact_number':
                $this->handleAdditionalContactNumberState($applicant, $body, $client, $to, $from, $token);
                break;

            case 'contact_number':
                $this->handleContactNumberState($applicant, $body, $client, $to, $from, $token);
                break;

            case 'public_holidays':
                $this->handlePublicHolidaysState($applicant, $body, $client, $to, $from, $token);
                break;

            case 'highest_qualification':
                $this->handleHighestQualificationState($applicant, $body, $client, $to, $from, $token);
                break;

            case 'consent':
                $this->handleConsentState($applicant, $body, $client, $to, $from, $token);
                break;

            case 'environment':
                $this->handleEnvironmentState($applicant, $body, $client, $to, $from, $token);
                break;

            case 'experience':
                $this->handleExperienceState($applicant, $body, $client, $to, $from, $token);
                break;

            case 'brand':
                $this->handleBrandState($applicant, $body, $client, $to, $from, $token);
                break;

            case 'location_type':
                $this->handleLocationTypeState($applicant, $body, $client, $to, $from, $token);
                break;

            case 'location':
                $this->handleLocationState($applicant, $body, $latitude, $longitude, $client, $to, $from, $token);
                break;

            case 'location_confirmation':
                $this->handleLocationConfirmationState($applicant, $body, $client, $to, $from, $token);
                break;

            case 'has_email':
                $this->handleHasEmailState($applicant, $body, $client, $to, $from, $token);
                break;

            case 'email':
                $this->handleEmailState($applicant, $body, $client, $to, $from, $token);
                break;

            case 'disability':
                $this->handleDisabilityState($applicant, $body, $client, $to, $from, $token);
                break;

            case 'literacy_start':
                $this->handleLiteracyStartState($applicant, $body, $client, $to, $from, $token);
                break;

            case 'literacy':
                $this->handleLiteracyState($applicant, $body, $client, $to, $from, $token);
                break;

            case 'numeracy_start':
                $this->handleNumeracyStartState($applicant, $body, $client, $to, $from, $token);
                break;

            case 'numeracy':
                $this->handleNumeracyState($applicant, $body, $client, $to, $from, $token);
                break;

            case 'situational_start':
                $this->handleSituationalStartState($applicant, $body, $client, $to, $from, $token);
                break;

            case 'situational':
                $this->handleSituationalState($applicant, $body, $client, $to, $from, $token);
                break;

            case 'complete':
                $this->handleCompleteState($applicant, $body, $client, $to, $from, $token);
                break;

            case 'schedule_start':
                    $this->handleScheduleStartState($applicant, $body, $client, $to, $from, $token);
                break;

            case 'schedule':
                $this->handleScheduleState($applicant, $body, $client, $to, $from, $token);
                break;

            case 'reschedule':
                $this->handleRescheduleState($applicant, $body, $client, $to, $from, $token);
                break;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Welcome
    |--------------------------------------------------------------------------
    */

    protected function handleWelcomeState($applicant, $client, $to, $from, $token)
    {
        try {
            /*
            // **Check Eligibility**: public_holidays, environment, education_id, and created_at within 6 months
            $sixMonthsAgo = now()->subMonths(6);

            if (($applicant->public_holidays === 'No' || $applicant->environment === 'No' || $applicant->education_id === 1) && $applicant->updated_at > $sixMonthsAgo) {
                // Calculate the date 6 months from applicant's creation date
                $eligibleDate = $applicant->updated_at->addMonths(6)->format('d F Y');

                // Send message indicating ineligibility and when they can try again
                $message = "Thank you for your interest in a position with the Shoprite Group of Companies.\n\nYou are not eligible for this position. You can try again on *$eligibleDate*.\n\nHave a wonderful day!";
                $this->sendAndLogMessages($applicant, [$message], $client, $to, $from, $token);

                // Exit the function early as the applicant is ineligible
                return;
            }
            */

            // Get the current hour using the server's current time
            $currentHour = now()->hour;

            // Default the time of day to 'afternoon' as a safe fallback
            $timeOfDay = 'afternoon';

            // Determine the appropriate time of day greeting based on the current hour
            if ($currentHour < 12) {
                // If the hour is before 12 PM, it's morning
                $timeOfDay = 'morning';
            } elseif ($currentHour >= 18) {
                // If the hour is 6 PM or later, it's evening
                $timeOfDay = 'evening';
            }

            // Fetch the welcome messages for the chatbot's current state
            $messages = $this->fetchStateMessages('welcome');

            // Replace the {timeofday} placeholder in each message with the actual greeting
            foreach ($messages as &$messageSet) {
                if (is_array($messageSet)) {
                    // If the messageSet is an array, loop through its elements
                    foreach ($messageSet as &$message) {
                        if (is_string($message)) {
                            $message = str_replace('{timeofday}', $timeOfDay, $message);
                        }
                    }
                } elseif (is_string($messageSet)) {
                    // If messageSet is a string (not an array), replace {timeofday}
                    $messageSet = str_replace('{timeofday}', $timeOfDay, $messageSet);
                }
            }

            // Send the personalized welcome messages to the applicant and log them
            $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);

            // Get the next state ID based on the current state's sort order
            $stateID = State::where('sort', ($applicant->state->sort + 1))->value('id');

            // Update the applicant's state to the next state in the flow
            $applicant->update(['state_id' => $stateID]);
        } catch (Exception $e) {
            // Log any exception that occurs during this process for debugging
            Log::error('Error in handleWelcomeState: ' . $e->getMessage());

            // Get the fallback error message that will be sent to the applicant
            $errorMessage = $this->getErrorMessage();

            // Send the error message to the applicant and log it
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Introduction
    |--------------------------------------------------------------------------
    */

    protected function handleIntroductionState($applicant, $body, $client, $to, $from, $token)
    {
        try {
            // Normalize the applicant's input (remove case sensitivity by converting to lowercase)
            $body = strtolower(trim($body));

            // Check if the applicant's input is one of the valid options (1, 2, or 3)
            if ($body === '1') {
                // Update to the 'employment_journey' state
                $stateID = State::where('code', 'employment_journey')->value('id');
                $applicant->update(['state_id' => $stateID]);

                // Send messages for the selected state
                $messages = $this->fetchStateMessages('employment_journey');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            } elseif ($body === '2') {
                // Applicant selected option 2: Navigate to 'career_page' state
                $stateID = State::where('code', 'career_page')->value('id');
                $applicant->update(['state_id' => $stateID]);

                // Send messages for the selected state
                $messages = $this->fetchStateMessages('career_page');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);

                // Set state back to welcome
                $stateID = State::where('code', 'welcome')->value('id');
                $applicant->update(['state_id' => $stateID]);
            } else {
                // If the applicant's input is not a valid option, send an error message
                $errorMessage = "Invalid option. Please reply with:\n\n1. Our stores\n2. Our offices";
                $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
            }
        } catch (Exception $e) {
            // Log any exceptions that occur for debugging purposes
            Log::error('Error in handleIntroductionState: ' . $e->getMessage());

            // Fetch a generic error message
            $errorMessage = $this->getErrorMessage();

            // Send the error message to the applicant to notify them of the issue
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Employment Journey
    |--------------------------------------------------------------------------
    */

    protected function handleEmploymentJourneyState($applicant, $body, $client, $to, $from, $token)
    {
        try {
            // Normalize the applicant's input (remove case sensitivity by converting to lowercase)
            $body = strtolower(trim($body));

            // Check if the applicant's input is one of the valid options (1)
            if ($body === '1' || $body === 'yes' || $body === 'agree' || $body === 'i agree') {
                // Update the applicant's consent
                $applicant->update([
                    'terms_conditions' => 'Yes',
                    'consent' => 'Yes',
                ]);

                // Applicant selected option 1: Navigate to 'id_number' state
                $stateID = State::where('code', 'id_number')->value('id');
                $applicant->update(['state_id' => $stateID]);

                // Send messages for the selected state
                $messages = $this->fetchStateMessages('id_number');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            } elseif ($body === '2' || $body === 'no' || $body === 'disagree' || $body === 'i disagree') {
                // Update the applicant's consent
                $applicant->update([
                    'terms_conditions' => 'No',
                    'consent' => 'No',
                ]);

                // Send message that they are not eligible
                $message = "Thank you for your interest in a position with the Shoprite Group of Companies.\n\nYou cannot continue this application unless you *agree* to the terms and conditions.";
                $this->sendAndLogMessages($applicant, [$message], $client, $to, $from, $token);

                // Resend employment journey message
                $messages = $this->fetchStateMessages('employment_journey');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            } else {
                // If the applicant's input is not a valid option, send an error message
                $errorMessage = "Invalid option. Please reply with:\n\n1. I agree\n2. I disagree";
                $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
            }
        } catch (Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error in handleEmploymentJourneyState: ' . $e->getMessage());

            // Get the error message from the method
            $errorMessage = $this->getErrorMessage();

            // Send the error message to the applicant
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | ID Number
    |--------------------------------------------------------------------------
    */

    protected function handleIdNumberState($applicant, $body, $client, $to, $from, $token)
    {
        try {
            // Check if the input is a 13-digit ID number
            if (preg_match('/^\d{13}$/', $body)) {
                // Validate the ID number
                if ($this->isValidSAIdNumber($body)) {
                    // Extract details from the ID number
                    $year = substr($body, 0, 2);
                    $month = substr($body, 2, 2);
                    $day = substr($body, 4, 2);
                    // Correct for century ambiguity
                    $century = $year >= date('y') ? '19' : '20';
                    $birthdate = $century . $year . '-' . $month . '-' . $day;

                    // Calculate age
                    $age = \Carbon\Carbon::parse($birthdate)->age;

                    // If the applicant is under 18
                    if ($age < 18) {
                        // Send message that they are not eligible
                        $message = "Thank you for your interest in a position at the Shoprite Group of Companies. You are under the age of 18 and therefore not eligible for a position. Have a wonderful day!";
                        $this->sendAndLogMessages($applicant, [$message], $client, $to, $from, $token);

                        // Set under_18 to 'Yes' and reset the state to 'welcome'
                        $applicant->update([
                            'under_18' => 'Yes',
                            'state_id' => State::where('code', 'welcome')->value('id')
                        ]);

                        return; // End the process here for under 18 applicants
                    }

                    // Check if the ID number already exists in the applicants table, excluding the current applicant
                    $existingApplicant = Applicant::where('id_number', $body)
                        ->where('id', '!=', $applicant->id)
                        ->first();

                    if ($existingApplicant) {
                        // Send message that this ID number has already been registered
                        $message = "Sorry, this ID number has already been registered. Please try again with a different ID number.";
                        $this->sendAndLogMessages($applicant, [$message], $client, $to, $from, $token);

                        return; // End the process here if the ID is already registered
                    }

                    // Determine gender (SSSS)
                    $genderCode = substr($body, 6, 4);
                    $genderId = $genderCode < 5000 ? 2 : 1; // Female: 2, Male: 1

                    // Citizenship status (C)
                    $resident = substr($body, 10, 1);

                    // Update the applicant's details
                    $applicant->update([
                        'id_number' => $body,
                        'birth_date' => $applicant->birth_date ?? $birthdate,
                        'age' => $applicant->age ?? $age,
                        'gender_id' => $applicant->gender_id ?? $genderId,
                        'resident' => $applicant->resident ?? $resident,
                        'id_verified' => 'Yes',
                        'under_18' => 'No'
                    ]);

                    // Move to the next state (first_name)
                    $stateID = State::where('code', 'first_name')->value('id');
                    $applicant->update(['state_id' => $stateID]);

                    // Fetch messages for the 'first_name' state
                    $messages = $this->fetchStateMessages('first_name');
                    $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);

                    // Dispatch the new job to process the ID number, pass the applicant object as well
                    SendIdNumberToSap::dispatch($body, $applicant);
                } else {
                    // Send a message if the ID number is not valid
                    $message = "Please provide a valid *South African ID* number.";
                    $this->sendAndLogMessages($applicant, [$message], $client, $to, $from, $token);
                }
            } else {
                // Send a message prompting for a valid ID number if the input is not 13 digits
                $message = "Please provide a valid *South African ID* number.";
                $this->sendAndLogMessages($applicant, [$message], $client, $to, $from, $token);
            }
        } catch (Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error in handleIdNumberState: ' . $e);

            // Get the error message from the method
            $errorMessage = $this->getErrorMessage();

            // Send the error message to the applicant
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | ID Validation
    |--------------------------------------------------------------------------
    */

    // Validation function for South African ID number
    public static function isValidSAIdNumber($id): bool
    {
        $id = preg_replace('/\D/', '', $id); // Ensure the ID is only digits
        if (strlen($id) != 13) {
            return false; // Early return if ID length is incorrect
        }

        $sum = 0;
        $length = strlen($id);
        for ($i = 0; $i < $length - 1; $i++) { // Exclude the last digit for the main loop
            $number = (int)$id[$i];
            if (($length - $i) % 2 === 0) {
                $number = $number * 2;
                if ($number > 9) {
                    $number = $number - 9;
                }
            }
            $sum += $number;
        }

        // Calculate checksum based on the sum
        $checksum = (10 - ($sum % 10)) % 10;

        // Last digit of the ID should match the calculated checksum
        return (int)$id[$length - 1] === $checksum;
    }

    /*
    |--------------------------------------------------------------------------
    | First Name
    |--------------------------------------------------------------------------
    */

    protected function handleFirstNameState($applicant, $body, $client, $to, $from, $token)
    {
        try {
            // Check if the body does not contain any digit and has more than one character
            if (!preg_match('/\d/', $body) && strlen($body) > 1) {
                // Update the applicant's first name
                $applicant->update(['firstname' => ucwords(strtolower($body))]);

                $stateID = State::where('code', 'last_name')->value('id');
                $applicant->update(['state_id' => $stateID]);

                $messages = $this->fetchStateMessages('last_name');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            } else {
                // Send a message prompting for a valid first name
                $message = "Please enter a valid first name:";
                $this->sendAndLogMessages($applicant, [$message], $client, $to, $from, $token);
            }
        } catch (Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error in handleFirstNameState: ' . $e->getMessage());

            // Get the error message from the method
            $errorMessage = $this->getErrorMessage();

            // Send the error message to the applicant
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Last Name
    |--------------------------------------------------------------------------
    */

    protected function handleLastNameState($applicant, $body, $client, $to, $from, $token)
    {
        try {
            // Check if the body does not contain any digit and has more than one character
            if (!preg_match('/\d/', $body) && strlen($body) > 1) {
                // Update the applicant's last name
                $applicant->update(['lastname' => ucwords(strtolower($body))]);

                $stateID = State::where('code', 'race')->value('id');
                $applicant->update(['state_id' => $stateID]);

                $messages = $this->fetchStateMessages('race');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            } else {
                // Send a message prompting for a valid last name
                $message = "Please enter a valid last name:";
                $this->sendAndLogMessages($applicant, [$message], $client, $to, $from, $token);
            }
        } catch (Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error in handleLastNameState: ' . $e->getMessage());

            // Get the error message from the method
            $errorMessage = $this->getErrorMessage();

            // Send the error message to the applicant
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Race
    |--------------------------------------------------------------------------
    */

    protected function handleRaceState($applicant, $body, $client, $to, $from, $token)
    {
        try {
            // Normalize the applicant's input (remove case sensitivity by converting to lowercase)
            $body = strtolower(trim($body));

            // Initialize the race variable to null, which will be updated based on the applicant's input
            $raceId = null;

            // Use a switch case to handle the applicant's input and map it to the corresponding race
            switch ($body) {
                case '1':
                case 'african':
                    $raceId = 1;
                    break;
                case '2':
                case 'coloured':
                    $raceId = 2;
                    break;
                case '3':
                case 'indian':
                    $raceId = 3;
                    break;
                case '4':
                case 'white':
                    $raceId = 4;
                    break;
            }

            // If race has been set based on the applicant's input
            if ($raceId) {
                // Update the applicant's race_id in the database
                $applicant->update(['race_id' => $raceId]);

                // Transition to the next state 'avatar_upload'
                $stateID = State::where('code', 'avatar_upload')->value('id');
                $applicant->update(['state_id' => $stateID]);

                // Fetch and send messages for the 'avatar_upload' state
                $messages = $this->fetchStateMessages('avatar_upload');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            } else {
                // If the applicant's input is not a valid option, send an error message
                $errorMessage = "Invalid option. Please reply with:\n\n1. African\n2. Coloured\n3. Indian\n4. White";
                $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
            }
        } catch (Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error in handleRaceState: ' . $e->getMessage());

            // Get the error message from the method
            $errorMessage = $this->getErrorMessage();

            // Send the error message to the applicant
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Avatar Upload
    |--------------------------------------------------------------------------
    */

    protected function handleAvatarUploadState($applicant, $body, $client, $to, $from, $token)
    {
        try {
            // Normalize the applicant's input (remove case sensitivity by converting to lowercase)
            $body = strtolower(trim($body));

            // Check if the applicant's input is one of the valid options (1, 2, or 3)
            if ($body === '1') {
                // Update the applicant's avatar_upload
                $applicant->update([
                    'avatar_upload' => 'Yes',
                    'avatar' => '/images/avatar.jpg'
                ]);

                // Applicant selected option 1 or 2: Navigate to 'avatar' state
                $stateID = State::where('code', 'avatar')->value('id');
                $applicant->update(['state_id' => $stateID]);

                // Send messages for the selected state
                $messages = $this->fetchStateMessages('avatar');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            } elseif ($body === '2') {
                // Update the applicant's avatar_upload
                $applicant->update([
                    'avatar_upload' => 'No',
                    'avatar' => '/images/avatar.jpg'
                ]);

                // Applicant selected option 2: Navigate to 'additional_contact_number' state
                $stateID = State::where('code', 'additional_contact_number')->value('id');
                $applicant->update(['state_id' => $stateID]);

                // Send messages for the selected state
                $messages = $this->fetchStateMessages('additional_contact_number');

                // Get the applicant's phone number
                $phoneNumber = $applicant->phone;

                // Replace the {current number} placeholder in each message with the applicant phone
                foreach ($messages as &$messageSet) {
                    if (is_array($messageSet)) {
                        // If the messageSet is an array, loop through its elements
                        foreach ($messageSet as &$message) {
                            if (is_string($message)) {
                                $message = str_replace('{current number}', $phoneNumber, $message);
                            }
                        }
                    } elseif (is_string($messageSet)) {
                        // If messageSet is a string (not an array), replace {current number}
                        $messageSet = str_replace('{current number}', $phoneNumber, $messageSet);
                    }
                }

                //Send the state messages
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            } else {
                // If the applicant's input is not a valid option, send an error message
                $errorMessage = "Invalid option. Please reply with:\n\n1. I will take or upload a picture\n2. I don't have a camera on my phone - can't upload picture";
                $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
            }
        } catch (Exception $e) {
            // Log any exceptions that occur for debugging purposes
            Log::error('Error in handleAvatarUploadState: ' . $e->getMessage());

            // Fetch a generic error message
            $errorMessage = $this->getErrorMessage();

            // Send the error message to the applicant to notify them of the issue
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Avatar
    |--------------------------------------------------------------------------
    */

    protected function handleAvatarState($applicant, $body, $client, $to, $from, $token, $mediaId)
    {
        try {
            // Check if multiple images were sent by examining if $mediaId is an array with more than 1 element
            if (is_array($mediaId) && count($mediaId) > 1) {
                // Send a message stating that only one image is allowed
                $errorMessage = "Please send only one picture.";
                $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
                return; // Exit the function early since more than one image was provided
            }

            // Ensure that mediaId is a single value (if $mediaId is an array, we take the first element)
            $mediaId = is_array($mediaId) ? $mediaId[0] : $mediaId;

            // Check if a valid mediaId is provided
            if (isset($mediaId)) {
                // Define the URL to fetch the media details from Meta's API
                $url = "https://graph.facebook.com/v23.0/$mediaId";
                $response = $client->get($url, [
                    'headers' => ['Authorization' => "Bearer $token"] // Add the authorization token to the request
                ]);

                // Check if the response from Meta's API is successful (status code 200)
                if ($response->getStatusCode() == 200) {
                    // Parse the response to retrieve the media URL
                    $data = json_decode($response->getBody(), true);
                    $mediaUrl = $data['url'] ?? null; // Fetch the media URL from the response data

                    // Check if the media URL was successfully retrieved
                    if ($mediaUrl) {
                        // Fetch the actual image using the media URL
                        $imageResponse = $client->get($mediaUrl, [
                            'headers' => ['Authorization' => "Bearer $token"]
                        ]);

                        // Get the content type (format) of the image
                        $contentType = $imageResponse->getHeaderLine('Content-Type');
                        // Get the content length (size) of the image
                        $contentLength = $imageResponse->getHeaderLine('Content-Length'); // In bytes

                        // Define the allowed content types (file formats)
                        $allowedContentTypes = ['image/jpeg', 'image/png', 'image/jpg'];

                        // Check if the image format is allowed (jpeg, jpg, or png)
                        if (!in_array($contentType, $allowedContentTypes)) {
                            // Send an error message if the format is invalid
                            $errorMessage = "Invalid picture type! Please provide a picture in .jpg, .jpeg, or .png format.";
                            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
                            return; // Exit the function early since the file type is invalid
                        }

                        // Check if the image size exceeds 5MB
                        if ($contentLength > 5 * 1024 * 1024) { // 5MB in bytes
                            // Send an error message if the file exceeds the size limit
                            $errorMessage = "The picture size exceeds 5MB! Please provide a picture smaller than 5MB.";
                            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
                            return; // Exit the function early since the file size is too large
                        }

                        // If the image is valid, retrieve its content
                        $fileContent = $imageResponse->getBody()->getContents();

                        // Determine the file extension based on the content type
                        $fileExtension = array_search($contentType, [
                            'jpg' => 'image/jpeg',
                            'jpg' => 'image/jpg',
                            'png' => 'image/png'
                        ]) ?: 'jpg'; // Default to 'jpg' if no match is found

                        // Create a unique file name using the applicant's first and last name, and the current timestamp
                        $fileName = $applicant->firstname . ' ' . $applicant->lastname . '-' . time() . '.' . $fileExtension;
                        $filePath = storage_path('app/public/images/' . $fileName); // Define the file path where the image will be saved

                        // Save the image file to the specified path
                        if (file_put_contents($filePath, $fileContent)) {
                            // Create a new document record instead of updating the applicant's avatar
                            Document::create([
                                'applicant_id' => $applicant->id,
                                'name' => $fileName,
                                'type' => $fileExtension,
                                'size' => $contentLength,
                                'url' => '/storage/images/' . $fileName,
                            ]);

                            // Transition the applicant to the 'additional_contact_number' state
                            $stateID = State::where('code', 'additional_contact_number')->value('id');
                            $applicant->update(['state_id' => $stateID]);

                            // Fetch and send messages for the 'additional_contact_number' state
                            $messages = $this->fetchStateMessages('additional_contact_number');

                            // Get the applicant's phone number
                            $phoneNumber = $applicant->phone;

                            // Replace the {current number} placeholder in each message with the applicant phone
                            foreach ($messages as &$messageSet) {
                                if (is_array($messageSet)) {
                                    // If the messageSet is an array, loop through its elements
                                    foreach ($messageSet as &$message) {
                                        if (is_string($message)) {
                                            $message = str_replace('{current number}', $phoneNumber, $message);
                                        }
                                    }
                                } elseif (is_string($messageSet)) {
                                    // If messageSet is a string (not an array), replace {current number}
                                    $messageSet = str_replace('{current number}', $phoneNumber, $messageSet);
                                }
                            }

                            //Send the state messages
                            $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
                        } else {
                            // If the image could not be saved, send an error message to the applicant
                            $errorMessage = "There was an issue uploading your picture. Please try again.";
                            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
                        }
                    } else {
                        // If the media URL could not be retrieved, send an error message
                        $errorMessage = "Could not retrieve image URL from Meta.";
                        $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
                    }
                } else {
                    // If the request to Meta failed, send an error message with the status code
                    $errorMessage = "Failed to fetch media from Meta. Status code: " . $response->getStatusCode();
                    $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
                }
            } else {
                // If no mediaId is provided, prompt the applicant to upload a valid image
                $errorMessage = "Please provide a picture of of your South African ID in .jpg, .jpeg, or .png format.";
                $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
            }
        } catch (Exception $e) {
            // Log any exception that occurs for debugging purposes
            Log::error('Error in handleAvatarState: ' . $e->getMessage());

            // Send a generic error message to the applicant
            $errorMessage = $this->getErrorMessage();
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Terms & Conditions
    |--------------------------------------------------------------------------
    */

    protected function handleTermsConditionsState($applicant, $body, $client, $to, $from, $token)
    {
        try {
            // Normalize the applicant's input (remove case sensitivity by converting to lowercase)
            $body = strtolower(trim($body));

            // Check if the applicant's input is one of the valid options (1)
            if ($body === '1' || $body === 'yes' || $body === 'i agree' || $body === 'agree') {
                // Update the applicant's terms_conditions
                $applicant->update(['terms_conditions' => 'Yes']);

                // Applicant selected option 1: Navigate to 'additional_contact_number' state
                $stateID = State::where('code', 'additional_contact_number')->value('id');
                $applicant->update(['state_id' => $stateID]);

                // Send messages for the selected state
                $messages = $this->fetchStateMessages('additional_contact_number');

                // Get the applicant's phone number
                $phoneNumber = $applicant->phone;

                // Replace the {current number} placeholder in each message with the applicant phone
                foreach ($messages as &$messageSet) {
                    if (is_array($messageSet)) {
                        // If the messageSet is an array, loop through its elements
                        foreach ($messageSet as &$message) {
                            if (is_string($message)) {
                                $message = str_replace('{current number}', $phoneNumber, $message);
                            }
                        }
                    } elseif (is_string($messageSet)) {
                        // If messageSet is a string (not an array), replace {current number}
                        $messageSet = str_replace('{current number}', $phoneNumber, $messageSet);
                    }
                }

                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            } elseif ($body === '2' || $body === 'no' || $body === 'i disagree' || $body === 'disagree') {
                // Update the applicant's terms_conditions
                $applicant->update(['terms_conditions' => 'No']);

                // Applicant selected option 2: Navigate to 'welcome' state
                $stateID = State::where('code', 'welcome')->value('id');
                $applicant->update(['state_id' => $stateID]);

                // Send message that they are not eligible
                $message = "Thank you for your interest in a position at the Shoprite Group of Companies. You are not eligible for this position. Have a wonderful day!";
                $this->sendAndLogMessages($applicant, [$message], $client, $to, $from, $token);
            } else {
                // If the applicant's input is not a valid option, send an error message
                $errorMessage = "Invalid option. Please reply with:\n\n1. I agree\n2. I Disagree";
                $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
            }
        } catch (Exception $e) {
            // Log any exceptions that occur for debugging purposes
            Log::error('Error in handleTermsConditionsState: ' . $e->getMessage());

            // Fetch a generic error message
            $errorMessage = $this->getErrorMessage();

            // Send the error message to the applicant to notify them of the issue
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Additional Contact Number
    |--------------------------------------------------------------------------
    */

    protected function handleAdditionalContactNumberState($applicant, $body, $client, $to, $from, $token)
    {
        try {
            // Normalize the applicant's input (remove case sensitivity by converting to lowercase)
            $body = strtolower(trim($body));

            // Check if the applicant's input is one of the valid options (1)
            if ($body === '1' || $body === 'yes') {
                // Update the applicant's additional_contact_number
                $applicant->update([
                    'additional_contact_number' => 'No',
                    'contact_number' => $applicant->phone
                ]);

                // Applicant selected option 1: Navigate to 'public_holidays' state
                $stateID = State::where('code', 'public_holidays')->value('id');
                $applicant->update(['state_id' => $stateID]);

                // Send messages for the selected state
                $messages = $this->fetchStateMessages('public_holidays');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            } elseif ($body === '2' || $body === 'no') {
                // Update the applicant's additional_contact_number
                $applicant->update(['additional_contact_number' => 'Yes']);

                // Applicant selected option 2: Navigate to 'contact_number' state
                $stateID = State::where('code', 'contact_number')->value('id');
                $applicant->update(['state_id' => $stateID]);

                // Send messages for the selected state
                $messages = $this->fetchStateMessages('contact_number');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            } else {
                // If the applicant's input is not a valid option, send an error message
                $errorMessage = "Invalid option. Please reply with:\n\n1. Yes\n2. No - I want to use a different number";
                $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
            }
        } catch (Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error in handleAdditionalContactNumberState: ' . $e->getMessage());

            // Get the error message from the method
            $errorMessage = $this->getErrorMessage();

            // Send the error message to the applicant
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Contact Number
    |--------------------------------------------------------------------------
    */

    protected function handleContactNumberState($applicant, $body, $client, $to, $from, $token)
    {
        try {
            // Remove any non-digit characters from the input
            $number = preg_replace('/\D/', '', $body);

            // Check if the number starts with '0' and replace it with '+27'
            if (substr($number, 0, 1) === '0') {
                // Replace the leading '0' with the South African country code '+27'
                $number = preg_replace('/^0/', '+27', $number);
            } elseif (substr($number, 0, 2) === '27') {
                // If the number starts with '27' (without the +), add the '+'
                $number = '+' . $number;
            } elseif (substr($number, 0, 3) === '+27') {
                // If the number already starts with '+27', leave it as is
            } else {
                // If the number doesn't fit any of these formats, it may be invalid
                $message = "Please enter a valid South African contact number (e.g., +2782893278, 2782893278, or 082893278).";
                $this->sendAndLogMessages($applicant, [$message], $client, $to, $from, $token);
                return; // Exit early
            }

            // Now ensure the number is valid after formatting: it should be exactly 12 digits long
            if (preg_match('/^\+27\d{9}$/', $number)) {
                // The number is valid, proceed to update the applicant's contact number
                $applicant->update(['contact_number' => $number]);

                // Move to the next state ('public_holidays')
                $stateID = State::where('code', 'public_holidays')->value('id');
                $applicant->update(['state_id' => $stateID]);

                // Fetch and send the appropriate messages for the 'public_holidays' state
                $messages = $this->fetchStateMessages('public_holidays');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            } else {
                // If the number doesn't match the expected format, ask the applicant to re-enter the number
                $message = "Please enter a valid South African contact number (e.g., 0828932788).";
                $this->sendAndLogMessages($applicant, [$message], $client, $to, $from, $token);
            }
        } catch (Exception $e) {
            // Log any exceptions that occur for debugging purposes
            Log::error('Error in handleContactNumberState: ' . $e->getMessage());

            // Get the error message from the method
            $errorMessage = $this->getErrorMessage();

            // Send the error message to the applicant to notify them of the issue
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Public Holidays
    |--------------------------------------------------------------------------
    */

    protected function handlePublicHolidaysState($applicant, $body, $client, $to, $from, $token)
    {
        try {
            // Normalize the applicant's input (remove case sensitivity by converting to lowercase)
            $body = strtolower(trim($body));

            // Check if the applicant's input is one of the valid options (1)
            if ($body === '1' || $body === 'yes') {
                // Update the applicant's public_holidays
                $applicant->update(['public_holidays' => 'Yes']);

                // Applicant selected option 1: Navigate to 'highest_qualification' state
                $stateID = State::where('code', 'highest_qualification')->value('id');
                $applicant->update(['state_id' => $stateID]);

                // Send messages for the selected state
                $messages = $this->fetchStateMessages('highest_qualification');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            } elseif ($body === '2' || $body === 'no') {
                // Update the applicant's public_holidays
                $applicant->update(['public_holidays' => 'No']);

                // Applicant selected option 2: Navigate to 'highest_qualification' state
                $stateID = State::where('code', 'highest_qualification')->value('id');
                $applicant->update(['state_id' => $stateID]);

                // Send messages for the selected state
                $messages = $this->fetchStateMessages('highest_qualification');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            } else {
                // If the applicant's input is not a valid option, send an error message
                $errorMessage = "Invalid option. Please reply with:\n\n1. Yes\n2. No";
                $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
            }
        } catch (Exception $e) {
            // Log any exceptions that occur for debugging purposes
            Log::error('Error in handleTermsConditionsState: ' . $e->getMessage());

            // Fetch a generic error message
            $errorMessage = $this->getErrorMessage();

            // Send the error message to the applicant to notify them of the issue
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Highest Qualification
    |--------------------------------------------------------------------------
    */

    protected function handleHighestQualificationState($applicant, $body, $client, $to, $from, $token)
    {
        try {
            // Normalize the applicant's input (remove case sensitivity by converting to lowercase)
            $body = strtolower(trim($body));

            // Initialize the education variable to null, which will be updated based on the applicant's input
            $education = null;

            // Use a switch case to handle the applicant's input and map it to the corresponding education level
            switch ($body) {
                case '1':
                case 'grade 9':
                    $education = 1;
                    break;
                case '2':
                case 'grade 10':
                case 'grade 11':
                    $education = 2;
                    break;
                case '3':
                case 'grade 12':
                    $education = 4;
                    break;
                case '4':
                case 'diploma':
                    $education = 5;
                    break;
                case '5':
                case 'degree':
                    $education = 6;
                    break;
            }

            // If the education level has been set based on the applicant's input
            if ($education) {
                // Update the applicant's education_id in the database
                $applicant->update(['education_id' => $education]);

                // Transition to the next state 'environment'
                $stateID = State::where('code', 'environment')->value('id');
                $applicant->update(['state_id' => $stateID]);

                // Fetch and send messages for the 'environment' state
                $messages = $this->fetchStateMessages('environment');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            } else {
                // If the applicant's input is not a valid option, send an error message
                $errorMessage = "Invalid option. Please reply with:\n\n1. Grade 9\n2. Grade 10 or 11\n3. Grade 12\n4. Diploma\n5. Degree";
                $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
            }
        } catch (Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error in handleHighestQualificationState: ' . $e->getMessage());

            // Get the generic error message to send to the applicant
            $errorMessage = $this->getErrorMessage();

            // Send the error message to the applicant
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Consent
    |--------------------------------------------------------------------------
    */

    protected function handleConsentState($applicant, $body, $client, $to, $from, $token)
    {
        try {
            // Normalize the applicant's input (remove case sensitivity by converting to lowercase)
            $body = strtolower(trim($body));

            // Check if the applicant's input is one of the valid options (1)
            if ($body === '1' || $body === 'yes') {
                // Update the applicant's consent
                $applicant->update(['consent' => 'Yes']);

                // Applicant selected option 1: Navigate to 'environment' state
                $stateID = State::where('code', 'environment')->value('id');
                $applicant->update(['state_id' => $stateID]);

                // Send messages for the selected state
                $messages = $this->fetchStateMessages('environment');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            } elseif ($body === '2' || $body === 'no') {
                // Update the applicant's consent
                $applicant->update(['consent' => 'No']);

                // Applicant selected option 2: Navigate to 'welcome' state
                $stateID = State::where('code', 'welcome')->value('id');
                $applicant->update(['state_id' => $stateID]);

                // Send message that they are not eligible
                $message = "Thank you for your interest in a position at the Shoprite Group of Companies. You are not eligible for this position. Have a wonderful day!";
                $this->sendAndLogMessages($applicant, [$message], $client, $to, $from, $token);
            } else {
                // If the applicant's input is not a valid option, send an error message
                $errorMessage = "Invalid option. Please reply with:\n\n1. Yes\n2. No";
                $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
            }
        } catch (Exception $e) {
            // Log any exceptions that occur for debugging purposes
            Log::error('Error in handleConsentState: ' . $e->getMessage());

            // Fetch a generic error message
            $errorMessage = $this->getErrorMessage();

            // Send the error message to the applicant to notify them of the issue
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Environment
    |--------------------------------------------------------------------------
    */

    protected function handleEnvironmentState($applicant, $body, $client, $to, $from, $token)
    {
        try {
            // Check if the applicant meets the exclusion criteria
            if ($applicant->public_holidays === 'No' || $applicant->education_id === 1) {
                // Applicant is not eligible: Navigate to 'welcome' state
                $stateID = State::where('code', 'welcome')->value('id');
                $applicant->update(['state_id' => $stateID]);

                // Send message that they are not eligible
                $message = "Thank you for your interest in a position at the Shoprite Group of Companies. You are not eligible for this position. Have a wonderful day!";
                $this->sendAndLogMessages($applicant, [$message], $client, $to, $from, $token);

                // Exit the function early as the applicant is ineligible
                return;
            }

            // Normalize the applicant's input (remove case sensitivity by converting to lowercase)
            $body = strtolower(trim($body));

            // Check if the applicant's input is one of the valid options (1)
            if ($body === '1' || $body === 'yes') {
                // Update the applicant's environment
                $applicant->update(['environment' => 'Yes']);

                // Applicant selected option 1: Navigate to 'experience' state
                $stateID = State::where('code', 'experience')->value('id');
                $applicant->update(['state_id' => $stateID]);

                // Send messages for the selected state
                $messages = $this->fetchStateMessages('experience');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            } elseif ($body === '2' || $body === 'no') {
                // Update the applicant's environment
                $applicant->update(['environment' => 'No']);

                // Applicant selected option 2: Navigate to 'welcome' state
                $stateID = State::where('code', 'welcome')->value('id');
                $applicant->update(['state_id' => $stateID]);

                // Send message that they are not eligible
                $message = "Thank you for your interest in a position at the Shoprite Group of Companies. You are not eligible for this position. Have a wonderful day!";
                $this->sendAndLogMessages($applicant, [$message], $client, $to, $from, $token);
            } else {
                // If the applicant's input is not a valid option, send an error message
                $errorMessage = "Invalid option. Please reply with:\n\n1. Yes\n2. No";
                $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
            }
        } catch (Exception $e) {
            // Log any exceptions that occur for debugging purposes
            Log::error('Error in handleEnvironmentState: ' . $e->getMessage());

            // Fetch a generic error message
            $errorMessage = $this->getErrorMessage();

            // Send the error message to the applicant to notify them of the issue
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Experience
    |--------------------------------------------------------------------------
    */

    protected function handleExperienceState($applicant, $body, $client, $to, $from, $token)
    {
        try {
            // Normalize the applicant's input (remove case sensitivity by converting to lowercase)
            $body = strtolower(trim($body));

            // Initialize the experience variable to null, which will be updated based on the applicant's input
            $experience = null;

            // Use a switch case to handle the applicant's input and map it to the corresponding experience level
            switch ($body) {
                case '1':
                case 'none':
                    $experience = 1;
                    break;
                case '2':
                case '1 - 6 months':
                    $experience = 2;
                    break;
                case '3':
                case '7 - 12 months':
                    $experience = 3;
                    break;
                case '4':
                case '1 - 2 years':
                    $experience = 4;
                    break;
                case '5':
                case '2 - 5 years':
                    $experience = 5;
                    break;
                case '6':
                case '6+ years':
                    $experience = 6;
                    break;
            }

            // If the experience level has been set based on the applicant's input
            if ($experience) {
                // Update the applicant's duration_id in the database
                $applicant->update(['duration_id' => $experience]);

                // Transition to the next state 'brand'
                $stateID = State::where('code', 'brand')->value('id');
                $applicant->update(['state_id' => $stateID]);

                // Fetch and send messages for the 'brand' state
                $messages = $this->fetchStateMessages('brand');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            } else {
                // If the applicant's input is not a valid option, send an error message
                $errorMessage = "Invalid option. Please reply with:\n\n1. None\n2. 1 - 6 Months\n3. 7 - 12 Months\n4. 1 - 2 Years\n5. 2 - 5 Years\n6. 6+ Years";
                $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
            }
        } catch (Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error in handleExperienceState: ' . $e->getMessage());

            // Get the generic error message to send to the applicant
            $errorMessage = $this->getErrorMessage();

            // Send the error message to the applicant
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Brand
    |--------------------------------------------------------------------------
    */

    protected function handleBrandState($applicant, $body, $client, $to, $from, $token)
    {
        try {
            // Remove all spaces from the input
            $cleanedBody = preg_replace('/\s+/', '', $body);

            // Split the input by commas to handle multiple selections
            $inputs = array_map('strtolower', explode(',', $cleanedBody));

            // Prevent invalid combinations like "1, 4" or "2, 4"
            if (in_array('4', $inputs) && count($inputs) > 1) {
                // Send an error message if "All" (4) is combined with other selections
                $errorMessage = "You’ve selected 'Any' along with a specific brand, which isn’t allowed. Please reply with:\n\n1. Checkers\n2. Shoprite\n3. USave\n4. Any";
                $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
                return; // Exit early to prevent further processing
            }

            // Initialize an empty array to store the selected brand IDs
            $brandIds = [];

            // Iterate through the inputs and map them to the corresponding brand IDs
            foreach ($inputs as $input) {
                switch ($input) {
                    case '1':
                    case 'checkers':
                        // If Checkers is selected, add Checkers, Shoprite, and USave (2, 3, 4)
                        $brandIds = array_merge($brandIds, [2, 3, 4]);
                        break;
                    case '2':
                    case 'shoprite':
                        // If Shoprite is selected, only add Shoprite and USave (5)
                        $brandIds[] = 5;
                        break;
                    case '3':
                    case 'usave':
                        // If USave is selected, only add USave (6)
                        $brandIds[] = 6;
                        break;
                    case '4':
                    case 'all':
                        // If "All" is selected, override and only add brand_id = 1
                        $brandIds = [1];
                        break;
                }
            }

            // If any valid brands were selected, process the updates
            if (!empty($brandIds)) {
                // Remove any duplicates in case multiple entries of the same brand were added
                $brandIds = array_unique($brandIds);

                // Attach the brands to the applicant in the pivot table
                foreach ($brandIds as $brandId) {
                    $applicant->brands()->attach($brandId, [
                        'created_at' => now(),
                        'updated_at' => now()
                    ]); // Assuming a belongsToMany relationship
                }

                // Transition to the next state 'location_type'
                $stateID = State::where('code', 'location_type')->value('id');
                $applicant->update(['state_id' => $stateID]);

                // Fetch and send messages for the 'location_type' state
                $messages = $this->fetchStateMessages('location_type');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            } else {
                // If the applicant's input is not a valid option, send an error message
                $errorMessage = "Invalid option. Please reply with:\n\n1. Checkers\n2. Shoprite\n3. USave\n4. All";
                $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
            }
        } catch (Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error in handleBrandState: ' . $e->getMessage());

            // Get the generic error message to send to the applicant
            $errorMessage = $this->getErrorMessage();

            // Send the error message to the applicant
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Location Type
    |--------------------------------------------------------------------------
    */

    protected function handleLocationTypeState($applicant, $body, $client, $to, $from, $token)
    {
        try {
            // Normalize the applicant's input (remove case sensitivity by converting to lowercase)
            $body = strtolower(trim($body));

            // Check if the applicant's input is one of the valid options (1)
            if ($body === '1' || $body === 'address') {
                // Update the applicant's location_type
                $applicant->update(['location_type' => 'Address']);

                // Applicant selected option 1: Navigate to 'location' state
                $stateID = State::where('code', 'location')->value('id');
                $applicant->update(['state_id' => $stateID]);

                // Send messages for the selected state
                $message = "Please provide your home *address* with every detail (e.g. street number, street name, suburb, town, postal code): 🏡";
                $this->sendAndLogMessages($applicant, [$message], $client, $to, $from, $token);
            } elseif ($body === '2' || $body === 'pin') {
                // Update the applicant's location_type
                $applicant->update(['location_type' => 'Pin']);

                // Applicant selected option 2: Navigate to 'location' state
                $stateID = State::where('code', 'location')->value('id');
                $applicant->update(['state_id' => $stateID]);

                // Send message that they are not eligible
                $message = "Please provide the location pin: 📍";
                $this->sendAndLogMessages($applicant, [$message], $client, $to, $from, $token);
            } else {
                // If the applicant's input is not a valid option, send an error message
                $errorMessage = "Invalid option. Please reply with:\n\n1. Type address \n2. Drop location pin";
                $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
            }
        } catch (Exception $e) {
            // Log any exceptions that occur for debugging purposes
            Log::error('Error in handleEnvironmentState: ' . $e->getMessage());

            // Fetch a generic error message
            $errorMessage = $this->getErrorMessage();

            // Send the error message to the applicant to notify them of the issue
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Location
    |--------------------------------------------------------------------------
    */

    protected function handleLocationState($applicant, $body, $latitude, $longitude, $client, $to, $from, $token)
    {
        try {
            // Send an initial message to the applicant indicating that the address is being verified
            $message = "Please give me a second to verify your address...";
            $this->sendAndLogMessages($applicant, [$message], $client, $to, $from, $token);

            // Initialize the Google Maps service to handle geocoding or reverse geocoding
            $googleMapsService = new GoogleMapsService();

            // Check if latitude and longitude are provided (i.e., the applicant sent their location coordinates)
            if (isset($latitude) && isset($longitude)) {
                // Update the applicant's location with the latitude and longitude coordinates
                $applicant->update(['location' => $latitude . ' ' . $longitude]);

                // Reverse geocode the coordinates to obtain the corresponding address
                $response = $googleMapsService->reverseGeocodeCoordinates(trim($latitude), trim($longitude));
            } else {
                // If no coordinates are provided, assume the applicant entered a text address
                $applicant->update(['location' => $body]);

                // Use the Google Maps service to geocode the text address and get location details
                $response = $googleMapsService->geocodeAddress($applicant->location);
            }

            // Check if the Google Maps service returned a valid response
            if ($response !== null) {
                // Extract the formatted address and city from the response
                $formattedAddress = $response['formatted_address'];
                $city = $response['city'] ?? null; // Use null if the city is not available

                // Prepare a message to confirm the applicant's address, with options for Yes or No
                $templateMessage = [
                    [
                        'message' => "I have picked up the address as:\n\n*$formattedAddress*\n\nPlease confirm that this is correct.\n\n1. Yes - that's correct\n2. No - re-enter address",
                        'type' => "text" // Indicating this is a text message
                    ]
                ];

                // Send the confirmation message to the applicant
                $this->sendAndLogMessages($applicant, $templateMessage, $client, $to, $from, $token);

                // Extract latitude and longitude from the response (in case the address was geocoded)
                $latitude = $response['latitude'];
                $longitude = $response['longitude'];

                // Update the applicant's information with the confirmed address, coordinates, and town (city)
                $applicant->update([
                    'location' => $formattedAddress, // Save the formatted address
                    'coordinates' => $latitude . ',' . $longitude, // Store coordinates as a string
                    'town_id' => $city  // Save the city as the applicant's town (if available)
                ]);

                // Transition the applicant to the 'location_confirmation' state
                $stateID = State::where('code', 'location_confirmation')->value('id');
                $applicant->update(['state_id' => $stateID]);
            } else {
                // If the Google Maps service could not verify the address, send an error message
                $errorMessage = "Sorry, we couldn't verify your address. Please enter your address again.";
                $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
            }
        } catch (Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error in handleLocationState: ' . $e->getMessage());

            // Get the error message from the method
            $errorMessage = $this->getErrorMessage();

            // Send the error message to the applicant
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Location Confirmation
    |--------------------------------------------------------------------------
    */

    protected function handleLocationConfirmationState($applicant, $body, $client, $to, $from, $token)
    {
        try {
            // Normalize the applicant's input (remove case sensitivity by converting to lowercase)
            $body = strtolower(trim($body));

            // Check if the applicant confirmed the address (valid responses: '1', 'yes', 'that is correct', etc.)
            if ($body === '1' || $body === 'yes' || $body === 'that is correct' || $body === 'correct' || $body === "that's correct") {
                // If the applicant confirms the address, transition to the 'contact_number' state
                $stateID = State::where('code', 'has_email')->value('id');
                $applicant->update(['state_id' => $stateID]); // Update the applicant's state in the database

                // Send a confirmation message to thank the applicant for confirming the address
                $confirmMessage = "Thank you for confirming your address.";
                $this->sendAndLogMessages($applicant, [$confirmMessage], $client, $to, $from, $token);

                // Fetch and send the next set of messages for the 'contact_number' state
                $messages = $this->fetchStateMessages('has_email');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);

            // Check if the applicant wants to re-enter the address (valid responses: '2', 'no', 're-enter address', etc.)
            } elseif ($body === '2' || $body === 'no' || $body === 're-enter address' || $body === 'incorrect') {
                // If the applicant wants to provide a new address, send a prompt to re-enter the address or provide a new pin
                $message = "Please re-enter your address or provide a new pin:";
                $this->sendAndLogMessages($applicant, [$message], $client, $to, $from, $token);

                // Transition the applicant back to the 'location' state to re-enter the address
                $stateID = State::where('code', 'location')->value('id');
                $applicant->update(['state_id' => $stateID]);

            // If the applicant's input is not recognized, prompt them with valid options
            } else {
                // Send an error message listing the valid options ('1' for confirmation, '2' for re-entering)
                $errorMessage = "Invalid option. Please reply with:\n\n1. Yes - that's correct\n2. No - re-enter address";
                $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
            }
        } catch (Exception $e) {
            // If an error occurs, log it for debugging purposes
            Log::error('Error in handleLocationConfirmationState: ' . $e->getMessage());

            // Retrieve the generic error message to send to the applicant
            $errorMessage = $this->getErrorMessage();

            // Send the error message to the applicant to inform them of the issue
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Has Email
    |--------------------------------------------------------------------------
    */

    protected function handleHasEmailState($applicant, $body, $client, $to, $from, $token)
    {
        try {
            // Normalize the applicant's input (remove case sensitivity by converting to lowercase)
            $body = strtolower(trim($body));

            // Check if the applicant's input is one of the valid options (1)
            if ($body === '1' || $body === 'yes') {
                // Update the applicant's has_email
                $applicant->update(['has_email' => 'Yes']);

                // Applicant selected option 1: Navigate to 'email' state
                $stateID = State::where('code', 'email')->value('id');
                $applicant->update(['state_id' => $stateID]);

                // Send messages for the selected state
                $messages = $this->fetchStateMessages('email');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            } elseif ($body === '2' || $body === 'no') {
                // Update the applicant's has_email
                $applicant->update(['has_email' => 'No']);

                // Applicant selected option 2: Navigate to 'disability' state
                $stateID = State::where('code', 'disability')->value('id');
                $applicant->update(['state_id' => $stateID]);

                // Send messages for the selected state
                $messages = $this->fetchStateMessages('disability');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            } else {
                // If the applicant's input is not a valid option, send an error message
                $errorMessage = "Invalid option. Please reply with:\n\n1. Yes\n2. No";
                $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
            }
        } catch (Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error in handleHasEmailState: ' . $e->getMessage());

            // Get the error message from the method
            $errorMessage = $this->getErrorMessage();

            // Send the error message to the applicant
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Email
    |--------------------------------------------------------------------------
    */

    protected function handleEmailState($applicant, $body, $client, $to, $from, $token)
    {
        try {
            // Use PHP's built-in filter to check if the provided input is a valid email address
            if (filter_var($body, FILTER_VALIDATE_EMAIL)) {
                // If the email is valid, update the applicant's email in lowercase (normalize the email format)
                $applicant->update(['email' => strtolower($body)]);

                // Transition the applicant to the next state, which is 'disability'
                $stateID = State::where('code', 'disability')->value('id');
                $applicant->update(['state_id' => $stateID]); // Update the applicant's state in the database

                // Send messages for the selected state
                $messages = $this->fetchStateMessages('disability');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            } else {
                // Send messages for prompting for valid email
                $message = "Please enter a valid email address:";
                $this->sendAndLogMessages($applicant, [$message], $client, $to, $from, $token);
            }
        } catch (Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error in handleEmailState: ' . $e->getMessage());

            // Get the error message from the method
            $errorMessage = $this->getErrorMessage();

            // Send the error message to the applicant
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Disability
    |--------------------------------------------------------------------------
    */

    protected function handleDisabilityState($applicant, $body, $client, $to, $from, $token)
    {
        try {
            // Normalize the applicant's input (remove case sensitivity by converting to lowercase)
            $body = strtolower(trim($body));

            // Check if the applicant's input is one of the valid options (1)
            if ($body === '1' || $body === 'yes') {
                // Update the applicant's disability
                $applicant->update(['disability' => 'Yes']);

                // Applicant selected option 1: Navigate to 'literacy_start' state
                $stateID = State::where('code', 'literacy_start')->value('id');
                $applicant->update(['state_id' => $stateID]);

                // Send messages for the selected state
                $messages = $this->fetchStateMessages('literacy_start');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            } elseif ($body === '2' || $body === 'no') {
                // Update the applicant's disability
                $applicant->update(['disability' => 'No']);

                // Applicant selected option 2: Navigate to 'literacy_start' state
                $stateID = State::where('code', 'literacy_start')->value('id');
                $applicant->update(['state_id' => $stateID]);

                // Send messages for the selected state
                $messages = $this->fetchStateMessages('literacy_start');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            } else {
                // If the applicant's input is not a valid option, send an error message
                $errorMessage = "Invalid option. Please reply with:\n\n1. Yes\n2. No";
                $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
            }
        } catch (Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error in handleDisabilityState: ' . $e->getMessage());

            // Get the error message from the method
            $errorMessage = $this->getErrorMessage();

            // Send the error message to the applicant
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Literacy Start
    |--------------------------------------------------------------------------
    */

    protected function handleLiteracyStartState($applicant, $body, $client, $to, $from, $token)
    {
        try {
            // Check if the applicant has entered the keyword 'start' (case-insensitive)
            if (strtolower($body) == 'start') {
                // Fetch all messages associated with the 'literacy' state (representing the literacy questions)
                $messages = $this->fetchStateMessages('literacy');

                // Check if any literacy questions were found
                if (count($messages) > 0) {
                    // Randomize the order of the messages (questions) to create a shuffled question pool
                    shuffle($messages);

                    // Extract and store the sort order of the questions as a comma-separated string
                    $sortOrderValues = implode(',', array_column($messages, 'sort'));

                    // Update the applicant's literacy info
                    $applicant->update([
                        'literacy_question_pool' => $sortOrderValues,
                        'literacy_score' => 0,
                        'literacy_questions' => count($messages)
                    ]);

                    // Extract the first question from the shuffled message pool
                    $firstQuestionMessages = array_column($messages, 'message');
                    $firstQuestion = array_shift($firstQuestionMessages); // Get the first question

                    // Send the first question to the applicant and log the message
                    $this->sendAndLogMessages($applicant, [$firstQuestion], $client, $to, $from, $token);

                    // Transition the applicant to the next state, 'literacy'
                    $stateID = State::where('code', 'literacy')->value('id');
                    $applicant->update(['state_id' => $stateID]);
                } else {
                    // If no literacy questions are found, send an error message to the applicant
                    $message = "Sorry, we could not find any questions. Please try again later.";
                    $this->sendAndLogMessages($applicant, [$message], $client, $to, $from, $token);
                }
            } else {
                // If the applicant did not respond with 'start', prompt them to do so
                $message = "When you are ready, please reply with *Start*.";
                $this->sendAndLogMessages($applicant, [$message], $client, $to, $from, $token);
            }
        } catch (Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error in handleLiteracyStartState: ' . $e->getMessage());

            // Get the error message from the method
            $errorMessage = $this->getErrorMessage();

            // Send the error message to the applicant
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Literacy
    |--------------------------------------------------------------------------
    */

    protected function handleLiteracyState($applicant, $body, $client, $to, $from, $token)
    {
        try {
            // Extract the order of the literacy questions from the applicant's data (stored as a comma-separated string).
            $sortOrderPool = explode(',', $applicant->literacy_question_pool);

            // Retrieve the current question's sort order (first value from the pool).
            $currentQuestionSortOrder = array_shift($sortOrderPool);

            // Fetch the current question based on the sort order.
            $stateID = State::where('code', 'literacy')->value('id'); // Get the state ID for the literacy state.
            $currentQuestion = ChatTemplate::where('state_id', $stateID)
                                           ->where('sort', $currentQuestionSortOrder)
                                           ->first(); // Fetch the question corresponding to the current sort order.

            // Check if the applicant's response is one of the valid options ('a', 'b', 'c', 'd', or 'e').
            if (in_array(strtolower($body), ['a', 'b', 'c', 'd', 'e'])) {
                // If the applicant's answer matches the correct answer, increment their literacy score.
                if (strtolower($currentQuestion->answer) == strtolower($body)) {
                    $applicant->update(['literacy_score' => $applicant->literacy_score + 1]);
                }

                // Check if there are more questions left in the pool to present.
                if (count($sortOrderPool) > 0) {
                    // Retrieve the sort order of the next question.
                    $nextQuestionSortOrder = $sortOrderPool[0]; // Get the next question's sort order.
                    $nextQuestion = ChatTemplate::where('state_id', $stateID)
                                                ->where('sort', $nextQuestionSortOrder)
                                                ->first(); // Fetch the next question.

                    // Update the applicant's question pool to reflect the remaining questions.
                    $applicant->update(['literacy_question_pool' => implode(',', $sortOrderPool)]);

                    // Send the next question to the applicant.
                    $nextQuestionText = $nextQuestion->message; // Extract the question text.
                    $this->sendAndLogMessages($applicant, [$nextQuestionText], $client, $to, $from, $token);
                } else {
                    // If no more questions are left, calculate the final literacy score.
                    $correctAnswers = $applicant->literacy_score; // Retrieve the number of correct answers.
                    $literacyQuestions = $applicant->literacy_questions; // Retrieve the total number of questions.

                    // Update the applicant's final literacy score in the format correct/total.
                    $applicant->update(['literacy' => "$correctAnswers/$literacyQuestions"]);

                    // Move the applicant to the 'numeracy_start' state for the numeracy test.
                    $stateID = State::where('code', 'numeracy_start')->value('id');
                    $applicant->update(['state_id' => $stateID]);

                    // Fetch and send the messages related to starting the numeracy test.
                    $messages = $this->fetchStateMessages('numeracy_start');
                    $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
                }
            } else {
                // If the applicant's response is not a valid option (not 'a', 'b', 'c', 'd', or 'e'),
                // restore the current question to the pool and send an error message.
                array_unshift($sortOrderPool, $currentQuestionSortOrder); // Prepend the current question back to the pool.
                $applicant->update(['literacy_question_pool' => implode(',', $sortOrderPool)]); // Update the pool.

                // Send a message indicating the need for a valid response.
                $invalidAnswerMessage = "Please choose a valid option (a, b, c, d, or e).";
                $this->sendAndLogMessages($applicant, [$invalidAnswerMessage], $client, $to, $from, $token);
            }
        } catch (Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error in handleLiteracyState: ' . $e->getMessage());

            // Get the error message from the method
            $errorMessage = $this->getErrorMessage();

            // Send the error message to the applicant
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Numeracy Start
    |--------------------------------------------------------------------------
    */

    protected function handleNumeracyStartState($applicant, $body, $client, $to, $from, $token)
    {
        try {
            // Check if the applicant has entered the keyword 'start' (case-insensitive)
            if (strtolower($body) == 'start') {
                // Fetch all messages associated with the 'numeracy' state (representing the numeracy questions)
                $messages = $this->fetchStateMessages('numeracy');

                // Check if any numeracy questions were found
                if (count($messages) > 0) {
                    // Randomize the order of the messages (questions) to create a shuffled question pool
                    shuffle($messages);

                    // Extract and store the sort order of the questions as a comma-separated string
                    $sortOrderValues = implode(',', array_column($messages, 'sort'));

                    // Update the applicant's numeracy info
                    $applicant->update([
                        'numeracy_question_pool' => $sortOrderValues,
                        'numeracy_score' => 0,
                        'numeracy_questions' => count($messages)
                    ]);

                    // Extract the first question from the shuffled message pool
                    $firstQuestionMessages = array_column($messages, 'message');
                    $firstQuestion = array_shift($firstQuestionMessages); // Get the first question

                    // Send the first question to the applicant and log the message
                    $this->sendAndLogMessages($applicant, [$firstQuestion], $client, $to, $from, $token);

                    // Transition the applicant to the next state, 'numeracy'
                    $stateID = State::where('code', 'numeracy')->value('id');
                    $applicant->update(['state_id' => $stateID]);
                } else {
                    // If no numeracy questions are found, send an error message to the applicant
                    $message = "Sorry, we could not find any questions. Please try again later.";
                    $this->sendAndLogMessages($applicant, [$message], $client, $to, $from, $token);
                }
            } else {
                // Fetch and send messages for the 'numeracy_start' state if the applicant didn't respond with 'start'
                $messages = $this->fetchStateMessages('numeracy_start');
                $lastMessage = end($messages); // Get the last message
                $this->sendAndLogMessages($applicant, [$lastMessage], $client, $to, $from, $token);
            }
        } catch (Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error in handleNumeracyStartState: ' . $e->getMessage());

            // Get the error message from the method
            $errorMessage = $this->getErrorMessage();

            // Send the error message to the applicant
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Numeracy
    |--------------------------------------------------------------------------
    */

    protected function handleNumeracyState($applicant, $body, $client, $to, $from, $token)
    {
        try {
            // Extract the order of the numeracy questions from the applicant's data (stored as a comma-separated string).
            $sortOrderPool = explode(',', $applicant->numeracy_question_pool);

            // Retrieve the current question's sort order (first value from the pool).
            $currentQuestionSortOrder = array_shift($sortOrderPool);

            // Fetch the current question based on the sort order.
            $stateID = State::where('code', 'numeracy')->value('id'); // Get the state ID for numeracy state.
            $currentQuestion = ChatTemplate::where('state_id', $stateID)
                                           ->where('sort', $currentQuestionSortOrder)
                                           ->first(); // Fetch the question corresponding to the current sort order.

            // Check if the applicant's response is one of the valid options ('a', 'b', 'c', 'd', or 'e').
            if (in_array(strtolower($body), ['a', 'b', 'c', 'd', 'e'])) {
                // If the applicant's answer matches the correct answer, increment their numeracy score.
                if (strtolower($currentQuestion->answer) == strtolower($body)) {
                    $applicant->update(['numeracy_score' => $applicant->numeracy_score + 1]);
                }

                // Check if there are more questions left in the pool to present.
                if (count($sortOrderPool) > 0) {
                    // Retrieve the sort order of the next question.
                    $nextQuestionSortOrder = $sortOrderPool[0]; // Get the next question's sort order.
                    $nextQuestion = ChatTemplate::where('state_id', $stateID)
                                                ->where('sort', $nextQuestionSortOrder)
                                                ->first(); // Fetch the next question.

                    // Update the applicant's question pool to reflect the remaining questions.
                    $applicant->update(['numeracy_question_pool' => implode(',', $sortOrderPool)]);

                    // Send the next question to the applicant.
                    $nextQuestionText = $nextQuestion->message; // Extract the question text.
                    $this->sendAndLogMessages($applicant, [$nextQuestionText], $client, $to, $from, $token);
                } else {
                    // If no more questions are left, calculate the final numeracy score.
                    $correctAnswers = $applicant->numeracy_score; // Retrieve the number of correct answers.
                    $numeracyQuestions = $applicant->numeracy_questions; // Retrieve the total number of questions.

                    // Update the applicant's final numeracy score in the format correct/total.
                    $applicant->update(['numeracy' => "$correctAnswers/$numeracyQuestions"]);

                    // Move the applicant to the 'situational_start' state, marking the test as finished.
                    $stateID = State::where('code', 'situational_start')->value('id');
                    $applicant->update(['state_id' => $stateID]);

                    // Fetch and send the situational_start messages.
                    $messages = $this->fetchStateMessages('situational_start');
                    $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
                }
            } else {
                // If the applicant's response is not a valid option (not 'a', 'b', 'c', 'd', or 'e'),
                // restore the current question to the pool and send an error message.
                array_unshift($sortOrderPool, $currentQuestionSortOrder); // Prepend the current question back to the pool.
                $applicant->update(['numeracy_question_pool' => implode(',', $sortOrderPool)]); // Update the pool.

                // Send a message indicating the need for a valid response.
                $invalidAnswerMessage = "Please choose a valid option (a, b, c, d, or e).";
                $this->sendAndLogMessages($applicant, [$invalidAnswerMessage], $client, $to, $from, $token);
            }
        } catch (Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error in handleNumeracyState: ' . $e->getMessage());

            // Get the error message from the method
            $errorMessage = $this->getErrorMessage();

            // Send the error message to the applicant
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Situational Start
    |--------------------------------------------------------------------------
    */

    protected function handleSituationalStartState($applicant, $body, $client, $to, $from, $token)
    {
        try {
            // Check if the applicant has entered the keyword 'start' (case-insensitive)
            if (strtolower($body) == 'start') {
                // Fetch all messages associated with the 'situational' state (representing the situational assessment questions)
                $messages = $this->fetchStateMessages('situational');

                // Check if any situational assessment questions were found
                if (count($messages) > 0) {
                    // Randomize the order of the messages (questions) to create a shuffled question pool
                    shuffle($messages);

                    // Extract and store the sort order of the questions as a comma-separated string
                    $sortOrderValues = implode(',', array_column($messages, 'sort'));

                    // Update the applicant's situational assessment info
                    $applicant->update([
                        'situational_question_pool' => $sortOrderValues,
                        'situational_score' => 0,
                        'situational_questions' => count($messages)
                    ]);

                    // Extract the first question from the shuffled message pool
                    $firstQuestionMessages = array_column($messages, 'message');
                    $firstQuestion = array_shift($firstQuestionMessages); // Get the first question

                    // Send the first question to the applicant and log the message
                    $this->sendAndLogMessages($applicant, [$firstQuestion], $client, $to, $from, $token);

                    // Transition the applicant to the next state, 'situational'
                    $stateID = State::where('code', 'situational')->value('id');
                    $applicant->update(['state_id' => $stateID]);
                } else {
                    // If no situational assessment questions are found, send an error message to the applicant
                    $message = "Sorry, we could not find any questions. Please try again later.";
                    $this->sendAndLogMessages($applicant, [$message], $client, $to, $from, $token);
                }
            } else {
                // Fetch and send messages for the 'situational_start' state if the applicant didn't respond with 'start'
                $messages = $this->fetchStateMessages('situational_start');
                $lastMessage = end($messages); // Get the last message from the fetched state messages
                $this->sendAndLogMessages($applicant, [$lastMessage], $client, $to, $from, $token);
            }
        } catch (Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error in handleSituationalStartState: ' . $e->getMessage());

            // Get the error message from the method and send it to the applicant
            $errorMessage = $this->getErrorMessage();
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Situational
    |--------------------------------------------------------------------------
    */

    protected function handleSituationalState($applicant, $body, $client, $to, $from, $token)
    {
        try {
            // Extract the order of the situational questions from the applicant's data (stored as a comma-separated string).
            $sortOrderPool = explode(',', $applicant->situational_question_pool);

            // Retrieve the current question's sort order (first value from the pool).
            $currentQuestionSortOrder = array_shift($sortOrderPool);

            // Fetch the current question based on the sort order.
            $stateID = State::where('code', 'situational')->value('id'); // Get the state ID for the situational state.
            $currentQuestion = ChatTemplate::where('state_id', $stateID)
                                        ->where('sort', $currentQuestionSortOrder)
                                        ->first(); // Fetch the question corresponding to the current sort order.

            // Check if the applicant's response is one of the valid options ('a', 'b', 'c', or 'd').
            if (in_array(strtolower($body), ['a', 'b', 'c', 'd'])) {
                // If the applicant's answer matches the correct answer, increment their situational score.
                if (strtolower($currentQuestion->answer) == strtolower($body)) {
                    $applicant->update(['situational_score' => $applicant->situational_score + 1]);
                }

                // Check if there are more questions left in the pool to present.
                if (count($sortOrderPool) > 0) {
                    // Retrieve the sort order of the next question.
                    $nextQuestionSortOrder = $sortOrderPool[0]; // Get the next question's sort order.
                    $nextQuestion = ChatTemplate::where('state_id', $stateID)
                                                ->where('sort', $nextQuestionSortOrder)
                                                ->first(); // Fetch the next question.

                    // Update the applicant's question pool to reflect the remaining questions.
                    $applicant->update(['situational_question_pool' => implode(',', $sortOrderPool)]);

                    // Send the next question to the applicant.
                    $nextQuestionText = $nextQuestion->message; // Extract the question text.
                    $this->sendAndLogMessages($applicant, [$nextQuestionText], $client, $to, $from, $token);
                } else {
                    // If no more questions are left, calculate the final situational score.
                    $correctAnswers = $applicant->situational_score; // Retrieve the number of correct answers.
                    $situationalQuestions = $applicant->situational_questions; // Retrieve the total number of questions.

                    // Update the applicant's final situational score in the format correct/total.
                    $applicant->update(['situational' => "$correctAnswers/$situationalQuestions"]);

                    // Move the applicant to the 'complete' state, marking the assessment as finished.
                    $stateID = State::where('code', 'complete')->value('id');
                    $applicant->update(['state_id' => $stateID]);

                    // Fetch and send the complete state messages.
                    $messages = $this->fetchStateMessages('complete');
                    $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);

                    // Calculate and set applicants score.
                    $score = $this->calculateScore($applicant);
                    $applicant->update(['score' => $score]);
                }
            } else {
                // If the applicant's response is not a valid option (not 'a', 'b', 'c', or 'd'),
                // restore the current question to the pool and send an error message.
                array_unshift($sortOrderPool, $currentQuestionSortOrder); // Prepend the current question back to the pool.
                $applicant->update(['situational_question_pool' => implode(',', $sortOrderPool)]); // Update the pool.

                // Send a message indicating the need for a valid response.
                $invalidAnswerMessage = "Please choose a valid option (a, b, c, or d).";
                $this->sendAndLogMessages($applicant, [$invalidAnswerMessage], $client, $to, $from, $token);
            }
        } catch (Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error in handleSituationalState: ' . $e->getMessage());

            // Get the error message from the method
            $errorMessage = $this->getErrorMessage();

            // Send the error message to the applicant
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Complete
    |--------------------------------------------------------------------------
    */

    protected function handleCompleteState($applicant, $body, $client, $to, $from, $token)
    {
        try {
            // Get the 'complete' state ID
            $completeStateID = State::where('code', 'complete')->value('id');

            // Check if the applicant's state_id is greater than or equal to 'complete' and if the applicant has interviews
            if ($applicant->state_id == $completeStateID && $applicant->interviews()->exists()) {
                // Get the latest interview details
                $latestInterview = $applicant->interviews()->latest('created_at')->first();

                if ($latestInterview && in_array($latestInterview->status, ['Scheduled', 'Reschedule', 'Confirmed'])) {
                    // Prepare the variables for the interview welcome message
                    $variables = [
                        $applicant->firstname, // Applicant's full name
                        $latestInterview->scheduled_date->format('d M Y'),  // Interview date
                        $latestInterview->start_time->format('H:i'),         // Interview time
                        $latestInterview->vacancy->position->name ?? 'N/A', // Position name
                        ($latestInterview->vacancy->store->brand->name ?? 'N/A') . ' (' . ($latestInterview->vacancy->store->town->name ?? 'N/A') . ')' // Store and town name in parentheses
                    ];

                    // Construct the template message for interview_welcome
                    $templateMessage = [
                        [
                            'message' => "Dear {$variables[0]},\n\nI have picked up that you currently have an interview scheduled for the *{$variables[1]}* at *{$variables[2]}* for the position of *{$variables[3]}* at *{$variables[4]}*.\n\nWould you like to view or edit the details of this interview? 📆\n\n1. Yes\n2. No",
                            'type' => "text"
                        ]
                    ];

                    // Send the template message
                    $this->sendAndLogMessages($applicant, $templateMessage, $client, $to, $from, $token);

                    // Set the applicant's state to 'schedule'
                    $scheduleStateID = State::where('code', 'schedule_start')->value('id');
                    $applicant->update(['state_id' => $scheduleStateID]);

                    return;
                }
            }

            // Check if the score is null and then set it.
            if (is_null($applicant->score)) {
                $score = $this->calculateScore($applicant);
                $applicant->update(['score' => $score]);
            }

            // Fetch and send the complete state messages.
            $messages = $this->fetchStateMessages('complete');
            $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
        } catch (Exception $e) {
            // Log the error for debugging purposes.
            Log::error('Error in handleCompleteState: ' . $e->getMessage());

            // Get the error message from the method.
            $errorMessage = $this->getErrorMessage();

            // Send the error message to the applicant.
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Schedule Start
    |--------------------------------------------------------------------------
    */

    protected function handleScheduleStartState($applicant, $body, $client, $to, $from, $token)
    {
        try {
            // Normalize the applicant's input (remove case sensitivity by converting to lowercase)
            $body = strtolower(trim($body));

            // Retrieve the latest interview for the applicant, ordered by the 'created_at' timestamp.
            $latestInterview = $applicant->interviews()->latest('created_at')->first();

            // Check if the applicant has any interviews.
            if ($latestInterview) {
                // Handle the 'yes' response from the applicant.
                if ($body === '1' || $body === 'yes') {
                    // Data to replace placeholders in the scheduled interview messages.
                    $dataToReplace = [
                        "Applicant Name" => $applicant->firstname,
                        "Position Name" => $latestInterview->vacancy->position->name ?? 'N/A', // If no position, set 'N/A'
                        "Store Name" => ($latestInterview->vacancy->store->brand->name ?? '') . ' (' . ($latestInterview->vacancy->store->name ?? '') . ') in ' . ($latestInterview->vacancy->store->town->name ?? ''),
                        "Interview Location" => $latestInterview->location ?? 'N/A', // Interview location or 'N/A'

                        // Check if scheduled_date is an instance of Carbon or try parsing the date string
                        "Interview Date" => $latestInterview->scheduled_date instanceof Carbon
                                            ? $latestInterview->scheduled_date->format('d M Y')
                                            : (strtotime($latestInterview->scheduled_date) ? date('d M Y', strtotime($latestInterview->scheduled_date)) : 'N/A'), // Fallback to strtotime if not Carbon

                        // Check if start_time is an instance of Carbon or try parsing the time string
                        "Interview Time" => $latestInterview->start_time instanceof Carbon
                                            ? $latestInterview->start_time->format('H:i')
                                            : (strtotime($latestInterview->start_time) ? date('H:i', strtotime($latestInterview->start_time)) : 'N/A'), // Fallback to strtotime if not Carbon

                        // Check if reschedule_date is an instance of Carbon or try parsing the date string
                        "Reschedule Date" => $latestInterview->reschedule_date instanceof Carbon
                                            ? $latestInterview->reschedule_date->format('d M Y')
                                            : (strtotime($latestInterview->reschedule_date) ? date('d M Y', strtotime($latestInterview->reschedule_date)) : 'N/A'), // Fallback to strtotime if not Carbon

                        // Check if reschedule_date is an instance of Carbon or try parsing the time string
                        "Reschedule Time" => $latestInterview->reschedule_date instanceof Carbon
                                            ? $latestInterview->reschedule_date->format('H:i')
                                            : (strtotime($latestInterview->reschedule_date) ? date('H:i', strtotime($latestInterview->reschedule_date)) : 'N/A'), // Fallback to strtotime if not Carbon

                        "Notes" => $latestInterview->notes ?? 'N/A', // Additional notes or 'N/A'
                        "Status" => $latestInterview->status ?? 'N/A' // Interview status
                    ];

                    // Check if interview status is "Reschedule"
                    if ($latestInterview->status === "Reschedule") {
                        if ($latestInterview->reschedule_by === "Applicant") {
                            // Fetch the messages associated with the 'reschedule_applicant' state.
                            $messages = $this->fetchStateMessages('reschedule_applicant');

                            // Loop through each message and replace placeholders with the corresponding applicant/interview data.
                            foreach ($messages as &$message) {
                                foreach ($dataToReplace as $key => $value) {
                                    // Check if the message is an array (structured message) or a simple string.
                                    if (is_array($message)) {
                                        $message['message'] = str_replace("[$key]", $value, $message['message']); // Replace placeholders in the message.
                                    } else {
                                        $message = str_replace("[$key]", $value, $message); // Replace placeholders in plain text message.
                                    }
                                }
                            }

                            // Send the updated messages and log the outgoing messages.
                            $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);

                            // Update the applicant's state to 'complete'.
                            $stateID = State::where('code', 'complete')->value('id');
                            $applicant->update(['state_id' => $stateID]);
                        } elseif ($latestInterview->reschedule_by === "Manager") {
                            // Fetch the messages associated with the 'reschedule_manager' state.
                            $messages = $this->fetchStateMessages('reschedule_manager');

                            // Loop through each message and replace placeholders with the corresponding applicant/interview data.
                            foreach ($messages as &$message) {
                                foreach ($dataToReplace as $key => $value) {
                                    // Check if the message is an array (structured message) or a simple string.
                                    if (is_array($message)) {
                                        $message['message'] = str_replace("[$key]", $value, $message['message']); // Replace placeholders in the message.
                                    } else {
                                        $message = str_replace("[$key]", $value, $message); // Replace placeholders in plain text message.
                                    }
                                }
                            }

                            // Send the updated messages and log the outgoing messages.
                            $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);

                            // Update the applicant's state to 'schedule'.
                            $stateID = State::where('code', 'schedule')->value('id');
                            $applicant->update(['state_id' => $stateID]);
                        }
                    } else {
                        // Handle the regular scheduled interview case
                        $messages = $this->fetchStateMessages('schedule');

                        // Loop through each message and replace placeholders with the corresponding applicant/interview data.
                        foreach ($messages as &$message) {
                            foreach ($dataToReplace as $key => $value) {
                                // Check if the message is an array (structured message) or a simple string.
                                if (is_array($message)) {
                                    $message['message'] = str_replace("[$key]", $value, $message['message']); // Replace placeholders in the message.
                                } else {
                                    $message = str_replace("[$key]", $value, $message); // Replace placeholders in plain text message.
                                }
                            }
                        }

                        // Send the updated messages and log the outgoing messages.
                        $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);

                        // Update the applicant's state to 'schedule'.
                        $stateID = State::where('code', 'schedule')->value('id');
                        $applicant->update(['state_id' => $stateID]);
                    }
                } elseif ($body === '2' || $body === 'no') {
                    // Update the interview status to 'Declined' for the latest interview.
                    //$latestInterview->status = 'Declined';
                    //$latestInterview->save();

                    // If the interview status was changed and the applicant has an associated user, create a notification.
                    /*
                    if ($latestInterview->wasChanged() && $applicant->user) {
                        // Create a new notification for the interviewer.
                        $notification = new Notification();
                        $notification->user_id = $latestInterview->interviewer_id; // Set the interviewer as the recipient.
                        $notification->causer_id = $applicant->user->id; // The applicant caused the action.
                        $notification->subject()->associate($latestInterview); // Associate the interview as the subject of the notification.
                        $notification->type_id = 1; // Set the notification type (e.g., 1 for interview declined).
                        $notification->notification = "Declined your interview request 🚫"; // Notification message.
                        $notification->read = "No"; // Mark the notification as unread.
                        $notification->save();
                    }*/

                    // Send a response to the applicant confirming the interview has been declined.
                    /*$messages = [
                        "We have received your response and your interview for the position of " .
                        $latestInterview->vacancy->position->name .
                        " is now declined. If this was a mistake, please contact us immediately."
                    ];*/
                    $messages = [
                        "Thank you for your response. Should you wish to view, reschedule or decline your interview, start by typing Hi. Have a wonderful day!"
                    ];
                    $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);

                    // Update the applicant's state to 'complete' after declining the interview.
                    $stateID = State::where('code', 'complete')->value('id');
                    $applicant->update(['state_id' => $stateID]);
                } else {
                    // Handle invalid responses that are neither 'yes' nor 'no'.

                    // Send an error message listing the valid options ('1' Yes, '2' No)
                    $errorMessage = "Dear {$applicant->firstname},\n\n" . "You have been scheduled for an interview. Would you like to view the details? 📆\n\n" . "1. Yes\n2. No";
                    $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
                }
            } else {
                // If no interview was found for the applicant.

                // Send a message indicating no interviews were found for the applicant.
                $message = "No interviews found, have a wonderful day.";
                $this->sendAndLogMessages($applicant, [$message], $client, $to, $from, $token);

                // Update the applicant's state to 'complete'.
                $stateID = State::where('code', 'complete')->value('id');
                $applicant->update(['state_id' => $stateID]);
            }
        } catch (Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error in handleScheduleStartState: ' . $e);

            // Get the error message from the method
            $errorMessage = $this->getErrorMessage();

            // Send the error message to the user
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Schedule
    |--------------------------------------------------------------------------
    */

    protected function handleScheduleState($applicant, $body, $client, $to, $from, $token)
    {
        try {
            // Normalize the applicant's input by trimming whitespace and converting to lowercase for easier comparison.
            $body = strtolower(trim($body));

            // Retrieve the latest interview for the applicant, ordered by 'created_at'.
            $latestInterview = $applicant->interviews()->latest('created_at')->first();

            // Check if an interview exists for the applicant.
            if ($latestInterview) {
                // Check if the interview status is either 'Scheduled' or 'Reschedule' or 'Confirmed'
                if (!in_array($latestInterview->status, ['Scheduled', 'Reschedule', 'Confirmed'])) {
                    // Send a message indicating the interview can no longer be edited
                    $errorMessage = "Sorry, but you can no longer edit this interview. Have wonderful day!";
                    $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);

                    // Update the applicant's state to 'complete'
                    $stateID = State::where('code', 'complete')->value('id');
                    $applicant->update(['state_id' => $stateID]);

                    return; // Exit the method as no further action is required
                }

                // Handle the 'confirm' response, where the applicant confirms the interview.
                if ($body == '1' || $body == 'confirm') {
                    if (in_array($latestInterview->status, ['Scheduled', 'Reschedule']) && ($latestInterview->reschedule_by === 'Manager' || $latestInterview->reschedule_by === null)) {
                        // Check if the interview was rescheduled by the applicant.
                        if ($latestInterview->status === 'Reschedule' && $latestInterview->reschedule_by === 'Manager') {
                            // Parse the rescheduled date-time.
                            $rescheduleDateTime = Carbon::parse($latestInterview->reschedule_date);

                            // Set the scheduled_date and start_time based on the reschedule_date.
                            $scheduledDate = $rescheduleDateTime->format('Y-m-d'); // Extract only the date.
                            $startTime = $rescheduleDateTime->format('H:i:s'); // Extract only the time.

                            // Set the end_time to 1 hour after the start_time.
                            $endTime = $rescheduleDateTime->addHour()->format('H:i:s');

                            // Update the interview with the new scheduled_date, start_time, and end_time, and reset reschedule fields.
                            $latestInterview->update([
                                'status' => 'Confirmed',
                                'scheduled_date' => $scheduledDate,
                                'start_time' => $startTime,
                                'end_time' => $endTime,
                                'reschedule_date' => null,
                                'reschedule_by' => null
                            ]);
                        } else {
                            // Regular confirmation flow for scheduled or manager-rescheduled interviews.
                            $latestInterview->status = 'Confirmed';
                            $latestInterview->save();
                        }

                        // Update the interview status to 'Confirmed'.
                        $latestInterview->status = 'Confirmed';
                        $latestInterview->save();

                        // If the interview status was changed and the applicant has a user, create a notification.
                        if ($latestInterview->wasChanged()) {
                            // Create a new notification for the interviewer.
                            $notification = new Notification();
                            $notification->user_id = $latestInterview->interviewer_id; // Set the interviewer as the recipient.
                            $notification->causer_id = optional($applicant->user)->id ?? null; // Set the applicant as the causer or fallback to null.
                            $notification->applicant_id = $applicant->id; // Set the applicant as the applicant.
                            $notification->subject()->associate($latestInterview); // Associate the interview with the notification.
                            $notification->type_id = 1; // Set the type (e.g., 1 for interview confirmation).
                            $notification->notification = "Confirmed your interview request ✅"; // Set the notification message.
                            $notification->read = "No"; // Mark the notification as unread.
                            $notification->save(); // Save the notification to the database.
                        }
                    }

                    // Send confirmation message to the applicant.
                    $messages = [
                        "Thank you, your interview for the position of *" .
                        $latestInterview->vacancy->position->name .
                        "* on *" . $latestInterview->scheduled_date->format('d M') .
                        "* at *" . $latestInterview->start_time->format('H:i') .
                        "* has been confirmed!"
                    ];
                    $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);

                    // Update the applicant's state to 'complete' after confirmation.
                    $stateID = State::where('code', 'complete')->value('id');
                    $applicant->update(['state_id' => $stateID]);
                // Handle the 'reschedule' response, where the applicant requests to reschedule the interview.
                } elseif ($body == '2' || $body == 'reschedule') {
                    // Update the interview status to 'Reschedule'.
                    $latestInterview->status = 'Reschedule';
                    $latestInterview->reschedule_by = 'Applicant';
                    $latestInterview->save();

                    // If the interview status was changed, create a notification for rescheduling.
                    if ($latestInterview->wasChanged()) {
                        $notification = new Notification();
                        $notification->user_id = $latestInterview->interviewer_id; // Set the interviewer as the recipient.
                        $notification->causer_id = optional($applicant->user)->id ?? null; // Set the applicant as the causer or fallback to null.
                        $notification->applicant_id = $applicant->id; // Set the applicant as the applicant.
                        $notification->subject()->associate($latestInterview); // Associate the interview with the notification.
                        $notification->type_id = 1; // Set the type (e.g., 1 for interview reschedule request).
                        $notification->notification = "Requested to reschedule 📅"; // Set the notification message.
                        $notification->read = "No"; // Mark the notification as unread.
                        $notification->save(); // Save the notification to the database.
                    }

                    //Get the current scheduled datew
                    $scheduledDate = $latestInterview->scheduled_date->format('d M Y'); // Format the current scheduled date
                    // Add 1 day to the scheduled date using Carbon
                    $suggestedDateTime = Carbon::parse($latestInterview->scheduled_date)->addDay();
                    $suggestedDate = $suggestedDateTime->format('d M Y');

                    // Send a message prompting the applicant to suggest a new date and time.
                    $messages = [
                        "Please suggest a new *date* and *time* for your interview after the current scheduled date: *{$scheduledDate}*. We will do our best to accommodate your schedule. For example: *{$suggestedDate} 14:00*. 📆"
                    ];
                    $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);

                    // Update the applicant's state to 'reschedule' for the next step.
                    $stateID = State::where('code', 'reschedule')->value('id');
                    $applicant->update(['state_id' => $stateID]);
                } elseif ($body == '3' || $body == 'decline') {
                    if (in_array($latestInterview->status, ['Scheduled', 'Reschedule', 'Confirmed'])) {
                        // Handle the 'decline' response, where the applicant declines the interview.

                        // Update the interview status to 'Declined'.
                        $latestInterview->status = 'Declined';
                        $latestInterview->save();

                        // If the interview status was changed and the applicant has a user, create a notification.
                        if ($latestInterview->wasChanged()) {
                            $notification = new Notification();
                            $notification->user_id = $latestInterview->interviewer_id; // Set the interviewer as the recipient.
                            $notification->causer_id = optional($applicant->user)->id ?? null; // Set the applicant as the causer or fallback to null.
                            $notification->applicant_id = $applicant->id; // Set the applicant as the applicant.
                            $notification->subject()->associate($latestInterview); // Associate the interview with the notification.
                            $notification->type_id = 1; // Set the type (e.g., 1 for interview decline).
                            $notification->notification = "Declined your interview request 🚫"; // Set the notification message.
                            $notification->read = "No"; // Mark the notification as unread.
                            $notification->save(); // Save the notification to the database.
                        }
                    }

                    // Send a message to the applicant confirming the interview decline.
                    $messages = [
                        "We have received your response and your interview for the position of *" .
                        $latestInterview->vacancy->position->name .
                        "* is now declined."
                    ];
                    $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);

                    // Update the applicant's state to 'complete' after declining the interview.
                    $stateID = State::where('code', 'complete')->value('id');
                    $applicant->update(['state_id' => $stateID]);
                } else {
                    // Handle invalid input if the response is not 'confirm', 'reschedule', or 'decline'.

                    // Send an error message listing the valid options ('1' Confirm, '2' Reschedule, '3' Decline)
                    $errorMessage = "Invalid option. Please reply with:\n\n1. Confirm\n2. Reschedule\n3. Decline";
                    $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
                }
            } else {
                // If no interview was found, send a error message to the applicant indicating no interviews are scheduled.

                $errorMessage = "No interviews found, have a wonderful day.";
                $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);

                // Update the applicant's state to 'complete'.
                $stateID = State::where('code', 'complete')->value('id');
                $applicant->update(['state_id' => $stateID]);
            }
        } catch (Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error in handleScheduleState: ' . $e->getMessage());

            // Get the error message from the method
            $errorMessage = $this->getErrorMessage();

            // Send the error message to the applicant
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Reshedule
    |--------------------------------------------------------------------------
    */

    protected function handleRescheduleState($applicant, $body, $client, $to, $from, $token)
    {
        try {
            // Retrieve the latest interview for the applicant where the status is 'Reschedule', ordered by 'created_at'.
            $latestInterview = $applicant->interviews()
                ->where('status', 'Reschedule')
                ->latest('created_at')
                ->first();

            // Check if there is an interview available for the applicant.
            if ($latestInterview) {
                try {
                    // Attempt to parse the provided date and time using flexible formats.
                    $newDateTime = $this->parseDateInput($body);

                    // Check if the new date is on or after the current scheduled date
                    if ($newDateTime->lt($latestInterview->scheduled_date)) {
                        // If the new date is earlier than the scheduled date, send an error message
                        $messages = [
                            "Please enter a date after the current scheduled date: *" . $latestInterview->scheduled_date->format('d M Y') . "*."
                        ];
                    } else {
                        // If the new date is valid and later than the current scheduled date, update the interview
                        $latestInterview->reschedule_date = $newDateTime;
                        $latestInterview->save(); // Save the changes to the interview.

                        // Format the new date as "01 Sep 2024 15:00"
                        $formattedDate = $newDateTime->format('d M Y H:i');

                        // Send a confirmation message
                        $messages = [
                            "Thank you, we have noted the new date and time *" . ($formattedDate) . "*. We have notified the relevant parties and we will get back to you as soon as possible. 📆"
                        ];

                        // Update the applicant's state to 'complete' after noting the new interview date and time.
                        $stateID = State::where('code', 'complete')->value('id');
                        $applicant->update(['state_id' => $stateID]); // Update the state.
                    }
                } catch (\Exception $e) {
                    // If the date and time could not be parsed successfully (e.g., invalid format), inform the applicant.
                    $messages = [
                        "Please provide a valid *date* and *time* for your interview. For example, '01 Sep 2024 14:00'."
                    ];
                }

                // Send the appropriate messages (confirmation or error) to the applicant and log them.
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            } else {
                // If no interview is found, inform the applicant that no interviews are available.
                $messages = ["No interviews found, have a wonderful day."];

                // Send the message to the applicant and log it.
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);

                // Update the applicant's state to 'complete' since no interviews are found.
                $stateID = State::where('code', 'complete')->value('id');
                $applicant->update(['state_id' => $stateID]);
            }
        } catch (Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error in handleRescheduleState: ' . $e->getMessage());

            // Get the error message from the method
            $errorMessage = $this->getErrorMessage();

            // Send the error message to the applicant
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Parse Date Input
    |--------------------------------------------------------------------------
    */

    protected function parseDateInput($input)
    {
        // Try parsing with Carbon's default flexibility first
        try {
            return Carbon::parse($input);
        } catch (\Exception $e) {
            // If it fails, continue to try custom formats below
        }

        // Define a list of common date formats
        $formats = [
            'Y-m-d H:i',        // 2024-02-20 14:00
            'd/m/Y H:i',        // 20/02/2024 14:00
            'm/d/Y H:i',        // 02/20/2024 14:00
            'Y-m-d',            // 2024-02-20
            'd/m/Y',            // 20/02/2024
            'm/d/Y',            // 02/20/2024
            'Y M d H:i',        // 2024 Feb 20 14:00
            'M d, Y H:i',       // Feb 20, 2024 14:00
            'M d Y H:i',        // Feb 20 2024 14:00
            'F d, Y h:i A',     // February 20, 2024 2:00 PM
            'F d Y h:i A',      // February 20 2024 2:00 PM
            'd M Y H:i',        // 20 Feb 2024 14:00
            'd M Y h:i A',      // 20 Feb 2024 2:00 PM
            'd M Y',            // 20 Feb 2024
            'F d Y',            // February 20 2024
            'Y-m-d H:i:s',      // 2024-02-20 14:00:00
            'd M Y H\hi',       // 20 Feb 2024 14h00
            'F d Y H\hi',       // February 20 2024 14h00
            'Y-m-d H\hi',       // 2024-02-20 14h00
        ];

        // Attempt to parse with each format
        foreach ($formats as $format) {
            try {
                return Carbon::createFromFormat($format, $input);
            } catch (\Exception $e) {
                // Continue to the next format
            }
        }

        // If none of the formats work, throw an exception
        throw new \Exception('Invalid date format');
    }

    /*
    |--------------------------------------------------------------------------
    | Calculate Score
    |--------------------------------------------------------------------------
    */

    protected function calculateScore($applicant)
    {
        // Initialize variables to store the total score and total weight
        $totalScore = 0;
        $totalWeight = 0;

        // Retrieve all the score weightings (likely from a database table)
        $weightings = ScoreWeighting::all(); // Fetch all weightings

        // Loop through each weighting to calculate the score
        foreach ($weightings as $weighting) {
            // Check if the score type is 'education_id' and apply custom logic
            if ($weighting->score_type == 'education_id') {
                // Get the education level from the applicant's data
                $educationLevel = $applicant->{$weighting->score_type} ?? 0;

                // Apply custom weight distribution based on the education level
                switch ($educationLevel) {
                    case 1: // Level 1 gets 0% of the weight
                        $percentage = 0;
                        break;
                    case 2:
                    case 3: // Levels 2 and 3 get 15% of the weight
                        $percentage = 0.15;
                        break;
                    case 4: // Level 4 gets 40% of the weight
                        $percentage = 0.40;
                        break;
                    case 5: // Level 5 gets 25% of the weight
                        $percentage = 0.25;
                        break;
                    case 6: // Level 6 gets 20% of the weight
                        $percentage = 0.20;
                        break;
                    default:
                        $percentage = 0;
                        break;
                }

                // Add the weighted score to the total score
                $totalScore += $percentage * $weighting->weight;

            // Check if the score type is 'duration_id' and apply custom logic
            } elseif ($weighting->score_type == 'duration_id') {
                // Get the duration value from the applicant's data
                $durationLevel = $applicant->{$weighting->score_type} ?? 0;

                // Apply custom weight distribution based on the duration level
                switch ($durationLevel) {
                    case 1: // Level 1 gets 0% of the weight
                        $percentage = 0;
                        break;
                    case 2: // Level 2 gets 10% of the weight
                        $percentage = 0.10;
                        break;
                    case 3: // Level 3 gets 15% of the weight
                        $percentage = 0.15;
                        break;
                    case 4: // Level 4 gets 20% of the weight
                        $percentage = 0.20;
                        break;
                    case 5: // Level 5 gets 25% of the weight
                        $percentage = 0.25;
                        break;
                    case 6: // Level 6 gets 30% of the weight
                        $percentage = 0.30;
                        break;
                    default:
                        $percentage = 0;
                        break;
                }

                // Add the weighted score to the total score
                $totalScore += $percentage * $weighting->weight;

            // Check if the score type is 'literacy_score', 'numeracy_score', or 'situational_score'
            } elseif (in_array($weighting->score_type, ['literacy_score', 'numeracy_score', 'situational_score'])) {
                // Get the applicant's score for the current score type
                $scoreValue = $applicant->{$weighting->score_type} ?? 0;
                $maxValue = $weighting->max_value;

                // Calculate the percentage score
                if ($maxValue > 0) {
                    $scorePercentage = ($scoreValue / $maxValue) * 100;

                    // Apply weight based on the percentage score
                    if ($scorePercentage >= 0 && $scorePercentage <= 30) {
                        $percentage = 0; // 0% of the weight for 0-30% score
                    } elseif ($scorePercentage > 30 && $scorePercentage <= 55) {
                        $percentage = 0.05; // 5% of the weight for 31-55% score
                    } elseif ($scorePercentage > 55 && $scorePercentage <= 70) {
                        $percentage = 0.20; // 20% of the weight for 56-70% score
                    } elseif ($scorePercentage > 70 && $scorePercentage <= 85) {
                        $percentage = 0.35; // 35% of the weight for 71-85% score
                    } elseif ($scorePercentage > 85) {
                        $percentage = 0.40; // 40% of the weight for >85% score
                    }

                    // Add the weighted score to the total score
                    $totalScore += $percentage * $weighting->weight;
                }

            // Check if the weighting has a condition (i.e., applies to a specific field and value)
            } elseif (!empty($weighting->condition_field)) {
                // Apply conditional logic: if the applicant's field matches the condition value, use the specified weight
                // Otherwise, use the fallback value as the score
                $scoreValue = $applicant->{$weighting->condition_field} == $weighting->condition_value
                    ? $weighting->weight
                    : $weighting->fallback_value;

                // Add the calculated score value to the total score
                $totalScore += $scoreValue;
            } else {
                // For numeric scoring (without a condition), handle the score calculation based on the score type and max value

                // Get the score value from the applicant's data, using the score type as the field name
                // Default to 0 if no value is present
                $scoreValue = $applicant->{$weighting->score_type} ?? 0;

                // Get the max value from the weighting record (used for percentage calculation)
                $maxValue = $weighting->max_value;

                // If the max value is greater than 0, calculate the percentage score and weight it accordingly
                if ($maxValue > 0) {
                    $percentage = ($scoreValue / $maxValue) * $weighting->weight;
                    $totalScore += $percentage; // Add the weighted score to the total score
                }
            }

            // Add the current weighting's weight to the total weight
            $totalWeight += $weighting->weight;
        }

        // Normalize the total score to a scale of 0 to 5
        // If total weight is greater than 0, divide total score by total weight, then multiply by 5
        // Otherwise, default to normalizing based on 100% scale
        $normalizedScore = $totalWeight > 0 ? ($totalScore / $totalWeight) * 5 : ($totalScore / 100) * 5;

        // Add 3 to the final score
        $finalScore = $normalizedScore + 3;

        // Round the normalized score to 2 decimal places and return it
        return round($finalScore, 2);
    }

    /*
    |--------------------------------------------------------------------------
    | Get Messages
    |--------------------------------------------------------------------------
    */

    protected function fetchStateMessages($stateCode)
    {
        // Retrieve messages from the database
        $stateID = State::where('code', $stateCode)->first()->id;

        // Check if the state is 'literacy' or 'numeracy'
        if (in_array($stateCode, ['literacy', 'numeracy'])) {
            return ChatTemplate::where('state_id', $stateID)
                ->select(['message', 'sort'])
                ->orderBy('sort')
                ->get()
                ->toArray(); // Returns both the 'message' and 'sort' columns as an array of arrays
        }

        // Retrieve messages and optionally the template names
        $messages = ChatTemplate::where('state_id', $stateID)
        ->orderBy('sort')
        ->with('interactiveOptions')
        ->get()
        ->toArray();

        return $messages;
    }

    /*
    |--------------------------------------------------------------------------
    | Send & Log Messages
    |--------------------------------------------------------------------------
    */

    public function sendAndLogMessages($applicant, $messages, $client, $to, $from, $token)
    {
        $lockKey = "wa_lock_{$to}";

        if (Cache::has($lockKey)) {
            Log::warning("Rate limit lock active for {$to}, skipping all messages.");
            return;
        }

        foreach ($messages as $messageData) { // Ensure $messageData is used to clarify it's an array from messages
            try {
                // Check if $messageData is a string and adjust accordingly
                if (is_string($messageData)) {
                    $body = $messageData;
                    $template = null;
                    $type = null;
                    $interactiveOptions = [];
                    $variables = [];
                } else {  // Assume $messageData is an array
                    $body = $messageData['message'];
                    $type = $messageData['type'];
                    $template = $messageData['template'] ?? null;
                    $variables = $messageData['variables'] ?? [];
                    $interactiveOptions = $messageData['interactive_options'] ?? [];
                }

                // Prepare the API URL
                $url = "https://graph.facebook.com/v23.0/$from/messages";

                // Initialize the payload with common elements
                $payload = [
                    'messaging_product' => 'whatsapp',
                    'to' => $to,
                    'type' => $type ? $type : 'text'
                ];

                // Conditional structure based on the type
                if ($type == 'template') {
                    $payload['template'] = [
                        'name' => $template,
                        'language' => ['code' => 'en_US'],
                        'components' => [
                            [
                                'type' => 'body',
                                'parameters' => array_map(function ($var) {
                                    return ['type' => 'text', 'text' => $var];
                                }, $variables)
                            ]
                        ]
                    ];
                } elseif ($type == 'interactive') {
                    $interactivePayload = [
                        'type' => 'list',
                        'body' => [
                            'text' => $body
                        ],
                        'footer' => [
                            'text' => 'Please select one of the options below:'
                        ],
                        'action' => [
                            'button' => 'Options',
                            'sections' => [
                                [
                                    'title' => 'Options',
                                    'rows' => array_map(function ($option) {
                                        return [
                                            'id' => (string)$option['value'], // Ensure value is a string
                                            'title' => $option['title'],
                                            'description' => $option['description']
                                        ];
                                    }, $interactiveOptions)
                                ]
                            ]
                        ]
                    ];

                    $payload['interactive'] = $interactivePayload;
                } else {
                    $payload['text'] = ['body' => $body];
                }

                // Send the message via Meta's WhatsApp API
                $response = $client->post($url, [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $token,
                        'Content-Type' => 'application/json'
                    ],
                    'body' => json_encode($payload)
                ]);

                // Extract the response body and decode it to capture the message ID
                $responseData = json_decode($response->getBody()->getContents(), true);
                $messageId = $responseData['messages'][0]['id'] ?? null; // Extract the message ID from the response

                // Log the outgoing message with the message ID, status as 'Sent', and template if applicable
                $this->logMessage($applicant->id, $body, 2, $messageId, 'Sent', $template);
            } catch (\GuzzleHttp\Exception\ClientException $e) {
                $responseBody = $e->getResponse()->getBody()->getContents();
                $errorData = json_decode($responseBody, true);
                $errorCode = $errorData['error']['code'] ?? null;

                if ($errorCode === 130429) {
                    // Global rate limit
                    Log::warning('Global rate limit hit for WhatsApp API. Sending fallback message directly.');

                    $rateLimitMessage = "⚠️ We're experiencing high traffic. Please wait 2 minutes before continuing your application.";

                    try {
                        $url = "https://graph.facebook.com/v23.0/$from/messages";
                        $payload = [
                            'messaging_product' => 'whatsapp',
                            'to' => $to,
                            'type' => 'text',
                            'text' => ['body' => $rateLimitMessage]
                        ];

                        $client->post($url, [
                            'headers' => [
                                'Authorization' => 'Bearer ' . $token,
                                'Content-Type' => 'application/json'
                            ],
                            'body' => json_encode($payload)
                        ]);

                        $this->logMessage($applicant->id, $rateLimitMessage, 2, null, 'Sent', null);
                    } catch (\Exception $ex) {
                        Log::error('Failed to send global rate-limit message: ' . $ex->getMessage());
                    }
                } elseif ($errorCode === 131056) {
                    // Pair-specific rate limit
                    Cache::put($lockKey, true, now()->addMinutes(5)); // block user for 1 min
                    Log::warning("Pair rate limit hit for applicant ID {$applicant->id} and number {$to}. Message skipped.");
                    return; // stop further messages for this user
                } else {
                    // Generic error handling
                    Log::error('Error in sendAndLogMessages: ' . $e->getMessage());

                    $errorMessage = $this->getErrorMessage();
                    sleep(1); // brief delay to reduce pressure on API

                    try {
                        $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
                    } catch (\Exception $ex) {
                        Log::error('Secondary error while sending fallback message: ' . $ex->getMessage());
                    }
                }
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Error Message
    |--------------------------------------------------------------------------
    */

    protected function getErrorMessage()
    {
        return "Something went wrong. Please try again later.";
    }
}
