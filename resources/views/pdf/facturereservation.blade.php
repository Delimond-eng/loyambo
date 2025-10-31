<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Facture Réservation #{{ $reservation->id }}</title>
    <style>
        /* Styles généraux */
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            color: #333; 
            margin: 0;
            padding: 20px;
            line-height: 1.5;
            background-color: #f8f9fa;
        }
        
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.08);
            border-radius: 8px;
        }
        
        /* En-tête */
        .header { 
            text-align: center; 
            border-bottom: 2px solid #2c3e50; 
            margin-bottom: 30px;
            padding-bottom: 20px;
        }
        
        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #2c3e50;
            font-weight: 700;
        }
        
        .header h4 {
            margin: 10px 0 5px 0;
            font-size: 16px;
            font-weight: 500;
            color: #7f8c8d;
        }
        
        .header p {
            margin: 5px 0 0 0;
            font-size: 14px;
            color: #95a5a6;
        }
        
        /* Sections */
        .section {
            margin-bottom: 25px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 6px;
        }
        
        .section h3 {
            margin-top: 0;
            color: #2c3e50;
            font-size: 16px;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 8px;
        }
        
        /* Tableau */
        table {
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 10px;
        }
        
        th, td { 
            padding: 10px 12px; 
            text-align: left; 
            border-bottom: 1px solid #dee2e6;
        }
        
        th {
            font-weight: 600;
            color: #2c3e50;
            background-color: #e9ecef;
            width: 35%;
        }
        
        /* Total */
        .total { 
            text-align: right; 
            font-size: 18px; 
            margin-top: 30px;
            padding: 15px;
            background-color: #2c3e50;
            color: white;
            border-radius: 6px;
            font-weight: 700;
        }
        
        /* Pied de page */
        .footer {
            text-align: center; 
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            color: #7f8c8d;
            font-size: 14px;
        }
        
        /* Badge de durée */
        .duration-badge {
            display: inline-block;
            padding: 4px 10px;
            background: #3498db;
            color: white;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            margin-left: 10px;
        }
        
        .highlight {
            font-weight: 600;
            color: #2c3e50;
        }
        
        /* État conditionnel */
        .conditional-info {
            margin-top: 10px;
            padding: 8px 12px;
            background: #e8f4fd;
            border-left: 4px solid #3498db;
            border-radius: 4px;
        }
        
        .info-item {
            margin-bottom: 8px;
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <div class="header">
                        <h4>Facture de Réservation</h4>
            <p>Référence : #{{ $facture->numero_facture }}</p>
        </div>

        <div class="section">
            <h3>Informations du client</h3>
            <div class="info-item">
                <strong>Nom :</strong> {{ $reservation->client->nom }}
            </div>
            
            <!-- Téléphone conditionnel -->
            @if(!empty($reservation->client->telephone))
            <div class="info-item">
                <strong>Téléphone :</strong> {{ $reservation->client->telephone }}
            </div>
            @endif
            
            <!-- Email conditionnel -->
            @if(!empty($reservation->client->email))
            <div class="info-item">
                <strong>Email :</strong> {{ $reservation->client->email }}
            </div>
            @endif
            
            <!-- Adresse conditionnelle -->
            @if(!empty($reservation->client->adresse))
            <div class="info-item">
                <strong>Adresse :</strong> {{ $reservation->client->adresse }}
            </div>
            @endif
            
            <!-- Ville et code postal conditionnels -->
            @if(!empty($reservation->client->ville) || !empty($reservation->client->code_postal))
            <div class="info-item">
                <strong>Ville :</strong> 
                {{ $reservation->client->code_postal ?? '' }} {{ $reservation->client->ville ?? '' }}
            </div>
            @endif
            
            <!-- Pays conditionnel -->
            @if(!empty($reservation->client->pays))
            <div class="info-item">
                <strong>Pays :</strong> {{ $reservation->client->pays }}
            </div>
            @endif
            
            <!-- Informations complémentaires conditionnelles -->
            @if(!empty($reservation->client->notes))
            <div class="conditional-info">
                <strong>Notes :</strong> {{ $reservation->client->notes }}
            </div>
            @endif
        </div>

        <div class="section">
            <h3>Détails de la réservation</h3>
            <table>
                <tr>
                    <th>Chambre</th>
                    <td>{{ $chambre->numero ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Date d'arrivée</th>
                    <td>{{ \Carbon\Carbon::parse($reservation->date_debut)->format('d/m/Y H:i') }}</td>
                </tr>
                <tr>
                    <th>Date de départ</th>
                    <td>{{ \Carbon\Carbon::parse($reservation->date_fin)->format('d/m/Y H:i') }}</td>
                </tr>
                <tr>
                    <th>Durée du séjour</th>
                    <td>
                        <?php
                        $dateDebut = \Carbon\Carbon::parse($reservation->date_debut);
                        $dateFin = \Carbon\Carbon::parse($reservation->date_fin);
                        
                        // Calcul de la durée totale en secondes
                        $dureeTotaleSecondes = $dateDebut->diffInSeconds($dateFin);
                        
                        // Conversion en différentes unités
                        $mois = floor($dureeTotaleSecondes / (30 * 24 * 60 * 60));
                        $resteApresMois = $dureeTotaleSecondes % (30 * 24 * 60 * 60);
                        
                        $semaines = floor($resteApresMois / (7 * 24 * 60 * 60));
                        $resteApresSemaines = $resteApresMois % (7 * 24 * 60 * 60);
                        
                        $jours = floor($resteApresSemaines / (24 * 60 * 60));
                        $resteApresJours = $resteApresSemaines % (24 * 60 * 60);
                        
                        $heures = floor($resteApresJours / (60 * 60));
                        $resteApresHeures = $resteApresJours % (60 * 60);
                        
                        $minutes = floor($resteApresHeures / 60);
                        $secondes = $resteApresHeures % 60;
                        
                        // Construction de la chaîne de durée
                        $dureeFormatee = '';
                        $elements = [];
                        
                        if ($mois > 0) {
                            $elements[] = $mois . ' mois';
                        }
                        if ($semaines > 0) {
                            $elements[] = $semaines . ' semaine' . ($semaines > 1 ? 's' : '');
                        }
                        if ($jours > 0) {
                            $elements[] = $jours . ' jour' . ($jours > 1 ? 's' : '');
                        }
                        if ($heures > 0) {
                            $elements[] = $heures . ' heure' . ($heures > 1 ? 's' : '');
                        }
                        if ($minutes > 0) {
                            $elements[] = $minutes . ' minute' . ($minutes > 1 ? 's' : '');
                        }
                        if ($secondes > 0 && count($elements) < 2) {
                            $elements[] = $secondes . ' seconde' . ($secondes > 1 ? 's' : '');
                        }
                        
                        // On prend les 2 premiers éléments les plus significatifs
                        $dureeFormatee = implode(' et ', array_slice($elements, 0, 2));
                        
                        echo $dureeFormatee ?: '0 minute';
                        ?>
                        <span class="duration-badge">
                            @if($mois > 0)
                            Séjour longue durée
                            @elseif($semaines > 0)
                            Séjour semaine
                            @elseif($jours > 0)
                            Séjour court
                            @else
                            Séjour très court
                            @endif
                        </span>
                    </td>
                </tr>
                
                <!-- Type de chambre conditionnel -->
                @if(!empty($chambre->type))
                <tr>
                    <th>Type de chambre</th>
                    <td>{{ $chambre->type }}</td>
                </tr>
                @endif
                
                <!-- Équipements conditionnels -->
                @if(!empty($chambre->equipements))
                <tr>
                    <th>Équipements</th>
                    <td>{{ $chambre->equipements }}</td>
                </tr>
                @endif
                
                <!-- Services additionnels conditionnels -->
                @if(!empty($reservation->services_additionnels))
                <tr>
                    <th>Services additionnels</th>
                    <td>{{ $reservation->services_additionnels }}</td>
                </tr>
                @endif
                
                <tr>
                    <th>Montant total</th>
                    <td class="highlight">{{ number_format($facture->total_ttc, 2, ',', ' ') }} {{$chambre->prix_devise ?? 'EUR'}}</td>
                </tr>
            </table>
            
            <!-- Notes conditionnelles sur la réservation -->
            @if(!empty($reservation->notes))
            <div class="conditional-info" style="margin-top: 15px;">
                <strong>Notes de réservation :</strong> {{ $reservation->notes }}
            </div>
            @endif
        </div>

        <div class="total">
            <strong>Total à payer :</strong> {{ number_format($facture->total_ttc, 2, ',', ' ') }} {{$chambre->prix_devise ?? 'EUR'}}
        </div>

        <!-- Informations de paiement conditionnelles -->
        @if(!empty($facture->modalites_paiement))
        <div class="section">
            <h3>Modalités de paiement</h3>
            <p>{{ $facture->modalites_paiement }}</p>
        </div>
        @endif

        <div class="footer">
            <p>Merci pour votre confiance !</p>
            <!-- Coordonnées conditionnelles -->
            @if(!empty($facture->emplacement->contact))
            <p style="font-size: 12px; margin-top: 5px;">
                Pour toute question : {{ $facture->emplacement->contact }}
            </p>
            @endif
        </div>
    </div>
</body>
</html>