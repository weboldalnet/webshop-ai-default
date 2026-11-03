@php
    $wsRunStatus = [
        'pending' => ['Indításra vár', ''],
        'running' => ['Folyamatban', 'is-accent'],
        'paused' => ['Szünetel', 'is-warn'],
        'done' => ['Kész', 'is-good'],
        'cancelled' => ['Leállítva / visszavonva', ''],
    ][$status] ?? [$status, ''];
@endphp
<span class="ws-chip {{ $wsRunStatus[1] }}">{{ $wsRunStatus[0] }}</span>
