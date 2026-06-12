<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CorsHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethod('OPTIONS')) {
            return $this->addHeaders(response()->noContent());
        }

        return $this->addHeaders($next($request));
    }

    private function addHeaders(Response $response): Response
    {
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, X-IAE-KEY');
        $response->headers->set('Access-Control-Max-Age', '86400');

        return $response;
    }
}
