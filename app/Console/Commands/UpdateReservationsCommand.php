<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Reservation;
use App\Models\Chambre;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class UpdateReservationsCommand extends Command
{
    protected $signature = 'reservations:update';
    protected $description = 'Met à jour les réservations expirées (nuit/passages) et libère automatiquement les chambres.';

    public function handle()
    {
        $today = Carbon::today();

        $this->info("Début du traitement des réservations expirées...");

        DB::transaction(function () use ($today) {

            // 1. Récupérer toutes les réservations arrivées à échéance (nuit + passage)
            $activeStatuses = ['confirmée', 'confirmÃ©e', 'confirmÃƒÂ©e', 'en_attente', 'occupée', 'occupÃ©e', 'occupÃƒÂ©e'];
            $expired = Reservation::whereIn('statut', $activeStatuses)
                ->where(function ($q) use ($today) {
                    $q->where('date_fin', '<', $today)
                      ->orWhere(function ($q2) use ($today) {
                          $q2->where('type_sejour', 'passage')
                             ->where('date_debut', '<', $today);
                      });
                })
                ->get();
            $this->info($expired->count() . " réservations expirées trouvées.");

            foreach ($expired as $reservation) {

                // 2. Mettre à jour la réservation
                $reservation->update([
                    'statut' => 'terminée'
                ]);

                $this->info("Reservation #{$reservation->id} terminée.");

                // 2.b Clôturer la facture liée si encore en attente
                if ($reservation->facture && $reservation->facture->statut === 'en_attente') {
                    $reservation->facture->update(['statut' => 'annulée']);
                    $this->info("Facture #{$reservation->facture->id} passée en annulée (expiration).");
                }

                // 3. Vérifier la chambre concernée
                if ($reservation->chambre_id) {

                    $chambre = Chambre::find($reservation->chambre_id);

                    if ($chambre) {

                        // Vérifier s’il y a encore des réservations actives pour cette chambre
                        $hasActive = Reservation::where('chambre_id', $chambre->id)
                            ->whereIn('statut', $activeStatuses)
                            ->where('date_fin', '>=', $today)
                            ->exists();

                        if (!$hasActive) {
                            // 4. Libérer la chambre
                            $chambre->update(['statut' => 'libre']);
                            $this->info("Chambre #{$chambre->id} remise à libre.");
                        }
                    }
                }
            }
        });

        $this->info("Mise à jour terminée !");
        return 0;
    }
}
