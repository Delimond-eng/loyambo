import { post, get, postJson } from "../modules/http.js";
import Paginator from "../components/pagination.js";

const Store = Vue.observable({ currentInventory: null });

new Vue({
    el: "#AppInventory",
    components: { Paginator },
    data() {
        return {
            error: null,
            result: null,
            isLoading: false,
            isDataLoading: false,
            load_id: "",
            store: Store,
            search: "",
            form: { emplacement_id: "" },
            pagination: { current_page: 1, last_page: 1, total: 10, per_page: 10 },
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
            get(`/inventories.all?page=${this.pagination.current_page}&per_page=${this.pagination.per_page}`)
                .then(({ data }) => {
                    this.isDataLoading = false;
                    this.inventories = data.inventories.data || [];
                    this.pagination = {
                        current_page: data.inventories.current_page,
                        last_page: data.inventories.last_page,
                        total: data.inventories.total,
                        per_page: data.inventories.per_page,
                    };
                })
                .catch(() => { this.isDataLoading = false; });
        },

        changePage(page) { this.pagination.current_page = page; this.viewInventoriesHistory(); },
        onPerPageChange(perPage) { this.pagination.per_page = perPage; this.pagination.current_page = 1; this.viewInventoriesHistory(); },

        // Chargement de l'inventaire en cours
        loadCurrentInventory() {
            this.isDataLoading = true;
            get("/inventory.current")
                .then((res) => {
                    this.isDataLoading = false;
                    if (res.data.status === "success" && res.data.inventory) {
                        this.store.currentInventory = res.data.inventory;
                        this.viewAllProducts(res.data.inventory.emplacement_id);
                        this.loadInventoryFromCache();
                    }
                })
                .catch(() => { this.isDataLoading = false; });
        },

        // Voir tous les produits (stock global) avec prix de r�f�rence par emplacement si fourni
        viewAllProducts(empId) {
            this.isDataLoading = true;
            get(`/inventory.products?emp_id=${empId || ""}`)
                .then((res) => {
                    this.products = res.data.products || [];
                    this.isDataLoading = false;
                })
                .catch(() => { this.isDataLoading = false; });
        },

        // Ajouter / retirer produit de l�inventaire
        toggleProductSelection(product) {
            const exists = this.selectedProductIds.includes(product.id);
            if (exists) {
                this.selectedProductIds = this.selectedProductIds.filter((id) => id !== product.id);
                this.inventoryLines = this.inventoryLines.filter((p) => p.id !== product.id);
            } else {
                this.selectedProductIds.push(product.id);
                this.inventoryLines.push({ ...product, real_quantity: product.stock_global ?? null });
            }
            this.saveInventoryToCache();
        },

        getRefPrice(line) {
            if (this.currentInventory && this.currentInventory.emplacement_id && line.emplacements) {
                const emp = line.emplacements.find(e => e.id === this.currentInventory.emplacement_id);
                if (emp && emp.pivot && emp.pivot.prix !== undefined) return emp.pivot.prix;
            }
            return line.prix_unitaire || 0;
        },

        getInventoryGap(line) {
            if (line.real_quantity === null || line.real_quantity === "") return "";
            const theoretical = line.stock_global || 0;
            return line.real_quantity - theoretical;
        },

        getInventoryValue(line) {
            const gap = this.getInventoryGap(line);
            if (gap === "") return "";
            return gap * this.getRefPrice(line);
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

        openStartModal() { $(".modal-inventory-start").modal("show"); },

        startInventory() {
            this.isLoading = true;
            postJson("/inventory.start", this.form)
                .then(({ data }) => {
                    this.isLoading = false;
                    if (data.errors) {
                        $.toast({ heading: "Echec de traitement", text: data.errors.toString(), icon: "error", position: "top-right" });
                        return;
                    }
                    if (data.result) {
                        $(".modal-inventory-start").modal("hide");
                        this.loadCurrentInventory();
                    }
                })
                .catch(() => { this.isLoading = false; });
        },

        validateInventory() {
            const items = this.inventoryLines.map(el => ({
                theoretical_quantity: parseInt(el.stock_global),
                real_quantity: el.real_quantity,
                product_id: el.id,
            }));
            const formData = { inventory_id: this.currentInventory.id, items };
            this.isLoading = true;
            postJson("/inventory.validate", formData)
                .then(({ data }) => {
                    this.isLoading = false;
                    if (data.errors) {
                        $.toast({ heading: "Echec de traitement", text: data.errors.toString(), icon: "error", position: "top-right" });
                        return;
                    }
                    if (data.result !== undefined) {
                        this.loadCurrentInventory();
                        this.clearAll();
                        this.viewInventoriesHistory();
                        localStorage.removeItem("inventory_cache");
                        Swal.fire({ title: data.result, icon: "success", timer: 3000, showConfirmButton: false });
                    }
                })
                .catch(() => { this.isLoading = false; });
        },

        deleteInventory(id) {
            Swal.fire({ title: "Attention! Action irréversible.", text: "Voulez-vous vraiment annuler l'inventaire en cours ?", icon: "warning", showCancelButton: true, confirmButtonText: "Confirmer", cancelButtonText: "Annuler" })
                .then((result) => {
                    if (result.isConfirmed) {
                        this.load_id = id;
                        postJson("/inventory.delete", { inventory_id: id })
                            .then(() => { this.load_id = ""; this.viewInventoriesHistory(); })
                            .catch(() => { this.load_id = ""; });
                    }
                });
        },

        saveInventoryToCache() {
            if (this.currentInventory) {
                const cache = { inventory_id: this.currentInventory.id, selectedProductIds: this.selectedProductIds, inventoryLines: this.inventoryLines };
                localStorage.setItem("inventory_cache", JSON.stringify(cache));
            }
        },

        loadInventoryFromCache() {
            const cached = localStorage.getItem("inventory_cache");
            if (cached) {
                try {
                    const data = JSON.parse(cached);
                    if (this.store.currentInventory && data.inventory_id === this.currentInventory.id) {
                        this.selectedProductIds = data.selectedProductIds || [];
                        this.inventoryLines = data.inventoryLines || [];
                    }
                } catch (e) { console.error("Erreur de lecture du cache inventaire :", e); }
            }
        },

        clearInventoryCache() { localStorage.removeItem("inventory_cache"); },
        clearAll() { this.store.currentInventory = null; this.selectedProductIds = []; this.inventoryLines = []; },
    },

    computed: {
        allProducts() {
            if (this.search && this.search.trim()) {
                return this.products.filter((el) => el.libelle.toLowerCase().includes(this.search.toLowerCase()));
            }
            return this.products;
        },
        allInventories() { return this.inventories; },
        currentInventory() { return this.store.currentInventory; },
        formateSimpleDate() { return (date) => moment(date, "YYYY-MM-DD").locale("fr").format("DD MMMM YYYY"); },
        formateDate() { return (date) => moment(date, "YYYY-MM-DD HH:mm").locale("fr").format("DD MMM YYYY HH:mm"); },
        formateTime() { return (date) => moment(date, "DD/MM/YYYY HH:mm").locale("fr").format("hh:mm"); },
        getStatus() { return (status) => status === "pending" ? "En cours" : status === "closed" ? "Terminé" : status; },
    },
});
