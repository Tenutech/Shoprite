<?php

namespace App\Http\Reports\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Services\DataService\Reports\StoresDataService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class StoresController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(['auth', 'verified']);
    }

    /**
     * Show the stores report.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
    }
}
