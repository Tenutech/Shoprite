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
            'gender_id' => $applicantData['gender_id'],
            'resident' => $applicantData['resident'],
            'position_id' => $applicantData['position_id'],
            'role_id' => $applicantData['role_id'],
            'state_id' => $applicantData['state_id'],
            'type_id' => $applicantData['type_id'],
        ]);

        return $applicant;
    }

    /**
     * Update Applicant
     *
     * @param Request $request
     * @param User $user
     * @param string $avatarName
     * @return Applicant
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
        $applicant->gender_id = $request->gender_id;
        $applicant->resident = $request->resident;
        $applicant->position_id = $request->position_id;
        $applicant->role_id = $request->role_id;
        $applicant->state_id = $request->state_id;
        $applicant->type_id = $request->type_id;
        $applicant->save();

        return $applicant;
    }

    /**
     * Retrieve Applicant Avatar
     *
     * @param array $applicantData
     * @return String
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
