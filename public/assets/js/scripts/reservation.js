import { post, get, postJson } from "../modules/http.js";
import Paginator from "../components/pagination.js";
document.querySelectorAll(".AppReservation").forEach((el) => {
    new Vue({
        el: el,
        components: { Paginator },
        data() {
            return {
                error: null,
                result: null,
                isLoading: false,
                isDataLoading: false,
                step: 1,
                chambres: [],
                reservations: [],
                pagination: { current_page: 1, last_page: 1, total: 0, per_page: 10 },
                form: {
                    chambre_id: "",
                    date_debut: moment().format("YYYY-MM-DD"),
                    date_fin: moment().add(1, "days").format("YYYY-MM-DD"),
                    reservation_id: "",
                    type_sejour: "nuit",
                    prix_negocie: null,
                    remove_payment: false,
                    paiement: { mode: "", amount: "", devise: "USD", mode_ref: "" },
                    client: { nom: "", telephone: "", email: "", identite: "", identite_type: "" },
                },
                search: "",
                selectedChambre: null,
                selectedReservation: null,
                selectedMode: null,
                filter: "",
                modes: {
                    cash: { icon: "fa fa-money", label: "Espèces" },
                    mobile: { icon: "fa fa-mobile", label: "Mobile" },
                    cheque: { icon: "fa fa-file-text", label: "Chèque" },
                    virement: { icon: "fa fa-university", label: "Virement" },
                    card: { icon: "fa fa-credit-card", label: "Carte" },
                },
                formPay: { mode: "", amount: "", devise: "", mode_ref: "", reservation_id: "" },
                cancel_id: "",
                bed_id: "",
            };
        },
        mounted() {
            this.viewAllReservations();
            this.viewAllChambres();
            this.whenModalHidden();
        },
        methods: {
            normalizeText(value) {
                if (!value || typeof value !== 'string') return value;
                if (!value.includes('\u00C3')) return value;
                try {
                    const bytes = Uint8Array.from(value, (c) => c.charCodeAt(0));
                    return new TextDecoder('utf-8').decode(bytes);
                } catch {
                    return value;
                }
            },


            normalizeChambre(chambre) {
                if (!chambre) return chambre;
                chambre.statut = this.normalizeText(chambre.statut);
                chambre.type = this.normalizeText(chambre.type);
                if (Array.isArray(chambre.reservations)) {
                    chambre.reservations = chambre.reservations.map((r) => this.normalizeReservation(r));
                }
                return chambre;
            },


            normalizeReservation(reservation) {
                if (!reservation) return reservation;
                reservation.statut = this.normalizeText(reservation.statut);
                reservation.type_sejour = this.normalizeText(reservation.type_sejour);
                if (reservation.chambre) reservation.chambre = this.normalizeChambre(reservation.chambre);
                if (reservation.client) reservation.client.nom = this.normalizeText(reservation.client.nom);
                return reservation;
            },

            statusKey(value) {
                const source = this.normalizeText(value || "");
                const s = source.normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase();
                if (s.includes("libre")) return "libre";
                if (s.includes("occup")) return "occupee";
                if (s.includes("reserv")) return "reservee";
                return "reservee";
            },

            statusClass(value) {
                return `status-${this.statusKey(value)}`;
            },

            roomActionLabel(chambre) {
                const key = this.statusKey(chambre?.statut || "");
                if (key === "libre") return "Réserver maintenant";
                if (key === "occupee") return "Gérer occupation";
                return "Voir réservations";
            },

            hasFactureInfo(reservation) {
                if (!reservation) return false;
                return Object.prototype.hasOwnProperty.call(reservation, "facture");
            },

            setRoomFilter(value) {
                this.filter = value || "";
            },

            normalizeSejourDates() {
                if (this.form.type_sejour === "passage" && this.form.date_debut) {
                    this.form.date_fin = this.form.date_debut;
                }
            },

            getPricing() {
                if (!this.selectedChambre) return null;
                const prixUnit = this.form.type_sejour === "passage" ? this.selectedChambre.prix_passage : this.selectedChambre.prix_nuit;
                const days = this.form.type_sejour === "nuit" ? this.getDays(this.form.date_debut, this.form.date_fin) : 1;
                const safeDays = Math.max(1, Number(days) || 1);
                const unit = parseFloat(prixUnit || 0) || 0;
                const baseTotal = safeDays * unit;
                const negotiated = this.form.prix_negocie;
                const hasNegotiated = negotiated !== null && negotiated !== "" && !Number.isNaN(Number(negotiated));
                const applied = hasNegotiated ? Math.max(0, Number(negotiated)) : baseTotal;
                const discount = Math.max(0, baseTotal - applied);
                return { unit, days: safeDays, baseTotal, applied, discount };
            },

            prepareReservationPayload() {
                const payload = JSON.parse(JSON.stringify(this.form));
                if (!payload.paiement || !payload.paiement.mode) {
                    delete payload.paiement;
                }
                if (payload.type_sejour === "passage" && payload.date_debut) {
                    payload.date_fin = payload.date_debut;
                }
                return payload;
            },

            requestRemovePayment() {
                if (!this.selectedReservation) {
                    $.toast({ heading: "Information", text: "Sélectionnez une réservation.", icon: "info", position: "top-right" });
                    return;
                }
                if (this.hasFactureInfo(this.selectedReservation) && !this.selectedReservation.facture) {
                    $.toast({ heading: "Information", text: "Aucune facture à annuler pour cette réservation.", icon: "info", position: "top-right" });
                    return;
                }
                Swal.fire({
                    icon: "warning",
                    title: "Retirer le paiement ?",
                    text: "La facture et les paiements seront supprimés. La réservation repassera en attente.",
                    showCancelButton: true,
                    confirmButtonText: "Oui, retirer",
                    cancelButtonText: "Non"
                }).then((res) => {
                    if (!res.isConfirmed) return;
                    this.form.remove_payment = true;
                    this.updateReservation();
                });
            },


            viewAllChambres() {
                this.isDataLoading = true;
                get("/chambres.all")
                    .then(({ data }) => {
                        this.isDataLoading = false;
                        this.chambres = (data.chambres || []).map((c) => this.normalizeChambre(c));
                        if ($("#filter").length) {
                            const val = $("#filter").val();
                            if (val === "reservee") this.filter = "réservée";
                            else if (val === "occupee") this.filter = "occupée";
                            else if (val === "all") this.filter = "";
                            else this.filter = val || "";
                        }
                    })
                    .catch(() => (this.isDataLoading = false));
            },

            viewAllReservations() {
                if (location.pathname === "/reservations") {
                    this.isDataLoading = true;
                    get(`/reservations.all?page=${this.pagination.current_page}&per_page=${this.pagination.per_page}`)
                        .then(({ data }) => {
                            this.isDataLoading = false;
                            this.reservations = (data.reservations.data || []).map((r) => this.normalizeReservation(r));
                            this.pagination = {
                                current_page: data.reservations.current_page,
                                last_page: data.reservations.last_page,
                                total: data.reservations.total,
                                per_page: data.reservations.per_page,
                            };
                        })
                        .catch(() => (this.isDataLoading = false));
                }
            },

            changePage(page) {
                this.pagination.current_page = page;
                this.viewAllReservations();
            },
            onPerPageChange(perPage) {
                this.pagination.per_page = perPage;
                this.pagination.current_page = 1;
                this.viewAllReservations();
            },

            refreshPricing() {
                this.computeMontant();
            },

            viewReservation(data) {
                if (!data.facture || !data.facture.id) {
                    $.toast({ heading: "Indisponible", text: "Aucune facture associée à cette réservation.", icon: "warning", position: "top-right" });
                    return;
                }
                let url = `/reservation.details/${data.facture.id}`;
                document.getElementById("detailIframe").src = url;
                setTimeout(() => { $("#reservationDetailsModal").modal("show"); }, 300);
            },

            goToStep(num) {
                if (num === 2) {
                    this.normalizeSejourDates();
                    const needsDateFin = this.form.type_sejour !== "passage";
                    if (!this.form.client.identite || !this.form.client.identite_type || !this.form.client.nom || !this.form.date_debut || (needsDateFin && !this.form.date_fin)) {
                        Swal.fire({ icon: "warning", title: "Infos incomplètes !", text: "Veuillez remplir les champs obligatoires.", timer: 3000, showConfirmButton: false });
                        return;
                    }
                    this.computeMontant();
                }
                this.step = num;
            },

            createReservation() {
                if (this.step === 1) { this.goToStep(2); return; }
                Swal.fire({ icon: "question", title: "Confirmer la réservation ?", text: `Chambre ${this.selectedChambre.numero} - ${this.form.type_sejour.toUpperCase()}`, showCancelButton: true, confirmButtonText: "Oui, Confirmer" })
                    .then((res) => {
                        if (!res.isConfirmed) return;
                        this.isLoading = true;
                        this.normalizeSejourDates();
                        const payload = this.prepareReservationPayload();
                        postJson("/reservation.create", payload)
                            .then(({ data }) => {
                                this.isLoading = false;
                                if (data.errors || data.reserved) {
                                    $.toast({ heading: "Erreur", text: data.errors || data.reserved, icon: "error", position: "top-right" });
                                    return;
                                }
                                if (data.status === "success") {
                                    Swal.fire({ icon: "success", title: "Succès !", timer: 2000, showConfirmButton: false });
                                    if (data.facture_url) {
                                        document.getElementById("factureIframe").src = data.facture_url;
                                        $(".modal-reservation-create").modal("hide");
                                        setTimeout(() => { $("#factureModal").modal("show"); }, 500);
                                    } else { $(".modal-reservation-create").modal("hide"); }
                                    this.viewAllReservations();
                                    this.viewAllChambres();
                                    this.cleanField();
                                }
                            })
                            .catch(() => { this.isLoading = false; });
                    });
            },

            updateReservation() {
                this.normalizeSejourDates();
                const needsDateFin = this.form.type_sejour !== "passage";
                if (!this.form.chambre_id || !this.form.date_debut || (needsDateFin && !this.form.date_fin)) {
                    $.toast({ heading: "Champs manquants", text: "Veuillez compléter les informations obligatoires.", icon: "warning", position: "top-right" });
                    return;
                }
                this.isLoading = true;
                const payload = this.prepareReservationPayload();
                postJson("/reservation.update", payload)
                    .then(({ data }) => {
                        this.isLoading = false;
                        this.form.remove_payment = false;
                        if (data.errors || data.reserved) {
                            $.toast({ heading: "Erreur", text: data.errors || data.reserved, icon: "error", position: "top-right" });
                            return;
                        }
                        if (data.status === "success") {
                            $(".modal-reservation-update").modal("hide");
                            this.viewAllReservations();
                            this.viewAllChambres();
                            Swal.fire({ icon: "success", title: "Modifiée !", timer: 2000, showConfirmButton: false });
                        }
                    }).catch(() => {
                        this.isLoading = false;
                        this.form.remove_payment = false;
                    });
            },

            cancelReservation(id) {
                Swal.fire({ title: "Annuler ?", text: "Cette action est irréversible.", icon: "warning", showCancelButton: true }).then((res) => {
                    if (res.isConfirmed) {
                        this.cancel_id = id;
                        get(`/reservation.cancel/${id}`).then(({ data }) => {
                            this.cancel_id = "";
                            if (data.status === "success") {
                                this.viewAllReservations();
                                this.viewAllChambres();
                            } else if (data.errors) {
                                $.toast({ heading: "Erreur", text: data.errors, icon: "error", position: "top-right" });
                            }
                        });
                    }
                });
            },

            occuperChambre(chambre) {
                const action = (chambre.statut === "occupée") ? "libérer" : "occuper";
                Swal.fire({ title: "Confirmation", text: `Voulez-vous ${action} la chambre ${chambre.numero} ?`, showCancelButton: true })
                    .then((res) => {
                        if (!res.isConfirmed) return;
                        this.bed_id = chambre.id;
                        get(`/chambre.occuper/${chambre.id}`).then(({ data }) => {
                            this.bed_id = "";
                            $(".modal-reservation-action").modal("hide");
                            if (data.status === "success") {
                                this.viewAllChambres();
                                this.viewAllReservations();
                            } else {
                                Swal.fire({ icon: "error", text: data.failed || data.errors });
                            }
                        }).catch(() => { this.bed_id = ""; });
                    });
            },

            extendReservation() {
                if (this.step === 1) { this.goToStep(2); return; }
                this.isLoading = true;
                this.normalizeSejourDates();
                const payload = {
                    reservation_id: this.form.reservation_id,
                    new_date_fin: this.form.date_fin
                };
                if (this.form.paiement && this.form.paiement.mode) {
                    payload.paiement = this.form.paiement;
                }
                postJson("/reservation.extend", payload).then(({ data }) => {
                    this.isLoading = false;
                    if (data.errors || data.failed) {
                        $.toast({ heading: "Echec", text: data.errors || data.failed, icon: "error", position: "top-right" });
                        return;
                    }
                    if (data.status === "success") {
                        $(".modal-reservation-create").modal("hide");
                        this.viewAllReservations();
                        if (data.facture_url) {
                            document.getElementById("factureIframe").src = data.facture_url;
                            setTimeout(() => { $("#factureModal").modal("show"); }, 500);
                        }
                    }
                }).catch(() => (this.isLoading = false));
            },

            payReservation() {
                this.formPay.mode = this.selectedMode;
                this.isLoading = true;
                postJson("/reservation.pay", this.formPay).then(({ data }) => {
                    this.isLoading = false;
                    if (data.errors) {
                        $.toast({ heading: "Echec", text: data.errors, icon: "error", position: "top-right" });
                        return;
                    }
                    if (data.status === "success") {
                        $("#paymentModal").modal("hide");
                        this.viewAllReservations();
                        if (data.facture_url) {
                            document.getElementById("factureIframe").src = data.facture_url;
                            setTimeout(() => { $("#factureModal").modal("show"); }, 500);
                        }
                    }
                }).catch(() => (this.isLoading = false));
            },

            triggerOpenCreateReservationModal(chambre) {
                this.selectedChambre = chambre;
                this.cleanField();
                this.form.chambre_id = chambre.id;
                this.computeMontant();
                $(".modal-reservation-create").modal("show");
            },

            triggerOpenUpdateReservationModal(data) {
                this.selectedChambre = data.chambre;
                this.selectedReservation = data;
                this.form = {
                    reservation_id: data.id,
                    chambre_id: data.chambre_id,
                    date_debut: data.date_debut,
                    date_fin: data.date_fin,
                    type_sejour: data.type_sejour || "nuit",
                    prix_negocie: data.prix_facture || null,
                    remove_payment: false,
                    paiement: { mode: "", amount: "", devise: data.chambre?.prix_devise || "USD", mode_ref: "" },
                    client: { ...data.client }
                };
                this.step = 1;
                this.computeMontant();
                $(".modal-reservation-update").modal("show");
            },

            triggerExtendDayModal(data) {
                this.selectedChambre = data.chambre;
                this.form = {
                    reservation_id: data.id,
                    chambre_id: data.chambre_id,
                    client: { ...data.client },
                    date_debut: data.date_fin,
                    date_fin: moment(data.date_fin).add(1, "days").format("YYYY-MM-DD"),
                    type_sejour: data.type_sejour || "nuit",
                    prix_negocie: null,
                    paiement: { mode: "", amount: "", devise: data.chambre?.prix_devise || "USD", mode_ref: "" }
                };
                this.step = 1;
                this.computeMontant();
                $(".modal-reservation-create").modal("show");
            },

            triggerActionReservationModal(chambre) {
                this.selectedChambre = chambre;
                this.selectedReservation = this.getActiveReservation(chambre);
                $(".modal-reservation-action").modal("show");
            },

            triggerOpenPaymentModal(data) {
                this.formPay.reservation_id = data.id;
                this.formPay.amount = data.prix_facture || 0;
                this.formPay.devise = data.chambre.prix_devise;
                $("#paymentModal").modal("show");
            },

            whenModalHidden() {
                document.querySelectorAll(".modal").forEach((el) => {
                    el.addEventListener("hidden.bs.modal", () => { if (!$(el).hasClass('modal-reservation-update')) this.cleanField(); });
                });
            },

            cleanField() {
                this.form = {
                    chambre_id: "",
                    type_sejour: "nuit",
                    prix_negocie: null,
                    remove_payment: false,
                    date_debut: moment().format("YYYY-MM-DD"),
                    date_fin: moment().add(1, "days").format("YYYY-MM-DD"),
                    paiement: { mode: "", amount: "", devise: "USD", mode_ref: "" },
                    client: { nom: "", telephone: "", email: "", identite: "", identite_type: "" }
                };
                this.step = 1;
                this.selectedReservation = null;
            },

            computeMontant() {
                if (!this.selectedChambre) return;
                this.normalizeSejourDates();
                const pricing = this.getPricing();
                if (!pricing) return;
                this.form.paiement.amount = pricing.applied;
                this.form.paiement.devise = this.selectedChambre.prix_devise;
            },

            getActiveReservation(chambre) {
                if (!chambre || !Array.isArray(chambre.reservations)) return null;
                const candidates = chambre.reservations.filter(r => ['en_attente', 'confirmée', 'occupée'].includes(r.statut));
                if (candidates.length === 0) return null;
                return candidates.sort((a, b) => new Date(b.created_at) - new Date(a.created_at))[0];
            },

            isExpired(res) {
                const today = moment().startOf('day');
                const end = res.type_sejour === 'passage' ? moment(res.date_debut) : moment(res.date_fin);
                return ['en_attente', 'confirmée', 'occupée'].includes(res.statut) && end.isBefore(today, 'day');
            }
        },

        computed: {
            allChambres() {
                let list = this.chambres;
                if (this.search) list = list.filter(c => c.numero.toString().includes(this.search));
                if (this.filter) {
                    const key = this.statusKey(this.filter);
                    list = list.filter(c => this.statusKey(c.statut) === key);
                }
                return list;
            },
            allReservations() {
                let list = this.reservations;
                if (this.filter) {
                    list = list.filter(r => r.statut === this.filter);
                }
                if (this.search) {
                    const q = this.search.toString().toLowerCase();
                    list = list.filter((r) => {
                        const client = (r.client?.nom || '').toLowerCase();
                        const chambre = (r.chambre?.numero || '').toString().toLowerCase();
                        const facture = (r.facture?.numero_facture || '').toLowerCase();
                        return client.includes(q) || chambre.includes(q) || facture.includes(q);
                    });
                }
                return list;
            },
            getDays() {
                return (start, end) => {
                    const s = moment(start, "YYYY-MM-DD");
                    const e = moment(end, "YYYY-MM-DD");
                    const diff = e.diff(s, "days");
                    return diff <= 0 ? 1 : diff;
                };
            },
            formateDate() { return (date) => moment(date).locale("fr").format("DD MMM YYYY"); },
            isDateAvailable() { return (start, end) => moment().isBetween(moment(start), moment(end), 'day', '[]'); },
            chambreStats() {
                const stats = { total: 0, libres: 0, reservees: 0, occupees: 0 };
                this.chambres.forEach((c) => {
                    stats.total += 1;
                    const s = (c.statut || '').toLowerCase();
                    if (s.includes('libre')) stats.libres += 1;
                    else if (s.includes('réserv') || s.includes('reserv')) stats.reservees += 1;
                    else if (s.includes('occup')) stats.occupees += 1;
                });
                return stats;
            },
            reservationStats() {
                const stats = { total: 0, en_attente: 0, confirmee: 0, terminee: 0, annulee: 0, expiree: 0 };
                const today = moment().startOf('day');
                this.reservations.forEach((r) => {
                    stats.total += 1;
                    if (r.statut === 'en_attente') stats.en_attente += 1;
                    else if (r.statut === 'confirmée') stats.confirmee += 1;
                    else if (r.statut === 'terminée') stats.terminee += 1;
                    else if (r.statut === 'annulée') stats.annulee += 1;
                    const end = r.type_sejour === 'passage' ? moment(r.date_debut) : moment(r.date_fin);
                    if (['en_attente', 'confirmée', 'occupée'].includes(r.statut) && end.isBefore(today, 'day')) stats.expiree += 1;
                });
                return stats;
            },
            pricingSummary() {
                return this.getPricing();
            },
        },

        watch: {
            "form.type_sejour"() { this.refreshPricing(); },
            "form.date_debut"() { this.refreshPricing(); },
            "form.date_fin"() { this.refreshPricing(); },
            "form.prix_negocie"() { this.refreshPricing(); }
        }
    });
});
