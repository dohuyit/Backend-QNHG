<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;

class BroadcastingController extends Controller
{
    /**
     * Authenticate the incoming request.
     */
    public function authenticate(Request $request)
    {
        return Broadcast::auth($request);
    }
}
