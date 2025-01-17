<?php

namespace App\Exports;

use App\Models\Applicant;
use App\Models\State;
use Box\Spout\Common\Exception\IOException;
use Box\Spout\Common\Exception\WriterNotOpenedException;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class ApplicantsExport
{
    protected $type;
    protected $id;
    protected $startDate;
    protected $endDate;
    protected $maxDistanceFromStore;
    protected $filters;

    public function __construct($type, $id, $startDate, $endDate, $maxDistanceFromStore, $filters)
    {
        $this->type = $type;
        $this->id = $id;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->maxDistanceFromStore = $maxDistanceFromStore;
        $this->filters = $filters;
    }

    public function export(string $filePath): void
    {
        try {
            $writer = WriterEntityFactory::createWriterFromFile($filePath);
            $writer->openToFile($filePath);

            // Style for headers
            $headerStyle = (new StyleBuilder())->setFontBold()->build();

            // Write header row
            $headerRow = WriterEntityFactory::createRowFromArray($this->headings(), $headerStyle);
            $writer->addRow($headerRow);

            // Fetch applicants with filters and chunk the results
            $this->query()->chunk(1000, function ($applicants) use ($writer) {
                foreach ($applicants as $applicant) {
                    $writer->addRow(WriterEntityFactory::createRowFromArray($this->map($applicant)));
                }
            });

            $writer->close();
        } catch (IOException $e) {
            Log::error('Failed to export applicants: ' . $e->getMessage());
        }
    }

    public function query()
    {
        $query = Applicant::query();

        if (isset($this->filters['gender_id'])) {
            $query->where('gender_id', $this->filters['gender_id']);
        }

        if (isset($this->filters['race_id'])) {
            $query->where('race_id', $this->filters['race_id']);
        }

        if (isset($this->filters['education_id'])) {
            $query->where('education_id', $this->filters['education_id']);
        }

        if (isset($this->filters['duration_id'])) {
            $query->where('duration_id', $this->filters['duration_id']);
        }

        if (isset($this->filters['employment'])) {
            $query->where('employment', $this->filters['employment']);
        }

        // Age, literacy, numeracy, situational, and overall score filters
        if (isset($this->filters['min_age']) && isset($this->filters['max_age'])) {
            $query->whereBetween('age', [$this->filters['min_age'], $this->filters['max_age']]);
        }
        if (isset($this->filters['min_literacy']) && isset($this->filters['max_literacy'])) {
            $query->whereBetween('literacy_score', [$this->filters['min_literacy'], $this->filters['max_literacy']]);
        }
        if (isset($this->filters['min_numeracy']) && isset($this->filters['max_numeracy'])) {
            $query->whereBetween('numeracy_score', [$this->filters['min_numeracy'], $this->filters['max_numeracy']]);
        }
        if (isset($this->filters['min_situational']) && isset($this->filters['max_situational'])) {
            $query->whereBetween('situational_score', [$this->filters['min_situational'], $this->filters['max_situational']]);
        }
        if (isset($this->filters['min_overall']) && isset($this->filters['max_overall'])) {
            $query->whereBetween('score', [$this->filters['min_overall'], $this->filters['max_overall']]);
        }

        if (isset($this->filters['completed'])) {
            $completeStateID = State::where('code', 'complete')->value('id');
            if ($this->filters['completed'] === 'Yes') {
                $query->where('state_id', '>=', $completeStateID);
            } elseif ($this->filters['completed'] === 'No') {
                $query->where('state_id', '<', $completeStateID);
            }
        }

        // Shortlisted, Interviewed, and Appointed filters
        if (isset($this->filters['shortlisted'])) {
            $query->whereNotNull('shortlist_id');
        }

        if (isset($this->filters['interviewed'])) {
            $query->whereHas('interviews', function ($interviewQuery) {
                $interviewQuery->whereNotNull('score');
            });
        }

        if (isset($this->filters['appointed']) && $this->filters['appointed'] === 'Yes') {
            $query->whereNotNull('appointed_id');
        }

        $query->whereBetween('created_at', [$this->startDate, $this->endDate]);

        return $query;
    }

    protected function headings(): array
    {
        return [
            'Application Date',
            'ID Number',
            'ID Image URL',
            'First Name',
            'Last Name',
            'Date of Birth',
            'Age',
            'Gender',
            'Race',
            'Phone Number',
            'Email Address',
            'Highest Qualification',
            'Experience',
            'Town',
            'Province',
            'Brands',
            'Home Address',
            'Location Type',
            'Terms & Conditions',
            'Shift Basis',
            'Work Environment',
            'Background Check',
            'Disability',
            'Literacy Score (%)',
            'Numeracy Score (%)',
            'Situational Awareness Score (%)',
            'Total Assessment Score (%)',
            'Overall Candidate Score',
            'Application Channel',
            'Drop off',
            'Workflow Stage',
            'Appointed',
            'SAP Number',
        ];
    }

    protected function map($applicant): array
    {
        $completeStateID = State::where('code', 'complete')->value('id');

        $literacyPercentage = isset($applicant->literacy_score, $applicant->literacy_questions) && $applicant->literacy_questions > 0
            ? round(($applicant->literacy_score / $applicant->literacy_questions) * 100)
            : '';

        $numeracyPercentage = isset($applicant->numeracy_score, $applicant->numeracy_questions) && $applicant->numeracy_questions > 0
            ? round(($applicant->numeracy_score / $applicant->numeracy_questions) * 100)
            : '';

        $situationalPercentage = isset($applicant->situational_score, $applicant->situational_questions) && $applicant->situational_questions > 0
            ? round(($applicant->situational_score / $applicant->situational_questions) * 100)
            : '';

        $assessmentScore = '';
        if (
            isset($applicant->literacy_score, $applicant->numeracy_score, $applicant->situational_score) &&
            isset($applicant->literacy_questions, $applicant->numeracy_questions, $applicant->situational_questions)
        ) {
            $totalScore = $applicant->literacy_score + $applicant->numeracy_score + $applicant->situational_score;
            $totalQuestions = $applicant->literacy_questions + $applicant->numeracy_questions + $applicant->situational_questions;
            if ($totalQuestions > 0) {
                $assessmentScore = round(($totalScore / $totalQuestions) * 100);
            }
        }

        $brands = $applicant->brands->pluck('name')->join(', ');
        $appointed = $applicant->appointed_id ? 'Yes' : 'No';
        $sapNumber = $appointed === 'Yes' ? $applicant->vacanciesFilled()->latest()->first()->pivot->sap_number ?? '' : '';

        return [
            $applicant->created_at->format('Y-m-d H:i'),
            $applicant->id_number ?? '',
            $applicant->documents()->latest()->first() ? url('documents/view/' . Crypt::encryptString($applicant->documents()->latest()->first()->id)) : '',
            $applicant->firstname ?? '',
            $applicant->lastname ?? '',
            $applicant->birth_date ? date('Y-m-d', strtotime($applicant->birth_date)) : '',
            $applicant->age ?? '',
            optional($applicant->gender)->name ?? '',
            optional($applicant->race)->name ?? '',
            $applicant->phone ?? '',
            $applicant->email ?? '',
            optional($applicant->education)->name ?? '',
            optional($applicant->duration)->name ?? '',
            optional($applicant->town)->name ?? '',
            optional(optional($applicant->town)->province)->name ?? '',
            $brands ?? '',
            $applicant->location ?? '',
            $applicant->location_type ?? '',
            $applicant->terms_conditions ?? '',
            $applicant->public_holidays,
            $applicant->environment,
            $applicant->consent ?? '',
            $applicant->disability ?? '',
            $literacyPercentage,
            $numeracyPercentage,
            $situationalPercentage,
            $assessmentScore ?? '',
            $applicant->score ?? '',
            $applicant->application_type ?? '',
            $applicant->state_id < $completeStateID ? 'Yes' : 'No',
            optional($applicant->state)->name ?? '',
            $appointed ?? '',
            $sapNumber ?? '',
        ];
    }
}