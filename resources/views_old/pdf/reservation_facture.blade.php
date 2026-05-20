<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facture Hotel NO.{{ $facture->numero_facture }}</title>
    <style>
        /* --- Styles de base et Réinitialisation pour domPDF --- */
        * {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-size: 10pt; /* Taille de base plus petite pour une page unique */
            line-height: 1.4;
            color: #333;
        }

        .container {
            width: 100%;
            max-width: 750px; /* Largeur A4 typique pour PDF */
            margin: 0 auto;
            padding: 20px;
        }

        /* --- Tableau de mise en page général (très important pour domPDF) --- */
        .full-width-table {
            width: 100%;
            border-collapse: collapse;
        }

        /* --- En-tête --- */
        .header-section {
            padding: 15px 0 30px 0;
            border-bottom: 2px solid #2563eb;
            margin-bottom: 10px;
        }

        .header-section h1 {
            font-size: 24pt;
            font-weight: bold;
            color: #2563eb;
        }

        .header-section .subtitle {
            font-size: 14pt;
            color: #666;
            margin-top: 5px;
            padding-bottom: 20px;
        }

        .header-section .meta-label {
            font-size: 8pt;
            color: #888;
            padding-bottom: 2px;
        }

        .header-section .meta-value {
            font-size: 10pt;
            font-weight: bold;
             padding-bottom: 10px;
        }

        /* --- Sections d'information (Client/Séjour) --- */
        .info-box-header {
            font-size: 9pt;
            font-weight: bold;
            color: #4b5563;
            text-transform: uppercase;
            padding: 5px 0;
            border-bottom: 1px solid #ddd;
            margin-bottom: 10px;
        }

        .data-label {
            font-size: 8pt;
            color: #888;
            padding-bottom: 2px;
        }

        .data-value {
            font-size: 10pt;
            font-weight: bold;
            color: #1f2937;
        }

        .info-cell {
            padding-bottom: 15px;
        }
        
        .client-section {
            padding-bottom: 20px;
        }

        /* --- Tableau de Facturation (Détails) --- */
        .billing-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .billing-table thead {
            background-color: #f3f4f6;
        }

        .billing-table th {
            padding: 10px 15px;
            text-align: left;
            font-size: 9pt;
            font-weight: bold;
            color: #374151;
            text-transform: uppercase;
            border-bottom: 1px solid #ddd;
        }

        .billing-table td {
            padding: 10px 15px;
            font-size: 9pt;
            color: #1f2937;
            border-bottom: 1px solid #eee;
        }
        
        .billing-table th:last-child, 
        .billing-table td:last-child {
            text-align: right;
        }

        /* --- Totaux et Paiements --- */
        .totals-container {
            width: 100%;
            margin-top: 20px;
        }

        .totals-box {
            width: 300px;
            float: right; /* Pour aligner à droite */
        }
        
        .totals-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .totals-table td {
            padding: 6px 0;
            font-size: 10pt;
        }
        
        .totals-table .total-label {
            text-align: left;
            color: #4b5563;
        }
        
        .totals-table .total-amount {
            text-align: right;
            font-weight: bold;
        }
        
        .totals-table .paid .total-amount {
            color: #16a34a;
        }

        .totals-table tr.final td {
            padding-top: 10px;
            font-size: 12pt;
            border-top: 2px solid #ccc;
        }
        
        .totals-table tr.final .total-amount {
            color: #dc2626; /* Reste à payer en rouge */
        }
        
        .totals-table tr.final.paid .total-amount {
            color: #16a34a; /* Si le reste est zéro, en vert */
        }
        
        /* --- Pied de page --- */
        .footer-section {
            clear: both; /* Important pour les floats */
            padding-top: 30px;
            text-align: center;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 9pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            background-color: #dcfce7;
            color: #166534;
            margin-bottom: 10px;
        }

        .thank-you-message {
            font-size: 9pt;
            color: #666;
        }
    </style>
</head>

