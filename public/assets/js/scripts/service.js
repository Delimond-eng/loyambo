import { post, postJson, get } from "../modules/http.js";

const Store = Vue.observable({
    cart: [],
});

document.querySelectorAll(".AppService").forEach((el) => {
    new Vue({
        el: el,
        data() {
            return {
                error: null,
                result: null,
                isLoading: false,
                isDataLoading: false,
                serveurs: [],
                tables: [],
                categories: [],
                products: [],
                session: null,
                table: null,
                selectedPendingTable: null,
                selectedFacture: null,
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
                operation: null,
                selectedTables: [],
                store: Store,
                search: "",
                load_id: "",
            };
        },

        mounted() {
            this.refreshUserOrderSession();
            this.refreshTableData();
            this.getAllServeursServices();
            this.viewAllTables();
            this.viewAllCategories();
        },

        methods: {
            setOperation(op) {
                this.operation = op;
            },

            triggerOperation(data) {
                postJson(`/table.operation`, data)
                    .then(({ data, status }) => {
                        if (data.status === "success") {
                            this.setOperation("");
                            this.selectedTables = [];
                            this.viewAllTables();
                        }
                    })
                    .catch((err) => {
                        this.isLoading = false;
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

            libererTable(table) {
                postJson(`/table.liberer`, { table_id: table.id })
                    .then(({ data, status }) => {
                        $(".modal-commande").modal("hide");
                        if (data.status === "success") {
                            this.viewAllTables();
                        }
                    })
                    .catch((err) => {
                        this.isLoading = false;
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

            triggerPayment() {
                const facture = this.selectedFacture;
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
                            this.selectedPendingTable.commandes =
                                this.selectedPendingTable.commandes.filter(
                                    (c) => c.id !== facture.id
                                );
                            $.toast({
                                heading: "Commande servie.",
                                text: "Commande servie et payée avec succès!",
                                position: "top-right",
                                loaderBg: "#49ff86ff",
                                icon: "success",
                                hideAfter: 3000,
                                stack: 6,
                            });
                            this.viewAllTables();
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
            //ADD TO CART
            addToCart(product) {
                product.qte = 1;
                const found = this.cart.find((p) => p.id === product.id);
                if (found) {
                    /* if (found.quantity > product.stock) {
                        new Swal({
                            title: "Stock insuffisant !",
                            text:
                                "Impossible d'ajouter un produit au panier, stock insuffisant ! Stock actuel : " +
                                product.stock,
                            icon: "warning",
                            timer: 3000,
                            showConfirmButton: !1,
                        });
                        if (product.stock == 0) {
                            this.cart = this.cart.filter(
                                (p) => p.id !== product.id
                            );
                        } else {
                            found.quantity = product.stock;
                        }
                        return;
                    } */
                    found.qte += 1;
                } else {
                    /* if (product.stock == 0) {
                        new Swal({
                            title: "Stock insuffisant !",
                            text:
                                "Impossible d'ajouter un produit au panier, stock insuffisant. stock actuel : " +
                                product.stock,
                            icon: "warning",
                            timer: 3000,
                            showConfirmButton: !1,
                        });
                        return;
                    } */
                    this.cart.push({ ...product, qte: 1 });
                }
            },

            //REMOVE ITEM
            removeFromCart(product) {
                this.store.cart = this.store.cart.filter(
                    (p) => p.id !== product.id
                );
            },

            cancelCart() {
                this.store.cart = [];
            },

            //GET ALL AGENTS SERVEUR
            getAllServeursServices() {
                this.isDataLoading = true;
                if (location.pathname === "/serveurs") {
                    get("/users.all")
                        .then(({ data, status }) => {
                            this.isDataLoading = false;
                            this.serveurs = data.users;
                        })
                        .catch((err) => {
                            this.isDataLoading = false;
                        });
                } else {
                    get("/serveurs.services")
                        .then(({ data, status }) => {
                            this.isDataLoading = false;
                            this.serveurs = data.serveurs;
                        })
                        .catch((err) => {
                            this.isDataLoading = false;
                        });
                }
            },

            viewAllTables() {
                this.refreshUserOrderSession();
                const validPath = true;
                if (validPath) {
                    let url = this.session
                        ? `/tables.all?place=${this.session.emplacement_id}`
                        : "/tables.all";
                    this.isDataLoading = true;
                    get(url)
                        .then(({ data, status }) => {
                            this.isDataLoading = false;
                            this.tables = data.tables;
                        })
                        .catch((err) => {
                            this.isDataLoading = false;
                        });
                }
            },

            goToUserOrderSession(user) {
                localStorage.setItem("user", JSON.stringify(user));
                location.href = "/orders.portal";
            },

            goToOrderPannel(table, isTable = false) {
                if (this.operation) {
                    this.selectedTables.push(table);
                    if (this.selectedTables.length === 2) {
                        if (this.operation === "transfert") {
                            let source = this.selectedTables.find(
                                (t) => t.statut === "occupée"
                            );
                            let cible = this.selectedTables.find(
                                (t) => t.statut === "libre"
                            );

                            if (!source || !cible) {
                                $.toast({
                                    heading: "Erreur de transfert",
                                    text: "Choisissez une table occupée et une table libre !",
                                    position: "top-right",
                                    loaderBg: "#49f3ff",
                                    icon: "info",
                                    hideAfter: 3000,
                                    stack: 6,
                                });
                            } else {
                                this.triggerOperation({
                                    op: "transfert",
                                    source_id: source.id,
                                    cible_id: cible.id,
                                });
                            }
                        }

                        if (this.operation === "combiner") {
                            let tablesOccupees = this.selectedTables.filter(
                                (t) => t.statut === "occupée"
                            );
                            if (tablesOccupees.length !== 2) {
                                $.toast({
                                    heading: "Erreur de combinaison",
                                    text: "Choisissez deux tables occupées !",
                                    position: "top-right",
                                    loaderBg: "#ff5733",
                                    icon: "error",
                                    hideAfter: 3000,
                                    stack: 6,
                                });
                            } else {
                                this.triggerOperation({
                                    op: "combiner",
                                    table1_id: tablesOccupees[0].id,
                                    table2_id: tablesOccupees[1].id,
                                });
                            }
                        }
                        // reset toujours après deux sélections
                        this.selectedTables = [];
                        this.operation = "";
                    }
                    return;
                }

                if (table.statut === "occupée" && !isTable) {
                    this.selectedPendingTable = table;
                    $(".modal-commande").modal("show");
                    return;
                }
                localStorage.setItem("table", JSON.stringify(table));
                location.href = "/orders.interface";
            },

            refreshUserOrderSession() {
                const data = localStorage.getItem("user");
                this.session = JSON.parse(data);
            },

            refreshTableData() {
                if (location.pathname === "/orders.interface") {
                    const data = localStorage.getItem("table");
                    this.table = JSON.parse(data);
                }
            },

            viewAllCategories() {
                const validPath = true;
                if (validPath) {
                    this.isDataLoading = true;
                    get("/categories.all")
                        .then(({ data, status }) => {
                            this.isDataLoading = false;
                            this.categories = data.categories;
                        })
                        .catch((err) => {
                            this.isDataLoading = false;
                        });
                }
            },

            createFacture() {
                const user = JSON.parse(localStorage.getItem("user"));
                const table = JSON.parse(localStorage.getItem("table"));
                let details = [];
                this.store.cart.forEach((el) => {
                    details.push({
                        produit_id: el.id,
                        quantite: el.qte,
                        prix_unitaire: el.prix_unitaire,
                    });
                });
                const form = {
                    table_id: table.id,
                    user_id: user ? user.id : null,
                    details: details,
                };

                this.isLoading = true;
                postJson("/facture.create", form)
                    .then(({ data, status }) => {
                        this.isLoading = false;
                        // Gestion des erreurs
                        if (data.errors !== undefined) {
                            this.error = data.errors;
                            $.toast({
                                heading: "Echec de traitement",
                                text: `${data.errors}`,
                                position: "top-right",
                                loaderBg: "#ff4949ff",
                                icon: "error",
                                hideAfter: 3000,
                                stack: 6,
                            });
                        }
                        if (data.status === "success") {
                            this.error = null;
                            this.result = data.result;
                            $.toast({
                                heading: "Opération effectuée",
                                text: data.message,
                                position: "top-right",
                                loaderBg: "#49ff5eff",
                                icon: "success",
                                hideAfter: 3000,
                                stack: 6,
                            });

                            setTimeout(() => {
                                location.href = "/orders.portal";
                            }, 1000);
                        }
                    })
                    .catch((err) => {
                        this.isLoading = false;
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

            printInvoiceFromJson(facture, place, copies = 3) {
                // Génération du contenu HTML
                const singleTicket = `
            <div class="ticket">
                <h2>${place.libelle}</h2>
                <p><strong>Facture N°:</strong> ${facture.numero_facture}</p>
                <p><strong>Date:</strong> ${this.formateDate2(
                    facture.date_facture
                )}</p>
                <hr/>
                <table>
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
                                <td class="right">${d.prix_unitaire}</td>
                                <td class="right">${d.total_ligne}</td>
                            </tr>
                        `
                            )
                            .join("")}
                    </tbody>
                </table>
                <hr/>
                <p class="right">Total HT: ${facture.total_ht}</p>
                <p class="right">Remise: ${facture.remise}%</p>
                <p class="right">TVA: 0</p>
                <h3 class="right">TOTAL TTC: ${facture.total_ttc}</h3>
                <hr/>
                <p class="center">Merci pour votre visite !</p>
            </div>
        `;

                // Répéter le ticket selon le nombre de copies
                let content = "";
                for (let i = 0; i < copies; i++) {
                    content +=
                        singleTicket +
                        '<div style="page-break-after: always;"></div>';
                }

                const style = `
            <style>
                *, *::before, *::after { box-sizing: border-box; }
                body { font-family: 'Courier New', monospace; font-size: 14px; margin: 0; padding: 0; }
                .ticket { width: 240px; max-width: 100%; margin: 0 auto; padding: 10px; }
                h2 { text-align: center; margin: 0 0 10px; font-size: 16px; }
                table { width: 100%; border-collapse: collapse; margin-bottom: 5px; }
                th, td { padding: 2px 0; font-size: 12px; }
                th { border-bottom: 1px dashed #000; text-align: left; }
                td { vertical-align: top; }
                .right { text-align: right; }
                .center { text-align: center; }
                hr { border: none; border-top: 1px dashed #000; margin: 5px 0; }
                @media print {
                    body { margin: 0; padding: 0; }
                    .ticket { width: 100%; max-width: 240px; margin: 0 auto; }
                    table, th, td { border: none; }
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
        },

        computed: {
            cart() {
                return this.store.cart;
            },

            allProducts() {
                if (this.search) {
                    return this.products.filter((p) =>
                        p.libelle
                            .toLowerCase()
                            .includes(this.search.toLowerCase())
                    );
                }
                return this.products;
            },

            allCategories() {
                return this.categories;
            },

            userSession() {
                return this.session;
            },

            selectedTable() {
                return this.table;
            },

            totalGlobal() {
                return this.cart.reduce((sum, item) => {
                    return sum + item.prix_unitaire * item.qte;
                }, 0);
            },

            allTables() {
                return this.tables;
            },
            allServeurs() {
                return this.serveurs;
            },

            formateDate2() {
                return (date) =>
                    moment(date, "YYYY-MM-DD HH:mm")
                        .locale("fr")
                        .format("DD MMMM YYYY");
                // ex: "14 avril 2021"
            },
            formateDate() {
                return (date) =>
                    moment(date, "DD/MM/YYYY HH:mm")
                        .locale("fr")
                        .format("DD MMMM YYYY");
                // ex: "14 avril 2021"
            },

            formateTime() {
                return (date) =>
                    moment(date, "DD/MM/YYYY HH:mm")
                        .locale("fr")
                        .format("hh:mm");
                // ex: "03:13 AM"
            },

            getTableOperationColorClass() {
                let borderClass = "border-primary";
                switch (this.operation) {
                    case "transfert":
                        borderClass = "border-info";
                        break;
                    case "combiner":
                        borderClass = "border-success";
                        break;
                    case "reservation":
                        borderClass = "border-info";
                        break;
                    default:
                        borderClass = "border-primary";
                        break;
                }
                return borderClass;
            },

            getTextColor() {
                return (hex) => {
                    // Supprimer le # si présent
                    hex = hex.replace("#", "");
                    // Convertir en valeurs RGB
                    let r = parseInt(hex.substr(0, 2), 16);
                    let g = parseInt(hex.substr(2, 2), 16);
                    let b = parseInt(hex.substr(4, 2), 16);

                    // Calcul de la luminance
                    let luminance = 0.299 * r + 0.587 * g + 0.114 * b;

                    return luminance > 186 ? "#000000" : "#ffffff";
                };
            },
        },
    });
});
