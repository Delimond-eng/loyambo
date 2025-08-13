<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            margin: 40px;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #2c3e50;
        }

        .header small {
            color: #7f8c8d;
            font-size: 14px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            margin-top: 10px;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 6px 8px;
            vertical-align: top;
        }

        th {
            background-color: #ecf0f1;
            text-align: left;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .footer {
            margin-top: 40px;
            font-size: 12px;
            text-align: center;
            color: #999;
        }

        .logo {
            width: 60px;
            position: absolute;
            top: 30px;
            left: 30px;
        }
    </style>
</head>
<body>

    {{-- Logo fictif --}}
    <!-- <img src="https://dummyimage.com/60x60/3498db/ffffff&text=VM" alt="Logo" class="logo"> -->

    <div class="header">
        <h1>Rapport des Visites</h1>
        <small>Export généré le {{ now()->format('d/m/Y à H:i') }}</small>
    </div>

    <table>
        <thead>
            <tr>
                <th>Nom du Visiteur</th>
                <th>Entreprise / Adresse</th>
                <th>Type</th>
                <th>Date</th>
                <th>Entrée</th>
                <th>Sortie</th>
                <th>Durée</th>
                <th>Département</th>
                <th>Objet</th>
                <th>Autorisé par</th>
                <th>Statut</th>
                <th>Remarques</th>
            </tr>
        </thead>
        <tbody>
            @foreach($visits as $visit)
                <tr>
                    <td>{{ $visit->full_name }}</td>
                    <td>{{ $visit->company_or_address }}</td>
                    <td>{{ ucfirst($visit->visitor_type) }}</td>
                    <td>{{ optional($visit->visit_date)->format('d/m/Y') }}</td>
                    <td>{{ $visit->time_in }}</td>
                    <td>{{ $visit->time_out }}</td>
                    <td>{{ $visit->stay_time }}</td>
                    <td>{{ $visit->department }}</td>
                    <td>{{ $visit->purpose }}</td>
                    <td>{{ $visit->entry_authorized_by }}</td>
                    <td>{{ ucfirst($visit->status) }}</td>
                    <td>{{ $visit->remarks }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        © {{ date('Y') }} Visitor Management System — Rapport automatisé
    </div>

</body>
</html>
