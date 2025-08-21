import { post, get } from "../modules/http.js";

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
                store: Store,
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
                const validPath = location.pathname === "/orders.portal";
                if (validPath) {
                    this.isDataLoading = true;
                    get(`/tables.all?place=${this.session.emplacement_id}`)
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
            goToOrderPannel(table) {
                localStorage.setItem("table", JSON.stringify(table));
                location.href = "/orders.interface";
            },

            refreshUserOrderSession() {
                const data = localStorage.getItem("user");
                this.session = JSON.parse(data);
            },
            refreshTableData() {
                const data = localStorage.getItem("table");
                this.table = JSON.parse(data);
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
        },

        computed: {
            cart() {
                return this.store.cart;
            },
            allProducts() {
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
