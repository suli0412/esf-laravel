<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Validation\Rule;

use App\Models\Teilnehmer;
use App\Models\TeilnehmerDokument;

class TeilnehmerDokumentController extends Controller
{
    /** Datei hochladen (Teilnehmer-Seite) */


public function store(Request $r, \App\Models\Teilnehmer $teilnehmer)
{
    // 2a) Normalisieren, damit "foto", "image", etc. akzeptiert werden
    $rawTyp = (string) $r->input('typ');
    $normTyp = match (strtolower(trim($rawTyp))) {
        'pdf','scan','dokument'                 => 'PDF',
        'foto','photo','bild','image','jpeg','jpg','png' => 'Foto',
        'sonstiges','other','misc','anderes'    => 'Sonstiges',
        default                                 => $rawTyp,
    };
    $r->merge(['typ' => $normTyp]);

    // 2b) Whitelist aus der Config
    $allowed = config('dokumente.teilnehmer_types', ['PDF','Foto','Sonstiges']);

    $data = $r->validate([
        'file'  => ['required','file','max:20480'],
        'typ'   => ['required', Rule::in($allowed)],
        'titel' => ['nullable','string','max:255'],
    ]);

    $file         = $r->file('file');
    $originalName = $file->getClientOriginalName();
    $originalBase = pathinfo($originalName, PATHINFO_FILENAME);

    $dir  = 'teilnehmer/'.$teilnehmer->Teilnehmer_id;
    $path = $file->store($dir, 'public');

    \App\Models\TeilnehmerDokument::create([
        'teilnehmer_id'  => $teilnehmer->Teilnehmer_id,
        'dokument_pfad'  => $path,
        'typ'            => $data['typ'],                 // jetzt sicher gültig
        'titel'          => $data['titel'] ?: $originalBase,
        'original_name'  => $originalName,
        'hochgeladen_am' => now(),
    ]);

    return back()->with('success','Dokument hochgeladen.');
}

    /** Datei im Browser anzeigen (PDF/Bild) */
    public function show(TeilnehmerDokument $dokument)
    {
        $url = Storage::disk('public')->url($dokument->dokument_pfad); // php artisan storage:link
        return redirect()->away($url);
    }

    /** Datei herunterladen */
    public function download(TeilnehmerDokument $dokument): StreamedResponse
    {
        return Storage::disk('public')->download(
            $dokument->dokument_pfad,
            $dokument->original_name ?: basename($dokument->dokument_pfad)
        );
    }

    /** Datei löschen */
    public function destroy(Teilnehmer $teilnehmer, TeilnehmerDokument $dokument)
    {
        // Sicherheitscheck: Dokument gehört zu diesem Teilnehmer?
        if ($dokument->teilnehmer_id !== $teilnehmer->Teilnehmer_id) {
            abort(404);
        }

        Storage::disk('public')->delete($dokument->dokument_pfad);
        $dokument->delete();

        return back()->with('success', 'Dokument gelöscht.');
    }
}
