<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>{{ $title ?? 'Rapport' }}</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; color: #111; }
        .header { text-align: center; margin-bottom: 12px; }
        .header h2 { margin: 0 0 4px 0; font-size: 18px; }
        .header p { margin: 2px 0; color: #555; }
        .meta { font-size: 10px; color: #777; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 6px 8px; }
        th { background: #f2f2f2; text-align: left; }
        tfoot td { font-weight: bold; background: #fafafa; }
    </style>
</head>
<body>
    <div class="header">
        <h2>{{ $title ?? 'Rapport' }}</h2>
        @if(!empty($subtitle))
            <p>{{ $subtitle }}</p>
        @endif
        @if(!empty($filters))
            <p class="meta">{{ $filters }}</p>
        @endif
        <p class="meta">Généré le {{ $generated_at ?? now()->format('d/m/Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                @foreach($headers ?? [] as $header)
                    <th>{{ $header }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($rows ?? [] as $row)
                <tr>
                    @foreach($row as $cell)
                        <td>{{ $cell }}</td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($headers ?? []) }}" style="text-align:center; color:#666;">Aucune donnée.</td>
                </tr>
            @endforelse
        </tbody>
        @if(!empty($footer))
            <tfoot>
                <tr>
                    @foreach($footer as $cell)
                        <td>{{ $cell }}</td>
                    @endforeach
                </tr>
            </tfoot>
        @endif
    </table>
</body>
</html>
