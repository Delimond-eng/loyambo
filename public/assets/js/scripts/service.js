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
                store: Store,
                search: "",
            };
        },

        mounted() {
            this.refreshUserOrderSession();
            this.refreshTableData();
            this.getAllServeurs();
            this.viewAllTables();
            this.viewAllCategories();
        },

        methods: {
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
            getAllServeurs() {
                this.isDataLoading = true;
                get("/users.all")
                    .then(({ data, status }) => {
                        this.isDataLoading = false;
                        this.serveurs = data.users;
                    })
                    .catch((err) => {
                        this.isDataLoading = false;
                    });
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
