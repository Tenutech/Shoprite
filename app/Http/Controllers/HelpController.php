<?php

namespace App\Http\Controllers;

use App\Models\Query;
use Illuminate\Http\Request;

class HelpController extends Controller
{
    public function index()
    {
        if (view()->exists('faqs')) {
            //Qualifications
            $queries = Query::where('user_id', auth()->id())->get();

            return view('faqs', [
                'queries' => $queries,
            ]);
        }
        return view('404');
    }
}
