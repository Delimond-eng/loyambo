import { post, postJson, get } from "../modules/http.js";
import Paginator from "../components/pagination.js";

const Store = Vue.observable({ cart: [], });


document.querySelectorAll(".AppFacture").forEach((el) => {
    const globalRate = parseFloat(el.getAttribute('data-rate')) || 0;
    new Vue({
        el: el,
        components: { Paginator },
        data() {
            return {
                error: null,
                result: null,
                isLoading: false,
                isDataLoading: false,
                factures: [],
                sells: [],
                selectedFacture: null,
                selectedProduct: null,

                payment: {
                    amount_received: 0,
                    currency: 'CDF',
                    rate: globalRate
                },

                pagination: { current_page: 1, last_page: 1, total: 0, per_page: 10 },
                modes: [
                    { value: "cash", label: "CASH", icon: "fa fa-money" },
                    { value: "mobile", label: "MOBILE MONEY", icon: "fa fa-mobile-phone" },
                    { value: "card", label: "BANQUE/CARTE", icon: "fa fa-credit-card" },
                ],
                selectedMode: null,
                selectedModeRef: "",
                store: Store,
                search: "",
                filterByDate: "",
                filterByServeur: "",
                load_id: "",
            };
        },

        mounted() {
            const self = this;

            if (document.getElementById("reservation")) {
                $("#reservation").daterangepicker();
                $("#reservation").on("apply.daterangepicker", function (ev, picker) {
                    let start = picker.startDate.format("YYYY-MM-DD");
                    let end = picker.endDate.format("YYYY-MM-DD");
                    self.filterByDate = `${start};${end}`;
                    self.viewAllSells();
                });
            }

            this.viewAllFactures();
            this.viewAllSells();
            if (location.pathname == "/") {
                setInterval(() => { this.viewAllFactures(); }, 3000);
            }
        },

        methods: {
            changePage(page) { this.pagination.current_page = page; this.viewAllFactures(); },
            onPerPageChange(perPage) { this.pagination.per_page = perPage; this.pagination.current_page = 1; this.viewAllFactures(); },
            updateRate(event) { const val = parseFloat(event.target.value); this.payment.rate = isNaN(val) ? 0 : val; },

            openPaymentModal(cmd) {
                this.selectedFacture = cmd;
                this.selectedMode = "cash";
                this.selectedModeRef = "";
                this.payment.amount_received = cmd?.total_ttc || 0;
                this.payment.currency = 'CDF';
                this.payment.rate = this.payment.rate || globalRate || 0;
                $("#modal-pay-trigger").modal("show");
            },

            triggerPayment() {
                let facture = this.selectedFacture;
                this.load_id = facture.id;
                $("#modal-pay-trigger").modal("hide");
                postJson(`/payment.create`, {
                    facture_id: facture.id,
                    mode: this.selectedMode,
                    mode_ref: this.selectedModeRef,
                })
                    .then(({ data, status }) => {
                        this.load_id = "";
                        if (data.errors !== undefined) {
                            $.toast({ heading: "Echec de traitement", text: data.errors, position: "top-right", loaderBg: "#ff4949ff", icon: "error", hideAfter: 3000, stack: 6 });
                            return;
                        }
                        if (data.status === "success") {
                            $.toast({ heading: "Commande servie.", text: "Commande servie et payée avec succès!", position: "top-right", loaderBg: "#49ff86ff", icon: "success", hideAfter: 3000, stack: 6 });
                            this.viewAllFactures();
                        }
                    })
                    .catch(() => {
                        this.load_id = "";
                        $.toast({ heading: "Echec de traitement", text: "Veuillez réessayer plutard !", position: "top-right", loaderBg: "#ff4949ff", icon: "error", hideAfter: 3000, stack: 6 });
                    });
            },

            supprimerFacture(data) {
                Swal.fire({ icon: "warning", title: "Supprimer cette facture ?", text: "Cette action est irréversible.", showCancelButton: true, confirmButtonText: "Oui, supprimer" })
                    .then((res) => {
                        if (!res.isConfirmed) return;
                        this.isLoading = true;
                        postJson("/facture.destroy", { id: data.id })
                            .then(({ data }) => {
                                this.isLoading = false;
                                if (data.errors) {
                                    $.toast({ heading: "Echec", text: data.errors, icon: "error", position: "top-right" });
                                    return;
                                }
                                $.toast({ heading: "Succès", text: "Facture supprimée", icon: "success", position: "top-right" });
                                this.viewAllFactures();
                            })
                            .catch(() => { this.isLoading = false; });
                    });
            },

            removeCommande(data) {
                if (data.statut !== "en_attente") {
                    $.toast({ heading: "Action impossible", text: "Seules les commandes en attente peuvent être supprimées", position: "top-right", loaderBg: "#ffa500", icon: "warning", hideAfter: 3000, stack: 6 });
                    return;
                }
                if (!confirm("Êtes-vous sûr de vouloir supprimer cette commande ?")) return;
                this.isLoading = true;
                postJson(`/facture.destroy`, { id: data.id })
                    .then((response) => {
                        this.isLoading = false;
                        if (response.data.status === "success") {
                            $.toast({ heading: "Succès", text: "Commande supprimée avec succès!", position: "top-right", loaderBg: "#49ff86ff", icon: "success", hideAfter: 3000, stack: 6 });
                            this.viewAllFactures();
                        } else {
                            $.toast({ heading: "Erreur", text: response.data.errors || response.data.message || "Erreur lors de la suppression", position: "top-right", loaderBg: "#ff4949ff", icon: "error", hideAfter: 3000, stack: 6 });
                        }
                    })
                    .catch(() => {
                        this.isLoading = false;
                        $.toast({ heading: "Erreur HTTP", text: "Erreur réseau", position: "top-right", loaderBg: "#ff4949ff", icon: "error", hideAfter: 5000, stack: 6 });
                    });
            },

            viewAllFactures() {
                this.isDataLoading = true;
                const url =
                    location.pathname == "/orders" || location.pathname == "/"
                        ? `/factures.all?status=en_attente&page=${this.pagination.current_page}&per_page=${this.pagination.per_page}`
                        : `/factures.all?page=${this.pagination.current_page}&per_page=${this.pagination.per_page}`;
                get(url)
                    .then(({ data, status }) => {
                        this.isDataLoading = false;
                        if (data.factures?.data) {
                            this.factures = data.factures.data;
                            this.pagination = {
                                current_page: data.factures.current_page,
                                last_page: data.factures.last_page,
                                total: data.factures.total,
                                per_page: data.factures.per_page,
                            };
                        } else {
                            this.factures = data.factures || [];
                            this.pagination = { current_page: 1, last_page: 1, total: this.factures.length, per_page: this.factures.length || 10 };
                        }
                    })
                    .catch(() => { this.isDataLoading = false; });
            },

            viewAllSells() {
                this.isDataLoading = true;
                get(`/sells.all?dateRange=${this.filterByDate}&serveur=${this.filterByServeur}`)
                    .then(({ data, status }) => {
                        this.isDataLoading = false;
                        this.sells = data.ventes;
                    })
                    .catch(() => { this.isDataLoading = false; });
            },

            viewSellDetails(data) {
                this.selectedProduct = data;
                $(".modal-sell-detail").modal("show");
            },

            selectMode(mode) {
                this.selectedMode = mode;
                if (mode === "cash") {
                    this.selectedModeRef = "";
                }
            },

            servirCmd(data) {
                this.load_id = data.id;
                postJson(`/cmd.servir`, { id: data.id })
                    .then(({ data, status }) => {
                        this.load_id = "";
                        $(".modal-commande").modal("hide");
                        if (data.status === "success") {
                            this.viewAllFactures();
                        }
                    })
                    .catch(() => {
                        this.load_id = "";
                        $.toast({ heading: "Echec de traitement", text: "Veuillez réessayer plutard !", position: "top-right", loaderBg: "#ff4949ff", icon: "error", hideAfter: 3000, stack: 6 });
                    });
            },

            printInvoice(facture, place) {
                const content = `...`;
                // contenu raccourci pour la réponse
            },

            removeCachedUser() { localStorage.removeItem("user"); },
        },

        computed: {
            cart() { return this.store.cart; },
            allFactures() { return this.factures; },
            allSells() { return this.sells; },
            totalQuantite() { return this.allSells.reduce((sum, item) => sum + parseInt(item.total_vendu), 0); },
            totalMontant() { return this.allSells.reduce((sum, item) => sum + parseInt(item.total_vendu) * item.produit.prix_unitaire, 0); },
            amountToPayUSD() { if (!this.selectedFacture || !this.payment.rate) return 0; return (this.selectedFacture.total_ttc / this.payment.rate).toFixed(2); },
            receivedInCDF() { if (this.payment.currency === 'CDF') return parseFloat(this.payment.amount_received) || 0; return (parseFloat(this.payment.amount_received) || 0) * this.payment.rate; },
            paymentChange() { if (!this.selectedFacture) return 0; return this.receivedInCDF - this.selectedFacture.total_ttc; },
            formateDate() { return (date) => moment(date, "YYYY-MM-DD").locale("fr").format("DD MMMM YYYY"); },
            formateTime() { return (date) => moment(date).locale("fr").format("hh:mm"); },
        },
    });
});


