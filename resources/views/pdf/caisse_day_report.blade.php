<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rapport de Caisse - {{ $saleDay->sale_date }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 0;
            background-color: #f9fafc;
            color: #333;
        }
        .page {
            padding: 30px;
        }
        .header {
            text-align: center;
            margin-bottom: 25px;
            border-bottom: 2px solid #1565c0;
            padding-bottom: 10px;
        }
        .header h2 {
            color: #1565c0;
            margin: 0;
            text-transform: uppercase;
        }
        .header p {
            margin: 3px 0;
            color: #666;
        }

        .section {
            background: #fff;
            border: 1px solid #d1d9e6;
            padding: 15px 20px;
            margin-bottom: 30px;
        }

        .section h3 {
            margin: 0 0 12px;
            color: #1565c0;
            font-size: 14px;
            border-left: 4px solid #1565c0;
            padding-left: 8px;
            text-transform: uppercase;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th {
            background: #1565c0;
            color: #fff;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.4px;
        }

        th, td {
            padding: 7px;
            border: 1px solid #ccd4e0;
            text-align: center;
        }

        tr:nth-child(even) {
            background-color: #f4f7fb;
        }

        tr.total-row {
            background-color: #e3f2fd;
            font-weight: bold;
            color: #0d47a1;
        }

        .footer {
            text-align: center;
            font-size: 10px;
            color: #777;
            border-top: 1px solid #ddd;
            margin-top: 40px;
            padding-top: 8px;
        }

        .negative {
            color: #d32f2f;
        }
        .positive {
            color: #388e3c;
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="header">
            <h2>Rapport de Caisse</h2>
            <p><strong>Date de vente : </strong> {{ $saleDay->start_time->format('d/m/Y H:i') }} au {{ $saleDay->end_time->format('d/m/Y H:i') }}</p>
        </div>

        @foreach($groupedReports as $caissierId => $reports)
            @php
                $caissier = $reports->first()->caissier;
                $totaux = [
                    'valeur_theorique' => $reports->sum('valeur_theorique'),
                    'total_especes' => $reports->sum('total_especes'),
                    'tickets_emis' => $reports->sum('tickets_emis'),
                    'tickets_serveur' => $reports->sum('tickets_serveur'),
                    'taux' => $reports->avg('taux'),
                ];
                $ecarts = [
                    'especes' => $totaux['total_especes'] - $totaux['valeur_theorique'],
                    'tickets' => $totaux['tickets_serveur'] - $totaux['tickets_emis'],
                ];
            @endphp

            <div class="section">
                <h3>Caissier : {{ $caissier->name ?? 'Inconnu' }}</h3>

                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Serveur</th>
                            <th>Valeur Théorique</th>
                            <th>Total Espèces</th>
                            <th>Tickets Système</th>
                            <th>Tickets Serveur</th>
                            <th>Taux</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reports as $i => $r)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $r->serveur->name ?? '-' }}</td>
                                <td>{{ number_format($r->valeur_theorique, 2, ',', ' ') }}</td>
                                <td>{{ number_format($r->total_especes, 2, ',', ' ') }}</td>
                                <td>{{ $r->tickets_emis }}</td>
                                <td>{{ $r->tickets_serveur }}</td>
                                <td>{{ number_format($r->taux, 2) }}</td>
                            </tr>
                        @endforeach

                        <!-- Ligne Totaux intégrée -->
                        <tr class="total-row">
                            <td colspan="2">TOTAL</td>
                            <td>{{ number_format($totaux['valeur_theorique'], 2, ',', ' ') }}</td>
                            <td>{{ number_format($totaux['total_especes'], 2, ',', ' ') }}</td>
                            <td>{{ $totaux['tickets_emis'] }}</td>
                            <td>{{ $totaux['tickets_serveur'] }}</td>
                            <td>-</td>
                        </tr>

                        <!-- Ligne Écarts -->
                        <tr class="total-row">
                            <td colspan="2">ÉCARTS</td>
                            <td colspan="2" class="{{ $ecarts['especes'] < 0 ? 'negative' : ($ecarts['especes'] > 0 ? 'positive' : '') }}">
                                {{ number_format($ecarts['especes'], 2, ',', ' ') }}
                            </td>
                            <td colspan="2" class="{{ $ecarts['tickets'] < 0 ? 'negative' : ($ecarts['tickets'] > 0 ? 'positive' : '') }}">
                                {{ $ecarts['tickets'] }}
                            </td>
                            <td>-</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @endforeach

        <div class="footer">
            <p>Loyambo — Rapport généré automatiquement le {{ now()->format('d/m/Y à H:i') }}</p>
        </div>
    </div>
</body>
</html>