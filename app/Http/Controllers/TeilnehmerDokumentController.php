<?php

namespace App\Http\Controllers;

use App\Models\Teilnehmer;
use App\Models\TeilnehmerDokument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;


class TeilnehmerDokumentController extends Controller
{
   /** Datei hochladen (Teilnehmer-Seite) */
public function store(Request $r, \App\Models\Teilnehmer $teilnehmer)
{
    // ⚠️ Dein Formular nutzt name="file", nicht "datei"
    $data = $r->validate([
        'file'  => ['required', 'file', 'max:20480'], // 20 MB
        'typ'   => ['required', 'in:PDF,Foto,Sonstiges'],
        'titel' => ['nullable', 'string', 'max:255'],
    ]);

    $file         = $r->file('file');
    $originalName = $file->getClientOriginalName();
    $originalBase = pathinfo($originalName, PATHINFO_FILENAME);

    $dir  = 'teilnehmer/'.$teilnehmer->Teilnehmer_id;
    // speichert unter storage/app/public/teilnehmer/{id}/HASH.ext
    $path = $file->store($dir, 'public');

    $doc = \App\Models\TeilnehmerDokument::create([
        'teilnehmer_id' => $teilnehmer->Teilnehmer_id,
        'dokument_pfad' => $path,
        'typ'           => $data['typ'],
        'titel'         => $data['titel'] ?: $originalBase, // Fallback: Dateiname ohne Endung
        'original_name' => $originalName,
        'hochgeladen_am'=> now(),
    ]);

    // Kurzes Logging zur Diagnose
    \Log::info('Upload OK', [
        'teilnehmer_id' => $teilnehmer->Teilnehmer_id,
        'path'          => $path,
        'typ'           => $data['typ'],
        'titel'         => $data['titel'] ?: $originalBase,
        'doc_id'        => $doc->dokument_id ?? null,
    ]);

    return back()->with('success', 'Dokument hochgeladen.');
}

/** Anzeige im Browser (PDF/Bild) */
public function show(\App\Models\TeilnehmerDokument $doc)
{
    // Nutzt den öffentlichen Storage-URL (erfordert: php artisan storage:link)
    $url = \Illuminate\Support\Facades\Storage::disk('public')->url($doc->dokument_pfad);
    return redirect()->away($url);
}




    /** Löschen */
    public function destroy(TeilnehmerDokument $doc)
    {
        Storage::disk('public')->delete($doc->dokument_pfad);
        $doc->delete();

        return back()->with('success', 'Dokument gelöscht.');
    }

    /** Download (attachment) */
public function download(TeilnehmerDokument $doc): StreamedResponse
{
    $filename = pathinfo($doc->dokument_pfad, PATHINFO_BASENAME);
    return Storage::disk('public')->download($doc->dokument_pfad, $filename);
}
}
