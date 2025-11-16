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
            emplacements: [], // AJOUT: Liste des emplacements
            mouvements: [],
            selectedCategory: null,
            formCategory: {
                libelle: "",
                code: "",
                type_service: "",
                couleur: "",
            },

            formProduct: {
                code_barre: this.generateEAN13(), // GÉNÉRATION AUTO
                reference:
                    "PROD-" + Math.floor(100000 + Math.random() * 900000), // GÉNÉRATION AUTO
                categorie_id: "",
                libelle: "",
                prix_unitaire: "",
                unite: "",
                seuil_reappro: "",
                qte_init: "",
                quantified: true,
                tva: false,
                emplacement_id: "", // AJOUT: Champ emplacement
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
        supprimerCategorie(categorie) {
            console.log("Suppression de la catégorie:", categorie);

            if (
                confirm(
                    `Êtes-vous sûr de vouloir supprimer la catégorie "${categorie.libelle}" ?\n\nAttention : Cette action supprimera également tous les produits de cette catégorie.`
                )
            ) {
                this.isLoading = true;

                postJson(`/categorie.delete`, { id: categorie.id })
                    .then(({ data: response, status }) => {
                        this.isLoading = false;
                        console.log("Réponse suppression catégorie:", response);

                        if (response.status === "success") {
                            $.toast({
                                heading: "Succès",
                                text: "Catégorie supprimée avec succès!",
                                position: "top-right",
                                loaderBg: "#49ff86ff",
                                icon: "success",
                                hideAfter: 3000,
                                stack: 6,
                            });
                            // Recharger la liste des catégories
                            this.viewAllCategories();
                        } else {
                            $.toast({
                                heading: "Erreur",
                                text:
                                    response.message ||
                                    response.errors ||
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
                        console.error("Erreur suppression catégorie:", err);
                        $.toast({
                            heading: "Erreur",
                            text: "Erreur lors de la suppression",
                            position: "top-right",
                            loaderBg: "#ff4949ff",
                            icon: "error",
                            hideAfter: 3000,
                            stack: 6,
                        });
                    });
            }
        },

        supprimerProduit(produit) {
            console.log("Suppression du produit:", produit);

            if (
                confirm(
                    `Êtes-vous sûr de vouloir supprimer le produit "${produit.libelle}" ? Cette action est irréversible.`
                )
            ) {
                this.isLoading = true;

                postJson(`/product.delete`, { id: produit.id })
                    .then(({ data: response, status }) => {
                        this.isLoading = false;
                        console.log("Réponse suppression:", response);

                        if (response.status === "success") {
                            $.toast({
                                heading: "Succès",
                                text: "Produit supprimé avec succès!",
                                position: "top-right",
                                loaderBg: "#49ff86ff",
                                icon: "success",
                                hideAfter: 3000,
                                stack: 6,
                            });
                            // Recharger la liste des produits
                            this.viewAllProducts();
                        } else {
                            $.toast({
                                heading: "Erreur",
                                text:
                                    response.message ||
                                    response.errors ||
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
                        console.error("Erreur suppression:", err);
                        $.toast({
                            heading: "Erreur",
                            text: "Erreur lors de la suppression",
                            position: "top-right",
                            loaderBg: "#ff4949ff",
                            icon: "error",
                            hideAfter: 3000,
                            stack: 6,
                        });
                    });
            }
        },

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
                        this.emplacements = data.emplacements || []; // S'assurer que c'est un tableau
                        console.log("Produits chargés:", this.products.length);
                        console.log(
                            "Emplacements chargés:",
                            this.emplacements.length
                        );
                        console.log("produits:", JSON.stringify(this.products));
                    })
                    .catch((err) => {
                        this.isDataLoading = false;
                        console.error("Erreur:", err);
                    });
            }
        },

        viewAllStockMvts() {
            const validPath = location.pathname === "/products.mvts";
            if (validPath) {
                this.isDataLoading = true;
                get("/mvts.all")
                    .then(({ data, status }) => {
                        console.log(JSON.stringify(data));
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
                            text: `Nouveau produit créé !`,
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

        updateQuantified(data, event) {
            const checked = event.target.checked;
            const productId = data.id;
            postJson("/product.update.quantified", {
                id: productId,
                quantified: checked,
            })
                .then(({ data, status }) => {
                    if (data.status === "success") {
                        this.error = null;
                        this.result = data.result;
                        this.products = [];
                        this.viewAllProducts();
                        $.toast({
                            heading: "Opération effectuée",
                            text: `Quantification du produit mise à jour !`,
                            position: "top-right",
                            loaderBg: "#49ff5eff",
                            icon: "success",
                            hideAfter: 3000,
                            stack: 6,
                        });
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

        updateTva(data, event) {
            const checked = event.target.checked;
            const productId = data.id;
            postJson("/product.update.tva", {
                id: productId,
                tva: checked,
            })
                .then(({ data, status }) => {
                    if (data.status === "success") {
                        this.error = null;
                        this.result = data.result;
                        this.products = [];
                        this.viewAllProducts();
                        $.toast({
                            heading: "Opération effectuée",
                            text: `Tva mise à jour!`,
                            position: "top-right",
                            loaderBg: "#49ff5eff",
                            icon: "success",
                            hideAfter: 3000,
                            stack: 6,
                        });
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
            postJson("/mvt.create", this.formMvt)
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

        editMvt(data) {
            this.formMvt = data;
            $(".select2").val(data.produit_id).trigger("change");
        },

        resetAll() {
            this.formCategory = {
                libelle: "",
                code: "",
                type_service: "",
                couleur: "",
            };

            this.formProduct = {
                code_barre: this.generateEAN13(), // REGÉNÉRATION AUTO
                reference:
                    "PROD-" + Math.floor(100000 + Math.random() * 900000), // REGÉNÉRATION AUTO
                categorie_id: "",
                libelle: "",
                prix_unitaire: "",
                unite: "",
                seuil_reappro: "",
                qte_init: "",
                quantified: true,
                tva: false,
                emplacement_id: "", // AJOUT: Réinitialiser emplacement
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

        generateEAN13() {
            // Générer les 12 premiers chiffres aléatoires
            let digits = "";
            for (let i = 0; i < 12; i++) {
                digits += Math.floor(Math.random() * 10);
            }

            // Calcul du chiffre de contrôle
            let sum = 0;
            for (let i = 0; i < digits.length; i++) {
                let num = parseInt(digits[i]);
                sum += i % 2 === 0 ? num : num * 3;
            }
            let checkDigit = (10 - (sum % 10)) % 10;

            return digits + checkDigit; // Code-barres EAN-13 valide
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

        formateSimpleDate() {
            return (date) =>
                moment(date, "YYYY-MM-DD").locale("fr").format("DD MMMM YYYY");
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
                moment(date, "DD/MM/YYYY HH:mm").locale("fr").format("hh:mm");
            // ex: "03:13 AM"
        },
    },
});
