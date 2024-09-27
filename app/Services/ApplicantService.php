<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use App\Models\Applicant;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class ApplicantService
{
    /**
     * Create A New Applicant
     *
     * @param array $applicantData
     * @return Applicant
     */
    public function store(array $applicantData): Applicant
    {
        $applicantData['avatarName'] = 'avatar.jpg';

        if (isset($applicantData['avatar'])) {
            $applicantData['avatarName'] = $this->avatar($applicantData);
        }

        $applicant = Applicant::create([
            'firstname' => ucwords($applicantData['firstname']),
            'lastname' => ucwords($applicantData['lastname']),
            'email' => $applicantData['email'],
            'phone' => $applicantData['phone'],
            'id_number' => $applicantData['id_number'],
            'id_verified' => $applicantData['id_verified'],
            'avatar' => $applicantData['avatarName'],
            'birth_date' => date('Y-m-d', strtotime($applicantData['birth_date'])),
            'age' => $applicantData['age'],
            'disability' => $applicantData['disability'],
            'gender_id' => $applicantData['gender_id'],
            'town_id' => $applicantData['town_id'],
            'race_id' => $applicantData['race_id'],
            'role_id' => $applicantData['role_id'],
            'state_id' => $applicantData['state_id'],
            'education_id' => $applicantData['education_id'],
            'duration_id' => $applicantData['duration_id'],
            'applicant_type_id' => $applicantData['applicant_type_id'],
            'application_type' => $applicantData['application_type'],
            'no_show' => $applicantData['no_show'],
        ]);

        return $applicant;
    }

    /**
     *
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Applicant $applicant
     * @param string $avatarName
     * @return \App\Models\Applicant
     */
    public function update(Request $request, Applicant $applicant, string $avatarName): Applicant
    {
        $applicant->firstname = ucwords($request->firstname);
        $applicant->lastname = ucwords($request->lastname);
        $applicant->email = $request->email;
        $applicant->phone = $request->phone;
        $applicant->id_number = $request->id_number;
        $applicant->id_verified = $request->id_verified;
        $applicant->avatar = $avatarName;
        $applicant->birth_date = date('Y-m-d', strtotime($request->birth_date));
        $applicant->age = $request->age;
        $applicant->disability = $request->disability;
        $applicant->gender_id = $request->gender_id;
        $applicant->town_id = $request->town_id;
        $applicant->race_id = $request->race_id;
        $applicant->role_id = $request->role_id;
        $applicant->state_id = $request->state_id;
        $applicant->education_id = $request->education_id;
        $applicant->duration_id = $request->duration_id;
        $applicant->applicant_type_id = $request->applicant_type_id;
        $applicant->application_type = $request->application_type;
        $applicant->no_show = $request->no_show;
        $applicant->save();

        return $applicant;
    }

    /**
     * Retrieve Applicant Avatar
     *
     * @param array $applicantData
     * @return string
     */
    private function avatar(array $applicantData): string
    {
        $avatar = $applicantData['avatar'];
        $applicantData['avatarName'] = $applicantData['firstname'] . ' ' . $applicantData['lastname'] . '-' . time() . '.' . $avatar->getClientOriginalExtension();
        $avatarPath = public_path('/images/');
        $avatar->move($avatarPath, $applicantData['avatarName']);

        return $applicantData['avatarName'];
    }

    /**
     * Check Avatar Exists
     *
     * @param Request $request
     * @param string $currentAvatar
     * @return string
     */
    public function checkAvatar(Request $request, string $currentAvatar): string
    {
        // Check if a previous avatar exists and is not the default one
        if ($currentAvatar && $currentAvatar !== 'avatar.jpg') {
            // Construct the path to the old avatar
            $oldAvatarPath = public_path('/images/') . $currentAvatar;
            // Check if the file exists and delete it
            if (File::exists($oldAvatarPath)) {
                File::delete($oldAvatarPath);
            }
        }

        $avatar = request()->file('avatar');
        $avatarName = $request->firstname . ' ' . $request->lastname . '-' . time() . '.' . $avatar->getClientOriginalExtension();
        $avatarPath = public_path('/images/');
        $avatar->move($avatarPath, $avatarName);

        return $avatarName;
    }
}
