import { post, get, postJson } from "../modules/http.js";
import Paginator from "../components/pagination.js";

new Vue({
    el: "#AppProduct",
    components: { Paginator },
    data() {
        return {
            error: null,
            result: null,
            isLoading: false,
            isDataLoading: false,
            categories: [],
            products: [],
            emplacements: [],
            mouvements: [],
            selectedCategory: null,
            load_id: "",
            filter_type: "",
            filter_date1: "",
            filter_date2: "",
            pagination: { current_page: 1, last_page: 1, total: 10, per_page: 10 },

            formCategory: { id: null, libelle: "", code: "", type_service: "", couleur: "#4361ee" },
            formProduct: {
                id: null,
                code_barre: this.generateEAN13(),
                reference: "PROD-" + Math.floor(100000 + Math.random() * 900000),
                categorie_id: "",
                libelle: "",
                prix_unitaire: 0,
                unite: "pce",
                seuil_reappro: 0,
                qte_init: "",
                quantified: true,
                tva: false,
                emplacement_id: "",
                prices_config: [],
            },
            formMvt: { produit_id: "", type_mouvement: "", numdoc: "", quantite: "", source: "", destination: "", date_mouvement: "" },
            formEntree: { produit_id: "", emplacement_id: "", quantite: "" },
            disabled: false,
        };
    },

    mounted() {
        if ($(".select2").length) {
            const self = this;
            $(".select2")
                .select2({ placeholder: "Sélectionnez un produit" })
                .on("select2:select", function (e) {
                    self.formMvt.produit_id = e.params.data.id;
                    self.formEntree.produit_id = e.params.data.id;
                });
        }

        this.viewAllCategories();
        if (location.pathname === "/products") this.viewAllProducts();
        if (location.pathname === "/products.mvts") this.viewAllStockMvts();
        this.whenModalHidden();
    },

    methods: {
        // --- UTILITAIRES ---
        formateSimpleDate(date) { return date ? moment(date, "YYYY-MM-DD").locale("fr").format("DD MMM YYYY") : "---"; },

        // --- PAGINATION ---
        changePage(page) { this.pagination.current_page = page; this.viewAllStockMvts(); },
        onPerPageChange(perPage) { this.pagination.per_page = perPage; this.pagination.current_page = 1; this.viewAllStockMvts(); },
        filterByType(type) { this.filter_type = type; this.pagination.current_page = 1; this.viewAllStockMvts(); },

        // --- CHARGEMENT DATA ---
        viewAllCategories() {
            this.isDataLoading = true;
            get("/categories.all")
                .then(({ data }) => { this.categories = data.categories || []; this.isDataLoading = false; })
                .catch(() => (this.isDataLoading = false));
        },
        viewAllProducts() {
            this.isDataLoading = true;
            get("/products.all")
                .then(({ data }) => {
                    this.products = data.produits || [];
                    this.emplacements = data.emplacements || [];
                    this.isDataLoading = false;
                })
                .catch(() => (this.isDataLoading = false));
        },
        viewAllStockMvts() {
            if (location.pathname !== "/products.mvts") return;
            this.isDataLoading = true;
            get(`/mvts.all?page=${this.pagination.current_page}&per_page=${this.pagination.per_page}&type=${this.filter_type}&date_debut=${this.filter_date1}&date_fin=${this.filter_date2}`)
                .then(({ data }) => {
                    this.mouvements = data.mouvements.data || [];
                    this.pagination = {
                        current_page: data.mouvements.current_page,
                        last_page: data.mouvements.last_page,
                        total: data.mouvements.total,
                        per_page: data.mouvements.per_page,
                    };
                    this.isDataLoading = false;
                })
                .catch(() => (this.isDataLoading = false));
        },

        // --- ACTIONS CATEGORIES ---
        submitCategorie() {
            this.isLoading = true;
            postJson("/categorie.create", this.formCategory)
                .then(({ data }) => {
                    this.isLoading = false;
                    if (data.errors) {
                        $.toast({ heading: "Echec", text: data.errors, icon: "error", position: "top-right" });
                        return;
                    }
                    if (data.status === "success") {
                        $.toast({ heading: "Succès", text: "Catégorie enregistrée", icon: "success", position: "top-right" });
                        this.viewAllCategories();
                        this.resetAll();
                        $("#categoryModal").modal("hide");
                    }
                })
                .catch(() => { this.isLoading = false; });
        },

        // --- TARIFICATION PAR EMPLACEMENT ---
        getLocationPrice(emplacementId) {
            const config = this.formProduct.prices_config.find(c => c.emplacement_id === emplacementId);
            return config ? config.prix : "";
        },
        updateLocationPrice(emplacementId, value) {
            let config = this.formProduct.prices_config.find(c => c.emplacement_id === emplacementId);
            if (!config) this.formProduct.prices_config.push({ emplacement_id: emplacementId, prix: value });
            else config.prix = value;
        },

        // --- PRODUITS ---
        editProduct(data) {
            this.resetAll();
            this.formProduct = {
                ...this.formProduct,
                ...data,
                prices_config: [],
            };
            if (data.emplacements) {
                data.emplacements.forEach(emp => {
                    this.formProduct.prices_config.push({ emplacement_id: emp.id, prix: emp.pivot.prix });
                });
            }
            $("#productModal").modal("show");
        },

        submitProduct() {
            this.isLoading = true;
            this.formProduct.prices_config = this.formProduct.prices_config.filter(c => c.prix !== "");
            postJson("/product.create", this.formProduct)
                .then(({ data }) => {
                    this.isLoading = false;
                    if (data.errors) {
                        $.toast({ heading: "Echec", text: data.errors, icon: "error", position: "top-right" });
                        return;
                    }
                    if (data.status === "success") {
                        $.toast({ heading: "Succès", text: "Produit enregistré", icon: "success", position: "top-right" });
                        this.viewAllProducts();
                        this.resetAll();
                        $("#productModal").modal("hide");
                    }
                })
                .catch(() => { this.isLoading = false; });
        },

        updateQuantified(data, event) { postJson("/product.update.quantified", { id: data.id, quantified: event.target.checked }); },
        updateTva(data, event) { postJson("/product.update.tva", { id: data.id, tva: event.target.checked }); },

        // --- MOUVEMENTS ---
        submitMvt() {
            if (this.formMvt.source && this.formMvt.source === this.formMvt.destination) {
                Swal.fire({ icon: "warning", title: "Source identique à la destination" });
                return;
            }
            this.isLoading = true;
            postJson("/mvt.create", this.formMvt)
                .then(({ data }) => {
                    this.isLoading = false;
                    if (data.errors) {
                        $.toast({ heading: "Echec", text: data.errors, icon: "error", position: "top-right" });
                        return;
                    }
                    if (data.status === "success") {
                        $.toast({ heading: "Succès", text: "Mouvement enregistré", icon: "success", position: "top-right" });
                        this.viewAllStockMvts();
                        this.resetAll();
                    }
                })
                .catch(() => { this.isLoading = false; });
        },

                submitStockMvt() {
            if (!this.formEntree.produit_id || !this.formEntree.emplacement_id) {
                $.toast({ heading: "Erreur", text: "Sélectionnez un produit et un emplacement", icon: "error", position: "top-right" });
                return;
            }
            this.isLoading = true;
            postJson("/mvt.entree", this.formEntree)
                .then(({ data }) => {
                    this.isLoading = false;
                    if (data.errors) {
                        $.toast({ heading: "Echec", text: data.errors, icon: "error", position: "top-right" });
                        return;
                    }
                    $.toast({ heading: "Succès", text: "Approvisionnement enregistré", icon: "success", position: "top-right" });
                    this.resetAll();
                    if ($('.select2').length) $('.select2').val(null).trigger('change');
                })
                .catch(() => { this.isLoading = false; });
        },

        editMvt(data) {
            this.formMvt = data;
            this.disabled = true;
            if ($(".select2").length) $(".select2").val(data.produit_id).trigger("change");
        },

        deleteMvt(data) {
            Swal.fire({ icon: "warning", title: "Confirmer la suppression ?", showCancelButton: true })
                .then((res) => {
                    if (!res.isConfirmed) return;
                    this.isLoading = true;
                    postJson("/mvts.delete", { id: data.id })
                        .then(({ data }) => {
                            this.isLoading = false;
                            if (data.errors) {
                                $.toast({ heading: "Echec", text: data.errors, icon: "error", position: "top-right" });
                                return;
                            }
                            this.viewAllStockMvts();
                            Swal.fire({ icon: "success", title: "Supprimé", timer: 1500, showConfirmButton: false });
                        })
                        .catch(() => { this.isLoading = false; });
                });
        },

        supprimerProduit(data) {
            Swal.fire({ icon: "warning", title: "Supprimer ce produit ?", showCancelButton: true, confirmButtonText: "Oui, supprimer" })
                .then((res) => {
                    if (!res.isConfirmed) return;
                    this.isLoading = true;
                    postJson("/product.delete", { id: data.id })
                        .then(({ data }) => {
                            this.isLoading = false;
                            if (data.errors) {
                                $.toast({ heading: "Echec", text: data.errors, icon: "error", position: "top-right" });
                                return;
                            }
                            $.toast({ heading: "Succès", text: "Produit supprimé", icon: "success", position: "top-right" });
                            this.viewAllProducts();
                        })
                        .catch(() => { this.isLoading = false; });
                });
        },

        supprimerCategorie(cat) {
            Swal.fire({ icon: "warning", title: "Supprimer cette catégorie ?", showCancelButton: true, confirmButtonText: "Oui, supprimer" })
                .then((res) => {
                    if (!res.isConfirmed) return;
                    this.isLoading = true;
                    postJson("/categorie.delete", { id: cat.id })
                        .then(({ data }) => {
                            this.isLoading = false;
                            if (data.errors) {
                                $.toast({ heading: "Echec", text: data.errors, icon: "error", position: "top-right" });
                                return;
                            }
                            $.toast({ heading: "Succès", text: "Catégorie supprimée", icon: "success", position: "top-right" });
                            this.viewAllCategories();
                        })
                        .catch(() => { this.isLoading = false; });
                });
        },

        resetAll() {
            this.formCategory = { id: null, libelle: "", code: "", type_service: "", couleur: "#4361ee" };
            this.formProduct = {
                id: null,
                code_barre: this.generateEAN13(),
                reference: "PROD-" + Math.floor(100000 + Math.random() * 900000),
                categorie_id: "",
                libelle: "",
                prix_unitaire: 0,
                unite: "pce",
                seuil_reappro: 0,
                qte_init: "",
                quantified: true,
                tva: false,
                emplacement_id: "",
                prices_config: [],
            };
            this.formMvt = { produit_id: "", type_mouvement: "", numdoc: "", quantite: "", source: "", destination: "", date_mouvement: "" };
            this.formEntree = { produit_id: "", emplacement_id: "", quantite: "" };
            this.disabled = false;
        },

        whenModalHidden() {
            const modals = document.querySelectorAll(".modal");
            modals.forEach((el) => { el.addEventListener("hidden.bs.modal", () => this.resetAll()); });
        },

        generateEAN13() {
            let digits = "";
            for (let i = 0; i < 12; i++) digits += Math.floor(Math.random() * 10);
            let sum = 0;
            for (let i = 0; i < digits.length; i++) {
                let num = parseInt(digits[i]);
                sum += i % 2 === 0 ? num : num * 3;
            }
            let checkDigit = (10 - (sum % 10)) % 10;
            return digits + checkDigit;
        },
    },

    computed: {
        allProducts() { return this.products; },
        allCategories() { return this.categories; },
        allStockMvts() { return this.mouvements; }
    },
});

