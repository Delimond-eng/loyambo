import { post, postJson, get } from "../modules/http.js";

const Store = Vue.observable({
    cart: [],
});
const MISSING_REPORTS_REDIRECT_KEY = "missing_day_close_reports";

document.querySelectorAll(".AppService").forEach((el) => {
    const globalRate = parseFloat(el.getAttribute('data-rate')) || 0;

    new Vue({
        el: el,
        data() {
            return {
                error: null,
                result: null,
                isLoading: false,
                isDataLoading: true,
                emplacementsLoading: false,
                serveurs: [],
                tables: [],
                chambres: [],
                categories: [],
                products: [],
                session: null,
                table: null,
                chambre: null,
                selectedPendingTable: null,
                selectedFacture: null,
                editedCommandeId: null,
                modes: [
                    { value: "cash", label: "ESPÃƒË†CES", icon: "fa fa-money" },
                    { value: "mobile", label: "M-PESA/ORANGE", icon: "fa fa-mobile-phone" },
                    { value: "card", label: "BANQUE/CARTE", icon: "fa fa-credit-card" },
                ],
                selectedMode: 'cash',
                selectedModeRef: "",
                operation: null,
                selectedTables: [],
                selectedData: null,
                store: Store,
                search: "",
                load_id: "",

                payment: {
                    amount_received: 0,
                    currency: 'CDF',
                    rate: globalRate
                },

                form: {
                    total_especes: 0,
                    tickets_serveur: 0,
                },

                emplacements: [],
                currentEmplacement: null,
                showMobileCart: false,
                selectedCategory: null,
                isClosingDayLoading: false,
                isRefreshingMissingReports: false,
                missingServeursReports: [],
                missingReportsMessage: "",
                reopenMissingModalAfterReport: false,
            };
        },

        mounted() {
            this.refreshUserOrderSession();
            this.refreshTableData();
            const path = location.pathname;
            if (path === "/orders.interface") {
                this.loadEditedCommande();
                this.initOrderInterfaceFast();
                if(path !== '/orders.interface'){
                    window.addEventListener("beforeunload", this.cleanupLocalCache);
                }
            } else if (path === "/orders.portal") {
                // On arrive sur le portail : on purge les caches d'ÃƒÂ©dition pour repartir propre
                this.cleanupLocalCache();
                this.viewAllTables();
            } else if (path === "/serveurs" || path === "/serveurs.activities") {
                this.getAllServeursServices().then(() => {
                    if (path === "/serveurs.activities") {
                        this.tryOpenMissingReportsAfterRedirect();
                    }
                });
            } else {
                this.isDataLoading = false;
            }
        },

        methods: {
            cleanupLocalCache() {
                try {
                    localStorage.removeItem("edited-orders");
                    localStorage.removeItem("table");
                    localStorage.removeItem("chambre");
                    localStorage.removeItem("current_emplacement");
                } catch (e) {}
                this.store.cart = [];
            },

            // --- MÃƒâ€°THODE D'IMPRESSION RESTAURÃƒâ€°E ---
            printInvoiceFromJson(facture, place, copies = 1) {
                const singleTicket = `
                    <div style="font-family: monospace; width: 80mm; padding: 5px;">
                        <h3 style="text-align: center; margin-bottom: 5px;">${place?.libelle || 'LOYAMBO'}</h3>
                        <p style="text-align: center; font-size: 12px; margin-bottom: 10px;">Bon NÃ‚Â°: ${facture.id}</p>
                        <hr border="1" style="border-style: dashed;">
                        <table style="width: 100%; font-size: 12px;">
                            ${facture.details.map(d => `
                                <tr>
                                    <td>${d.produit.libelle}</td>
                                    <td style="text-align: right;">${d.quantite} x ${d.prix_unitaire}</td>
                                </tr>
                            `).join('')}
                        </table>
                        <hr border="1" style="border-style: dashed;">
                        <h4 style="text-align: right;">TOTAL: ${facture.total_ttc} CDF</h4>
                        <p style="text-align: center; font-size: 10px; margin-top: 20px;">Merci de votre visite !</p>
                    </div>
                `;
                const printWindow = window.open('', '', 'height=600,width=400');
                printWindow.document.write('<html><body>' + singleTicket + '</body></html>');
                printWindow.document.close();
                printWindow.focus();
                printWindow.print();
                printWindow.close();
            },

            formatAmount(value) {
                const amount = Number(value || 0);
                return Number.isFinite(amount) ? amount.toLocaleString() : "0";
            },

            normalizeServeurReport(srv) {
                const item = srv || {};
                const userId = item.user_id ?? item.serveur_id ?? item.user?.id ?? null;
                const totalEncaisse = Number(item.total_encaisse ?? item.montant_vendu ?? 0);
                const totalTicket = Number(item.total_ticket ?? item.factures_actives ?? 0);
                return {
                    ...item,
                    user_id: userId,
                    serveur_id: userId,
                    total_encaisse: totalEncaisse,
                    montant_vendu: Number(item.montant_vendu ?? totalEncaisse),
                    total_ticket: totalTicket,
                    rapport_statut: item.rapport_statut || "none",
                };
            },

            openMissingServeursReportsModal(serveurs = [], message = "") {
                this.missingReportsMessage = message || "Certains serveurs actifs doivent encore remettre leur rapport.";
                this.missingServeursReports = (serveurs || []).map((srv) => this.normalizeServeurReport(srv));
                $("#missingServeurReportsModal").modal("show");
            },

            closeMissingServeursReportsModal() {
                $("#missingServeurReportsModal").modal("hide");
            },

            saveMissingReportsRedirectPayload(serveurs = [], message = "") {
                try {
                    const payload = {
                        serveurs: (serveurs || []).map((srv) => this.normalizeServeurReport(srv)),
                        message: message || "La cloture est bloquee: des rapports serveurs sont manquants.",
                        created_at: Date.now(),
                    };
                    sessionStorage.setItem(MISSING_REPORTS_REDIRECT_KEY, JSON.stringify(payload));
                } catch (e) {}
            },

            consumeMissingReportsRedirectPayload() {
                try {
                    const raw = sessionStorage.getItem(MISSING_REPORTS_REDIRECT_KEY);
                    if (!raw) return null;
                    sessionStorage.removeItem(MISSING_REPORTS_REDIRECT_KEY);
                    const parsed = JSON.parse(raw);
                    if (!parsed || !Array.isArray(parsed.serveurs)) return null;
                    return parsed;
                } catch (e) {
                    return null;
                }
            },

            goToServeursActivitiesForMissingReports(serveurs = [], message = "") {
                this.saveMissingReportsRedirectPayload(serveurs, message);
                window.location.href = "/serveurs.activities?open_missing_reports=1";
            },

            tryOpenMissingReportsAfterRedirect() {
                const payload = this.consumeMissingReportsRedirectPayload();
                const search = new URLSearchParams(window.location.search || "");
                const askedByQuery = search.get("open_missing_reports") === "1";

                if (!payload && !askedByQuery) {
                    return;
                }

                const list = payload?.serveurs?.length
                    ? payload.serveurs
                    : this.missingServeursReports;

                if (list.length > 0) {
                    this.openMissingServeursReportsModal(list, payload?.message || "");
                }

                if (askedByQuery) {
                    search.delete("open_missing_reports");
                    const q = search.toString();
                    const cleanUrl = q ? `${location.pathname}?${q}` : location.pathname;
                    window.history.replaceState({}, "", cleanUrl);
                }
            },

            async refreshMissingServeursReports() {
                this.isRefreshingMissingReports = true;
                try {
                    const { data } = await get("/serveurs.services");
                    if (data.status !== "success") {
                        this.serveurs = [];
                        this.missingServeursReports = [];
                        return;
                    }

                    this.serveurs = data.serveurs || [];
                    this.missingServeursReports = this.serveurs
                        .filter((srv) => (srv.rapport_statut || "none") !== "done")
                        .map((srv) => this.normalizeServeurReport(srv));
                } catch (e) {
                    $.toast({
                        heading: "Echec de traitement",
                        text: "Impossible de rafraichir les rapports serveurs.",
                        position: "top-right",
                        loaderBg: "#ff4949ff",
                        icon: "error",
                        hideAfter: 3000,
                        stack: 6,
                    });
                } finally {
                    this.isRefreshingMissingReports = false;
                }
            },

            getTableOperationColorClass(table) {
                if (!this.operation) return "border-transparent";
                const isSelected = this.selectedTables.some(t => t.id === table.id);
                if (isSelected) return "table-selected-op shadow-lg";
                return this.operation === "transfert" ? "border-info" : "border-success";
            },

            loadEditedCommande() {
                const cached = localStorage.getItem("edited-orders");
                if (cached) {
                    try {
                        const data = JSON.parse(cached);
                        this.editedCommandeId = data.id;
                        this.store.cart = data.details.map(d => ({
                            id: d.produit.id, libelle: d.produit.libelle, prix_unitaire: parseFloat(d.prix_unitaire),
                            qte: parseInt(d.quantite), unite: d.produit.unite, reference: d.produit.reference,
                            code_barre: d.produit.code_barre, image: d.produit.image
                        }));
                    } catch (e) { console.error("Erreur cache", e); }
                }
            },

            formateSimpleDate(date) { return date ? moment(date).locale('fr').format('DD MMM YYYY') : '---'; },
            formateDate(date) { return date ? moment(date).locale('fr').format('DD/MM/YYYY') : '---'; },
            formateTime(date) { return date ? moment(date).format('HH:mm') : '---'; },

            async initOrderInterfaceFast() {
                this.isDataLoading = true;
                this.emplacementsLoading = true;
                try {
                    const [catRes, empRes] = await Promise.all([get("/categories.all"), get("/emplacements.all")]);
                    this.categories = catRes.data.categories || [];
                    this.emplacements = empRes.data.emplacements || [];
                    this.emplacementsLoading = false;
                    // Si la table possÃƒÂ¨de un emplacement, on l'utilise en prioritÃƒÂ©
                    if (this.table && this.table.emplacement) {
                        this.currentEmplacement = this.table.emplacement;
                        await this.viewAllProducts();
                    } else {
                        const savedEmp = localStorage.getItem("current_emplacement");
                        if (savedEmp) {
                            this.currentEmplacement = JSON.parse(savedEmp);
                            await this.viewAllProducts();
                        } else {
                            $("#modalEmplacement").modal("show");
                        }
                    }
                } catch (e) { console.error(e); }
                finally { this.isDataLoading = false; this.emplacementsLoading = false; }
            },

            async viewAllProducts() {
                const empId = this.currentEmplacement ? this.currentEmplacement.id : '';
                const { data } = await get(`/products.all?emp_id=${empId}`);
                this.products = (data.produits || []).map(p => ({...p, prix_unitaire: p.prix_emplacement ?? p.prix_unitaire }));
            },

            showEmplacementModal() { $("#modalEmplacement").modal("show"); },

            selectEmplacement(emp) {
                if (this.table && this.table.emplacement) return; // table dÃƒÂ©termine dÃƒÂ©jÃƒÂ  l'emplacement
                this.currentEmplacement = emp;
                localStorage.setItem("current_emplacement", JSON.stringify(emp));
                $("#modalEmplacement").modal("hide");
                this.isDataLoading = true;
                this.viewAllProducts().then(() => { this.isDataLoading = false; });
            },

            filterByCategory(cat) {
                this.selectedCategory = cat;
                this.isDataLoading = true;
                get(`/products.all?emp_id=${this.currentEmplacement?.id}&cat_id=${cat.id}`)
                    .then(({ data }) => { this.products = (data.produits || []).map(p => ({...p, prix_unitaire: p.prix_emplacement ?? p.prix_unitaire })); this.isDataLoading = false; });
            },

            toggleMobileCart() { this.showMobileCart = !this.showMobileCart; },

            triggerClosingDay(options = {}) {
                const skipConfirm = Boolean(options && options.skipConfirm);
                if (!skipConfirm) {
                    Swal.fire({
                        title: "Cloture ?",
                        text: "Confirmer la cloture de la journee en cours",
                        icon: "warning",
                        showCancelButton: true
                    }).then((res) => {
                        if (res.isConfirmed) this.executeClosingDayRequest();
                    });
                    return;
                }

                this.executeClosingDayRequest();
            },

            executeClosingDayRequest() {
                this.isClosingDayLoading = true;
                postJson("/day.close", {})
                    .then(({ data }) => {
                        if (data.status === "success") {
                            this.closeMissingServeursReportsModal();
                            this.missingServeursReports = [];
                            Swal.fire({
                                icon: "success",
                                title: "Succes",
                                text: data.message || "Journee cloturee."
                            }).then(() => {
                                if (data.report_url) window.open("/" + data.report_url, "_blank");
                            });
                            return;
                        }

                        if (data.status === "failed") {
                            const missingServeurs = (data.serveurs || []).map((srv) => this.normalizeServeurReport(srv));
                            if (missingServeurs.length > 0) {
                                if (location.pathname === "/serveurs.activities") {
                                    this.openMissingServeursReportsModal(missingServeurs, data.message);
                                } else {
                                    this.goToServeursActivitiesForMissingReports(missingServeurs, data.message);
                                }
                                return;
                            }
                        }

                        $.toast({
                            heading: "Echec de traitement",
                            text: data.errors || data.message || "Veuillez reessayer plus tard !",
                            position: "top-right",
                            loaderBg: "#ff4949ff",
                            icon: "error",
                            hideAfter: 3500,
                            stack: 6,
                        });
                    })
                    .catch(() => {
                        $.toast({
                            heading: "Echec de traitement",
                            text: "Veuillez reessayer plus tard !",
                            position: "top-right",
                            loaderBg: "#ff4949ff",
                            icon: "error",
                            hideAfter: 3500,
                            stack: 6,
                        });
                    })
                    .finally(() => {
                        this.isClosingDayLoading = false;
                    });
            },

            triggerSingleClosing(srv, options = {}) {
                const normalized = this.normalizeServeurReport(srv);
                this.selectedData = normalized;
                this.form.total_especes = Number(normalized.total_encaisse || 0);
                this.form.tickets_serveur = Number(normalized.total_ticket || 0);

                if (options && options.fromMissingModal) {
                    this.reopenMissingModalAfterReport = true;
                    this.closeMissingServeursReportsModal();
                } else {
                    this.reopenMissingModalAfterReport = false;
                }

                $("#reportAppendModal").modal("show");
            },

            triggerSendServeurReport() {
                if (!this.selectedData) return;
                this.isLoading = true;
                const shouldReopenMissingModal = this.reopenMissingModalAfterReport;

                const serveurId =
                    this.selectedData.user_id ??
                    this.selectedData.serveur_id ??
                    this.selectedData.user?.id;

                const payload = {
                    serveur_id: serveurId,
                    total_especes: Number(this.form.total_especes || 0),
                    tickets_serveur: Number(this.form.tickets_serveur || 0),
                    valeur_theorique: Number(this.selectedData.total_encaisse || 0),
                    tickets_emis: Number(this.selectedData.total_ticket || 0),
                };

                postJson("/day.close.report", payload)
                    .then(async ({ data }) => {
                        if (data.status === "success") {
                            $("#reportAppendModal").modal("hide");
                            this.selectedData = null;
                            this.form.total_especes = 0;
                            this.form.tickets_serveur = 0;

                            if (location.pathname === "/serveurs.activities") {
                                await this.refreshMissingServeursReports();
                            } else {
                                await this.getAllServeursServices();
                            }

                            if (shouldReopenMissingModal && location.pathname === "/serveurs.activities") {
                                const reopenMessage = this.missingServeursReports.length > 0
                                    ? "Continuez les rapports restants, puis relancez la cloture."
                                    : "Tous les rapports sont enregistres. Vous pouvez relancer la cloture.";
                                this.openMissingServeursReportsModal(this.missingServeursReports, reopenMessage);
                            }

                            $.toast({
                                heading: "Rapport enregistre",
                                text: data.message || "Rapport serveur valide.",
                                position: "top-right",
                                loaderBg: "#49ff86ff",
                                icon: "success",
                                hideAfter: 2500,
                                stack: 6,
                            });
                            return;
                        }

                        $.toast({
                            heading: "Echec de traitement",
                            text: data.errors || data.message || "Veuillez actualiser et reessayer.",
                            position: "top-right",
                            loaderBg: "#ff4949ff",
                            icon: "error",
                            hideAfter: 3500,
                            stack: 6,
                        });
                    })
                    .catch(() => {
                        $.toast({
                            heading: "Echec de traitement",
                            text: "Veuillez reessayer plus tard !",
                            position: "top-right",
                            loaderBg: "#ff4949ff",
                            icon: "error",
                            hideAfter: 3500,
                            stack: 6,
                        });
                    })
                    .finally(() => {
                        this.isLoading = false;
                        this.reopenMissingModalAfterReport = false;
                    });
            },
            addToCart(product) {
                const found = this.cart.find((p) => p.id === product.id);
                if (found) found.qte += 1;
                else this.cart.push({ ...product, qte: 1 });
            },

            removeFromCart(product) { this.store.cart = this.store.cart.filter((p) => p.id !== product.id); },

            getAllServeursServices() {
                this.isDataLoading = true;
                const url = (location.pathname === "/serveurs") ? "/serveurs.all" : "/serveurs.services";
                return get(url)
                    .then(({ data }) => {
                        if (data.status !== "success") {
                            this.serveurs = [];
                            this.missingServeursReports = [];
                            $.toast({
                                heading: "Information",
                                text: data.message || "Aucune donnÃƒÂ©e serveur disponible.",
                                position: "top-right",
                                loaderBg: "#ffb84d",
                                icon: "info",
                                hideAfter: 2500,
                                stack: 6,
                            });
                            return;
                        }
                        this.serveurs = (location.pathname === "/serveurs") ? (data.users || []) : (data.serveurs || []);
                        if (location.pathname === "/serveurs.activities") {
                            this.missingServeursReports = this.serveurs
                                .filter((srv) => (srv.rapport_statut || "none") !== "done")
                                .map((srv) => this.normalizeServeurReport(srv));
                        } else {
                            this.missingServeursReports = [];
                        }
                    })
                    .catch(() => {
                        this.serveurs = [];
                        this.missingServeursReports = [];
                        $.toast({
                            heading: "Echec de traitement",
                            text: "Impossible de charger les activitÃƒÂ©s serveurs.",
                            position: "top-right",
                            loaderBg: "#ff4949ff",
                            icon: "error",
                            hideAfter: 3500,
                            stack: 6,
                        });
                    })
                    .finally(() => {
                        this.isDataLoading = false;
                    });
            },

            viewAllTables() {
                this.isDataLoading = true;
                get("/tables.all").then(({ data }) => {
                    this.isDataLoading = false;
                    this.tables = data.tables || [];
                    this.chambres = data.chambres || [];
                });
            },

            goToUserOrderSession(user) { localStorage.setItem("user", JSON.stringify(user)); location.href = "/orders.portal"; },

            goToOrderPannel(table, isTable = false) {
                if (this.operation) {
                    this.selectedTables.push(table);
                    if (this.selectedTables.length === 2) {
                        if (this.operation === "transfert") {
                            const [t1, t2] = this.selectedTables;
                            const occValues = ["occupÃƒÂ©e", "occupÃƒÆ’Ã‚Â©e"];
                            const hasLibre = t1.statut === "libre" || t2.statut === "libre";
                            const hasOccupee = occValues.includes(t1.statut) || occValues.includes(t2.statut);
                            if (!hasLibre || !hasOccupee) {
                                Swal.fire({ icon: "warning", title: "Transfert impossible", text: "SÃƒÂ©lectionnez une table occupÃƒÂ©e et une table libre." });
                                this.selectedTables = [];
                                return;
                            }
                            const source = occValues.includes(t1.statut) ? t1 : t2;
                            const cible = source === t1 ? t2 : t1;
                            this.triggerOperation({ op: this.operation, source_id: source.id, cible_id: cible.id });
                        } else if (this.operation === "combiner") {
                            const [t1, t2] = this.selectedTables;
                            const occValues = ["occupÃƒÂ©e", "occupÃƒÆ’Ã‚Â©e"];
                            const bothOccupees = occValues.includes(t1.statut) && occValues.includes(t2.statut);
                            if (!bothOccupees) {
                                Swal.fire({ icon: "warning", title: "Combinaison impossible", text: "SÃƒÂ©lectionnez deux tables occupÃƒÂ©es." });
                                this.selectedTables = [];
                                return;
                            }
                            this.triggerOperation({ op: this.operation, source_id: t1.id, cible_id: t2.id });
                        } else {
                            this.triggerOperation({ op: this.operation, source_id: this.selectedTables[0].id, cible_id: this.selectedTables[1].id });
                        }
                        this.selectedTables = []; this.operation = "";
                    }
                    return;
                }
                if (table.statut === "occupÃƒÂ©e" && !isTable) {
                    this.selectedPendingTable = table;
                    $(".modal-commande").modal("show");
                    return;
                }
                this.cleanupLocalCache();
                localStorage.setItem("table", JSON.stringify(table));
                location.href = "/orders.interface";
            },

            triggerOperation(data) {
                postJson(`/table.operation`, data).then(({ data }) => {
                    if (data.status === "success") { this.setOperation(""); this.selectedTables = []; this.viewAllTables(); }
                });
            },

            libererTable(table) {
                postJson(`/table.liberer`, { table_id: table.id }).then(() => {
                    $(".modal-commande").modal("hide"); this.viewAllTables();
                });
            },

            servirCmd(cmd) {
                postJson(`/cmd.servir`, { id: cmd.id }).then(() => {
                    $(".modal-commande").modal("hide"); this.viewAllTables();
                });
            },

            editCommande(cmd) {
                localStorage.setItem("edited-orders", JSON.stringify(cmd));
                localStorage.setItem("table", JSON.stringify(this.selectedPendingTable));
                location.href = "/orders.interface";
            },

            fusionnerCmds(cmds) {
                let factures = cmds.map(f => f.id);
                postJson("/factures.link", { factures: factures, user_id: this.session?.id })
                    .then(() => { this.viewAllTables(); $(".modal-commande").modal("hide"); });
            },

            triggerPayment() {
                const facture = this.selectedFacture;
                this.load_id = facture.id;
                postJson(`/payment.create`, {
                    facture_id: facture.id, mode: this.selectedMode, mode_ref: this.selectedModeRef,
                })
                .then(({ data }) => {
                    this.load_id = "";
                    if (data.status === "success") {
                        $.toast({ heading: "SuccÃƒÂ¨s", text: "Paiement enregistrÃƒÂ©", icon: "success", position: "top-right" });
                        $("#modal-pay-trigger").modal("hide");
                        $(".modal-commande").modal("hide");
                        this.viewAllTables();
                        this.payment.amount_received = 0;
                    } else if (data.errors) {
                        $.toast({ heading: "Erreur", text: data.errors, icon: "error", position: "top-right" });
                    }
                });
            },

            refreshUserOrderSession() { this.session = JSON.parse(localStorage.getItem("user")); },
            refreshTableData() {
                const t = localStorage.getItem("table");
                if (t) { try { this.table = JSON.parse(t); } catch(e) {} }
                const c = localStorage.getItem("chambre");
                if (c) { try { this.chambre = JSON.parse(c); } catch(e) {} }
            },

            viewAllCategories() { get("/categories.all").then(({ data }) => { this.categories = data.categories || []; }); },

            createFacture() {
                this.isLoading = true;
                const payload = {
                    user_id: this.session?.id,
                    emplacement_id: this.currentEmplacement?.id,
                    table_id: this.table?.id,
                    chambre_id: this.chambre?.id,
                    details: this.cart.map(i => ({ produit_id: i.id, quantite: i.qte, prix_unitaire: i.prix_unitaire }))
                };
                postJson("/facture.create", payload).then(({ data }) => {
                    this.isLoading = false;
                    if (data.status === "success") {
                        $.toast({ heading: "SuccÃƒÂ¨s", text: "Commande enregistrÃƒÂ©e", icon: "success", position: "top-right" });
                        this.cleanupLocalCache();
                        location.href = "/orders.portal";
                    } else if (data.errors) {
                        $.toast({ heading: "Erreur", text: data.errors, icon: "error", position: "top-right" });
                    }
                });
            },

            setOperation(op) { this.operation = op; this.selectedTables = []; },
            setPaymentCurrency(cur) { this.payment.currency = cur; },
            quickAmount(val) { this.payment.amount_received = val; },

            openPaymentModal(cmd) {
                this.selectedFacture = cmd;
                this.payment.amount_received = 0;
                this.payment.currency = 'CDF';
                if (!this.payment.rate && globalRate) {
                    this.payment.rate = globalRate;
                }
                $("#modal-pay-trigger").modal("show");
            },

            viewInvoiceDetail(cmd) {
                this.selectedFacture = cmd;
                $("#modal-invoice-detail").modal("show");
            },

            updateRate(event) {
                const val = parseFloat(event.target.value);
                this.payment.rate = isNaN(val) ? 0 : val;
            }
        },

        computed: {
            cart() { return this.store.cart; },
            allProducts() { return this.search ? this.products.filter(p => p.libelle.toLowerCase().includes(this.search.toLowerCase())) : this.products; },
            allCategories() { return this.categories; },
            userSession() { return this.session; },
            selectedTable() { return this.table; },
            selectedChambre() { return this.chambre; },
            totalGlobal() { return this.cart.reduce((sum, item) => sum + (item.prix_unitaire * item.qte), 0); },
            totalQte() { return this.cart.reduce((sum, item) => sum + item.qte, 0); },
            allTables() { return this.tables; },
            allChambres() { return this.chambres; },
            allServeurs() { return this.serveurs; },
            pendingServeursReportsCount() {
                return (this.serveurs || []).filter((srv) => (srv.rapport_statut || "none") !== "done").length;
            },
            missingReportsTotal() {
                return (this.missingServeursReports || []).reduce((sum, srv) => {
                    return sum + Number(srv.montant_vendu || srv.total_encaisse || 0);
                }, 0);
            },
            groupedTables() {
                if (!this.tables || this.tables.length === 0) return {};
                return this.tables.reduce((groups, table) => {
                    const key = table.emplacement ? table.emplacement.libelle : 'Autre';
                    if (!groups[key]) groups[key] = [];
                    groups[key].push(table);
                    return groups;
                }, {});
            },
            amountToPayUSD() {
                if (!this.selectedFacture || !this.payment.rate) return 0;
                return (this.selectedFacture.total_ttc / this.payment.rate).toFixed(2);
            },
            receivedInCDF() {
                if (this.payment.currency === 'CDF') return parseFloat(this.payment.amount_received) || 0;
                return (parseFloat(this.payment.amount_received) || 0) * this.payment.rate;
            },
            paymentChange() {
                if (!this.selectedFacture) return 0;
                return this.receivedInCDF - this.selectedFacture.total_ttc;
            },
            checkServiceStatus() { return (cmds) => cmds && cmds.some(item => item.statut_service === "servie"); },
            formateDate2() { return (date) => date ? moment(date).locale('fr').format('DD MMMM YYYY') : '---'; },
        }
    });
});




