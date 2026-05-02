<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class SecurityMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $clientIp = $request->ip();
        $userAgent = $request->userAgent();
        
        // Rate limiting par IP
        $key = 'api_requests:' . $clientIp;
        $maxAttempts = 60; // 60 requêtes par minute
        $decayMinutes = 1;
        
        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $this->logSecurityEvent('RATE_LIMIT_EXCEEDED', $request, [
                'ip' => $clientIp,
                'attempts' => RateLimiter::attempts($key)
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Too many requests',
                'retry_after' => RateLimiter::availableIn($key)
            ], 429);
        }
        
        RateLimiter::hit($key, $decayMinutes);
        
        // Validation des headers
        $this->validateSecurityHeaders($request);
        
        // Protection contre les attaques communes
        if ($this->isSuspiciousRequest($request)) {
            $this->logSecurityEvent('SUSPICIOUS_REQUEST', $request, [
                'ip' => $clientIp,
                'user_agent' => $userAgent,
                'uri' => $request->uri(),
                'method' => $request->method()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Request blocked'
            ], 403);
        }
        
        $response = $next($request);
        
        // Ajout des headers de sécurité
        $this->addSecurityHeaders($response);
        
        return $response;
    }
    
    private function validateSecurityHeaders(Request $request): void
    {
        // Vérification du Content-Type pour les requêtes POST/PUT/PATCH
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH'])) {
            $contentType = $request->header('Content-Type');
            
            if ($request->expectsJson() && 
                (!str_contains($contentType ?? '', 'application/json') && 
                 !str_contains($contentType ?? '', 'multipart/form-data'))) {
                $this->logSecurityEvent('INVALID_CONTENT_TYPE', $request, [
                    'content_type' => $contentType,
                    'expected' => 'application/json'
                ]);
                
                abort(400, 'Invalid Content-Type');
            }
        }
    }
    
    private function isSuspiciousRequest(Request $request): bool
    {
        $patterns = [
            '/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi',
            '/javascript:/i',
            '/vbscript:/i',
            '/onload\s*=/i',
            '/onerror\s*=/i',
            '/onclick\s*=/i',
            '/<iframe\b[^<]*(?:(?!<\/iframe>)<[^<]*)*<\/iframe>/mi',
            '/<object\b[^<]*(?:(?!<\/object>)<[^<]*)*<\/object>/mi',
            '/<embed\b[^<]*(?:(?!<\/embed>)<[^<]*)*<\/embed>/mi'
        ];
        
        $input = $request->all();
        $inputString = json_encode($input);
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $inputString)) {
                return true;
            }
        }
        
        // Vérification des paramètres suspects dans l'URL
        $suspiciousParams = ['exec', 'system', 'eval', 'shell_exec', 'passthru'];
        foreach ($suspiciousParams as $param) {
            if ($request->has($param)) {
                return true;
            }
        }
        
        return false;
    }
    
    private function addSecurityHeaders(Response $response): void
    {
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        $response->headers->set('Content-Security-Policy', "default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self'; connect-src 'self'; frame-ancestors 'none';");
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');
    }
    
    private function logSecurityEvent(string $event, Request $request, array $context = []): void
    {
        Log::channel('security')->warning($event, array_merge([
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'uri' => $request->uri(),
            'method' => $request->method(),
            'timestamp' => now()->toISOString()
        ], $context));
    }
}
