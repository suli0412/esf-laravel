<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTeilnehmerRequest extends FormRequest
{
    public function rules(): array
    {
        $de  = implode(',', config('levels.deutsch'));
        $en  = implode(',', config('levels.englisch'));
        $ma  = implode(',', config('levels.mathe'));

        return [
            // Eintritt
            'de_lesen_in'    => "nullable|in:$de",
            'de_hoeren_in'   => "nullable|in:$de",
            'de_schreiben_in'=> "nullable|in:$de",
            'de_sprechen_in' => "nullable|in:$de",
            'en_in'          => "nullable|in:$en",
            'ma_in'          => "nullable|in:$ma",

            // Ausstieg – jetzt mit derselben „in:“ Regel, damit nur erlaubte Werte gewählt werden
            'de_lesen_out'     => "nullable|in:$de",
            'de_hoeren_out'    => "nullable|in:$de",
            'de_schreiben_out' => "nullable|in:$de",
            'de_sprechen_out'  => "nullable|in:$de",
            'en_out'           => "nullable|in:$en",
            'ma_out'           => "nullable|in:$ma",
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
