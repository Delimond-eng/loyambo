import { post, postJson, get } from "../modules/http.js";

const Store = Vue.observable({
    cart: [],
});

document.querySelectorAll(".AppFacture").forEach((el) => {
    new Vue({
        el: el,
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
                modes: [
                    { value: "cash", label: "CASH", icon: "fa fa-money" },
                    {
                        value: "mobile",
                        label: "MOBILE MONEY",
                        icon: "fa fa-mobile-phone",
                    },
                    {
                        value: "card",
                        label: "BANQUE/CARTE",
                        icon: "fa fa-credit-card",
                    },
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

            $("#reservation").daterangepicker();
            // Récupérer la valeur quand l’utilisateur applique une sélection
            $("#reservation").on(
                "apply.daterangepicker",
                function (ev, picker) {
                    let start = picker.startDate.format("YYYY-MM-DD");
                    let end = picker.endDate.format("YYYY-MM-DD");

                    self.filterByDate = `${start};${end}`;
                    self.viewAllSells();
                }
            );

            this.viewAllFactures();
            this.viewAllSells();
            if (location.pathname == "/") {
                setInterval(() => {
                    this.viewAllFactures();
                }, 3000);
            }
        },

        methods: {
            removeCommande(data) {
                console.log("🟡 ID à supprimer:", data.id);
                if (data.statut !== "en_attente") {
                    $.toast({
                        heading: "Action impossible",
                        text: "Seules les commandes en attente peuvent être supprimées",
                        position: "top-right",
                        loaderBg: "#ffa500",
                        icon: "warning",
                        hideAfter: 3000,
                        stack: 6,
                    });
                    return;
                }

                if (
                    confirm(
                        "Êtes-vous sûr de vouloir supprimer cette commande ?"
                    )
                ) {
                    this.isLoading = true;

                    console.log(
                        "🟡 Envoi POST à /facture.delete avec ID:",
                        data.id
                    );

                    postJson(`/facture.destroy`, { id: data.id })
                        .then((response) => {
                            this.isLoading = false;
                            console.log("🟢 Réponse complète:", response);
                            console.log("🟢 Data:", response.data);
                            console.log("🟢 Status:", response.status);

                            if (response.data.status === "success") {
                                $.toast({
                                    heading: "Succès",
                                    text: "Commande supprimée avec succès!",
                                    position: "top-right",
                                    loaderBg: "#49ff86ff",
                                    icon: "success",
                                    hideAfter: 3000,
                                    stack: 6,
                                });
                                this.viewAllFactures();
                            } else {
                                console.log(
                                    "🔴 Erreur réponse:",
                                    response.data
                                );
                                $.toast({
                                    heading: "Erreur",
                                    text:
                                        response.data.errors ||
                                        response.data.message ||
                                        "Erreur lors de la suppression",
                                    position: "top-right",
                                    loaderBg: "#ff4949ff",
                                    icon: "error",
                                    hideAfter: 3000,
                                    stack: 6,
                                });
                            }
                        })
                        .catch((err) => {
                            this.isLoading = false;
                            console.error("🔴 Erreur catch:", err);
                            console.error("🔴 Response error:", err.response);

                            $.toast({
                                heading: "Erreur HTTP",
                                text:
                                    "Erreur: " +
                                    (err.response?.status || "Network error"),
                                position: "top-right",
                                loaderBg: "#ff4949ff",
                                icon: "error",
                                hideAfter: 5000,
                                stack: 6,
                            });
                        });
                }
            },

            viewAllFactures() {
                this.isDataLoading = true;
                const url =
                    location.pathname == "/orders" || location.pathname == "/"
                        ? "/factures.all?status=en_attente"
                        : "/factures.all";
                get(url)
                    .then(({ data, status }) => {
                        this.isDataLoading = false;
                        this.factures = data.factures;
                    })
                    .catch((err) => {
                        this.isDataLoading = false;
                    });
            },

            viewAllSells() {
                this.isDataLoading = true;
                get(
                    `/sells.all?dateRange=${this.filterByDate}&serveur=${this.filterByServeur}`
                )
                    .then(({ data, status }) => {
                        this.isDataLoading = false;
                        this.sells = data.ventes;
                    })
                    .catch((err) => {
                        this.isDataLoading = false;
                    });
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

            triggerPayment() {
                let facture = this.selectedFacture;
                this.load_id = facture.id;
                $(".modal-pay-trigger").modal("hide");
                postJson(`/payment.create`, {
                    facture_id: facture.id,
                    mode: this.selectedMode,
                    mode_ref: this.selectedModeRef,
                })
                    .then(({ data, status }) => {
                        this.load_id = "";
                        if (data.errors !== undefined) {
                            $.toast({
                                heading: "Echec de traitement",
                                text: data.errors,
                                position: "top-right",
                                loaderBg: "#ff4949ff",
                                icon: "error",
                                hideAfter: 3000,
                                stack: 6,
                            });
                            return;
                        }
                        if (data.status === "success") {
                            $.toast({
                                heading: "Commande servie.",
                                text: "Commande servie et payée avec succès!",
                                position: "top-right",
                                loaderBg: "#49ff86ff",
                                icon: "success",
                                hideAfter: 3000,
                                stack: 6,
                            });
                            this.viewAllFactures();
                        }
                    })
                    .catch((err) => {
                        this.load_id = "";
                        $.toast({
                            heading: "Echec de traitement",
                            text: "Veuillez réessayer plutard !",
                            position: "top-right",
                            loaderBg: "#ff4949ff",
                            icon: "error",
                            hideAfter: 3000,
                            stack: 6,
                        });
                    });
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
                    .catch((err) => {
                        this.load_id = "";
                        $.toast({
                            heading: "Echec de traitement",
                            text: "Veuillez réessayer plutard !",
                            position: "top-right",
                            loaderBg: "#ff4949ff",
                            icon: "error",
                            hideAfter: 3000,
                            stack: 6,
                        });
                    });
            },

            printInvoice(facture, place) {
                const content = `
            <div class="ticket">
                <h2>${place.libelle}</h2>
                <p><strong>Facture N°:</strong> ${facture.numero_facture}</p>
                <p><strong>Date:</strong> ${this.formateDate(
                    facture.date_facture
                )}</p>
                <hr/>
                ${
                    facture.details.length > 0
                        ? `<table>
                    <thead>
                        <tr>
                            <th>Désignation</th>
                            <th>Qté</th>
                            <th class="right">PU</th>
                            <th class="right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${facture.details
                            .map(
                                (d) => `
                            <tr>
                                <td>${d.produit.libelle}</td>
                                <td>${d.quantite}</td>
                                <td class="right">${d.prix_unitaire}|</td>
                                <td class="right">${d.total_ligne}|</td>
                            </tr>
                        `
                            )
                            .join("")}
                    </tbody>
                </table>`
                        : `<table>
                    <thead>
                        <tr>
                            <th>Chambre</th>
                            <th>Capacité</th>
                            <th class="right">Type</th>
                            <th class="right">Prix</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>CH_N°${facture.chambre.numero}</td>
                            <td>${facture.chambre.capacite}</td>
                            <td class="right">${facture.chambre.type}</td>
                            <td class="right">${facture.chambre.prix}</td>
                        </tr>
                    </tbody>
                </table>`
                }
                
                <hr/>
                <p class="right">Total HT: ${facture.total_ht}</p>
                <p class="right">Remise: ${facture.remise}%</p>
                <p class="right">TVA: ${facture.tva}</p>
                <h3 class="right">TOTAL TTC: ${facture.total_ttc}</h3>
                <hr/>
                <p class="center">Merci pour votre visite !</p>
            </div>
        `;

                const style = `
            <style>
                *, *::before, *::after {
                    box-sizing: border-box;
                }
                body {
                    font-family: 'Courier New', monospace;
                    font-size: 14px;
                    margin: 0;
                    padding: 0;
                    -webkit-print-color-adjust: exact;
                    color-adjust: exact;
                }
                .ticket {
                    width: 240px;
                    max-width: 100%;
                    margin: 0 auto;
                    padding: 10px;
                }
                h2 {
                    text-align: center;
                    margin: 0 0 10px;
                    font-size: 16px;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-bottom: 5px;
                }
                th, td {
                    padding: 2px 0;
                    font-size: 12px;
                }
                th {
                    border-bottom: 1px dashed #000;
                    text-align: left;
                }
                td {
                    vertical-align: top;
                }
                .right {
                    text-align: right;
                }
                .center {
                    text-align: center;
                }
                hr {
                    border: none;
                    border-top: 1px dashed #000;
                    margin: 5px 0;
                }
                @media print {
                    body {
                        margin: 0;
                        padding: 0;
                    }
                    .ticket {
                        width: 100%;
                        max-width: 240px;
                        margin: 0 auto;
                    }
                    table, th, td {
                        border: none;
                    }
                }
            </style>
        `;

                const printWindow = window.open("", "", "height=600,width=400");
                printWindow.document.write(`
            <html>
                <head>
                    <title>Facture ${facture.numero_facture}</title>
                    ${style}
                </head>
                <body>${content}</body>
            </html>
        `);
                printWindow.document.close();
                printWindow.focus();
                printWindow.print();
                printWindow.close();
            },

            removeCachedUser() {
                localStorage.removeItem("user");
            },
        },

        computed: {
            cart() {
                return this.store.cart;
            },
            allFactures() {
                return this.factures;
            },

            allSells() {
                return this.sells;
            },

            totalQuantite() {
                return this.allSells.reduce(
                    (sum, item) => sum + parseInt(item.total_vendu),
                    0
                );
            },
            totalMontant() {
                return this.allSells.reduce(
                    (sum, item) =>
                        sum +
                        parseInt(item.total_vendu) * item.produit.prix_unitaire,
                    0
                );
            },

            formateDate() {
                return (date) =>
                    moment(date, "YYYY-MM-DD")
                        .locale("fr")
                        .format("DD MMMM YYYY");
                // ex: "14 avril 2021"
            },

            formateTime() {
                return (date) => moment(date).locale("fr").format("hh:mm");
                // ex: "03:13 AM"
            },
        },
    });
});
