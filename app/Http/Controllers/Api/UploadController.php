<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Helpers\ApiResponse;

class UploadController extends Controller
{
    /**
     * Upload file (logo or document).
     */
    public function upload(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'file' => [
                'required',
                'file',
                'max:5120', // 5MB max
                'mimes:jpeg,jpg,png,pdf,doc,docx'
            ],
            'type' => 'required|in:logo,document'
        ], [
            'file.required' => 'Le fichier est obligatoire',
            'file.file' => 'Le champ doit être un fichier',
            'file.max' => 'Le fichier ne peut pas dépasser 5MB',
            'file.mimes' => 'Le format du fichier n\'est pas autorisé (jpeg, jpg, png, pdf, doc, docx)',
            'type.required' => 'Le type de fichier est obligatoire',
            'type.in' => 'Le type doit être logo ou document',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors(), 'Erreur de validation du fichier');
        }

        try {
            $file = $request->file('file');
            $type = $request->input('type');
            
            // Generate unique filename
            $filename = $type . '_' . time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            
            // Define storage path
            $storagePath = $type === 'logo' ? 'logos' : 'documents';
            
            // Store file
            $path = $file->storeAs($storagePath, $filename, 'public');
            
            // Generate public URL
            $url = Storage::url($path);
            
            return ApiResponse::success([
                'filename' => $filename,
                'path' => $path,
                'url' => $url,
                'type' => $type,
                'size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
            ], 'Fichier uploadé avec succès');
            
        } catch (\Exception $e) {
            return ApiResponse::serverError('Erreur lors de l\'upload du fichier', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Upload logo specifically.
     */
    public function uploadLogo(Request $request): JsonResponse
    {
        $request->merge(['type' => 'logo']);
        return $this->upload($request);
    }

    /**
     * Upload document specifically.
     */
    public function uploadDocument(Request $request): JsonResponse
    {
        $request->merge(['type' => 'document']);
        return $this->upload($request);
    }

    /**
     * Delete file.
     */
    public function delete(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'path' => 'required|string',
        ], [
            'path.required' => 'Le chemin du fichier est obligatoire',
            'path.string' => 'Le chemin doit être une chaîne de caractères',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors());
        }

        try {
            $path = $request->input('path');
            
            if (!Storage::disk('public')->exists($path)) {
                return ApiResponse::notFound('Fichier non trouvé');
            }

            // Delete file
            Storage::disk('public')->delete($path);
            
            return ApiResponse::success(null, 'Fichier supprimé avec succès');
            
        } catch (\Exception $e) {
            return ApiResponse::serverError('Erreur lors de la suppression du fichier', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get file info.
     */
    public function info(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'path' => 'required|string',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors());
        }

        try {
            $path = $request->input('path');
            
            if (!Storage::disk('public')->exists($path)) {
                return ApiResponse::notFound('Fichier non trouvé');
            }

            $fileInfo = [
                'exists' => true,
                'path' => $path,
                'url' => Storage::url($path),
                'size' => Storage::disk('public')->size($path),
                'last_modified' => Storage::disk('public')->lastModified($path),
                'mime_type' => Storage::disk('public')->mimeType($path),
            ];
            
            return ApiResponse::success($fileInfo, 'Informations du fichier récupérées');
            
        } catch (\Exception $e) {
            return ApiResponse::serverError('Erreur lors de la récupération des informations', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * List files in directory.
     */
    public function list(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'directory' => 'required|string|in:logos,documents',
        ], [
            'directory.required' => 'Le répertoire est obligatoire',
            'directory.in' => 'Le répertoire doit être logos ou documents',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors());
        }

        try {
            $directory = $request->input('directory');
            $files = Storage::disk('public')->files($directory);
            
            $fileList = [];
            foreach ($files as $file) {
                $fileList[] = [
                    'filename' => basename($file),
                    'path' => $file,
                    'url' => Storage::url($file),
                    'size' => Storage::disk('public')->size($file),
                    'last_modified' => Storage::disk('public')->lastModified($file),
                ];
            }
            
            return ApiResponse::success($fileList, 'Liste des fichiers récupérée');
            
        } catch (\Exception $e) {
            return ApiResponse::serverError('Erreur lors de la récupération de la liste', [
                'error' => $e->getMessage()
            ]);
        }
    }
}
