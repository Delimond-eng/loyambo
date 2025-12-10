import { post, get, postJson } from "../modules/http.js";
import Paginator from "../components/pagination.js";

const Store = Vue.observable({
    currentInventory: null,
});

new Vue({
    el: "#AppInventory",
    components: {
        Paginator,
    },
    data() {
        return {
            error: null,
            result: null,
            isLoading: false,
            isDataLoading: false,
            load_id: "",

            store: Store,
            search: "",
            form: {
                emplacement_id: "",
            },
            pagination: {
                current_page: 1,
                last_page: 1,
                total: 10,
                per_page: 10,
            },
            products: [],
            inventories: [],
            selectedProductIds: [],
            inventoryLines: [],
        };
    },

    mounted() {
        this.loadCurrentInventory();
        this.viewInventoriesHistory();
    },

    methods: {
        viewInventoriesHistory() {
            this.isDataLoading = true;
            get("/inventories.all")
                .then(({ data, status }) => {
                    this.isDataLoading = false;
                    this.inventories = data.inventories.data;
                    this.pagination = {
                        current_page: data.inventories.current_page,
                        last_page: data.inventories.last_page,
                        total: data.inventories.total,
                        per_page: data.inventories.per_page,
                    };
                })
                .catch((err) => {
                    this.isDataLoading = false;
                    console.error("Erreur:", err);
                });
        },

        changePage(page) {
            this.pagination.current_page = page;
            this.viewInventoriesHistory();
        },

        onPerPageChange(perPage) {
            this.pagination.per_page = perPage;
            this.pagination.current_page = 1;
            this.viewInventoriesHistory();
        },

        // Chargement de l'inventaire en cours depuis l'API
        loadCurrentInventory() {
            this.isDataLoading = true;
            get("/inventory.current")
                .then((res) => {
                    this.isDataLoading = false;
                    if (res.data.status === "success" && res.data.inventory) {
                        this.store.currentInventory = res.data.inventory;
                        this.viewAllProducts(res.data.inventory.emplacement_id);
                        this.loadInventoryFromCache(); // Restaurer depuis le cache
                    }
                })
                .catch((err) => {
                    this.isDataLoading = false;
                    console.error(
                        "Erreur lors du chargement de l'inventaire en cours :",
                        err
                    );
                });
        },

        // Voir tous les produits
        viewAllProducts(id) {
            this.isDataLoading = true;
            get(`/inventory.products?emp_id=${id}`)
                .then((res) => {
                    this.isDataLoading = false;
                    this.products = res.data.products;
                })
                .catch((err) => {
                    console.log("error", err);
                    this.isDataLoading = false;
                });
        },

        // Ajouter ou retirer un produit de l'inventaire
        toggleProductSelection(product) {
            const exists = this.selectedProductIds.includes(product.id);
            if (exists) {
                this.selectedProductIds = this.selectedProductIds.filter(
                    (id) => id !== product.id
                );
                this.inventoryLines = this.inventoryLines.filter(
                    (p) => p.id !== product.id
                );
            } else {
                this.selectedProductIds.push(product.id);
                this.inventoryLines.push({
                    ...product,
                    real_quantity: null,
                });
            }

            this.saveInventoryToCache(); // Sauvegarder après chaque changement
        },

        getInventoryGap(line) {
            if (line.real_quantity === null || line.real_quantity === "")
                return "";
            const theoretical = line.stock_global || 0;
            return line.real_quantity - theoretical;
        },

        getInventoryValue(line) {
            const gap = this.getInventoryGap(line);
            if (gap === "") return "";
            const price = line.prix_unitaire || 0;
            return gap * price;
        },

        getTotalGap() {
            return this.inventoryLines.reduce((sum, line) => {
                const gap = this.getInventoryGap(line);
                return sum + (gap !== "" ? gap : 0);
            }, 0);
        },

        getTotalValue() {
            return this.inventoryLines.reduce((sum, line) => {
                const val = this.getInventoryValue(line);
                return sum + (val !== "" ? val : 0);
            }, 0);
        },

        openStartModal() {
            $(".modal-inventory-start").modal("show");
        },
        // Démarrer un nouvel inventaire
        startInventory() {
            this.isLoading = true;
            postJson("/inventory.start", this.form)
                .then(({ data }) => {
                    this.isLoading = false;
                    if (data.errors !== undefined) {
                        this.error = data.errors;
                        $.toast({
                            heading: "Echec de traitement",
                            text: data.errors.toString(),
                            position: "top-right",
                            loaderBg: "#ff4949ff",
                            icon: "error",
                            hideAfter: 3000,
                            stack: 6,
                        });
                    }
                    if (data.result !== undefined) {
                        $(".modal-inventory-start").modal("hide");
                        this.loadCurrentInventory(); // Charge et restaure depuis le cache
                    }
                })
                .catch((err) => {
                    console.log(err);
                    this.isLoading = false;
                    this.error = err;
                });
        },

        //valider un inventaire en cours
        validateInventory() {
            let items = [];
            this.inventoryLines.forEach((el) => {
                items.push({
                    theoretical_quantity: parseInt(el.stock_global),
                    real_quantity: el.real_quantity,
                    product_id: el.id,
                });
            });
            let formData = {
                inventory_id: this.currentInventory.id,
                items: items,
            };
            this.isLoading = true;
            postJson("/inventory.validate", formData)
                .then(({ data }) => {
                    this.isLoading = false;
                    if (data.errors !== undefined) {
                        this.error = data.errors;
                        $.toast({
                            heading: "Echec de traitement",
                            text: data.errors.toString(),
                            position: "top-right",
                            loaderBg: "#ff4949ff",
                            icon: "error",
                            hideAfter: 3000,
                            stack: 6,
                        });
                    }
                    if (data.result !== undefined) {
                        this.loadCurrentInventory();
                        this.clearAll();
                        this.viewInventoriesHistory();
                        Swal.fire({
                            title: data.result,
                            icon: "success",
                            timer: 3000,
                            showConfirmButton: !1,
                        });
                    }
                })
                .catch((err) => {
                    console.log(err);
                    this.isLoading = false;
                    this.error = err;
                });
        },

        //Supprimer un inventaire en cours
        deleteInventory(id) {
            let self = this;
            new Swal({
                title: "Attention! Action irréversible.",
                text: "Voulez-vous vraiment annuler l'inventaire en cours ?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Confirmer",
                cancelButtonText: "Annuler",
            }).then((result) => {
                if (result.isConfirmed) {
                    self.load_id = id;
                    postJson("/inventory.delete", {
                        inventory_id: id,
                    })
                        .then((res) => {
                            self.load_id = "";
                            self.viewInventoriesHistory();
                        })
                        .catch((err) => {
                            self.load_id = "";
                        });
                }
            });
        },

        // Sauvegarder l’état actuel dans le cache local
        saveInventoryToCache() {
            if (this.currentInventory) {
                const cache = {
                    inventory_id: this.currentInventory.id,
                    selectedProductIds: this.selectedProductIds,
                    inventoryLines: this.inventoryLines,
                };
                localStorage.setItem("inventory_cache", JSON.stringify(cache));
            }
        },

        // Restaurer les données depuis le cache si l'ID correspond
        loadInventoryFromCache() {
            const cached = localStorage.getItem("inventory_cache");
            if (cached) {
                try {
                    const data = JSON.parse(cached);
                    if (
                        this.store.currentInventory &&
                        data.inventory_id === this.currentInventory.id
                    ) {
                        this.selectedProductIds = data.selectedProductIds || [];
                        this.inventoryLines = data.inventoryLines || [];
                    }
                } catch (e) {
                    console.error("Erreur de lecture du cache inventaire :", e);
                }
            }
        },

        // Nettoyer le cache
        clearInventoryCache() {
            localStorage.removeItem("inventory_cache");
        },

        clearAll() {
            this.store.currentInventory = null;
            this.selectedProductIds = [];
            this.inventoryLines = [];
        },
    },

    computed: {
        allProducts() {
            if (this.search && this.search.trim()) {
                return this.products.filter((el) =>
                    el.libelle.toLowerCase().includes(this.search.toLowerCase())
                );
            } else {
                return this.products;
            }
        },

        allInventories() {
            return this.inventories;
        },

        currentInventory() {
            return this.store.currentInventory;
        },

        formateSimpleDate() {
            return (date) =>
                moment(date, "YYYY-MM-DD").locale("fr").format("DD MMMM YYYY");
            // ex: "14 avril 2021"
        },
        formateDate() {
            return (date) =>
                moment(date, "YYYY-MM-DD HH:mm")
                    .locale("fr")
                    .format("DD MMM YYYY HH:mm");
            // ex: "14 avril 2021"
        },

        formateTime() {
            return (date) =>
                moment(date, "DD/MM/YYYY HH:mm").locale("fr").format("hh:mm");
            // ex: "03:13 AM"
        },
        getStatus() {
            return (status) => {
                if (status === "pending") {
                    return "En cours";
                } else if (status === "closed") {
                    return "Terminé";
                }
            };
        },
    },
});
