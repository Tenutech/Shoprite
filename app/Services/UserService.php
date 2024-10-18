<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Company;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class UserService
{
    /**
     * Create A New User
     *
     * @param array $userData
     * @return User
     */
    public function store(array $userData): User
    {
        $userData['avatarName'] = 'avatar.jpg';

        if (isset($userData['avatar'])) {
            $userData['avatarName'] = $this->avatar($userData);
        }

        $user = User::create([
            'firstname' => ucwords($userData['firstname']),
            'lastname' => ucwords($userData['lastname']),
            'email' => $userData['email'],
            'phone' => $userData['phone'],
            'id_number' => $userData['id_number'],
            'id_verified' => $userData['id_verified'],
            'password' => Hash::make("Shoprite1!"),
            'avatar' => $userData['avatarName'],
            'birth_date' => date('Y-m-d', strtotime($userData['birth_date'])),
            'age' => $userData['age'],
            'gender_id' => $userData['gender_id'],
            'resident' => $userData['resident'],
            'position_id' => $userData['position_id'],
            'role_id' => $userData['role_id'],
            'store_id' => $userData['store_id'],
            'region_id' => $userData['region_id'],
            'division_id' => $userData['division_id'],
            'brand_id' => $userData['brand_id'],
            'internal' => $userData['internal'],
            'status_id' => 2,
        ]);

        return $user;
    }

    /**
     * Retrieve User Avatar
     *
     * @param array $userData
     * @return String
     */
    private function avatar(array $userData): string
    {
        $avatar = $userData['avatar'];
        $userData['avatarName'] = $userData['firstname'] . ' ' . $userData['lastname'] . '-' . time() . '.' . $avatar->getClientOriginalExtension();
        $avatarPath = public_path('/images/');
        $avatar->move($avatarPath, $userData['avatarName']);

        return $userData['avatarName'];
    }

    /**
     * Undocumented function
     *
     * @param Request $request
     * @param string $user
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

    /**
     * Update User
     *
     * @param Request $updateUser
     * @param User $user
     * @param string $avatarName
     * @return User
     */
    public function update(Request $updateUser, User $user, string $avatarName): User
    {
        // Check if the company exists or create a new one
        $inputCompanyName = strtolower($updateUser->company);
        $company = Company::whereRaw('LOWER(name) = ?', [$inputCompanyName])->first();

        //User Update
        $user->firstname = ucwords($updateUser->firstname);
        $user->lastname = ucwords($updateUser->lastname);
        $user->email = $updateUser->email;
        $user->phone = $updateUser->phone;
        $user->id_number = $updateUser->id_number;
        $user->id_verified = $updateUser->id_verified;
        $user->avatar = $avatarName;
        $user->birth_date = date('Y-m-d', strtotime($updateUser->birth_date));
        $user->age = $updateUser->age;
        $user->gender_id = $updateUser->gender_id;
        $user->resident = $updateUser->resident;
        $user->position_id = $updateUser->position_id;
        $user->role_id = $updateUser->role_id;
        $user->store_id = $updateUser->store_id;
        $user->region_id = $updateUser->region_id;
        $user->division_id = $updateUser->division_id;
        $user->brand_id = $updateUser->brand_id;
        $user->internal = $updateUser->internal;
        $user->save();

        return $user;
    }
}
