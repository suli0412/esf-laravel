@php
    // akzeptiere beide Varianten: createdBy/updatedBy ODER creator/updater
    $creator = $model->createdBy ?? $model->creator ?? null;
    $updater = $model->updatedBy ?? $model->updater ?? null;

    $formatName = function ($user) {
        if (!$user) return null;
        $vn = trim((string)($user->Vorname ?? ''));
        $nn = trim((string)($user->Nachname ?? ''));
        $full = trim($vn.' '.$nn);
        if ($full === '' && !empty($user->name)) {
            $full = $user->name;
        }
        return $full !== '' ? $full : null;
    };

    $creatorName = $formatName($creator) ?? 'unbekannt';
    $updaterName = $formatName($updater) ?? 'unbekannt';

    $createdAt = optional($model->created_at)->format('d.m.Y H:i');
    $updatedAt = optional($model->updated_at)->format('d.m.Y H:i');
@endphp

<div class="mt-4 text-sm text-gray-500">
    <div>
        Erstellt von: {{ $creatorName }}
        @if($createdAt) am {{ $createdAt }} @endif
    </div>
    <div>
        Zuletzt geändert von: {{ $updaterName }}
        @if($updatedAt) am {{ $updatedAt }} @endif
    </div>
</div>
