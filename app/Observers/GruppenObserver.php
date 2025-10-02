<?php


namespace App\Observers;

use App\Models\Gruppe;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class GruppenObserver
{
    public function created(Gruppe $gruppe): void
    {
        Log::channel('activity')->info('gruppe.created', [
            'gruppe_id' => $gruppe->getKey(),
            'attrs'     => Arr::only($gruppe->getAttributes(), [
                'name','code','projekt_id','standard_mitarbeiter_id','aktiv'
            ]),
            'user_id'   => Auth::id(),
        ]);
    }

    public function updated(Gruppe $gruppe): void
    {
        $changes  = Arr::except($gruppe->getChanges(), ['updated_at']);
        $original = Arr::only($gruppe->getOriginal(), array_keys($changes));

        Log::channel('activity')->info('gruppe.updated', [
            'gruppe_id' => $gruppe->getKey(),
            'before'    => $original,
            'after'     => $changes,
            'user_id'   => Auth::id(),
        ]);
    }

    // Fängt Speichern ohne Änderungen ab (z. B. wenn man "Speichern" klickt, aber nichts ändert)
    public function saved(Gruppe $gruppe): void
    {
        if (!$gruppe->wasRecentlyCreated && !$gruppe->wasChanged()) {
            Log::channel('activity')->info('gruppe.saved.nochange', [
                'gruppe_id' => $gruppe->getKey(),
                'user_id'   => Auth::id(),
            ]);
        }
    }

    public function deleted(Gruppe $gruppe): void
    {
        Log::channel('activity')->info('gruppe.deleted', [
            'gruppe_id' => $gruppe->getKey(),
            'user_id'   => Auth::id(),
        ]);
    }
}
