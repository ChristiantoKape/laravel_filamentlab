<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Settings\HomepageSettings;

class HomepageSettingController extends Controller
{
    protected $settings;

    public function __construct(HomepageSettings $settings)
    {
        $this->settings = $settings;
    }

    public function index()
    {
        $heroes = $this->settings->heroes ?? [];
        $achievements = $this->settings->achievements ?? [];

        dd($heroes);

        return view('homepage', compact('heroes', 'achievements'));
    }
}
