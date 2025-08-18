import { post, get, postJson } from "../modules/http.js";
new Vue({
    el: "#AppProduct",
    data() {
        return {
            error: null,
            result: null,
            isLoading: false,
            isDataLoading: false,
            categories: [],
            products: [],
            mouvements: [],
            selectedCategory: null,
            formCategory: {
                libelle: "",
                code: "",
                type_service: "",
                couleur: "",
            },

            formProduct: {
                code_barre: "",
                reference: "",
                categorie_id: "",
                libelle: "",
                prix_unitaire: "",
                unite: "",
                seuil_reappro: "",
                qte_init: "",
            },

            formMvt: {
                produit_id: "",
                type_mouvement: "",
                numdoc: "",
                quantite: "",
                source: "",
                destination: "",
                date_mouvement: "",
            },
        };
    },

    mounted() {
        if ($(".select2").length) {
            const self = this;
            $(".select2")
                .select2({
                    placeholder: "Sélectionnez un produit",
                })
                .on("select2:select", function (e) {
                    self.formMvt.produit_id = e.params.data.id;
                });
        }

        this.viewAllCategories();
        this.viewAllProducts();
        this.viewAllStockMvts();
        this.whenModalHidden();
    },

    methods: {
        //AFFICHE LA LISTE DES USERS
        viewAllCategories() {
            const validPath = location.pathname === "/products.categories";
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

        viewAllProducts() {
            const validPath = location.pathname === "/products";
            if (validPath) {
                this.isDataLoading = true;
                get("/products.all")
                    .then(({ data, status }) => {
                        this.isDataLoading = false;
                        this.products = data.produits;
                    })
                    .catch((err) => {
                        this.isDataLoading = false;
                    });
            }
        },

        viewAllStockMvts() {
            const validPath = location.pathname === "/products.mvts";
            if (validPath) {
                this.isDataLoading = true;
                get("/mvts.all")
                    .then(({ data, status }) => {
                        this.isDataLoading = false;
                        this.mouvements = data.mouvements;
                    })
                    .catch((err) => {
                        this.isDataLoading = false;
                    });
            }
        },

        //CREATE CATEGORIE
        submitCategorie() {
            this.isLoading = true;
            postJson("/categorie.create", this.formCategory)
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
                            text: `Nouvelle catégorie créée !`,
                            position: "top-right",
                            loaderBg: "#49ff5eff",
                            icon: "success",
                            hideAfter: 3000,
                            stack: 6,
                        });
                        this.viewAllCategories();
                        this.resetAll();
                        $("#categoryModal").modal("hide");
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

        //CREATE PRODUCT
        submitProduct() {
            this.isLoading = true;
            postJson("/product.create", this.formProduct)
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
                            text: `Nouveau produit créée !`,
                            position: "top-right",
                            loaderBg: "#49ff5eff",
                            icon: "success",
                            hideAfter: 3000,
                            stack: 6,
                        });
                        this.viewAllProducts();
                        this.resetAll();
                        $("#productModal").modal("hide");
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

        //CREATE MVTS
        submitStockMvt() {
            this.isLoading = true;
            postJson("/product.create", this.formProduct)
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
                            text: `Mouvement stock effectué avec succès !`,
                            position: "top-right",
                            loaderBg: "#49ff5eff",
                            icon: "success",
                            hideAfter: 3000,
                            stack: 6,
                        });
                        this.viewAllStockMvts();
                        this.resetAll();
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

        resetAll() {
            this.formCategory = {
                libelle: "",
                code: "",
                type_service: "",
                couleur: "",
            };

            this.formProduct = {
                code_barre: "",
                reference: "",
                categorie_id: "",
                libelle: "",
                prix_unitaire: "",
                unite: "",
                seuil_reappro: "",
                qte_init: "",
            };

            this.formMvt = {
                produit_id: "",
                type_mouvement: "",
                numdoc: "",
                quantite: "",
                source: "",
                destination: "",
                date_mouvement: "",
            };
            if ($(".select2").length) {
                $(".select2").val(null).trigger("change");
            }
        },

        whenModalHidden() {
            const self = this;
            const modals = document.querySelectorAll(".modal");
            modals.forEach((el) => {
                el.addEventListener("hidden.bs.modal", function (event) {
                    self.resetAll();
                });
            });
        },
    },

    computed: {
        allProducts() {
            return this.products;
        },

        allCategories() {
            return this.categories;
        },

        allStockMvts() {
            return this.mouvements;
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
                moment(date, "DD/MM/YYYY HH:mm").locale("fr").format("hh:mm");
            // ex: "03:13 AM"
        },
    },
});
