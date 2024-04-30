<?php

namespace App\Services;

use Log;
use DateTime;
use Exception;
use Carbon\Carbon;
use App\Models\Chat;
use App\Models\State;
use App\Models\Language;
use App\Models\Applicant;
use App\Models\Notification;
use App\Models\ChatTemplate;
use App\Models\ScoreWeighting;
use App\Models\ChatTotalData;
use App\Models\ChatMonthlyData;
use App\Jobs\ProcessUserIdNumber;
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

            // Extract message details from a deeply nested structure
            $messageData = $data['entry'][0]['changes'][0]['value']['messages'][0];
            $phone = '+' . $messageData['from'];
            $from = '+' . $messageData['from'];

            // Determine message type and extract content
            $body = $messageData['text']['body'] ?? $messageData['button']['text'] ?? null;
            $latitude = $messageData['location']['latitude'] ?? null;
            $longitude = $messageData['location']['longitude'] ?? null;
            $mediaUrl = $messageData['media'][0]['url'] ?? null;
            $mediaId = $messageData['image']['id'] ?? null;

            // Fetch existing applicant or create a new one
            $applicant = $this->getOrCreateApplicant($phone);

            // Log the received message
            $this->logMessage($applicant->id, $body, 1);

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
                'transport', 
                'role', 
                'interviews.vacancy.position'
            ])
            ->where('phone', $phone)
            ->first();

            // If the applicant doesn't exist, create a new entry
            if (!$applicant) {
                $applicant = new Applicant([
                    'phone' => $phone,
                    'role_id' => 5,
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
    protected function logMessage($applicantID, $message, $type)
    {
        try {
            // Create a new chat entry with the provided message data
            $chat = new Chat([
                'applicant_id' => $applicantID,
                'message' => $message,
                'type_id' => $type,
            ]);
            $chat->save();

            $currentYear = Carbon::now()->year;
            $currentMonth = strtolower(Carbon::now()->format('M'));

            // Determine the fields to update based on the message type
            $totalField = $type == 1 ? 'total_incoming' : 'total_outgoing';
            $monthField = $type == 1 ? $currentMonth.'_incoming' : $currentMonth.'_outgoing';

            $yearlyData = ChatTotalData::firstOrCreate(
                ['year' => $currentYear]
            );

            // Increment the total and monthly counters
            $yearlyData->increment($totalField);
            $yearlyData->increment($monthField);

            // Find or create ChatMonthlyData entry
            $monthlyData = ChatMonthlyData::firstOrCreate(
                [
                    'chat_total_data_id' => $yearlyData->id,
                    'chat_type' => $type == 1 ? 'Incoming' : 'Outgoing',
                    'month' => ucwords($currentMonth),
                    'created_at' => Carbon::now()->startOfMonth()
                ],
                ['count' => 1] // Initial count value
            );

            // Increment the count
            $monthlyData->increment('count');

            // Save the ChatMonthlyData entry
            $monthlyData->save();
    
        } catch (Exception $e) {
            Log::error("Error in logMessage: {$e->getMessage()}");
            throw new Exception('There was an error logging the message. Please try again later.');
        }
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

            // If the elapsed time exceeds the delay or if the applicant's state has a '_checkpoint' suffix, 
            // update the state of the applicant
            if ($applicant->state_id > 2 && ($timeDifference > 15 || $applicant->checkpoint == 'Yes')) {
                // Set applicant checkpoint to 'Yes'
                $applicant->update(['checkpoint' => 'Yes']);
                $checkpointTriggered = true;

                // Get the checkpoint message
                $checkpointMessage = $this->fetchStateMessages('checkpoint');
                $this->sendAndLogMessages($applicant, $checkpointMessage, $client, $to, $from, $token);

                // Get the message of the state_id
                $stateID = $applicant->state_id;
                $state = State::where('id', $stateID)->value('code');

                if ($state == 'literacy' || $state == 'numeracy') {
                    // Get the current question from the pool without removing it.
                    $questionPool = ($state == 'literacy') ? 'literacy_question_pool' : 'numeracy_question_pool';
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
    public function processStateActions($applicant, $body, $latitude, $longitude, $mediaId) {
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
        
            case 'personal_information':
                $this->handlePersonalInformationState($applicant, $body, $client, $to, $from, $token);
                break;
        
            case 'first_name':
                $this->handleFirstNameState($applicant, $body, $client, $to, $from, $token);
                break;
        
            case 'last_name':
                $this->handleLastNameState($applicant, $body, $client, $to, $from, $token);
                break;
        
            case 'id_number':
                $this->handleIdNumberState($applicant, $body, $client, $to, $from, $token);
                break;

            case 'location':
                $this->handleLocationState($applicant, $body, $latitude, $longitude, $client, $to, $from, $token);
                break;

            case 'location_confirmation':
                $this->handleLocationConfirmationState($applicant, $body, $client, $to, $from, $token);
                break;

            case 'contact_number':
                $this->handleContactNumberState($applicant, $body, $client, $to, $from, $token);
                break;

            case 'additional_contact_number':
                $this->handleAdditionalContactNumberState($applicant, $body, $client, $to, $from, $token);
                break;

            case 'gender':
                $this->handleGenderState($applicant, $body, $client, $to, $from, $token);
                break;
        
            case 'race':
                $this->handleRaceState($applicant, $body, $client, $to, $from, $token);
                break;
        
            case 'has_email':
                $this->handleHasEmailState($applicant, $body, $client, $to, $from, $token);
                break;
        
            case 'email':
                $this->handleEmailState($applicant, $body, $client, $to, $from, $token);
                break;
        
            case 'has_tax':
                $this->handleHasTaxState($applicant, $body, $client, $to, $from, $token);
                break;
        
            case 'tax_number':
                $this->handleTaxNumberState($applicant, $body, $client, $to, $from, $token);
                break;
        
            case 'citizen':
                $this->handleCitizenState($applicant, $body, $client, $to, $from, $token);
                break;
        
            case 'foreign_national':
                $this->handleForeignNationalState($applicant, $body, $client, $to, $from, $token);
                break;
        
            case 'criminal':
                $this->handleCriminalState($applicant, $body, $client, $to, $from, $token);
                break;

            case 'avatar':
                $this->handleAvatarState($applicant, $body, $client, $to, $from, $token, $mediaId);
                break;
        
            case 'position':
                $this->handlePositionState($applicant, $body, $client, $to, $from, $token);
                break;
        
            case 'position_specify':
                $this->handlePositionSpecifyState($applicant, $body, $client, $to, $from, $token);
                break;
        
            case 'qualifications':
                $this->handleQualificationsState($applicant, $body, $client, $to, $from, $token);
                break;
        
            case 'school':
                $this->handleSchoolState($applicant, $body, $client, $to, $from, $token);
                break;
        
            case 'highest_qualification':
                $this->handleHighestQualificationState($applicant, $body, $client, $to, $from, $token);
                break;
        
            case 'training':
                $this->handleTrainingState($applicant, $body, $client, $to, $from, $token);
                break;
        
            case 'other_training':
                $this->handleOtherTrainingState($applicant, $body, $client, $to, $from, $token);
                break;
        
            case 'drivers_license':
                $this->handleDriversLicenseState($applicant, $body, $client, $to, $from, $token);
                break;
        
            case 'drivers_license_code':
                $this->handleDriversLicenseCodeState($applicant, $body, $client, $to, $from, $token);
                break;
        
            case 'read':
                $this->handleReadState($applicant, $body, $client, $to, $from, $token);
                break;

            case 'speak':
                $this->handleSpeakState($applicant, $body, $client, $to, $from, $token);
                break;
        
            case 'experience':
                $this->handleExperienceState($applicant, $body, $client, $to, $from, $token);
                break;
        
            case 'job_previous':
                $this->handleJobPreviousState($applicant, $body, $client, $to, $from, $token);
                break;
        
            case 'job_leave':
                $this->handleJobLeaveState($applicant, $body, $client, $to, $from, $token);
                break;
        
            case 'job_leave_specify':
                $this->handleJobLeaveSpecifyState($applicant, $body, $client, $to, $from, $token);
                break;
        
            case 'job_business':
                $this->handleJobBusinessState($applicant, $body, $client, $to, $from, $token);
                break;
        
            case 'job_position':
                $this->handleJobPositionState($applicant, $body, $client, $to, $from, $token);
                break;
        
            case 'job_term':
                $this->handleJobTermState($applicant, $body, $client, $to, $from, $token);
                break;
        
            case 'job_salary':
                $this->handleJobSalaryState($applicant, $body, $client, $to, $from, $token);
                break;
        
            case 'job_reference_name':
                $this->handleJobReferenceNameState($applicant, $body, $client, $to, $from, $token);
                break;
        
            case 'job_reference_phone':
                $this->handleJobReferencePhoneState($applicant, $body, $client, $to, $from, $token);
                break;
                
            case 'job_retrenched':
                $this->handleJobRetrenchedState($applicant, $body, $client, $to, $from, $token);
                break;
        
            case 'job_retrenched_specify':
                $this->handleJobRetrenchedSpecifyState($applicant, $body, $client, $to, $from, $token);
                break;
        
            case 'job_shoprite':
                $this->handleJobShopriteState($applicant, $body, $client, $to, $from, $token);
                break;
        
            case 'job_shoprite_position':
                $this->handleJobShopritePositionState($applicant, $body, $client, $to, $from, $token);
                break;
        
            case 'job_shoprite_position_specify':
                $this->handleJobShopritePositionSpecifyState($applicant, $body, $client, $to, $from, $token);
                break;
        
            case 'job_shoprite_leave':
                $this->handleJobShopriteLeaveState($applicant, $body, $client, $to, $from, $token);
                break;
        
            case 'punctuality':
                $this->handlePunctualityState($applicant, $body, $client, $to, $from, $token);
                break;
        
            case 'transport':
                $this->handleTransportState($applicant, $body, $client, $to, $from, $token);
                break;
        
            case 'transport_specify':
                $this->handleTransportSpecifyState($applicant, $body, $client, $to, $from, $token);
                break;
        
            case 'illness':
                $this->handleIllnessState($applicant, $body, $client, $to, $from, $token);
                break;
        
            case 'illness_specify':
                $this->handleIllnessSpecifyState($applicant, $body, $client, $to, $from, $token);
                break;
        
            case 'commencement':
                $this->handleCommencementState($applicant, $body, $client, $to, $from, $token);
                break;
        
            case 'reason':
                $this->handleReasonState($applicant, $body, $client, $to, $from, $token);
                break;
        
            case 'application_reason':
                $this->handleApplicationReasonState($applicant, $body, $client, $to, $from, $token);
                break;
        
            case 'application_reason_specify':
                $this->handleApplicationReasonSpecifyState($applicant, $body, $client, $to, $from, $token);
                break;
        
            case 'relocate':
                $this->handleRelocateState($applicant, $body, $client, $to, $from, $token);
                break;
        
            case 'relocate_town':
                $this->handleRelocateTownState($applicant, $body, $client, $to, $from, $token);
                break;
        
            case 'vacancy':
                $this->handleVacancyState($applicant, $body, $client, $to, $from, $token);
                break;
        
            case 'shift':
                $this->handleShiftState($applicant, $body, $client, $to, $from, $token);
                break;
        
            case 'has_bank_account':
                $this->handleHasBankAccountState($applicant, $body, $client, $to, $from, $token);
                break;
        
            case 'bank':
                $this->handleBankState($applicant, $body, $client, $to, $from, $token);
                break;
        
            case 'bank_specify':
                $this->handleBankSpecifyState($applicant, $body, $client, $to, $from, $token);
                break;
        
            case 'bank_number':
                $this->handleBankNumberState($applicant, $body, $client, $to, $from, $token);
                break;
        
            case 'expected_salary':
                $this->handleExpectedSalaryState($applicant, $body, $client, $to, $from, $token);
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

    protected function handleWelcomeState($applicant, $client, $to, $from, $token) {
        try {
            $messages = $this->fetchStateMessages('welcome');
            $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);

            $stateID = State::where('sort', ($applicant->state->sort + 1))->value('id');
            $applicant->update(['state_id' => $stateID]);
        } catch (Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error in handleWelcomeState: ' . $e->getMessage());
    
            // Get the error message from the method
            $errorMessage = $this->getErrorMessage();
    
            // Send the error message to the user
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Personal Information
    |--------------------------------------------------------------------------
    */

    protected function handlePersonalInformationState($applicant, $body, $client, $to, $from, $token) {
        try {
            // Handle the 'start' keyword
            if (strtolower($body) == 'start') {
                $messages = $this->fetchStateMessages('personal_information');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);

                $stateID = State::where('code', 'first_name')->value('id');
                $applicant->update(['state_id' => $stateID]);

                $messages = $this->fetchStateMessages('first_name');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            } else {
                $messages = $this->fetchStateMessages('welcome');
                $lastMessage = end($messages);
                $this->sendAndLogMessages($applicant, [$lastMessage], $client, $to, $from, $token);
            }
        } catch (Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error in handlePersonalInformationState: ' . $e->getMessage());
    
            // Get the error message from the method
            $errorMessage = $this->getErrorMessage();
    
            // Send the error message to the user
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | First Name
    |--------------------------------------------------------------------------
    */

    protected function handleFirstNameState($applicant, $body, $client, $to, $from, $token) {
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
    
            // Send the error message to the user
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Last Name
    |--------------------------------------------------------------------------
    */

    protected function handleLastNameState($applicant, $body, $client, $to, $from, $token) {
        try {
            // Check if the body does not contain any digit and has more than one character
            if (!preg_match('/\d/', $body) && strlen($body) > 1) {
                // Update the applicant's last name
                $applicant->update(['lastname' => ucwords(strtolower($body))]);

                $stateID = State::where('code', 'id_number')->value('id');
                $applicant->update(['state_id' => $stateID]);

                $messages = $this->fetchStateMessages('id_number');
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
    
            // Send the error message to the user
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | ID Number
    |--------------------------------------------------------------------------
    */

    protected function handleIdNumberState($applicant, $body, $client, $to, $from, $token) {
        try {
            if (preg_match('/^\d{13}$/', $body)) {
                // Update the applicant's id number
                $applicant->update(['id_number' => $body]);

                $stateID = State::where('code', 'location')->value('id');
                $applicant->update(['state_id' => $stateID]);

                $messages = $this->fetchStateMessages('location');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);

                ProcessUserIdNumber::dispatch(null, $applicant->id);
            } else {
                // Send a message prompting for a valid ID number
                $message = "Please enter a valid ID number:";
                $this->sendAndLogMessages($applicant, [$message], $client, $to, $from, $token);
            }
        } catch (Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error in handleIdNumberState: ' . $e->getMessage());

            // Get the error message from the method
            $errorMessage = $this->getErrorMessage();

            // Send the error message to the user
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Location
    |--------------------------------------------------------------------------
    */

    protected function handleLocationState($applicant, $body, $latitude, $longitude, $client, $to, $from, $token) {
        try {
            // Send the "Please give a second to verify your address" message
            $message = "Please give me a second to verify your address...";
            $this->sendAndLogMessages($applicant, [$message], $client, $to, $from, $token);

            $googleMapsService = new GoogleMapsService();

            if (isset($latitude) && isset($longitude)) {
                $applicant->update(['location' => $latitude.' '.$longitude]);
                $response = $googleMapsService->reverseGeocodeCoordinates(trim($latitude), trim($longitude));
            } else {
                $applicant->update(['location' => $body]);
                $response = $googleMapsService->geocodeAddress($applicant->location);
            }

            if ($response !== null) {
                $formattedAddress = $response['formatted_address'];
                $city = $response['city'] ?? null;
        
                $templateMessage = [
                    [
                        'message' => "I have picked up the address as:\n\n*$formattedAddress*\n\nPlease confirm that this is correct.",
                        'template' => "location",
                        'variables' => [$formattedAddress]
                    ]
                ];

                $this->sendAndLogMessages($applicant, $templateMessage, $client, $to, $from, $token);

                $latitude = $response['latitude'];
                $longitude = $response['longitude'];
        
                $applicant->update([
                    'location' => $formattedAddress,
                    'coordinates' => $latitude.','.$longitude,
                    'town_id' => $city  // update the applicant's town with the city
                ]);

                $stateID = State::where('code', 'location_confirmation')->value('id');
                $applicant->update(['state_id' => $stateID]);
            } else {
                // Send a message prompting for a valid address
                $message = "Sorry, we couldn't verify your address. Please enter your address again.";
                $this->sendAndLogMessages($applicant, [$message], $client, $to, $from, $token);
            }
        } catch (Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error in handleLocationState: ' . $e->getMessage());

            // Get the error message from the method
            $errorMessage = $this->getErrorMessage();

            // Send the error message to the user
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Location Confirmation
    |--------------------------------------------------------------------------
    */

    protected function handleLocationConfirmationState($applicant, $body, $client, $to, $from, $token) {
        try {            
            if (strtolower($body) === 'that is correct' || strtolower($body) === 'correct' || strtolower($body) === "that's correct") {
                // If the user confirms the address, move on to contact_number
                $stateID = State::where('code', 'contact_number')->value('id');
                $applicant->update(['state_id' => $stateID]);

                $confirmMessage = "Thank you for confirming your address.";
                $this->sendAndLogMessages($applicant, [$confirmMessage], $client, $to, $from, $token);

                $messages = $this->fetchStateMessages('contact_number');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            } elseif (strtolower($body) === 're-enter address' || strtolower($body) === 'no' || strtolower($body) === 'incorrect') {
                // If the user wants to re-enter the address, go back to location
                $message = "Please re-enter your address.";
                $this->sendAndLogMessages($applicant, [$message], $client, $to, $from, $token);

                $stateID = State::where('code', 'location')->value('id');
                $applicant->update(['state_id' => $stateID]);
            } else {
                // If the user's response is not recognized, ask them to confirm their choice again
                $message = "Please confirm your choice by replying with 'That's correct' or 'Re-enter address'.";
                $this->sendAndLogMessages($applicant, [$message], $client, $to, $from, $token);
            }
        } catch (Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error in handleLocationConfirmationState: ' . $e->getMessage());

            // Get the error message from the method
            $errorMessage = $this->getErrorMessage();

            // Send the error message to the user
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Contact Number
    |--------------------------------------------------------------------------
    */

    protected function handleContactNumberState($applicant, $body, $client, $to, $from, $token) {
        try {
            if (preg_match('/^\d{10}$/', $body)) {
                // Update the applicant's contact number
                $applicant->update(['contact_number' => $body]);

                $stateID = State::where('code', 'additional_contact_number')->value('id');
                $applicant->update(['state_id' => $stateID]);

                $messages = $this->fetchStateMessages('additional_contact_number');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            } else {
                // Send a message prompting for a valid contact number
                $message = "Please enter a valid 10-digit contact number:";
                $this->sendAndLogMessages($applicant, [$message], $client, $to, $from, $token);
            }
        } catch (Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error in handleContactNumberState: ' . $e->getMessage());

            // Get the error message from the method
            $errorMessage = $this->getErrorMessage();

            // Send the error message to the user
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Additional Contact Number
    |--------------------------------------------------------------------------
    */

    protected function handleAdditionalContactNumberState($applicant, $body, $client, $to, $from, $token) {
        try {
            if (preg_match('/^\d{10}$/', $body) || $body == '0' || strtolower($body) == 'no' || strtolower($body) == 'none') {
                // Update the applicant's additional contact number
                $applicant->update([
                    'additional_contact_number' => ($body === '0' || strtolower($body) === 'no' || strtolower($body) === 'none') ? null : $body,
                ]);  

                $stateID = State::where('code', 'gender')->value('id');
                $applicant->update(['state_id' => $stateID]);

                $messages = $this->fetchStateMessages('gender');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            } else {
                 // Send a message prompting for a valid additional contact number
                 $message = "Please enter a valid 10-digit contact number:";
                 $this->sendAndLogMessages($applicant, [$message], $client, $to, $from, $token);
            }
        } catch (Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error in handleAdditionalContactNumberState: ' . $e->getMessage());

            // Get the error message from the method
            $errorMessage = $this->getErrorMessage();

            // Send the error message to the user
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Gender
    |--------------------------------------------------------------------------
    */

    protected function handleGenderState($applicant, $body, $client, $to, $from, $token) {
        try {
            $gender = null;

            switch (strtolower($body)) {
                case '1':
                case 'male':
                    $gender = 1;
                    break;

                case '2':
                case 'female':
                    $gender = 2;
                    break;
            }

            if ($gender) {
                // Update the applicant's gender
                $applicant->update(['gender_id' => $gender]);

                $stateID = State::where('code', 'race')->value('id');
                $applicant->update(['state_id' => $stateID]);

                $messages = $this->fetchStateMessages('race');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            } else {
                // Send a message prompting for a valid gender
                $message = "Please select a valid option!";
                $this->sendAndLogMessages($applicant, [$message], $client, $to, $from, $token);

                $messages = $this->fetchStateMessages('gender');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            }
        } catch (Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error in handleGenderState: ' . $e->getMessage());

            // Get the error message from the method
            $errorMessage = $this->getErrorMessage();

            // Send the error message to the user
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Race
    |--------------------------------------------------------------------------
    */

    protected function handleRaceState($applicant, $body, $client, $to, $from, $token) {
        try {
            $race = null;

            switch (strtolower($body)) {
                case '1':
                case 'african':
                    $race = 1;
                    break;
                case '2':
                case 'asian':
                    $race = 2;
                    break;
                case '3':
                case 'coloured':
                    $race = 3;
                    break;
                case '4':
                case 'indian':
                    $race = 4;
                    break;
                case '5':
                case 'white':
                    $race = 5;
                    break;
            }

            if ($race) {
                // Update the applicant's race
                $applicant->update(['race_id' => $race]);

                $stateID = State::where('code', 'has_email')->value('id');
                $applicant->update(['state_id' => $stateID]);

                $messages = $this->fetchStateMessages('has_email');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            } else {
                // Send a message prompting for a valid race
                $message = "Please select a valid option!";
                $this->sendAndLogMessages($applicant, [$message], $client, $to, $from, $token);

                $messages = $this->fetchStateMessages('race');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            }
        } catch (Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error in handleRaceState: ' . $e->getMessage());

            // Get the error message from the method
            $errorMessage = $this->getErrorMessage();

            // Send the error message to the user
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Has Email
    |--------------------------------------------------------------------------
    */

    protected function handleHasEmailState($applicant, $body, $client, $to, $from, $token) {
        try {
            $hasEmail = null;

            switch (strtolower($body)) {
                case '1':
                case 'yes':
                    $hasEmail = 'Yes';
                    break;
                case '2':
                case 'no':
                    $hasEmail = 'No';
                    break;
            }

            if ($hasEmail && $hasEmail == 'Yes') {
                // Update the applicant's has email
                $applicant->update(['has_email' => $hasEmail]);

                $stateID = State::where('code', 'email')->value('id');
                $applicant->update(['state_id' => $stateID]);

                $messages = $this->fetchStateMessages('email');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);

            } elseif ($hasEmail && $hasEmail == 'No') {
                // Update the applicant's has email
                $applicant->update(['has_email' => $hasEmail]);

                $stateID = State::where('code', 'has_tax')->value('id');
                $applicant->update(['state_id' => $stateID]);

                $messages = $this->fetchStateMessages('has_tax');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            } else {
                // Send a message prompting for a valid has email
                $message = "Please select a valid option!";
                $this->sendAndLogMessages($applicant, [$message], $client, $to, $from, $token);

                $messages = $this->fetchStateMessages('has_email');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            }
        } catch (Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error in handleHasEmailState: ' . $e->getMessage());

            // Get the error message from the method
            $errorMessage = $this->getErrorMessage();

            // Send the error message to the user
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Email
    |--------------------------------------------------------------------------
    */

    protected function handleEmailState($applicant, $body, $client, $to, $from, $token) {
        try {
            if (filter_var($body, FILTER_VALIDATE_EMAIL)) {
                // Update the applicant's email
                $applicant->update(['email' => strtolower($body)]);

                $stateID = State::where('code', 'has_tax')->value('id');
                $applicant->update(['state_id' => $stateID]);

                $messages = $this->fetchStateMessages('has_tax');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            } else {
                // Send a message prompting for a valid email
                $message = "Please enter a valid email address:";
                $this->sendAndLogMessages($applicant, [$message], $client, $to, $from, $token);
            }  
        } catch (Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error in handleEmailState: ' . $e->getMessage());

            // Get the error message from the method
            $errorMessage = $this->getErrorMessage();

            // Send the error message to the user
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Has Tax Number
    |--------------------------------------------------------------------------
    */

    protected function handleHasTaxState($applicant, $body, $client, $to, $from, $token) {
        try {
            $hasTax = null;

            switch (strtolower($body)) {
                case '1':
                case 'yes':
                    $hasTax = 'Yes';
                    break;
                case '2':
                case 'no':
                    $hasTax = 'No';
                    break;
            }

            if ($hasTax && $hasTax == 'Yes') {
                // Update the applicant's has tax
                $applicant->update(['has_tax' => $hasTax]);

                $stateID = State::where('code', 'tax_number')->value('id');
                $applicant->update(['state_id' => $stateID]);

                $messages = $this->fetchStateMessages('tax_number');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            } elseif ($hasTax && $hasTax == 'No') {
                // Update the applicant's has tax
                $applicant->update(['has_tax' => $hasTax]);

                $stateID = State::where('code', 'citizen')->value('id');
                $applicant->update(['state_id' => $stateID]);

                $messages = $this->fetchStateMessages('citizen');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);                
            } else {
                // Send a message prompting for a valid has tax
                $message = "Please select a valid option!";
                $this->sendAndLogMessages($applicant, [$message], $client, $to, $from, $token);

                $messages = $this->fetchStateMessages('has_tax');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            }
        } catch (Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error in handleHasTaxState: ' . $e->getMessage());

            // Get the error message from the method
            $errorMessage = $this->getErrorMessage();

            // Send the error message to the user
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Tax Number
    |--------------------------------------------------------------------------
    */

    protected function handleTaxNumberState($applicant, $body, $client, $to, $from, $token) {
        try {
            if (preg_match('/^\d{10}$/', $body)) {
                // Update the applicant's has number
                $applicant->update(['tax_number' => $body]);

                $stateID = State::where('code', 'citizen')->value('id');
                $applicant->update(['state_id' => $stateID]);

                $messages = $this->fetchStateMessages('citizen');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            } else {
                // Send a message prompting for a valid tax number
                $message = "Please enter a valid 10-digit tax number:";
                $this->sendAndLogMessages($applicant, [$message], $client, $to, $from, $token);
            }
        } catch (Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error in handleTaxNumberState: ' . $e->getMessage());

            // Get the error message from the method
            $errorMessage = $this->getErrorMessage();

            // Send the error message to the user
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Citizen
    |--------------------------------------------------------------------------
    */

    protected function handleCitizenState($applicant, $body, $client, $to, $from, $token) {
        try {
            $citizen = null;

            switch (strtolower($body)) {
                case '1':
                case 'yes':
                    $citizen = 'Yes';
                    break;
                case '2':
                case 'no':
                    $citizen = 'No';
                    break;
            }

            if ($citizen == 'Yes') {
                // Update the applicant's citizen
                $applicant->update(['citizen' => $citizen]);

                $stateID = State::where('code', 'criminal')->value('id');
                $applicant->update(['state_id' => $stateID]);

                $messages = $this->fetchStateMessages('criminal');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            } elseif ($citizen == 'No') {
                // Update the applicant's citizen
                $applicant->update(['citizen' => $citizen]);

                $stateID = State::where('code', 'foreign_national')->value('id');
                $applicant->update(['state_id' => $stateID]);

                $messages = $this->fetchStateMessages('foreign_national');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            } else {
                // Send a message prompting for a valid citizen
                $message = "Please select a valid option!";
                $this->sendAndLogMessages($applicant, [$message], $client, $to, $from, $token);

                $messages = $this->fetchStateMessages('citizen');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            }
        } catch (Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error in handleCitizenState: ' . $e->getMessage());

            // Get the error message from the method
            $errorMessage = $this->getErrorMessage();

            // Send the error message to the user
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Foreign National
    |--------------------------------------------------------------------------
    */

    protected function handleForeignNationalState($applicant, $body, $client, $to, $from, $token) {
        try {
            $foreignNational = null;

            switch (strtolower($body)) {
                case '1':
                case 'yes':
                    $foreignNational = 'Yes';
                    break;
                case '2':
                case 'no':
                    $foreignNational = 'No';
                    break;
            }

            if ($foreignNational) {
                // Update the applicant's foreign national
                $applicant->update(['foreign_national' => $foreignNational]);

                $stateID = State::where('code', 'criminal')->value('id');
                $applicant->update(['state_id' => $stateID]);

                $messages = $this->fetchStateMessages('criminal');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            } else {
                // Send a message prompting for a valid citizen
                $message = "Please select a valid option!";
                $this->sendAndLogMessages($applicant, [$message], $client, $to, $from, $token);

                $messages = $this->fetchStateMessages('foreign_national');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            }
        } catch (Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error in handleForeignNationalState: ' . $e->getMessage());

            // Get the error message from the method
            $errorMessage = $this->getErrorMessage();

            // Send the error message to the user
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Criminal
    |--------------------------------------------------------------------------
    */

    protected function handleCriminalState($applicant, $body, $client, $to, $from, $token) {
        try {
            $criminal = null;

            switch (strtolower($body)) {
                case '1':
                case 'yes':
                    $criminal = 'Yes';
                    break;
                case '2':
                case 'no':
                    $criminal = 'No';
                    break;
            }

            if ($criminal) {
                // Update the applicant's criminal
                $applicant->update(['criminal' => $criminal]);

                $stateID = State::where('code', 'avatar')->value('id');
                $applicant->update(['state_id' => $stateID]);

                $messages = $this->fetchStateMessages('avatar');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            } else {
                // Send a message prompting for a valid citizen
                $message = "Please select a valid option!";
                $this->sendAndLogMessages($applicant, [$message], $client, $to, $from, $token);

                $messages = $this->fetchStateMessages('criminal');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            }
        } catch (Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error in handleCriminalState: ' . $e->getMessage());

            // Get the error message from the method
            $errorMessage = $this->getErrorMessage();

            // Send the error message to the user
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Avatar
    |--------------------------------------------------------------------------
    */

    protected function handleAvatarState($applicant, $body, $client, $to, $from, $token, $mediaId) {
        try {
            if (isset($mediaId)) {
                $url = "https://graph.facebook.com/v19.0/$mediaId";
                $response = $client->get($url, [
                    'headers' => ['Authorization' => "Bearer $token"]
                ]);

                if ($response->getStatusCode() == 200) {
                    $data = json_decode($response->getBody(), true);
                    $mediaUrl = $data['url'] ?? null;
    
                    if ($mediaUrl) {
                        $imageResponse = $client->get($mediaUrl, [
                            'headers' => ['Authorization' => "Bearer $token"]
                        ]);
                        $contentType = $imageResponse->getHeaderLine('Content-Type');
                        $allowedContentTypes = ['image/jpeg', 'image/png', 'image/jpg'];
    
                        if (in_array($contentType, $allowedContentTypes)) {
                            $fileContent = $imageResponse->getBody()->getContents();
    
                            $fileExtension = array_search($contentType, [
                                'jpg' => 'image/jpeg',
                                'jpg' => 'image/jpg',
                                'png' => 'image/png'
                            ]) ?: 'jpg'; // Default to jpg if no match found
    
                            $fileName = $applicant->firstname . ' ' . $applicant->lastname . '-' . time() . '.' . $fileExtension;
                            $filePath = public_path('/images/' . $fileName);
    
                            if (file_put_contents($filePath, $fileContent)) {
                                $applicant->update(['avatar' => '/images/' . $fileName]);
    
                                $stateID = State::where('code', 'position')->value('id');
                                $applicant->update(['state_id' => $stateID]);
    
                                $messages = $this->fetchStateMessages('position');
                                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
                            } else {
                                $errorMessage = "There was an issue uploading your picture. Please try again.";
                                $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
                            }
                        } else {
                            $errorMessage = "Invalid file type! Please provide a picture in .jpg, .jpeg, or .png format:";
                            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
                        }
                    } else {
                        $errorMessage = "Could not retrieve image URL from Meta.";
                        $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
                    }
                } else {
                    $errorMessage = "Failed to fetch media from Meta. Status code: " . $response->getStatusCode();
                    $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
                }
            } else {
                $errorMessage = "Please provide a picture of yourself in .jpg, .jpeg, or .png format:";
                $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
            }
        } catch (Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error in handlePictureState: ' . $e->getMessage());

            // Get the error message from the method
            $errorMessage = $this->getErrorMessage();

            // Send the error message to the user
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Position
    |--------------------------------------------------------------------------
    */

    protected function handlePositionState($applicant, $body, $client, $to, $from, $token) {
        try {
            $position = null;

            switch (strtolower($body)) {
                case '1':
                case 'any':
                    $position = 1;
                case '2':
                case 'assistant':
                    $position = 2;
                    break;
                case '3':
                case 'baker':
                    $position = 3;
                    break;
                case '4':
                case 'butcher/meat technician':
                case 'meat technician':
                case 'butcher':
                case 'meat':
                case 'technician':
                    $position = 4;
                    break;
                case '5':
                case 'cashier':
                    $position = 5;
                    break;
                case '6':
                case 'clerk':
                    $position = 6;
                    break;
                case '7':
                case 'deli, bakery or butchery assistant':
                case 'deli assistant':
                case 'bakery assistant':
                case 'butchery assistant':
                case 'deli':
                case 'bakery':
                case 'butchery':
                    $position = 7;
                    break;
                case '8':
                case 'general assistant':
                case 'general':
                    $position = 8;
                    break;
                case '9':
                case 'packer':
                    $position = 9;
                    break;
                case '10':
                case 'other':
                    $position = 10;
                    break;
            }

            if ($position && $position !== 10) {
                // Update the applicant's position
                $applicant->update(['position_id' => $position]);

                $stateID = State::where('code', 'qualifications')->value('id');
                $applicant->update(['state_id' => $stateID]);

                $messages = $this->fetchStateMessages('qualifications');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            } elseif ($position && $position == 10) {
                // Update the applicant's position
                $applicant->update(['position_id' => $position]);

                $stateID = State::where('code', 'position_specify')->value('id');
                $applicant->update(['state_id' => $stateID]);

                $messages = $this->fetchStateMessages('position_specify');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);            
            } else {
                // Send a message prompting for a valid position
                $message = "Please select a valid option!";
                $this->sendAndLogMessages($applicant, [$message], $client, $to, $from, $token);

                $messages = $this->fetchStateMessages('position');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            }
        } catch (Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error in handlePositionState: ' . $e->getMessage());

            // Get the error message from the method
            $errorMessage = $this->getErrorMessage();

            // Send the error message to the user
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Position Specify
    |--------------------------------------------------------------------------
    */

    protected function handlePositionSpecifyState($applicant, $body, $client, $to, $from, $token) {
        try {
            // Check if the body does not contain any digit and has more than two character
            if (!preg_match('/\d/', $body) && strlen($body) > 2) {
                // Update the applicant's position specify
                $applicant->update(['position_specify' => ucwords(strtolower($body))]);

                $stateID = State::where('code', 'qualifications')->value('id');
                $applicant->update(['state_id' => $stateID]);

                $messages = $this->fetchStateMessages('qualifications');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            } else {
                // Send a message prompting for a valid position specify
                $message = "Please enter a valid position:";
                $this->sendAndLogMessages($applicant, [$message], $client, $to, $from, $token);
            }
        } catch (Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error in handlePositionSpecifyState: ' . $e->getMessage());

            // Get the error message from the method
            $errorMessage = $this->getErrorMessage();

            // Send the error message to the user
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Qualifications
    |--------------------------------------------------------------------------
    */

    protected function handleQualificationsState($applicant, $body, $client, $to, $from, $token) {
        try {
            // Handle the 'start' keyword
            if (strtolower($body) == 'start') {
                $messages = $this->fetchStateMessages('school');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);

                $stateID = State::where('code', 'school')->value('id');
                $applicant->update(['state_id' => $stateID]);
            } else {
                $messages = $this->fetchStateMessages('qualifications');
                $lastMessage = end($messages);
                $this->sendAndLogMessages($applicant, [$lastMessage], $client, $to, $from, $token);
            }
        } catch (Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error in handleQualificationsState: ' . $e->getMessage());

            // Get the error message from the method
            $errorMessage = $this->getErrorMessage();

            // Send the error message to the user
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | School
    |--------------------------------------------------------------------------
    */

    protected function handleSchoolState($applicant, $body, $client, $to, $from, $token) {
        try {
            // Check if the body does not contain any digit and has more than two characters
            if (!preg_match('/\d/', $body) && strlen($body) > 2) {
                // Update the applicant's school
                $applicant->update(['school' => ucwords(strtolower($body))]);

                $stateID = State::where('code', 'highest_qualification')->value('id');
                $applicant->update(['state_id' => $stateID]);

                $messages = $this->fetchStateMessages('highest_qualification');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            } else {
                // Send a message prompting for a valid school
                $message = "Please enter a valid school:";
                $this->sendAndLogMessages($applicant, [$message], $client, $to, $from, $token);
            }
        } catch (Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error in handleSchoolState: ' . $e->getMessage());

            // Get the error message from the method
            $errorMessage = $this->getErrorMessage();

            // Send the error message to the user
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Highest Qualification
    |--------------------------------------------------------------------------
    */

    protected function handleHighestQualificationState($applicant, $body, $client, $to, $from, $token) {
        try {
            $education = null;

            switch (strtolower($body)) {
                case '1':
                case 'grade 9':
                    $education = 1;
                    break;
                case '2':
                case 'garde 10':
                    $education = 2;
                    break;
                case '3':
                case 'grade 11':
                    $education = 3;
                    break;
                case '4':
                case 'grade 12':
                    $education = 4;
                    break;
                case '5':
                case 'college/technicon':
                case 'college':
                case 'technicon':
                    $education = 5;
                    break;
                case '6':
                case 'university':
                    $education = 6;
                    break;                    
            }

            if ($education) {
                // Update the applicant's position
                $applicant->update(['education_id' => $education]);

                $stateID = State::where('code', 'training')->value('id');
                $applicant->update(['state_id' => $stateID]);

                $messages = $this->fetchStateMessages('training');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            } else {
                // Send a message prompting for a valid position
                $message = "Please select a valid option!";
                $this->sendAndLogMessages($applicant, [$message], $client, $to, $from, $token);

                $messages = $this->fetchStateMessages('highest_qualification');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            }
        } catch (Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error in handleHighestQualificationState: ' . $e->getMessage());

            // Get the error message from the method
            $errorMessage = $this->getErrorMessage();

            // Send the error message to the user
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Training
    |--------------------------------------------------------------------------
    */

    protected function handleTrainingState($applicant, $body, $client, $to, $from, $token) {
        try {
            $training = null;

            switch (strtolower($body)) {
                case '1':
                case 'yes':
                    $training = 'Yes';
                    break;
                case '2':
                case 'no':
                    $training = 'No';
                    break;
            }

            if ($training) {
                // Update the applicant's training
                $applicant->update(['training' => $training]);

                $stateID = State::where('code', 'other_training')->value('id');
                $applicant->update(['state_id' => $stateID]);

                $messages = $this->fetchStateMessages('other_training');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            } else {
                // Send a message prompting for a valid training
                $message = "Please select a valid option!";
                $this->sendAndLogMessages($applicant, [$message], $client, $to, $from, $token);

                $messages = $this->fetchStateMessages('training');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            }
        } catch (Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error in handleTrainingState: ' . $e->getMessage());

            // Get the error message from the method
            $errorMessage = $this->getErrorMessage();

            // Send the error message to the user
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Other Training
    |--------------------------------------------------------------------------
    */

    protected function handleOtherTrainingState($applicant, $body, $client, $to, $from, $token) {
        try {
            // Check if the body does not contain any digits
            if (!preg_match('/\d/', $body)) {
                // Update the applicant's other traininh
                $applicant->update(['other_training' => ucwords(strtolower($body))]);

                $stateID = State::where('code', 'drivers_license')->value('id');
                $applicant->update(['state_id' => $stateID]);

                $messages = $this->fetchStateMessages('drivers_license');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            } else {
                // Send a message prompting for a valid other training
                $message = "Please enter a valid training course or school:";
                $this->sendAndLogMessages($applicant, [$message], $client, $to, $from, $token);
            }
        } catch (Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error in handleOtherTrainingState: ' . $e->getMessage());

            // Get the error message from the method
            $errorMessage = $this->getErrorMessage();

            // Send the error message to the user
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Driver's License
    |--------------------------------------------------------------------------
    */

    protected function handleDriversLicenseState($applicant, $body, $client, $to, $from, $token) {
        try {
            $driversLicense = null;

            switch (strtolower($body)) {
                case '1':
                case 'yes':
                    $driversLicense = 'Yes';
                    break;
                case '2':
                case 'no':
                    $driversLicense = 'No';
                    break;
            }

            if ($driversLicense && $driversLicense == 'Yes') {
                // Update the applicant's drivers license
                $applicant->update(['drivers_license' => $driversLicense]);

                $stateID = State::where('code', 'drivers_license_code')->value('id');
                $applicant->update(['state_id' => $stateID]);

                $messages = $this->fetchStateMessages('drivers_license_code');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            } elseif ($driversLicense && $driversLicense == 'No') {
                // Update the applicant's drivers license
                $applicant->update(['drivers_license' => $driversLicense]);

                $stateID = State::where('code', 'read')->value('id');
                $applicant->update(['state_id' => $stateID]);

                $messages = $this->fetchStateMessages('read');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            } else {
                // Send a message prompting for a valid drivers license
                $message = "Please select a valid option!";
                $this->sendAndLogMessages($applicant, [$message], $client, $to, $from, $token);

                $messages = $this->fetchStateMessages('drivers_license');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            }
        } catch (Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error in handleDriversLicenseState: ' . $e->getMessage());

            // Get the error message from the method
            $errorMessage = $this->getErrorMessage();

            // Send the error message to the user
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Drivers License Code
    |--------------------------------------------------------------------------
    */

    protected function handleDriversLicenseCodeState($applicant, $body, $client, $to, $from, $token) {
        try {
            $licenseCode = null;

            switch (strtolower($body)) {
                case '1':
                case 'a':
                    $licenseCode = 'A';
                    break;
                case '2':
                case 'b':
                    $licenseCode = 'B';
                    break;
                case '3':
                case 'c1':
                    $licenseCode = 'C1';
                    break;
                case '4':
                case 'c':
                    $licenseCode = 'C';
                    break;
                case '5':
                case 'eb, ec1, ec':
                case 'eb':
                case 'ec1':
                case 'ec':
                    $licenseCode = 'EB, EC1, EC';
                    break;                   
            }

            if ($licenseCode) {
                // Update the applicant's position
                $applicant->update(['drivers_license_code' => $licenseCode]);

                $stateID = State::where('code', 'read')->value('id');
                $applicant->update(['state_id' => $stateID]);

                $messages = $this->fetchStateMessages('read');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            } else {
                // Send a message prompting for a valid position
                $message = "Please select a valid option!";
                $this->sendAndLogMessages($applicant, [$message], $client, $to, $from, $token);

                $messages = $this->fetchStateMessages('drivers_license_code');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            }
        } catch (Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error in handleDriversLicenseCodeState: ' . $e->getMessage());

            // Get the error message from the method
            $errorMessage = $this->getErrorMessage();

            // Send the error message to the user
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Read
    |--------------------------------------------------------------------------
    */

    protected function handleReadState($applicant, $body, $client, $to, $from, $token) {
        try {
            // Removing spaces and splitting the string by commas to get an array of selected language IDs
            $selectedLanguages = array_map('trim', explode(',', $body));
    
            // Validate if all provided IDs exist in the Language table and are numeric
            $validLanguageIds = Language::pluck('id')->all();
            foreach ($selectedLanguages as $id) {
                if (!is_numeric($id) || !in_array($id, $validLanguageIds)) {
                    $message = "Invalid selection. Please provide valid language numbers separated by a comma.";
                    $this->sendAndLogMessages($applicant, [$message], $client, $to, $from, $token);
                    return;
                }
            }

            // Prepare the data to sync including timestamps
            $syncData = [];
            foreach ($selectedLanguages as $id) {
                $syncData[$id] = ['created_at' => now(), 'updated_at' => now()];
            }
    
            // Sync the selected languages with the applicant
            $applicant->readLanguages()->sync($syncData);
    
            // Move to the next state
            $stateID = State::where('code', 'speak')->value('id');
            $applicant->update(['state_id' => $stateID]);
    
            $messages = $this->fetchStateMessages('speak');
            $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
        } catch (Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error in handleReadState: ' . $e->getMessage());

            // Get the error message from the method
            $errorMessage = $this->getErrorMessage();

            // Send the error message to the user
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Speak
    |--------------------------------------------------------------------------
    */

    protected function handleSpeakState($applicant, $body, $client, $to, $from, $token) {
        try {
            // Removing spaces and splitting the string by commas to get an array of selected language IDs
            $selectedLanguages = array_map('trim', explode(',', $body));
    
            // Validate if all provided IDs exist in the Language table and are numeric
            $validLanguageIds = Language::pluck('id')->all();
            foreach ($selectedLanguages as $id) {
                if (!is_numeric($id) || !in_array($id, $validLanguageIds)) {
                    $message = "Invalid selection. Please provide valid language numbers separated by a comma.";
                    $this->sendAndLogMessages($applicant, [$message], $client, $to, $from, $token);
                    return;
                }
            }
    
            // Prepare the data to sync including timestamps
            $syncData = [];
            foreach ($selectedLanguages as $id) {
                $syncData[$id] = ['created_at' => now(), 'updated_at' => now()];
            }
    
            // Sync the selected languages with the applicant
            $applicant->speakLanguages()->sync($syncData);
    
            // Move to the next state
            $stateID = State::where('code', 'experience')->value('id');
            $applicant->update(['state_id' => $stateID]);
    
            $messages = $this->fetchStateMessages('experience');
            $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
        } catch (Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error in handleSpeakState: ' . $e->getMessage());

            // Get the error message from the method
            $errorMessage = $this->getErrorMessage();

            // Send the error message to the user
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Experience
    |--------------------------------------------------------------------------
    */

    protected function handleExperienceState($applicant, $body, $client, $to, $from, $token) {
        try {
            try {
                // Handle the 'start' keyword
                if (strtolower($body) == 'start') {
                    $messages = $this->fetchStateMessages('job_previous');
                    $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
    
                    $stateID = State::where('code', 'job_previous')->value('id');
                    $applicant->update(['state_id' => $stateID]);
                } else {
                    $messages = $this->fetchStateMessages('experience');
                    $lastMessage = end($messages);
                    $this->sendAndLogMessages($applicant, [$lastMessage], $client, $to, $from, $token);
                }
            } catch (Exception $e) {
                // Log the error for debugging purposes
                Log::error('Error in handleExperienceState: ' . $e->getMessage());
    
                // Get the error message from the method
                $errorMessage = $this->getErrorMessage();
    
                // Send the error message to the user
                $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
            }
        } catch (Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error in handleExperienceState: ' . $e->getMessage());

            // Get the error message from the method
            $errorMessage = $this->getErrorMessage();

            // Send the error message to the user
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Job Previous
    |--------------------------------------------------------------------------
    */

    protected function handleJobPreviousState($applicant, $body, $client, $to, $from, $token) {
        try {
            $jobPrevious = null;

            switch (strtolower($body)) {
                case '1':
                case 'yes':
                    $jobPrevious = 'Yes';
                    break;
                case '2':
                case 'no':
                    $jobPrevious = 'No';
                    break;
            }

            if ($jobPrevious && $jobPrevious == 'Yes') {
                // Update the applicant's previous job
                $applicant->update(['job_previous' => $jobPrevious]);

                $stateID = State::where('code', 'job_leave')->value('id');
                $applicant->update(['state_id' => $stateID]);

                $messages = $this->fetchStateMessages('job_leave');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);

            } elseif ($jobLeave && $jobPrevious == 'No') {
                // Update the applicant's previous job
                $applicant->update(['job_previous' => $jobPrevious]);

                $stateID = State::where('code', 'punctuality')->value('id');
                $applicant->update(['state_id' => $stateID]);

                $messages = $this->fetchStateMessages('punctuality');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            } else {
                // Send a message prompting for a valid previous job
                $message = "Please select a valid option!";
                $this->sendAndLogMessages($applicant, [$message], $client, $to, $from, $token);

                $messages = $this->fetchStateMessages('job_previous');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            }
        } catch (Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error in handleJobPreviousState: ' . $e->getMessage());

            // Get the error message from the method
            $errorMessage = $this->getErrorMessage();

            // Send the error message to the user
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Job Leave
    |--------------------------------------------------------------------------
    */

    protected function handleJobLeaveState($applicant, $body, $client, $to, $from, $token) {
        try {
            $jobLeave = null;

            switch (strtolower($body)) {
                case '1':
                case 'salary was not enough':
                    $jobLeave = 1;
                    break;
                case '2':
                case 'i did not enjoy it':
                    $jobLeave = 2;
                    break;
                case '3':
                case 'i moved away':
                    $jobLeave = 3;
                    break;
                case '4':
                case 'i fell pregnant':
                    $jobLeave = 4;
                    break;
                case '5':
                case 'i was dismissed':
                    $jobLeave = 5;
                    break;
                case '6':
                case 'it was just a temporary/seasonal job':
                    $jobLeave = 6;
                    break;
                case '7':
                case 'got another job':
                case 'general':
                    $jobLeave = 7;
                    break;
                case '8':
                case 'other':
                    $jobLeave = 8;
                    break;
                    
            }

            if ($jobLeave && $jobLeave !== 9) {
                // Update the applicant's job leave
                $applicant->update(['reason_id' => $jobLeave]);

                $stateID = State::where('code', 'job_business')->value('id');
                $applicant->update(['state_id' => $stateID]);

                $messages = $this->fetchStateMessages('job_business');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            } elseif ($jobLeave && $jobLeave == 9) {
                // Update the applicant's job leave
                $applicant->update(['reason_id' => $jobLeave]);

                $stateID = State::where('code', 'job_leave_specify')->value('id');
                $applicant->update(['state_id' => $stateID]);

                $messages = $this->fetchStateMessages('job_leave_specify');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);            
            } else {
                // Send a message prompting for a valid job leave
                $message = "Please select a valid option!";
                $this->sendAndLogMessages($applicant, [$message], $client, $to, $from, $token);

                $messages = $this->fetchStateMessages('job_leave');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            }
        } catch (Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error in handleJobLeaveState: ' . $e->getMessage());

            // Get the error message from the method
            $errorMessage = $this->getErrorMessage();

            // Send the error message to the user
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Job Leave Specify
    |--------------------------------------------------------------------------
    */

    protected function handleJobLeaveSpecifyState($applicant, $body, $client, $to, $from, $token) {
        try {
            // Check if the body does not contain any digit and has more than two characters
            if (!preg_match('/\d/', $body) && strlen($body) > 2) {
                // Update the applicant's job leave specify
                $applicant->update(['job_leave_specify' => $body]);

                $stateID = State::where('code', 'job_business')->value('id');
                $applicant->update(['state_id' => $stateID]);

                $messages = $this->fetchStateMessages('job_business');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            } else {
                // Send a message prompting for a valid reason
                $message = "Please enter a valid reason:";
                $this->sendAndLogMessages($applicant, [$message], $client, $to, $from, $token);
            }
        } catch (Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error in handleJobLeaveSpecifyState: ' . $e->getMessage());

            // Get the error message from the method
            $errorMessage = $this->getErrorMessage();

            // Send the error message to the user
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Job Business
    |--------------------------------------------------------------------------
    */

    protected function handleJobBusinessState($applicant, $body, $client, $to, $from, $token) {
        try {
            // Check if the body does not contain any digit and has more than one character
            if (!preg_match('/\d/', $body) && strlen($body) > 1) {
                // Update the applicant's job business
                $applicant->update(['job_business' => ucwords(strtolower($body))]);

                $stateID = State::where('code', 'job_position')->value('id');
                $applicant->update(['state_id' => $stateID]);

                $messages = $this->fetchStateMessages('job_position');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            } else {
                // Send a message prompting for a valid business
                $message = "Please enter a valid business:";
                $this->sendAndLogMessages($applicant, [$message], $client, $to, $from, $token);
            }
        } catch (Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error in handleJobBusinessState: ' . $e->getMessage());

            // Get the error message from the method
            $errorMessage = $this->getErrorMessage();

            // Send the error message to the user
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Job Position
    |--------------------------------------------------------------------------
    */

    protected function handleJobPositionState($applicant, $body, $client, $to, $from, $token) {
        try {
            // Check if the body does not contain any digit and has more than two characters
            if (!preg_match('/\d/', $body) && strlen($body) > 2) {
                // Update the applicant's job business
                $applicant->update(['job_position' => ucwords(strtolower($body))]);

                $stateID = State::where('code', 'job_term')->value('id');
                $applicant->update(['state_id' => $stateID]);

                $messages = $this->fetchStateMessages('job_term');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            } else {
                // Send a message prompting for a valid business
                $message = "Please enter a valid position:";
                $this->sendAndLogMessages($applicant, [$message], $client, $to, $from, $token);
            }
        } catch (Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error in handleJobPositionState: ' . $e->getMessage());

            // Get the error message from the method
            $errorMessage = $this->getErrorMessage();

            // Send the error message to the user
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Job Term
    |--------------------------------------------------------------------------
    */

    protected function handleJobTermState($applicant, $body, $client, $to, $from, $token) {
        try {
            $jobTerm = null;

            switch (strtolower($body)) {
                case '1':
                case 'one month or less':
                    $jobTerm = 1;
                    break;
                case '2':
                case 'two to six months':
                    $jobTerm = 2;
                    break;
                case '3':
                case 'seven months to a year':
                    $jobTerm = 3;
                    break;
                case '4':
                case 'one to two years':
                    $jobTerm = 4;
                    break;
                case '5':
                case 'two to five years':
                    $jobTerm = 5;
                    break;
                case '6':
                case 'more than five years':
                    $jobTerm = 6;
                    break;                    
            }

            if ($jobTerm) {
                // Update the applicant's job term
                $applicant->update(['duration_id' => $jobTerm]);

                $stateID = State::where('code', 'job_salary')->value('id');
                $applicant->update(['state_id' => $stateID]);

                $messages = $this->fetchStateMessages('job_salary');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            } else {
                // Send a message prompting for a valid job term
                $message = "Please select a valid option!";
                $this->sendAndLogMessages($applicant, [$message], $client, $to, $from, $token);

                $messages = $this->fetchStateMessages('job_term');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            }
        } catch (Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error in handleJobTermState: ' . $e->getMessage());

            // Get the error message from the method
            $errorMessage = $this->getErrorMessage();

            // Send the error message to the user
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Job Salary
    |--------------------------------------------------------------------------
    */

    protected function handleJobSalaryState($applicant, $body, $client, $to, $from, $token) {
        try {
            // Strip spaces
            $body = preg_replace('/\s+/', '', $body);

            // Strip "R", "rand", or "p/m"
            $body = str_ireplace(['R', 'rand', 'p/m'], '', $body);

            // Check if the body contains only digits and has more than two characters
            if (ctype_digit($body) && strlen($body) > 2) {
                // Update the applicant's job salary
                $applicant->update(['job_salary' => $body]);

                $stateID = State::where('code', 'job_reference_name')->value('id');
                $applicant->update(['state_id' => $stateID]);

                $messages = $this->fetchStateMessages('job_reference_name');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            } else {
                // Send a message prompting for a valid salary
                $message = "Please enter a valid salary:";
                $this->sendAndLogMessages($applicant, [$message], $client, $to, $from, $token);
            }
        } catch (Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error in handleJobSalaryState: ' . $e->getMessage());

            // Get the error message from the method
            $errorMessage = $this->getErrorMessage();

            // Send the error message to the user
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Job Reference Name
    |--------------------------------------------------------------------------
    */

    protected function handleJobReferenceNameState($applicant, $body, $client, $to, $from, $token) {
        try {
            // Check if the body does not contain any digit and has more than one character
            if (!preg_match('/\d/', $body) && strlen($body) > 1) {
                // Update the applicant's job reference name
                $applicant->update(['job_reference_name' => ucwords(strtolower($body))]);

                $stateID = State::where('code', 'job_reference_phone')->value('id');
                $applicant->update(['state_id' => $stateID]);

                $messages = $this->fetchStateMessages('job_reference_phone');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            } else {
                // Send a message prompting for a valid job reference name
                $message = "Please enter a valid name:";
                $this->sendAndLogMessages($applicant, [$message], $client, $to, $from, $token);
            }
        } catch (Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error in handleJobReferenceNameState: ' . $e->getMessage());

            // Get the error message from the method
            $errorMessage = $this->getErrorMessage();

            // Send the error message to the user
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Job Reference Phone
    |--------------------------------------------------------------------------
    */

    protected function handleJobReferencePhoneState($applicant, $body, $client, $to, $from, $token) {
        try {
            if (preg_match('/^\d{10}$/', $body)) {
                // Update the applicant's job reference contact number
                $applicant->update(['job_reference_phone' => $body]);

                $stateID = State::where('code', 'job_retrenched')->value('id');
                $applicant->update(['state_id' => $stateID]);

                $messages = $this->fetchStateMessages('job_retrenched');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            } else {
                // Send a message prompting for a valid contact number
                $message = "Please enter a valid 10-digit contact number:";
                $this->sendAndLogMessages($applicant, [$message], $client, $to, $from, $token);
            }
        } catch (Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error in handleJobReferencePhoneState: ' . $e->getMessage());

            // Get the error message from the method
            $errorMessage = $this->getErrorMessage();

            // Send the error message to the user
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Job Retrenched
    |--------------------------------------------------------------------------
    */

    protected function handleJobRetrenchedState($applicant, $body, $client, $to, $from, $token) {
        try {
            $jobRetrench = null;

            switch (strtolower($body)) {
                case '1':
                case 'dismissed':
                    $jobRetrench = 1;
                    break;
                case '2':
                case 'retrenched':
                    $jobRetrench = 2;
                    break;
                case '3':
                case 'never been dismissed or retrenched':
                case 'never':
                case 'no':
                    $jobRetrench = 3;
                    break;                    
            }

            if ($jobRetrench && $jobRetrench == 3) {
                // Update the applicant's retrenchment id 
                $applicant->update(['retrenchment_id' => $jobRetrench]);

                $stateID = State::where('code', 'job_shoprite')->value('id');
                $applicant->update(['state_id' => $stateID]);

                $messages = $this->fetchStateMessages('job_shoprite');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            } elseif ($jobRetrench && $jobRetrench == 1 || $jobRetrench == 2) {
                // Update the applicant's retrenchment id 
                $applicant->update(['retrenchment_id' => $jobRetrench]);

                $stateID = State::where('code', 'job_retrenched_specify')->value('id');
                $applicant->update(['state_id' => $stateID]);

                $messages = $this->fetchStateMessages('job_retrenched_specify');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            } else {
                // Send a message prompting for a valid job retrenchment
                $message = "Please select a valid option!";
                $this->sendAndLogMessages($applicant, [$message], $client, $to, $from, $token);

                $messages = $this->fetchStateMessages('job_retrenched');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            }
        } catch (Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error in handleJobReferencePhoneState: ' . $e->getMessage());

            // Get the error message from the method
            $errorMessage = $this->getErrorMessage();

            // Send the error message to the user
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Job Retrenched Specify
    |--------------------------------------------------------------------------
    */

    protected function handleJobRetrenchedSpecifyState($applicant, $body, $client, $to, $from, $token) {
        try {
            // Check if the body does not contain any digit and has more than two characters
            if (!preg_match('/\d/', $body) && strlen($body) > 2) {
                // Update the applicant's job_retrenched specify
                $applicant->update(['job_retrenched_specify' => $body]);

                $stateID = State::where('code', 'job_shoprite')->value('id');
                $applicant->update(['state_id' => $stateID]);

                $messages = $this->fetchStateMessages('job_shoprite');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            } else {
                // Send a message prompting for a valid reason
                $message = "Please enter a valid reason:";
                $this->sendAndLogMessages($applicant, [$message], $client, $to, $from, $token);
            }
        } catch (Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error in handleJobRetrenchedSpecifyState: ' . $e->getMessage());

            // Get the error message from the method
            $errorMessage = $this->getErrorMessage();

            // Send the error message to the user
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Job Shoprite
    |--------------------------------------------------------------------------
    */

    protected function handleJobShopriteState($applicant, $body, $client, $to, $from, $token) {
        try {
            $jobShoprite = null;

            switch (strtolower($body)) {
                case '0':
                case 'no':
                    $jobShoprite = 'no';
                    break;
                case '1':
                case 'checkers':
                    $jobShoprite = 1;
                    break;
                case '2':
                case 'checkers food':
                    $jobShoprite = 2;
                    break;
                case '3':
                case 'checkers hyper':
                    $jobShoprite = 3;
                    break;
                case '4':
                case 'checkers sixty60':
                    $jobShoprite = 4;
                    break;
                case '5':
                case 'house & home':
                    $jobShoprite = 5;
                    break;
                case '6':
                case 'knect':
                    $jobShoprite = 6;
                    break;
                case '7':
                case 'LiquorShop':
                    $jobShoprite = 7;
                    break;
                case '8':
                case 'littleme':
                    $jobShoprite = 8;
                    break;
                case '9':
                case 'medirite':
                    $jobShoprite = 9;
                    break;
                case '10':
                case 'ok franchise':
                    $jobShoprite = 10;
                    break;
                case '11':
                case 'ok furniture':
                    $jobShoprite = 11;
                    break;
                case '12':
                case 'outdoor':
                    $jobShoprite = 12;
                    break;
                case '13':
                case 'petshop':
                    $jobShoprite = 13;
                    break;
                case '14':
                case 'shoprite':
                    $jobShoprite = 14;
                    break;
                case '15':
                case 'uniq':
                    $jobShoprite = 15;
                    break;
                case '16':
                case 'usave':
                    $jobShoprite = 16;
                    break;              
            }

            if ($jobShoprite && $jobShoprite == 'no') {
                $stateID = State::where('code', 'punctuality')->value('id');
                $applicant->update(['state_id' => $stateID]);

                $messages = $this->fetchStateMessages('punctuality');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            } elseif ($jobShoprite && $jobShoprite !== 0) {
                // Update the applicant's brand worked at
                $applicant->update(['brand_id' => $jobShoprite]);

                $stateID = State::where('code', 'job_shoprite_position')->value('id');
                $applicant->update(['state_id' => $stateID]);

                $messages = $this->fetchStateMessages('job_shoprite_position');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);            
            } else {
                // Send a message prompting for a valid brand worked at
                $message = "Please select a valid option!";
                $this->sendAndLogMessages($applicant, [$message], $client, $to, $from, $token);

                $messages = $this->fetchStateMessages('job_shoprite');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            }
        } catch (Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error in handleJobShopriteState: ' . $e->getMessage());

            // Get the error message from the method
            $errorMessage = $this->getErrorMessage();

            // Send the error message to the user
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Job Shoprite Position
    |--------------------------------------------------------------------------
    */

    protected function handleJobShopritePositionState($applicant, $body, $client, $to, $from, $token) {
        try {
            $jobShopritePosition = null;

            switch (strtolower($body)) {
                case '1':
                case 'assistant':
                    $jobShopritePosition = 2;
                    break;
                case '2':
                case 'baker':
                    $jobShopritePosition = 3;
                    break;
                case '3':
                case 'butcher/meat technician':
                case 'meat technician':
                case 'butcher':
                case 'meat':
                case 'technician':
                    $jobShopritePosition = 4;
                    break;
                case '4':
                case 'cashier':
                    $jobShopritePosition = 5;
                    break;
                case '5':
                case 'clerk':
                    $jobShopritePosition = 6;
                    break;
                case '6':
                case 'deli, bakery or butchery assistant':
                case 'deli assistant':
                case 'bakery assistant':
                case 'butchery assistant':
                case 'deli':
                case 'bakery':
                case 'butchery':
                    $jobShopritePosition = 7;
                    break;
                case '7':
                case 'general assistant':
                case 'general':
                    $jobShopritePosition = 8;
                    break;
                case '8':
                case 'packer':
                    $jobShopritePosition = 9;
                    break;
                case '9':
                case 'other':
                    $jobShopritePosition = 10;
                    break;                    
            }

            if ($jobShopritePosition && $jobShopritePosition !== 10) {
                // Update the applicant's position
                $applicant->update(['previous_job_position_id' => $jobShopritePosition]);

                $stateID = State::where('code', 'job_shoprite_leave')->value('id');
                $applicant->update(['state_id' => $stateID]);

                $messages = $this->fetchStateMessages('job_shoprite_leave');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            } elseif ($jobShopritePosition && $jobShopritePosition == 10) {
                // Update the applicant's position
                $applicant->update(['previous_job_position_id' => $jobShopritePosition]);

                $stateID = State::where('code', 'job_shoprite_position_specify')->value('id');
                $applicant->update(['state_id' => $stateID]);

                $messages = $this->fetchStateMessages('job_shoprite_position_specify');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);            
            } else {
                // Send a message prompting for a valid position
                $message = "Please select a valid option!";
                $this->sendAndLogMessages($applicant, [$message], $client, $to, $from, $token);

                $messages = $this->fetchStateMessages('job_shoprite_position');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            }
        } catch (Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error in handleJobShopritePositionState: ' . $e->getMessage());

            // Get the error message from the method
            $errorMessage = $this->getErrorMessage();

            // Send the error message to the user
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Job Shoprite Position Specify
    |--------------------------------------------------------------------------
    */

    protected function handleJobShopritePositionSpecifyState($applicant, $body, $client, $to, $from, $token) {
        try {
            // Check if the body does not contain any digits and has more than two characters
            if (!preg_match('/\d/', $body) && strlen($body) > 2) {
                // Update the applicant's job shoprite job leave
                $applicant->update(['job_shoprite_position_specify' => ucwords(strtolower($body))]);

                $stateID = State::where('code', 'job_shoprite_leave')->value('id');
                $applicant->update(['state_id' => $stateID]);

                $messages = $this->fetchStateMessages('job_shoprite_leave');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            } else {
                // Send a message prompting for a valid position
                $message = "Please enter a valid position:";
                $this->sendAndLogMessages($applicant, [$message], $client, $to, $from, $token);
            }
        } catch (Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error in handleJobShopritePositionSpecifyState: ' . $e->getMessage());

            // Get the error message from the method
            $errorMessage = $this->getErrorMessage();

            // Send the error message to the user
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Job Shoprite Leave
    |--------------------------------------------------------------------------
    */

    protected function handleJobShopriteLeaveState($applicant, $body, $client, $to, $from, $token) {
        try {
            // Check if the body does not contain any digits and has more than two characters
            if (!preg_match('/\d/', $body) && strlen($body) > 2) {
                // Update the applicant's job shoprite position specify
                $applicant->update(['job_shoprite_leave' => $body]);

                $stateID = State::where('code', 'punctuality')->value('id');
                $applicant->update(['state_id' => $stateID]);

                $messages = $this->fetchStateMessages('punctuality');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            } else {
                // Send a message prompting for a valid reason
                $message = "Please enter a valid position:";
                $this->sendAndLogMessages($applicant, [$message], $client, $to, $from, $token);
            }
        } catch (Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error in handleJobShopriteLeaveState: ' . $e->getMessage());

            // Get the error message from the method
            $errorMessage = $this->getErrorMessage();

            // Send the error message to the user
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Punctuality
    |--------------------------------------------------------------------------
    */

    protected function handlePunctualityState($applicant, $body, $client, $to, $from, $token) {
        try {
            // Handle the 'start' keyword
            if (strtolower($body) == 'start') {
                $messages = $this->fetchStateMessages('transport');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);

                $stateID = State::where('code', 'transport')->value('id');
                $applicant->update(['state_id' => $stateID]);
            } else {
                $messages = $this->fetchStateMessages('punctuality');
                $lastMessage = end($messages);
                $this->sendAndLogMessages($applicant, [$lastMessage], $client, $to, $from, $token);
            }
        } catch (Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error in handlePunctualityState: ' . $e->getMessage());

            // Get the error message from the method
            $errorMessage = $this->getErrorMessage();

            // Send the error message to the user
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Transport
    |--------------------------------------------------------------------------
    */

    protected function handleTransportState($applicant, $body, $client, $to, $from, $token) {
        try {
            $transport = null;

            switch (strtolower($body)) {
                case '1':
                case 'bicycle':
                    $transport = 1;
                    break;
                case '2':
                case 'bus':
                    $transport = 2;
                    break;
                case '3':
                case 'hitchhike':
                    $transport = 3;
                    break;
                case '4':
                case 'lift club':
                case 'lift':
                    $transport = 4;
                    break;
                case '5':
                case 'own car':
                case 'own':
                case 'car':
                    $transport = 5;
                    break;
                case '6':
                case 'taxi':
                    $transport = 6;
                    break;
                case '7':
                case 'train':
                    $transport = 7;
                    break;
                case '8':
                case 'walk':
                    $transport = 8;
                    break;
                case '9':
                case 'other':
                    $transport = 9;
                    break;                   
            }

            if ($transport && $transport !== 9) {
                // Update the applicant's transport
                $applicant->update(['transport_id' => $transport]);

                $stateID = State::where('code', 'illness')->value('id');
                $applicant->update(['state_id' => $stateID]);

                $messages = $this->fetchStateMessages('illness');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            } elseif ($transport && $transport == 9) {
                // Update the applicant's transport
                $applicant->update(['transport_id' => $transport]);

                $stateID = State::where('code', 'transport_specify')->value('id');
                $applicant->update(['state_id' => $stateID]);

                $messages = $this->fetchStateMessages('transport_specify');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);            
            } else {
                // Send a message prompting for a valid position
                $message = "Please select a valid option!";
                $this->sendAndLogMessages($applicant, [$message], $client, $to, $from, $token);

                $messages = $this->fetchStateMessages('transport');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            }
        } catch (Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error in handleTransportState: ' . $e->getMessage());

            // Get the error message from the method
            $errorMessage = $this->getErrorMessage();

            // Send the error message to the user
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Transport Specify
    |--------------------------------------------------------------------------
    */

    protected function handleTransportSpecifyState($applicant, $body, $client, $to, $from, $token) {
        try {
            // Check if the body does not contain any digits and has more than two characters
            if (!preg_match('/\d/', $body) && strlen($body) > 2) {
                // Update the applicant's transport specify
                $applicant->update(['transport_specify' => ucwords(strtolower($body))]);

                $stateID = State::where('code', 'illness')->value('id');
                $applicant->update(['state_id' => $stateID]);

                $messages = $this->fetchStateMessages('illness');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            } else {
                // Send a message prompting for a valid transport
                $message = "Please enter a valid transport:";
                $this->sendAndLogMessages($applicant, [$message], $client, $to, $from, $token);
            }
        } catch (Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error in handleTransportSpecifyState: ' . $e->getMessage());

            // Get the error message from the method
            $errorMessage = $this->getErrorMessage();

            // Send the error message to the user
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Illness
    |--------------------------------------------------------------------------
    */

    protected function handleIllnessState($applicant, $body, $client, $to, $from, $token) {
        try {
            $illness = null;

            switch (strtolower($body)) {
                case '1':
                case 'chronic illness':
                case 'chronic':
                    $illness = 1;
                    break;
                case '2':
                case 'disease':
                    $illness = 2;
                    break;
                case '3':
                case 'disability':
                    $illness = 3;
                    break;
                case '4':
                case 'none':
                case 'no':
                    $illness = 4;
                    break;              
            }

            if ($illness && $illness == 4) {
                // Update the applicant's illness
                $applicant->update(['disability_id' => $illness]);

                $stateID = State::where('code', 'commencement')->value('id');
                $applicant->update(['state_id' => $stateID]);

                $messages = $this->fetchStateMessages('commencement');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            } elseif ($illness && $illness !== 4) {
                // Update the applicant's ilness
                $applicant->update(['disability_id' => $illness]);

                $stateID = State::where('code', 'illness_specify')->value('id');
                $applicant->update(['state_id' => $stateID]);

                $messages = $this->fetchStateMessages('illness_specify');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);            
            } else {
                // Send a message prompting for a valid illness
                $message = "Please select a valid option!";
                $this->sendAndLogMessages($applicant, [$message], $client, $to, $from, $token);

                $messages = $this->fetchStateMessages('illness');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            }
        } catch (Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error in handleIllnessState: ' . $e->getMessage());

            // Get the error message from the method
            $errorMessage = $this->getErrorMessage();

            // Send the error message to the user
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Illness Specify
    |--------------------------------------------------------------------------
    */

    protected function handleIllnessSpecifyState($applicant, $body, $client, $to, $from, $token) {
        try {
            // Check if the body does not contain any digits and has more than one characters
            if (!preg_match('/\d/', $body) && strlen($body) > 1) {
                // Update the applicant's transport specify
                $applicant->update(['illness_specify' => $body]);

                $stateID = State::where('code', 'commencement')->value('id');
                $applicant->update(['state_id' => $stateID]);

                $messages = $this->fetchStateMessages('commencement');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            } else {
                // Send a message prompting for a valid illness
                $message = "Please enter a valid illness:";
                $this->sendAndLogMessages($applicant, [$message], $client, $to, $from, $token);
            }
        } catch (Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error in handleIllnessSpecifyState: ' . $e->getMessage());

            // Get the error message from the method
            $errorMessage = $this->getErrorMessage();

            // Send the error message to the user
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Commencement
    |--------------------------------------------------------------------------
    */

    protected function handleCommencementState($applicant, $body, $client, $to, $from, $token) {
        try {
            try {
                // Attempt to parse using DateTime constructor
                $date = new DateTime($body);
            } catch (Exception $e) {
                // Log the error for debugging purposes
                Log::error('Error in date creation: ' . $e->getMessage());

                // Send a message prompting for a valid date
                $message = "Please enter a valid date:";
                $this->sendAndLogMessages($applicant, [$message], $client, $to, $from, $token);
            }
            
            $date = new DateTime($body);

            if ($date) {
                // format the date
                $formattedDate = $date->format('Ymd');

                // Update the applicant's commencement date
                $applicant->update(['commencement' => $formattedDate]); 

                $stateID = State::where('code', 'reason')->value('id');
                $applicant->update(['state_id' => $stateID]);

                $messages = $this->fetchStateMessages('reason');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            } else {
                // Send a message prompting for a valid date
                $message = "Please enter a valid date:";
                $this->sendAndLogMessages($applicant, [$message], $client, $to, $from, $token);
            }
        } catch (Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error in handleCommencementState: ' . $e->getMessage());

            // Get the error message from the method
            $errorMessage = $this->getErrorMessage();

            // Send the error message to the user
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Reason
    |--------------------------------------------------------------------------
    */

    protected function handleReasonState($applicant, $body, $client, $to, $from, $token) {
        try {
            // Handle the 'start' keyword
            if (strtolower($body) == 'start') {
                $messages = $this->fetchStateMessages('application_reason');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);

                $stateID = State::where('code', 'application_reason')->value('id');
                $applicant->update(['state_id' => $stateID]);
            } else {
                $messages = $this->fetchStateMessages('reason');
                $lastMessage = end($messages);
                $this->sendAndLogMessages($applicant, [$lastMessage], $client, $to, $from, $token);
            }
        } catch (Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error in handleReasonState: ' . $e->getMessage());

            // Get the error message from the method
            $errorMessage = $this->getErrorMessage();

            // Send the error message to the user
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Application Reason
    |--------------------------------------------------------------------------
    */

    protected function handleApplicationReasonState($applicant, $body, $client, $to, $from, $token) {
        try {
            $application = null;

            switch (strtolower($body)) {
                case '1':
                case 'job':
                    $application = 1;
                    break;
                case '2':
                case 'seasonal job':
                case 'seasonal':
                    $application = 2;
                    break;
                case '3':
                case 'formal internship':
                case 'formal':
                case 'internship':
                    $application = 3;
                    break;
                case '4':
                case 'learnership':
                    $application = 4;
                    break;
                case '5':
                case 'co-operative training':
                case 'co-operative':
                case 'training':
                    $application = 5;
                    break; 
                case '6':
                case 'other':
                    $application = 6;
                    break;          
            }

            if ($application && $application !== 6) {
                // Update the applicant's job type
                $applicant->update(['type_id' => $application]);

                $stateID = State::where('code', 'relocate')->value('id');
                $applicant->update(['state_id' => $stateID]);

                $messages = $this->fetchStateMessages('relocate');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            } elseif ($application && $application == 6) {
                // Update the applicant's job type
                $applicant->update(['type_id' => $application]);

                $stateID = State::where('code', 'application_reason_specify')->value('id');
                $applicant->update(['state_id' => $stateID]);

                $messages = $this->fetchStateMessages('application_reason_specify');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);            
            } else {
                // Send a message prompting for a valid application reason
                $message = "Please select a valid option!";
                $this->sendAndLogMessages($applicant, [$message], $client, $to, $from, $token);

                $messages = $this->fetchStateMessages('application_reason');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            }
        } catch (Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error in handleApplicationReasonState: ' . $e->getMessage());

            // Get the error message from the method
            $errorMessage = $this->getErrorMessage();

            // Send the error message to the user
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Application Reason Specify
    |--------------------------------------------------------------------------
    */

    protected function handleApplicationReasonSpecifyState($applicant, $body, $client, $to, $from, $token) {
        try {
            // Check if the body does not contain any digits and has more than two characters
            if (!preg_match('/\d/', $body) && strlen($body) > 2) {
                // Update the applicant's application reason specify
                $applicant->update(['application_reason_specify' => $body]);

                $stateID = State::where('code', 'relocate')->value('id');
                $applicant->update(['state_id' => $stateID]);

                $messages = $this->fetchStateMessages('relocate');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            } else {
                // Send a message prompting for a valid reason
                $message = "Please enter a valid reason:";
                $this->sendAndLogMessages($applicant, [$message], $client, $to, $from, $token);
            }
        } catch (Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error in handleApplicationReasonSpecifyState: ' . $e->getMessage());

            // Get the error message from the method
            $errorMessage = $this->getErrorMessage();

            // Send the error message to the user
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Relocate
    |--------------------------------------------------------------------------
    */

    protected function handleRelocateState($applicant, $body, $client, $to, $from, $token) {
        try {
            $relocate = null;

            switch (strtolower($body)) {
                case '1':
                case 'yes':
                    $relocate = 'Yes';
                    break;
                case '2':
                case 'no':
                    $relocate = 'No';
                    break;
            }

            if ($relocate && $relocate == 'Yes') {
                // Update the applicant's relocate
                $applicant->update(['relocate' => $relocate]);

                $stateID = State::where('code', 'relocate_town')->value('id');
                $applicant->update(['state_id' => $stateID]);

                $messages = $this->fetchStateMessages('relocate_town');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            } elseif ($relocate && $relocate == 'No') {
                // Update the applicant's relocate
                $applicant->update(['relocate' => $relocate]);

                $stateID = State::where('code', 'vacancy')->value('id');
                $applicant->update(['state_id' => $stateID]);

                $messages = $this->fetchStateMessages('vacancy');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            } else {
                // Send a message prompting for a valid relocate
                $message = "Please select a valid option!";
                $this->sendAndLogMessages($applicant, [$message], $client, $to, $from, $token);

                $messages = $this->fetchStateMessages('relocate');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            }
        } catch (Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error in handleRelocateState: ' . $e->getMessage());

            // Get the error message from the method
            $errorMessage = $this->getErrorMessage();

            // Send the error message to the user
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Relocate Town
    |--------------------------------------------------------------------------
    */

    protected function handleRelocateTownState($applicant, $body, $client, $to, $from, $token) {
        try {
            // Check if the body does not contain any digits and has more than one character
            if (!preg_match('/\d/', $body) && strlen($body) > 1) {
                // Update the applicant's relocate town
                $applicant->update(['relocate_town' => ucwords(strtolower($body))]);

                $stateID = State::where('code', 'vacancy')->value('id');
                $applicant->update(['state_id' => $stateID]);

                $messages = $this->fetchStateMessages('vacancy');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            } else {
                // Send a message prompting for a valid relocate town
                $message = "Please enter a valid town or city:";
                $this->sendAndLogMessages($applicant, [$message], $client, $to, $from, $token);
            }
        } catch (Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error in handleTownState: ' . $e->getMessage());

            // Get the error message from the method
            $errorMessage = $this->getErrorMessage();

            // Send the error message to the user
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Vacancy
    |--------------------------------------------------------------------------
    */

    protected function handleVacancyState($applicant, $body, $client, $to, $from, $token) {
        try {
            $vacancy = null;

            switch (strtolower($body)) {
                case '1':
                case 'yes':
                    $vacancy = 'Yes';
                    break;
                case '2':
                case 'no':
                    $vacancy = 'No';
                    break;
            }

            if ($vacancy) {
                // Update the applicant's vacancy
                $applicant->update(['vacancy' => $vacancy]);

                $stateID = State::where('code', 'shift')->value('id');
                $applicant->update(['state_id' => $stateID]);

                $messages = $this->fetchStateMessages('shift');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            } else {
                // Send a message prompting for a valid vacancy
                $message = "Please select a valid option!";
                $this->sendAndLogMessages($applicant, [$message], $client, $to, $from, $token);

                $messages = $this->fetchStateMessages('vacancy');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            }
        } catch (Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error in handleVacancyState: ' . $e->getMessage());

            // Get the error message from the method
            $errorMessage = $this->getErrorMessage();

            // Send the error message to the user
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Shift
    |--------------------------------------------------------------------------
    */

    protected function handleShiftState($applicant, $body, $client, $to, $from, $token) {
        try {
            $shift = null;

            switch (strtolower($body)) {
                case '1':
                case 'yes':
                    $shift = 'Yes';
                    break;
                case '2':
                case 'no':
                    $shift = 'No';
                    break;
            }

            if ($shift) {
                // Update the applicant's shift
                $applicant->update(['shift' => $shift]);

                $stateID = State::where('code', 'has_bank_account')->value('id');
                $applicant->update(['state_id' => $stateID]);

                $messages = $this->fetchStateMessages('has_bank_account');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            } else {
                // Send a message prompting for a valid shift
                $message = "Please select a valid option!";
                $this->sendAndLogMessages($applicant, [$message], $client, $to, $from, $token);

                $messages = $this->fetchStateMessages('shift');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            }
        } catch (Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error in handleShiftState: ' . $e->getMessage());

            // Get the error message from the method
            $errorMessage = $this->getErrorMessage();

            // Send the error message to the user
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Has Bank Account
    |--------------------------------------------------------------------------
    */

    protected function handleHasBankAccountState($applicant, $body, $client, $to, $from, $token) {
        try {
            $hasBankAccount = null;

            switch (strtolower($body)) {
                case '1':
                case 'yes':
                    $hasBankAccount = 'Yes';
                    break;
                case '2':
                case 'no':
                    $hasBankAccount = 'No';
                    break;
            }

            if ($hasBankAccount && $hasBankAccount == 'Yes') {
                // Update the applicant's has bank account
                $applicant->update(['has_bank_account' => $hasBankAccount]);

                $stateID = State::where('code', 'bank')->value('id');
                $applicant->update(['state_id' => $stateID]);

                $messages = $this->fetchStateMessages('bank');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            } elseif ($hasBankAccount && $hasBankAccount == 'No') {
                // Update the applicant's has bank account
                $applicant->update(['has_bank_account' => $hasBankAccount]);

                $stateID = State::where('code', 'expected_salary')->value('id');
                $applicant->update(['state_id' => $stateID]);

                $messages = $this->fetchStateMessages('expected_salary');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            } else {
                // Send a message prompting for a valid has bank account
                $message = "Please select a valid option!";
                $this->sendAndLogMessages($applicant, [$message], $client, $to, $from, $token);

                $messages = $this->fetchStateMessages('has_bank_account');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            }
        } catch (Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error in handleHasBankAccountState: ' . $e->getMessage());

            // Get the error message from the method
            $errorMessage = $this->getErrorMessage();

            // Send the error message to the user
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Bank
    |--------------------------------------------------------------------------
    */

    protected function handleBankState($applicant, $body, $client, $to, $from, $token) {
        try {
            $bank = null;

            switch (strtolower($body)) {
                case '1':
                case 'absa bank':
                case 'absa':
                    $bank = 1;
                    break;
                case '2':
                case 'african bank':
                case 'african':
                    $bank = 2;
                    break;
                case '3':
                case 'bidvest bank':
                case 'bidvest':
                    $bank = 3;
                    break;
                case '4':
                case 'capitec bank':
                case 'capitec':
                    $bank = 4;
                    break;
                case '5':
                case 'discovery bank':
                case 'discovery':
                    $bank = 5;
                    break;
                case '6':
                case 'first national bank':
                case 'fnb':
                    $bank = 6;
                    break;
                case '7':
                case 'nedbank':
                    $bank = 7;
                    break;
                case '8':
                case 'standard bank':
                    $bank = 8;
                    break;
                case '9':
                case 'other':
                    $bank = 9;
                    break;                   
            }

            if ($bank && $bank !== 9) {
                // Update the applicant's bank
                $applicant->update(['bank_id' => $bank]);

                $stateID = State::where('code', 'bank_number')->value('id');
                $applicant->update(['state_id' => $stateID]);

                $messages = $this->fetchStateMessages('bank_number');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            } elseif ($bank && $bank == 9) {
                // Update the applicant's bank
                $applicant->update(['bank_id' => $bank]);

                $stateID = State::where('code', 'bank_specify')->value('id');
                $applicant->update(['state_id' => $stateID]);

                $messages = $this->fetchStateMessages('bank_specify');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);            
            } else {
                // Send a message prompting for a valid bank
                $message = "Please select a valid option!";
                $this->sendAndLogMessages($applicant, [$message], $client, $to, $from, $token);

                $messages = $this->fetchStateMessages('bank');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            }
        } catch (Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error in handleBankState: ' . $e->getMessage());

            // Get the error message from the method
            $errorMessage = $this->getErrorMessage();

            // Send the error message to the user
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Bank Specify
    |--------------------------------------------------------------------------
    */

    protected function handleBankSpecifyState($applicant, $body, $client, $to, $from, $token) {
        try {
            // Check if the body does not contain any digits and has more than two characters
            if (!preg_match('/\d/', $body) && strlen($body) > 2) {
                // Update the applicant's relocate town
                $applicant->update(['bank_specify' => ucwords(strtolower($body))]);

                $stateID = State::where('code', 'bank_number')->value('id');
                $applicant->update(['state_id' => $stateID]);

                $messages = $this->fetchStateMessages('bank_number');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            } else {
                // Send a message prompting for a valid relocate town
                $message = "Please enter a valid bank:";
                $this->sendAndLogMessages($applicant, [$message], $client, $to, $from, $token);
            }
        } catch (Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error in handleBankSpecifyState: ' . $e->getMessage());

            // Get the error message from the method
            $errorMessage = $this->getErrorMessage();

            // Send the error message to the user
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Bank Number
    |--------------------------------------------------------------------------
    */

    protected function handleBankNumberState($applicant, $body, $client, $to, $from, $token) {
        try {
            // Check if the body does not contain any digits and has more than two characters
            if (preg_match('/^\d{6,}$/', $body)) {
                // Update the applicant's bank number
                $applicant->update(['bank_number' => $body]);

                $stateID = State::where('code', 'expected_salary')->value('id');
                $applicant->update(['state_id' => $stateID]);

                $messages = $this->fetchStateMessages('expected_salary');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            } else {
                // Send a message prompting for a bank number
                $message = "Please enter a valid bank number:";
                $this->sendAndLogMessages($applicant, [$message], $client, $to, $from, $token);
            }
        } catch (Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error in handleBankNumberState: ' . $e->getMessage());

            // Get the error message from the method
            $errorMessage = $this->getErrorMessage();

            // Send the error message to the user
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Expected Salary
    |--------------------------------------------------------------------------
    */

    protected function handleExpectedSalaryState($applicant, $body, $client, $to, $from, $token) {
        try {
            // Strip spaces
            $body = preg_replace('/\s+/', '', $body);

            // Strip "R", "rand", or "p/m"
            $body = str_ireplace(['R', 'rand', 'p/m'], '', $body);

            // Check if the body contains only digits and has more than two characters
            if (ctype_digit($body) && strlen($body) > 2) {
                // Update the applicant's expected salary
                $applicant->update(['expected_salary' => $body]);

                $stateID = State::where('code', 'literacy_start')->value('id');
                $applicant->update(['state_id' => $stateID]);

                $messages = $this->fetchStateMessages('literacy_start');
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            } else {
                // Send a message prompting for a expected salary
                $message = "Please enter a valid salary amount:";
                $this->sendAndLogMessages($applicant, [$message], $client, $to, $from, $token);
            }
        } catch (Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error in handleExpectedSalaryState: ' . $e->getMessage());

            // Get the error message from the method
            $errorMessage = $this->getErrorMessage();

            // Send the error message to the user
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Literacy Start
    |--------------------------------------------------------------------------
    */

    protected function handleLiteracyStartState($applicant, $body, $client, $to, $from, $token) {
        try {
            // Handle the 'start' keyword
            if (strtolower($body) == 'start') {
                $messages = $this->fetchStateMessages('literacy');

                if (count($messages) > 0) {
                    shuffle($messages);
                    $sortOrderValues = implode(',', array_column($messages, 'sort'));
                    $applicant->update([
                        'literacy_question_pool' => $sortOrderValues,
                        'literacy_score' => 0,
                        'literacy_questions' => count($messages)                 
                    ]);
        
                    $firstQuestionMessages = array_column($messages, 'message');
                    $firstQuestion = array_shift($firstQuestionMessages);

                    $this->sendAndLogMessages($applicant, [$firstQuestion], $client, $to, $from, $token);

                    $stateID = State::where('code', 'literacy')->value('id');
                    $applicant->update(['state_id' => $stateID]);
                } else {
                    // Send a message prompting no questions found
                    $message = "Sorry, we could not find any questions. Please try again later.";
                    $this->sendAndLogMessages($applicant, [$message], $client, $to, $from, $token);
                }
            } else {
                $messages = $this->fetchStateMessages('literacy_start');
                $lastMessage = end($messages);
                $this->sendAndLogMessages($applicant, [$lastMessage], $client, $to, $from, $token);
            }
        } catch (Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error in handleLiteracyStartState: ' . $e->getMessage());

            // Get the error message from the method
            $errorMessage = $this->getErrorMessage();

            // Send the error message to the user
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Literacy
    |--------------------------------------------------------------------------
    */

    protected function handleLiteracyState($applicant, $body, $client, $to, $from, $token) {
        try {
            // Extract the order of the literacy questions from the applicant's data.
            $sortOrderPool = explode(',', $applicant->literacy_question_pool);
            // Retrieve the current question's sort order.
            $currentQuestionSortOrder = array_shift($sortOrderPool);
        
            // Fetch the current question based on the sort order.
            $stateID = State::where('code', 'literacy')->value('id');
            $currentQuestion = ChatTemplate::where('state_id', $stateID)
                                           ->where('sort', $currentQuestionSortOrder)
                                           ->first();
        
            // Check if the user's response is one of the valid options ('a', 'b', 'c', or 'd').
            if (in_array(strtolower($body), ['a', 'b', 'c', 'd'])) {
                // If the user's answer matches the correct answer, increment the score.
                if (strtolower($currentQuestion->answer) == strtolower($body)) {
                    $applicant->update(['literacy_score' => $applicant->literacy_score + 1]);
                }
        
                // If there are more questions in the pool, present the next one.
                if (count($sortOrderPool) > 0) {
                    $nextQuestionSortOrder = $sortOrderPool[0];
                    $nextQuestion = ChatTemplate::where('state_id', $stateID)
                                                ->where('sort', $nextQuestionSortOrder)
                                                ->first();
                    
                    // Update the applicant's data with the remaining questions.
                    $applicant->update(['literacy_question_pool' => implode(',', $sortOrderPool)]);
        
                    // Send the next question to the user.
                    $nextQuestionText = $nextQuestion->message;
                    $this->sendAndLogMessages($applicant, [$nextQuestionText], $client, $to, $from, $token);
                } else {
                    // If all the questions have been answered, calculate the final score.
                    $correctAnswers = $applicant->literacy_score;
                    $literacyQuestions = $applicant->literacy_questions;
                    $applicant->update(['literacy' => "$correctAnswers/$literacyQuestions"]);
                    
                    // Move to the numeracy questions.
                    $stateID = State::where('code', 'numeracy_start')->value('id');
                    $applicant->update(['state_id' => $stateID]);
    
                    $messages = $this->fetchStateMessages('numeracy_start');
                    $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
                }
            } else {
                // If the user's response is not valid, prepend the current question back and inform the user.
                array_unshift($sortOrderPool, $currentQuestionSortOrder);
                $applicant->update(['literacy_question_pool' => implode(',', $sortOrderPool)]);
        
                $invalidAnswerMessage = "Please choose a valid option (a, b, c or d).";
                $this->sendAndLogMessages($applicant, [$invalidAnswerMessage], $client, $to, $from, $token);
            }
        } catch (Exception $e) {
            // Handle exceptions by logging the error and informing the user.
            Log::error('Error in handleLiteracyState: ' . $e->getMessage());
            $errorMessage = $this->getErrorMessage();
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Numeracy Start
    |--------------------------------------------------------------------------
    */

    protected function handleNumeracyStartState($applicant, $body, $client, $to, $from, $token) {
        try {
            // Handle the 'start' keyword
            if (strtolower($body) == 'start') {
                $messages = $this->fetchStateMessages('numeracy');

                if (count($messages) > 0) {
                    shuffle($messages);
                    $sortOrderValues = implode(',', array_column($messages, 'sort'));
                    $applicant->update([
                        'numeracy_question_pool' => $sortOrderValues,
                        'numeracy_score' => 0,
                        'numeracy_questions' => count($messages)                 
                    ]);

                    $firstQuestionMessages = array_column($messages, 'message');
                    $firstQuestion = array_shift($firstQuestionMessages);

                    $this->sendAndLogMessages($applicant, [$firstQuestion], $client, $to, $from, $token);

                    $stateID = State::where('code', 'numeracy')->value('id');
                    $applicant->update(['state_id' => $stateID]);
                } else {
                    // Send a message prompting no questions found
                    $message = "Sorry, we could not find any questions. Please try again later.";
                    $this->sendAndLogMessages($applicant, [$message], $client, $to, $from, $token);
                }
            } else {
                $messages = $this->fetchStateMessages('numeracy_start');
                $lastMessage = end($messages);
                $this->sendAndLogMessages($applicant, [$lastMessage], $client, $to, $from, $token);
            }
        } catch (Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error in handleNumeracyStartState: ' . $e->getMessage());

            // Get the error message from the method
            $errorMessage = $this->getErrorMessage();

            // Send the error message to the user
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Numeracy
    |--------------------------------------------------------------------------
    */

    protected function handleNumeracyState($applicant, $body, $client, $to, $from, $token) {
        try {
            // Extract the order of the numeracy questions from the applicant's data.
            $sortOrderPool = explode(',', $applicant->numeracy_question_pool);
            
            // Retrieve the current question's sort order.
            $currentQuestionSortOrder = array_shift($sortOrderPool);
            
            // Fetch the current question based on the sort order.
            $stateID = State::where('code', 'numeracy')->value('id');
            $currentQuestion = ChatTemplate::where('state_id', $stateID)
                                           ->where('sort', $currentQuestionSortOrder)
                                           ->first();
            
            // Check if the user's response is one of the valid options ('a', 'b', or 'c').
            if (in_array(strtolower($body), ['a', 'b', 'c'])) {
                // If the user's answer matches the correct answer, increment the score.
                if (strtolower($currentQuestion->answer) == strtolower($body)) {
                    $applicant->update(['numeracy_score' => $applicant->numeracy_score + 1]);
                }
                
                // If there are more questions in the pool, present the next one.
                if (count($sortOrderPool) > 0) {
                    $nextQuestionSortOrder = $sortOrderPool[0];
                    $nextQuestion = ChatTemplate::where('state_id', $stateID)
                                                ->where('sort', $nextQuestionSortOrder)
                                                ->first();
                    
                    // Update the applicant's data with the remaining questions.
                    $applicant->update(['numeracy_question_pool' => implode(',', $sortOrderPool)]);
                    
                    // Send the next question to the user.
                    $nextQuestionText = $nextQuestion->message;
                    $this->sendAndLogMessages($applicant, [$nextQuestionText], $client, $to, $from, $token);
                } else {
                    // If all the questions have been answered, calculate the final score.
                    $correctAnswers = $applicant->numeracy_score;
                    $numeracyQuestions = $applicant->numeracy_questions;
                    $applicant->update(['numeracy' => "$correctAnswers/$numeracyQuestions"]);
                    
                    // Move to the complete.
                    $stateID = State::where('code', 'complete')->value('id');
                    $applicant->update(['state_id' => $stateID]);
    
                    $messages = $this->fetchStateMessages('complete');
                    $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
                }
            } else {
                // If the user's response is not valid, prepend the current question back and inform the user.
                array_unshift($sortOrderPool, $currentQuestionSortOrder);
                $applicant->update(['numeracy_question_pool' => implode(',', $sortOrderPool)]);
                
                $invalidAnswerMessage = "Please choose a valid option (a, b, or c).";
                $this->sendAndLogMessages($applicant, [$invalidAnswerMessage], $client, $to, $from, $token);
            }
        } catch (Exception $e) {
            // Handle exceptions by logging the error and informing the user.
            Log::error('Error in handleNumeracyState: ' . $e->getMessage());
            $errorMessage = $this->getErrorMessage();
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Complete
    |--------------------------------------------------------------------------
    */

    protected function handleCompleteState($applicant, $body, $client, $to, $from, $token) {
        try {
            // Check if the score is null and then set it
            if (is_null($applicant->score)) {
                $score = $this->calculateScore($applicant);
                $applicant->update(['score' => $score]);
            }    

            $messages = $this->fetchStateMessages('complete');
            $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
        } catch (Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error in handleCompleteState: ' . $e->getMessage());

            // Get the error message from the method
            $errorMessage = $this->getErrorMessage();

            // Send the error message to the user
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Shedule Start
    |--------------------------------------------------------------------------
    */

    protected function handleScheduleStartState($applicant, $body, $client, $to, $from, $token) {
        try {
            $latestInterview = $applicant->interviews()->latest('created_at')->first();

            if ($latestInterview) {
                // Handle the 'yes' keyword
                if (strtolower($body) == 'yes') {
                    //Data to replace
                    $dataToReplace = [
                        "Applicant Name" => $applicant->firstname.' '.$applicant->lastname,
                        "Position Name" => $latestInterview->vacancy->position->name ?? 'N/A',
                        "Store Name" => ($latestInterview->vacancy->store->brand->name ?? '') . ' ' . ($latestInterview->vacancy->store->town->name ?? 'Our Office'),
                        "Interview Location" => $latestInterview->location ?? 'N/A',
                        "Interview Date" => $latestInterview->scheduled_date->format('d M Y'),
                        "Interview Time" => $latestInterview->start_time->format('H:i'),
                        "Notes" => $latestInterview->notes ?? 'N/A',
                    ];

                    $messages = $this->fetchStateMessages('schedule');

                    foreach ($messages as &$message) {
                        foreach ($dataToReplace as $key => $value) {
                            $message = str_replace("[$key]", $value, $message);
                        }
                    }

                    $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);

                    $stateID = State::where('code', 'schedule')->value('id');
                    $applicant->update(['state_id' => $stateID]);
                } else if (strtolower($body) == 'no') {
                    // Update the status of the interview
                    $latestInterview->status = 'Declined';
                    $latestInterview->save();

                    // If a new interview was updated, then create a notification
                    if ($latestInterview->wasChanged() && $applicant->user) {
                        // Create Notification
                        $notification = new Notification();
                        $notification->user_id = $latestInterview->interviewer_id;
                        $notification->causer_id = $applicant->user->id;
                        $notification->subject()->associate($latestInterview);
                        $notification->type_id = 1;
                        $notification->notification = "Declined your interview request 🚫";
                        $notification->read = "No";
                        $notification->save();
                    }

                    $messages = [
                        "We have received your response and your interview for the position of " .
                        $latestInterview->vacancy->position->name .
                        " is now declined. If this was a mistake, please contact us immediately."
                    ];
                    $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);

                    $stateID = State::where('code', 'complete')->value('id');
                    $applicant->update(['state_id' => $stateID]);
                } else {
                    $messages = $this->fetchStateMessages('schedule_start');
                    $lastMessage = end($messages);
                    $this->sendAndLogMessages($applicant, [$lastMessage], $client, $to, $from, $token);
                }
            } else {
                $messages = ["No interviews found, have a wonderful day."];
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);

                $stateID = State::where('code', 'complete')->value('id');
                $applicant->update(['state_id' => $stateID]);
            }
        } catch (Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error in handleScheduleState: ' . $e->getMessage());
    
            // Get the error message from the method
            $errorMessage = $this->getErrorMessage();
    
            // Send the error message to the user
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Shedule
    |--------------------------------------------------------------------------
    */

    protected function handleScheduleState($applicant, $body, $client, $to, $from, $token) {
        try {
            $latestInterview = $applicant->interviews()->latest('created_at')->first();

            if ($latestInterview) {
                // Handle the 'confirm' keyword
                if (strtolower($body) == 'confirm') {
                    // Update the status of the interview
                    $latestInterview->status = 'Confirmed';
                    $latestInterview->save();

                    // If a new interview was updated, then create a notification
                    if ($latestInterview->wasChanged() && $applicant->user) {
                        // Create Notification
                        $notification = new Notification();
                        $notification->user_id = $latestInterview->interviewer_id;
                        $notification->causer_id = $applicant->user->id;
                        $notification->subject()->associate($latestInterview);
                        $notification->type_id = 1;
                        $notification->notification = "Confirmed your interview request ✅";
                        $notification->read = "No";
                        $notification->save();
                    }

                    $messages = [
                        "Thank you, your interview for the position of " .
                        $latestInterview->vacancy->position->name .
                        " on " . $latestInterview->scheduled_date->format('d M') .
                        " at " . $latestInterview->start_time->format('H:i') .
                        " has been confirmed!"
                    ];
                    $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);

                    $stateID = State::where('code', 'complete')->value('id');
                    $applicant->update(['state_id' => $stateID]);
                } else if (strtolower($body) == 'reschedule') {
                    // Update the status of the interview
                    $latestInterview->status = 'Reschedule';
                    $latestInterview->save();

                    // If a new interview was updated, then create a notification
                    if ($latestInterview->wasChanged()) {
                        // Create Notification
                        $notification = new Notification();
                        $notification->user_id = $latestInterview->interviewer_id;
                        $notification->causer_id = $userID;
                        $notification->subject()->associate($latestInterview);
                        $notification->type_id = 1;
                        $notification->notification = "Requested to reschedule 📅";
                        $notification->read = "No";
                        $notification->save();
                    }

                    $messages = [
                        "Please suggest a new date and time for your interview. We will do our best to accommodate your schedule. For example, '2024-02-20 14:00'."
                    ];
                    $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);

                    $stateID = State::where('code', 'reschedule')->value('id');
                    $applicant->update(['state_id' => $stateID]);
                } else if (strtolower($body) == 'decline') {
                    // Update the status of the interview
                    $latestInterview->status = 'Declined';
                    $latestInterview->save();

                    // If a new interview was updated, then create a notification
                    if ($latestInterview->wasChanged() && $applicant->user) {
                        // Create Notification
                        $notification = new Notification();
                        $notification->user_id = $latestInterview->interviewer_id;
                        $notification->causer_id = $applicant->user->id;
                        $notification->subject()->associate($latestInterview);
                        $notification->type_id = 1;
                        $notification->notification = "Declined your interview request 🚫";
                        $notification->read = "No";
                        $notification->save();
                    }

                    $messages = [
                        "We have received your response and your interview for the position of " .
                        $latestInterview->vacancy->position->name .
                        " is now declined. If this was a mistake, please contact us immediately."
                    ];
                    $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);

                    $stateID = State::where('code', 'complete')->value('id');
                    $applicant->update(['state_id' => $stateID]);
                } else {
                    $messages = $this->fetchStateMessages('schedule');
                    $lastMessage = end($messages);
                    $this->sendAndLogMessages($applicant, [$lastMessage], $client, $to, $from, $token);
                }
            } else {
                $messages = ["No interviews found, have a wonderful day."];
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);

                $stateID = State::where('code', 'complete')->value('id');
                $applicant->update(['state_id' => $stateID]);
            }
        } catch (Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error in handleScheduleState: ' . $e->getMessage());
    
            // Get the error message from the method
            $errorMessage = $this->getErrorMessage();
    
            // Send the error message to the user
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Reshedule
    |--------------------------------------------------------------------------
    */

    protected function handleRescheduleState($applicant, $body, $client, $to, $from, $token) {
        try {
            $latestInterview = $applicant->interviews()->latest('created_at')->first();

            if ($latestInterview) {
                try {
                    // Attempt to parse the provided date and time
                    $newDateTime = Carbon::parse($body);
        
                    // If parsing was successful, update the interview's reschedule field
                    $latestInterview->reschedule_date = $newDateTime;
                    $latestInterview->save();
        
                    // Send a confirmation message
                    $messages = [
                        "Thank you, we have noted the date and time. We will get back to you with a newly scheduled interview."
                    ];

                    $stateID = State::where('code', 'complete')->value('id');
                    $applicant->update(['state_id' => $stateID]);
                } catch (\Exception $e) {
                    // If the date and time couldn't be parsed, ask for a valid format
                    $messages = [
                        "Please provide a valid date and time for your interview. For example, '2024-02-20 14:00'."
                    ];
                }
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);
            } else {
                $messages = ["No interviews found, have a wonderful day."];
                $this->sendAndLogMessages($applicant, $messages, $client, $to, $from, $token);

                $stateID = State::where('code', 'complete')->value('id');
                $applicant->update(['state_id' => $stateID]);
            }
        } catch (Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error in handleScheduleState: ' . $e->getMessage());
    
            // Get the error message from the method
            $errorMessage = $this->getErrorMessage();
    
            // Send the error message to the user
            $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Calculate Score
    |--------------------------------------------------------------------------
    */

    protected function calculateScore($applicant) {
        $totalScore = 0;
        $totalWeight = 0;
        $weightings = ScoreWeighting::all(); // Fetch all weightings
    
        foreach ($weightings as $weighting) {
            // Check if this weighting involves a condition
            if (!empty($weighting->condition_field)) {
                // Handle conditional logic
                $scoreValue = $applicant->{$weighting->condition_field} == $weighting->condition_value ? $weighting->weight : $weighting->fallback_value;
                $totalScore += $scoreValue;
            } else {
                // Handle numeric scoring as before
                $scoreValue = $applicant->{$weighting->score_type} ?? 0;
                $maxValue = $weighting->max_value;
                if ($maxValue > 0) {
                    $percentage = ($scoreValue / $maxValue) * $weighting->weight;
                    $totalScore += $percentage;
                }
            }

            $totalWeight += $weighting->weight;
        }
    
        // Normalize the score to a scale of 0 to 5
        $normalizedScore = $totalWeight > 0 ? ($totalScore / $totalWeight) * 5 : ($totalScore / 100) * 5;
    
        return round($normalizedScore, 2); // Round to 2 decimal places
    }

    /*
    |--------------------------------------------------------------------------
    | Get Messages
    |--------------------------------------------------------------------------
    */

    protected function fetchStateMessages($stateCode) {
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
        ->get(['message', 'template'])
        ->toArray();
    
        return $messages;
    }

    /*
    |--------------------------------------------------------------------------
    | Send & Log Messages
    |--------------------------------------------------------------------------
    */

    public function sendAndLogMessages($applicant, $messages, $client, $to, $from, $token) {
        static $lastMessage = null; // Static variable to remember the last message se

        foreach ($messages as $messageData) { // Ensure $messageData is used to clarify it's an array from messages
                try {
                    // Check if $messageData is a string and adjust accordingly
                if (is_string($messageData)) {
                    $body = $messageData;
                    $template = null;
                } else {  // Assume $messageData is an array
                    $body = $messageData['message'];
                    $template = $messageData['template'] ?? null;
                    $variables = $messageData['variables'] ?? [];
                }

                // Check if current message is the same as the last one
                if ($lastMessage === $body) {
                    continue; // Skip sending this message
                }
    
                // Prepare the API URL
                $url = "https://graph.facebook.com/v19.0/$from/messages";
    
                // Initialize the payload with common elements
                $payload = [
                    'messaging_product' => 'whatsapp',
                    'to' => $to,
                    'type' => $template ? 'template' : 'text'
                ];
    
                // Conditional structure based on the type
                if ($template) {
                    $payload['template'] = [
                        'name' => $template,
                        'language' => ['code' => 'en_US'],
                        'components' => [
                            [
                                'type' => 'body',
                                'parameters' => array_map(function($var) { 
                                    return ['type' => 'text', 'text' => $var]; 
                                }, $variables)
                            ]
                        ]
                    ];
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

                // Update the last message sent
                $lastMessage = $body;
    
                // Log the outgoing message
                $this->logMessage($applicant->id, $body, 2);
            } catch (\GuzzleHttp\Exception\ClientException $e) {
                $responseBody = $e->getResponse()->getBody()->getContents();

                // Log the error for debugging purposes
                Log::error('Error in sendAndLogMessages: ' . $e->getMessage());
        
                // Get the error message from the method
                $errorMessage = $this->getErrorMessage();
        
                // Send the error message to the user
                $this->sendAndLogMessages($applicant, [$errorMessage], $client, $to, $from, $token);
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Error Message
    |--------------------------------------------------------------------------
    */

    protected function getErrorMessage() {
        return "Something went wrong. Please try again later.";
    }
}