<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: "1.0.0",
    title: "Service A - Lahan & Lokasi API",
    description: "Dokumentasi API Terintegrasi untuk Manajemen Lahan dan Slot Parkir"
)]
#[OA\Server(
    url: "http://127.0.0.1:3001/api/v1",
    description: "Docker Server"
)]
#[OA\Server(
    url: "http://127.0.0.1:8000/api/v1",
    description: "Local Development Server"
)]
#[OA\SecurityScheme(
    securityScheme: "ApiKeyAuth",
    type: "apiKey",
    in: "header",
    name: "X-IAE-KEY",
    description: "Masukkan NIM Anda (102022400039) untuk mengakses layanan"
)]
abstract class Controller
{
    //
}