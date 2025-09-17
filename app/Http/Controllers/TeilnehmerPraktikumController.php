<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Teilnehmer;
use App\Models\TeilnehmerPraktikum;

class TeilnehmerPraktikumController extends Controller
{
    public function store(Request $r, Teilnehmer $teilnehmer)
    {
        $data = $r->validateWithBag('praktikum', [
            'bereich'   => ['nullable','string','max:255'],
            'firma'     => ['nullable','string','max:255'],
            'land'      => ['nullable','string','max:255'],
            'von'       => ['nullable','date'],
            'bis'       => ['nullable','date','after_or_equal:von'],
            'stunden'   => ['nullable','numeric','min:0'],
            'anmerkung' => ['nullable','string'],
        ]);

        $teilnehmer->praktika()->create([
            'bereich'         => $data['bereich']   ?? null,
            'firma'           => $data['firma']     ?? null,
            'land'            => $data['land']      ?? null,
            'beginn'          => $data['von']       ?? null,
            'ende'            => $data['bis']       ?? null,
            'stunden_ausmass' => $data['stunden']   ?? null,
            'anmerkung'       => $data['anmerkung'] ?? null,
        ]);

        return redirect()->to(route('teilnehmer.show', $teilnehmer).'#praktika')
            ->with('success', 'Praktikum gespeichert.');
    }

    public function update(Request $r, Teilnehmer $teilnehmer, TeilnehmerPraktikum $praktikum)
    {
        abort_unless($praktikum->teilnehmer_id === $teilnehmer->Teilnehmer_id, 404);

        $data = $r->validateWithBag('praktikum', [
            'bereich'   => ['nullable','string','max:255'],
            'firma'     => ['nullable','string','max:255'],
            'land'      => ['nullable','string','max:255'],
            'von'       => ['nullable','date'],
            'bis'       => ['nullable','date','after_or_equal:von'],
            'stunden'   => ['nullable','numeric','min:0'],
            'anmerkung' => ['nullable','string'],
        ]);

        $praktikum->update([
            'bereich'         => $data['bereich']   ?? null,
            'firma'           => $data['firma']     ?? null,
            'land'            => $data['land']      ?? null,
            'beginn'          => $data['von']       ?? null,
            'ende'            => $data['bis']       ?? null,
            'stunden_ausmass' => $data['stunden']   ?? null,
            'anmerkung'       => $data['anmerkung'] ?? null,
        ]);

        return redirect()->to(route('teilnehmer.show', $teilnehmer).'#praktika')
            ->with('success', 'Praktikum aktualisiert.');
    }

    public function destroy(Teilnehmer $teilnehmer, TeilnehmerPraktikum $praktikum)
    {
        abort_unless($praktikum->teilnehmer_id === $teilnehmer->Teilnehmer_id, 404);

        $praktikum->delete();

        return redirect()->to(route('teilnehmer.show', $teilnehmer).'#praktika')
            ->with('success', 'Praktikum gelöscht.');
    }
}

