<?php

namespace App\Policies;

use App\Models\Gruppe;
use App\Models\Mitarbeiter;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class GruppePolicy
{
    /**
     * Darf Mitglieder verwalten (attach/detach)?
     * Regel: Admin ODER Standard-Mitarbeiter:in der Gruppe ODER in gruppe_mitarbeiter zugeordnet.
     */
    public function updateMembers(User $user, Gruppe $gruppe): bool
    {
        // 1) Admin?
        if (method_exists($user, 'isAdmin') ? $user->isAdmin() : (bool)($user->is_admin ?? false)) {
            return true;
        }

        // 2) Mitarbeiter-ID zum User robust ermitteln:
        $mitarbeiterId = $this->resolveMitarbeiterId($user);
        if (!$mitarbeiterId) {
            return false;
        }

        // 3) Standard-Mitarbeiter:in der Gruppe?
        if ((int)$gruppe->standard_mitarbeiter_id === (int)$mitarbeiterId) {
            return true;
        }

        // 4) In der Gruppe zugeordnete:r Mitarbeiter:in (Pivot)?
        $assigned = DB::table('gruppe_mitarbeiter')
            ->where('gruppe_id', $gruppe->getKey())
            ->where('mitarbeiter_id', $mitarbeiterId)
            ->exists();

        return $assigned;
    }

    /**
     * Versucht, die Mitarbeiter_id zum User zu finden.
     * - bevorzugt: users.mitarbeiter_id
     * - fallback: Match über E-Mail in tabelle mitarbeiter.Email
     */
    private function resolveMitarbeiterId(User $user): ?int
    {
        // direkte Spalte
        if (!empty($user->mitarbeiter_id)) {
            return (int)$user->mitarbeiter_id;
        }

        // fallback über Email
        if (!empty($user->email)) {
            $mit = Mitarbeiter::query()
                ->where('Email', $user->email)
                ->value('Mitarbeiter_id');
            if ($mit) return (int)$mit;
        }

        return null;
    }
}
