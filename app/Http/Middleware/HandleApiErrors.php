<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Illuminate\Database\QueryException;
use Throwable;
use App\Helpers\ApiResponse;

class HandleApiErrors
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            return $next($request);
        } catch (ValidationException $e) {
            return ApiResponse::validationError($e->errors(), 'Erreur de validation des données');
        } catch (AuthenticationException $e) {
            return ApiResponse::unauthorized('Non authentifié. Veuillez vous connecter.');
        } catch (AuthorizationException $e) {
            return ApiResponse::forbidden('Accès non autorisé. Permissions insuffisantes.');
        } catch (ModelNotFoundException $e) {
            return ApiResponse::notFound('Ressource non trouvée.');
        } catch (NotFoundHttpException $e) {
            return ApiResponse::notFound('Endpoint non trouvé.');
        } catch (MethodNotAllowedHttpException $e) {
            return ApiResponse::error('Méthode HTTP non autorisée.', null, 405);
        } catch (AccessDeniedHttpException $e) {
            return ApiResponse::forbidden('Accès refusé.');
        } catch (QueryException $e) {
            // Log l'erreur pour débogage
            \Log::error('Database error: ' . $e->getMessage(), [
                'request' => $request->all(),
                'trace' => $e->getTrace()
            ]);
            
            if (app()->environment('production')) {
                return ApiResponse::serverError('Erreur de base de données. Veuillez réessayer plus tard.');
            }
            
            return ApiResponse::serverError('Erreur de base de données: ' . $e->getMessage());
        } catch (Throwable $e) {
            // Log l'erreur pour débogage
            \Log::error('Unexpected error: ' . $e->getMessage(), [
                'request' => $request->all(),
                'trace' => $e->getTrace()
            ]);
            
            if (app()->environment('production')) {
                return ApiResponse::serverError('Une erreur inattendue est survenue. Veuillez réessayer plus tard.');
            }
            
            return ApiResponse::serverError('Erreur inattendue: ' . $e->getMessage());
        }
    }

    /**
     * Handle exceptions after response is sent.
     */
    public function terminate(Request $request, JsonResponse $response): void
    {
        if ($response->getStatusCode() >= 400) {
            \Log::warning('API Error Response', [
                'status' => $response->getStatusCode(),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }
    }
}
