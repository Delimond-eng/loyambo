<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-size: 12px; font-family: Arial, sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 5px; text-align: center; }
        th { background-color: #ddd; }
        h3 { text-align: center; margin-bottom: 15px; }
    </style>
</head>
<body>
<h3>FICHE DE STOCK</h3>
<table>
    <thead>
        <tr>
            <th>Produit</th>
            <th>Emplacement</th>
            <th>Stock Init.</th>
            <th>Entrée</th>
            <th>Sortie</th>
            <th>Transf. +</th>
            <th>Transf. -</th>
            <th>Vente</th>
            <th>Ajust. +</th>
            <th>Ajust. -</th>
            <th>Solde</th>
        </tr>
    </thead>
    <tbody>
        @foreach($stocks as $s)
        <tr>
            <td>{{ $s->produit->libelle }}</td>
            <td>{{ $s->emplacement->libelle ?? '-' }}</td>
            <td>{{ $s->stock_initial }}</td>
            <td>{{ $s->total_entree }}</td>
            <td>{{ $s->total_sortie }}</td>
            <td>{{ $s->total_transfert_entree }}</td>
            <td>{{ $s->total_transfert_sortie }}</td>
            <td>{{ $s->total_vente }}</td>
            <td>{{ $s->ajustement_plus }}</td>
            <td>{{ $s->ajustement_moins }}</td>
            <td>{{ $s->solde }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
</body>
</html>
