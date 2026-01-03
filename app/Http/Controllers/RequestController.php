<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class RequestController extends Controller
{
    /**
     * Afficher le formulaire de création de demande
     */
    public function create()
    {
        $types = [
            'retard' => 'Retard',
            'absence' => 'Absence',
            'sortie_anticipee' => 'Sortie anticipée',
            'teletravail' => 'Télétravail',
            'mission_exterieure' => 'Mission extérieure'
        ];

        return view('requests.create', compact('types'));
    }

    /**
     * Enregistrer une nouvelle demande
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'type' => 'required|in:retard,absence,sortie_anticipee,teletravail,mission_exterieure',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'reason' => 'required|string|min:10',
                'justification_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            ]);

            $data = [
                'type' => $validated['type'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'reason' => $validated['reason'],
            ];

            // Gestion du fichier justificatif
            if ($request->hasFile('justification_file')) {
                $file = $request->file('justification_file');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('justifications', $fileName, 'public');
                $data['justification_file'] = $path;
            }

            $response = Http::withToken(session('api_token'))
                ->attach(
                    'justification_file',
                    file_get_contents($request->file('justification_file')),
                    $fileName ?? null
                )
                ->post(url('/api/permissions'), $data);

            if (!$response->successful()) {
                $error = $response->json();
                throw new \Exception($error['message'] ?? 'Erreur lors de la création de la demande');
            }

            return redirect()->route('requests.index')
                ->with('success', 'Votre demande a été soumise avec succès.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Afficher la liste des demandes de l'utilisateur
     */
    public function index()
    {
        try {
            // Récupérer les demandes de l'utilisateur
            $response = Http::withToken(session('api_token'))
                ->get(url('/api/permissions/my-requests'));

            if (!$response->successful()) {
                throw new \Exception('Impossible de récupérer vos demandes');
            }

            $requests = $response->json()['data'] ?? [];
            $statuses = [
                'pending' => 'En attente',
                'approved' => 'Approuvée',
                'rejected' => 'Rejetée'
            ];

            return view('requests.index', compact('requests', 'statuses'));

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Afficher les détails d'une demande
     */
    public function show($id)
    {
        try {
            $response = Http::withToken(session('api_token'))
                ->get(url("/api/permissions/{$id}"));

            if (!$response->successful()) {
                throw new \Exception('Demande non trouvée');
            }

            $request = $response->json()['data'];
            
            return view('requests.show', compact('request'));

        } catch (\Exception $e) {
            return redirect()->route('requests.index')
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Annuler une demande
     */
    public function cancel($id)
    {
        try {
            $response = Http::withToken(session('api_token'))
                ->delete(url("/api/permissions/{$id}"));

            if (!$response->successful()) {
                $error = $response->json();
                throw new \Exception($error['message'] ?? 'Erreur lors de l\'annulation de la demande');
            }

            return redirect()->route('requests.index')
                ->with('success', 'La demande a été annulée avec succès.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Télécharger le fichier justificatif
     */
    public function downloadJustification($id)
    {
        try {
            $response = Http::withToken(session('api_token'))
                ->get(url("/api/permissions/{$id}/download-justification"));

            if (!$response->successful()) {
                throw new \Exception('Fichier non trouvé');
            }

            $contentType = $response->header('Content-Type');
            $contentDisposition = $response->header('Content-Disposition');
            $fileName = substr($contentDisposition, strpos($contentDisposition, 'filename=') + 9);
            $fileName = trim($fileName, '"');

            return response($response->body(), 200, [
                'Content-Type' => $contentType,
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            ]);

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }
}
