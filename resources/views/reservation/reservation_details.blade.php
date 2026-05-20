<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facture - {{ $ets_nom ?? 'Hôtel' }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f1f5f9;
            color: #1e293b;
            line-height: 1.5;
            padding: 40px 20px;
        }

        .invoice-wrapper {
            max-width: 850px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 32px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            position: relative;
        }

        /* Decorative top bar */
        .top-accent {
            height: 8px;
            background: linear-gradient(90deg, #0d6efd 0%, #0dcaf0 100%);
        }

        .invoice-header {
            padding: 50px 50px 30px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .brand h1 {
            font-size: 28px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.5px;
            margin-bottom: 8px;
        }

        .brand p {
            font-size: 14px;
            color: #64748b;
            max-width: 250px;
        }

        .invoice-title-box {
            text-align: right;
        }

        .invoice-label {
            display: inline-block;
            background: #eef2ff;
            color: #4f46e5;
            padding: 8px 16px;
            border-radius: 12px;
            font-weight: 800;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 15px;
        }

        .invoice-number {
            font-size: 18px;
            font-weight: 800;
            color: #0f172a;
        }

        .date-text {
            color: #94a3b8;
            font-size: 13px;
            margin-top: 4px;
        }

        .invoice-body {
            padding: 0 50px 50px;
        }

        .grid-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-bottom: 40px;
            padding: 30px;
            background: #f8fafc;
            border-radius: 24px;
        }

        .info-group h4 {
            font-size: 11px;
            font-weight: 800;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 12px;
        }

        .info-content p {
            font-size: 15px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 4px;
        }

        .info-content span {
            font-size: 13px;
            color: #64748b;
        }

        .table-container {
            margin-bottom: 40px;
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        th {
            text-align: left;
            padding: 15px 20px;
            font-size: 11px;
            font-weight: 800;
            color: #64748b;
            text-transform: uppercase;
            border-bottom: 2px solid #f1f5f9;
        }

        td {
            padding: 20px;
            font-size: 14px;
            font-weight: 600;
            color: #1e293b;
            border-bottom: 1px solid #f1f5f9;
        }

        .text-right { text-align: right; }

        .item-desc small {
            display: block;
            color: #94a3b8;
            font-weight: 500;
            margin-top: 4px;
        }

        .summary-wrapper {
            display: flex;
            justify-content: flex-end;
        }

        .summary-box {
            width: 320px;
            padding: 25px;
            background: #f8fafc;
            border-radius: 24px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            font-size: 14px;
        }

        .summary-row.total {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 2px dashed #e2e8f0;
            font-size: 20px;
            font-weight: 800;
            color: #0f172a;
        }

        .summary-row.paid { color: #10b981; }
        .summary-row.due { color: #ef4444; }

        .invoice-footer {
            padding: 30px 50px;
            background: #f8fafc;
            border-top: 1px solid #f1f5f9;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .status-pill {
            padding: 8px 20px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0.5px;
        }

        .status-pill.paid { background: #d1fae5; color: #065f46; }
        .status-pill.pending { background: #fee2e2; color: #991b1b; }

        .footer-note {
            text-align: right;
            font-size: 12px;
            color: #94a3b8;
        }

        @media print {
            body { background: white; padding: 0; }
            .invoice-wrapper { box-shadow: none; border-radius: 0; max-width: 100%; }
            .top-accent { display: none; }
        }
    </style>
</head>
<body>

    <div class="invoice-wrapper">
        <div class="top-accent"></div>

        <header class="invoice-header">
            <div class="brand">
                <h1>{{ $ets_nom }}</h1>
                <p>{{ $ets_adresse }}<br>Tél: {{ $ets_tel }}</p>
            </div>
            <div class="invoice-title-box">
                <span class="invoice-label">Facture Proforma</span>
                <div class="invoice-number">#{{ $facture->numero_facture ?? 'RES-'.str_pad($reservation->id, 5, '0', STR_PAD_LEFT) }}</div>
                <div class="date-text">Émise le {{ $facture->created_at->format('d/m/Y') }}</div>
            </div>
        </header>

        <main class="invoice-body">
            <div class="grid-info">
                <div class="info-group">
                    <h4>Client</h4>
                    <div class="info-content">
                        <p>{{ $reservation->client->nom }}</p>
                        <span>ID: {{ $reservation->client->identite_type }} • {{ $reservation->client->identite }}</span><br>
                        <span>Tél: {{ $reservation->client->telephone }}</span>
                    </div>
                </div>
                <div class="info-group">
                    <h4>Détails Séjour</h4>
                    <div class="info-content">
                        <p>Chambre #{{ $reservation->chambre->numero }}</p>
                        <span>Du {{ \Carbon\Carbon::parse($date_entree)->format('d/m/Y') }}</span><br>
                        <span>Au {{ \Carbon\Carbon::parse($date_sortie)->format('d/m/Y') }}</span>
                    </div>
                </div>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th class="text-right">Prix Unitaire</th>
                            <th class="text-right">Qte/Nuits</th>
                            <th class="text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $jours = $reservation->type_sejour === 'passage' ? 1 : max(1, $reservation->date_debut->diffInDays($reservation->date_fin));
                            $prixUnitaire = $reservation->type_sejour === 'passage' ? $reservation->chambre->prix_passage : $reservation->chambre->prix_nuit;
                            $devise = $reservation->chambre->prix_devise;
                        @endphp
                        <tr>
                            <td class="item-desc">
                                Hébergement - Chambre {{ $reservation->chambre->type }}
                                <small>Type de séjour : {{ ucfirst($reservation->type_sejour) }}</small>
                            </td>
                            <td class="text-right">{{ number_format($prixUnitaire, 2) }} {{ $devise }}</td>
                            <td class="text-right">{{ $jours }}</td>
                            <td class="text-right">{{ number_format($reservation->prix_facture ?? ($prixUnitaire * $jours), 2) }} {{ $devise }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="summary-wrapper">
                <div class="summary-box">
                    <div class="summary-row">
                        <span>Sous-total</span>
                        <span>{{ number_format($reservation->prix_facture, 2) }} {{ $devise }}</span>
                    </div>
                    <div class="summary-row paid">
                        <span>Déjà payé</span>
                        <span>- {{ number_format($total_paye, 2) }} {{ $devise }}</span>
                    </div>
                    <div class="summary-row total">
                        <span>Reste à payer</span>
                        <span>{{ number_format($reste_a_payer, 2) }} {{ $devise }}</span>
                    </div>
                </div>
            </div>
        </main>

        <footer class="invoice-footer">
            <span class="status-pill {{ $reste_a_payer <= 0 ? 'paid' : 'pending' }}">
                {{ $reste_a_payer <= 0 ? 'FACTURE RÉGLÉE' : 'PAIEMENT ATTENDU' }}
            </span>
            <div class="footer-note">
                <p>Merci pour votre confiance.</p>
                <p>Logiciel de gestion Loyambo • Millenium Horizon</p>
            </div>
        </footer>
    </div>

</body>
</html>
