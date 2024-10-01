<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Faq;
use App\Models\Query;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HelpController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Help Index
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        if (view()->exists('faqs')) {
            //User ID
            $userId = Auth::id();

            //Auth User
            $user = User::findorfail($userId);

            //Role
            $role = $user->role_id;

            //General FAQs
            $generalFaqs = Faq::where('type', 'General')
            ->where('role_id', $role)
            ->get();

            //Account FAQs
            $accountFaqs = Faq::where('type', 'Account')->get();

            //Queries
            $queries = Query::where('user_id', auth()->id())->get();

            return view('faqs', [
                'user' => $user,
                'generalFaqs' => $generalFaqs,
                'accountFaqs' => $accountFaqs,
                'queries' => $queries
            ]);
        }
        return view('404');
    }
}
