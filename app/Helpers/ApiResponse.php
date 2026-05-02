<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;

class ApiResponse
{
    /**
     * Success response
     */
    public static function success($data = null, string $message = 'Opération réussie', int $status = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $status);
    }

    /**
     * Error response
     */
    public static function error(string $message = 'Erreur', $errors = null, int $status = 400): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $status);
    }

    /**
     * Validation error response
     */
    public static function validationError($errors, string $message = 'Erreur de validation'): JsonResponse
    {
        return self::error($message, $errors, 422);
    }

    /**
     * Not found response
     */
    public static function notFound(string $message = 'Ressource non trouvée'): JsonResponse
    {
        return self::error($message, null, 404);
    }

    /**
     * Unauthorized response
     */
    public static function unauthorized(string $message = 'Non autorisé'): JsonResponse
    {
        return self::error($message, null, 401);
    }

    /**
     * Forbidden response
     */
    public static function forbidden(string $message = 'Accès interdit'): JsonResponse
    {
        return self::error($message, null, 403);
    }

    /**
     * Server error response
     */
    public static function serverError(string $message = 'Erreur serveur'): JsonResponse
    {
        return self::error($message, null, 500);
    }

    /**
     * Paginated response
     */
    public static function paginate(LengthAwarePaginator $paginator, string $message = 'Données récupérées'): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $paginator->items(),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
                'has_more_pages' => $paginator->hasMorePages(),
            ]
        ]);
    }

    /**
     * Created response
     */
    public static function created($data, string $message = 'Ressource créée avec succès'): JsonResponse
    {
        return self::success($data, $message, 201);
    }

    /**
     * No content response
     */
    public static function noContent(string $message = 'Opération réussie'): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
        ], 204);
    }

    /**
     * Updated response
     */
    public static function updated($data = null, string $message = 'Ressource mise à jour avec succès'): JsonResponse
    {
        return self::success($data, $message, 200);
    }

    /**
     * Deleted response
     */
    public static function deleted(string $message = 'Ressource supprimée avec succès'): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
        ], 200);
    }

    /**
     * Custom response with custom status
     */
    public static function custom(bool $success, string $message, $data = null, int $status = 200): JsonResponse
    {
        $response = [
            'success' => $success,
            'message' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $status);
    }

    /**
     * Too many requests response
     */
    public static function tooManyRequests(string $message = 'Trop de requêtes'): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], 429);
    }

    /**
     * Service unavailable response
     */
    public static function serviceUnavailable(string $message = 'Service indisponible'): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], 503);
    }
}
