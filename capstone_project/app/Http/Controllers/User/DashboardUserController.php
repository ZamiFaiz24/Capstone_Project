<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardUserController extends Controller
{
    public function index()
    {
        return Inertia::render('user/DashboardUser', [
            'statusPerangkat' => 'OFF', // atau fetch dari DB
            'hargaHariIni' => 2300, // atau fetch dari DB
            'persentaseKenaikan' => 2.5, // atau hitung dari data
        ]);
    }
}
