<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTeilnehmerRequest extends FormRequest
{
    public function rules(): array
    {
        $de  = implode(',', config('levels.deutsch'));
        $en  = implode(',', config('levels.englisch'));
        $ma  = implode(',', config('levels.mathe'));

        return [
            // … deine bestehenden Stammdatenregeln …

            // Eintritt (optional, aber falls befüllt: nur erlaubte Werte)
            'de_lesen_in'    => "nullable|in:$de",
            'de_hoeren_in'   => "nullable|in:$de",
            'de_schreiben_in'=> "nullable|in:$de",
            'de_sprechen_in' => "nullable|in:$de",
            'en_in'          => "nullable|in:$en",
            'ma_in'          => "nullable|in:$ma",

            // Ausstieg – beim Anlegen leer lassen, daher nur nullable (kein in-Check nötig hier)
            'de_lesen_out'     => 'nullable',
            'de_hoeren_out'    => 'nullable',
            'de_schreiben_out' => 'nullable',
            'de_sprechen_out'  => 'nullable',
            'en_out'           => 'nullable',
            'ma_out'           => 'nullable',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
