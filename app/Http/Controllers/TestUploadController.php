<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class TestUploadController extends Controller
{
    public function showUploadForm()
    {
        return view('test-upload');
    }

    public function upload(Request $request)
    {
        Log::info('Début du test d\'upload', $request->all());
        
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            
            Log::info('Fichier reçu', [
                'original_name' => $file->getClientOriginalName(),
                'extension' => $file->getClientOriginalExtension(),
                'size' => $file->getSize(),
                'mime' => $file->getMimeType(),
                'is_valid' => $file->isValid(),
                'error' => $file->getError(),
                'error_message' => $file->getErrorMessage()
            ]);
            
            try {
                $path = $file->store('test-uploads', 'public');
                $fullPath = storage_path('app/public/' . $path);
                
                Log::info('Fichier enregistré avec succès', [
                    'path' => $path,
                    'full_path' => $fullPath,
                    'file_exists' => file_exists($fullPath),
                    'is_readable' => is_readable($fullPath),
                    'is_writable' => is_writable(dirname($fullPath))
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Fichier téléchargé avec succès',
                    'path' => $path,
                    'url' => Storage::url($path)
                ]);
                
            } catch (\Exception $e) {
                Log::error('Erreur lors de l\'enregistrement du fichier', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors du téléchargement du fichier: ' . $e->getMessage()
                ], 500);
            }
        }
        
        Log::warning('Aucun fichier reçu dans la requête');
        
        return response()->json([
            'success' => false,
            'message' => 'Aucun fichier reçu'
        ], 400);
    }
}
