<?php

use Illuminate\Support\Facades\Route;
use App\Models\Location;
use App\Models\AuditReceipt;

Route::get('/', function () {
    $locations = Location::orderBy('created_at', 'desc')->get();
    $auditLogs = AuditReceipt::orderBy('created_at', 'desc')->get();
    return view('welcome', compact('locations', 'auditLogs'));
});
