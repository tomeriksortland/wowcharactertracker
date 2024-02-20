<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\CharacterUpdate;
use Inertia\Response as InertiaResponse;

class DashboardController extends Controller
{
    public function index() : InertiaResponse
    {
        return Inertia::render('Dashboard', [
            'charactersUpdateJob' => CharacterUpdate::where('user_id', Auth()->id())->latest()->first()->status
        ]);
    }
}