<body>
    <div class="container">

        <table class="full-width-table header-section" style="padding-bottom: 10px;">
            <tr>
                <td style="width: 50%;">
                    <h1>{{ $ets_nom ?? 'Hôtel Élégance' }}</h1>
                    <p style="margin-top: 10px; font-size: 9pt; color: #666;">
                        {{ $ets_adresse ?? 'Adresse de l\'établissement' }}<br>
                        Tél : {{ $ets_tel ?? '00000000' }}
                    </p>
                    <div class="subtitle">FACTURE</div>
                </td>
                <td style="width: 50%; text-align: right;">
                    <div style="margin-bottom: 5px;">
                        <span class="meta-label">N° de Facture :</span><br>
                        <span class="meta-value">#{{ $facture->numero_facture }}</span>
                    </div>
                    <div>
                        <span class="meta-label">Date :</span><br>
                        <span class="meta-value">{{ $facture->date_facture->format('d/m/Y') }}</span>
                    </div>
                </td>
            </tr>
        </table>

        <table class="full-width-table client-section">
            <tr>
                <td style="width: 50%; padding-right: 20px;">
                    <div class="info-box-header">Informations du Client</div>
                    <table class="full-width-table">
                        <tr>
                            <td class="info-cell"><span class="data-label">Nom complet</span><br><span class="data-value">{{ $reservation->client->nom }}</span></td>
                            <td class="info-cell"><span class="data-label">N° d'identité</span><br><span class="data-value">{{ $reservation->client->identite }}</span></td>
                        </tr>
                        <tr>
                            <td class="info-cell"><span class="data-label">Téléphone</span><br><span class="data-value">{{ $reservation->client->telephone ?? '-' }}</span></td>
                            <td class="info-cell"><span class="data-label">Email</span><br><span class="data-value">{{ $reservation->client->email ?? '-' }}</span></td>
                        </tr>
                    </table>
                </td>
                <td style="width: 50%;">
                    <div class="info-box-header">Détails du Séjour</div>
                    <table class="full-width-table">
                        <tr>
                            <td class="info-cell"><span class="data-label">Date d'entrée</span><br><span class="data-value">{{ \Carbon\Carbon::parse($date_entree)->format('d/m/Y') }}</span></td>
                            <td class="info-cell"><span class="data-label">Date de sortie</span><br><span class="data-value">{{ \Carbon\Carbon::parse($date_sortie)->format('d/m/Y') }}</span></td>
                        </tr>
                        <tr>
                             <td class="info-cell"></td>
                             <td class="info-cell"></td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <div class="info-box-header" style="border: none; margin-bottom: 0;">Détails de la Facturation</div>
        <table class="billing-table">
            <thead>
                <tr>
                    <th style="width: 50%;">Description</th>
                    <th style="width: 15%;">Prix/Jour</th>
                    <th style="width: 15%;">Jours</th>
                    <th style="width: 20%;">Total</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $prix  = (float) $reservation->chambre->prix;
                    $jours = $reservation->date_debut->diffInDays($reservation->date_fin);
                    $total = $prix * $jours;
                @endphp
                <tr>
                    <td>
                        Chambre #{{ $reservation->chambre->id }} 
                        @if(!empty($reservation->chambre->nom)) - {{ $reservation->chambre->nom }} @endif
                    </td>
                    <td>{{ number_format($prix, 2) }} {{ $facture->devise }}</td>
                    <td>{{ $jours }}</td>
                    <td>{{ number_format($total, 2) }} {{ $facture->devise }}</td>
                </tr>
                </tbody>
        </table>
        
        @if($facture->payments->count())
        <div class="info-box-header" style="margin-top: 20px; border: none; margin-bottom: 0;">Paiements Reçus</div>
        <table class="billing-table" style="width: 100%;">
            <thead>
                <tr>
                    <th style="width: 35%;">Date</th>
                    <th style="width: 30%;">Mode</th>
                    <th style="width: 35%;">Montant</th>
                </tr>
            </thead>
            <tbody>
                @foreach($facture->payments as $p)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($p->created_at)->format('d/m/Y') }}</td>
                        <td>{{ $p->mode }}</td>
                        <td style="text-align: right;">{{ number_format($p->amount, 2) }} {{ $facture->devise }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        <div class="totals-container">
            <div class="totals-box">
                <table class="totals-table">
                    <tr>
                        <td class="total-label">Total Facture (TTC)</td>
                        <td class="total-amount">{{ number_format($facture->total_ttc, 2) }} {{ $facture->devise }}</td>
                    </tr>
                    <tr class="paid">
                        <td class="total-label">Total Payé</td>
                        <td class="total-amount">{{ number_format($total_paye, 2) }} {{ $facture->devise }}</td>
                    </tr>
                    @php
                        $is_paid = $reste_a_payer <= 0.01;
                    @endphp
                    <tr class="final @if($is_paid) paid @else pending @endif">
                        <td class="total-label">Reste à Payer</td>
                        <td class="total-amount">{{ number_format(max(0, $reste_a_payer), 2) }} {{ $facture->devise }}</td>
                    </tr>
                </table>
            </div>
        </div>
        
        <div class="footer-section">
             <span class="status-badge">{{ strtoupper($facture->statut) }}</span>
            <p class="thank-you-message">
                Merci pour votre confiance et votre séjour.<br>
                Au plaisir de vous revoir bientôt !
            </p>
        </div>

    </div>
</body>
</html>