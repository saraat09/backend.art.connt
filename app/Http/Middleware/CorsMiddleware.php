<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CorsMiddleware
{
    private array $allowedOrigins = [
        'http://localhost:3000',
        'http://localhost:5173',
        'http://127.0.0.1:3000',
        'http://127.0.0.1:5173',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        // Répondre immédiatement aux requêtes OPTIONS (preflight)
        if ($request->getMethod() === 'OPTIONS') {
            return $this->preflight($request);
        }

        $response = $next($request);

        return $this->addCorsHeaders($request, $response);
    }

    private function preflight(Request $request): Response
    {
        return response('', 204)
            ->withHeaders($this->corsHeaders($request));
    }

    private function addCorsHeaders(Request $request, Response $response): Response
    {
        foreach ($this->corsHeaders($request) as $key => $value) {
            $response->headers->set($key, $value);
        }
        return $response;
    }

    private function corsHeaders(Request $request): array
    {
        $origin = $request->header('Origin', '*');

        if (!in_array($origin, $this->allowedOrigins)) {
            $origin = $this->allowedOrigins[0];
        }

        return [
            'Access-Control-Allow-Origin'      => $origin,
            'Access-Control-Allow-Methods'     => 'GET, POST, PUT, PATCH, DELETE, OPTIONS',
            'Access-Control-Allow-Headers'     => 'Content-Type, Authorization, X-Requested-With, Accept',
            'Access-Control-Allow-Credentials' => 'true',
            'Access-Control-Max-Age'           => '86400',
        ];
    }
}
