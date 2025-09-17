<!doctype html>
<html lang="de">
<head>
  <meta charset="utf-8">
  <title>{{ $title ?? 'Dokument' }}</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    html,body { background:#f5f6f8; margin:0; padding:0; }
    .wrap { max-width: 860px; margin: 24px auto; padding: 0 16px; }
    .card { background:#fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,.06); padding: 24px; }
    .toolbar { display:flex; gap:8px; margin-bottom:12px; }
    .btn { display:inline-block; padding:10px 14px; border-radius:8px; border:1px solid #d1d5db; text-decoration:none; color:#111; background:#fff; }
    .btn-primary { background:#2563eb; color:#fff; border-color:#2563eb; }
    .doc { background:#fff; padding:24px; border:1px solid #eee; border-radius:10px; }
  </style>
</head>
<body>
  <div class="wrap">
    <div class="toolbar">
      <a href="javascript:window.print()" class="btn">Drucken</a>

      @if(($pdfAvailable ?? false) && !empty($formPayload))
        <form action="{{ route('dokumente.go') }}" method="POST" target="_blank">
          @csrf
          <input type="hidden" name="dokument_slug" value="{{ $formPayload['dokument_slug'] ?? '' }}">
          <input type="hidden" name="teilnehmer_id" value="{{ $formPayload['teilnehmer_id'] ?? '' }}">
          @if(!empty($formPayload['projekt_id']))    <input type="hidden" name="projekt_id" value="{{ $formPayload['projekt_id'] }}"> @endif
          @if(!empty($formPayload['mitarbeiter_id']))<input type="hidden" name="mitarbeiter_id" value="{{ $formPayload['mitarbeiter_id'] }}"> @endif
          <input type="hidden" name="pdf" value="1">
          <button class="btn btn-primary" type="submit">Als PDF herunterladen</button>
        </form>
      @endif

      <a href="{{ route('dokumente.index') }}" class="btn">Zurück</a>
    </div>

    <div class="card">
      <div class="doc">
        {!! $html !!}
      </div>
    </div>
  </div>
</body>
</html>
