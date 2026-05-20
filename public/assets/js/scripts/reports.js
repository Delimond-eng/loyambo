import { post, postJson, get } from "../modules/http.js";

const Store = Vue.observable({
    cart: [],
});

document.querySelectorAll(".AppReport").forEach((el) => {
    new Vue({
        el: el,
        data() {
            return {
                error: null,
                result: null,
                isLoading: false,
                isDataLoading: false,
                reports: [],
                store: Store,
                search: "",
                load_id: "",
                selectedSell: null,
                sellFactures: [],
            };
        },

        mounted() {
            this.getGlobalReports();
        },

        methods: {
            //GET ALL AGENTS SERVEUR
            getGlobalReports() {
                this.isDataLoading = true;
                const params = new URLSearchParams(window.location.search);
                const query = params.toString();
                get(query ? `/reports.all?${query}` : "/reports.all")
                    .then(({ data, status }) => {
                        this.isDataLoading = false;
                        this.reports = data.reports || [];
                    })
                    .catch((err) => {
                        this.isDataLoading = false;
                        this.reports = [];
                    });
            },

            formatSaleDate(value) {
                if (!value) return "---";
                return moment(value).locale("fr").format("DD/MM/YYYY");
            },

            formatTime(value) {
                if (!value) return "---";
                return moment(value).locale("fr").format("HH:mm");
            },

            formatDuration(start, end) {
                if (!start) return "---";
                const startAt = moment(start);
                const endAt = end ? moment(end) : moment();
                const minutes = Math.max(0, endAt.diff(startAt, "minutes"));
                const hours = Math.floor(minutes / 60);
                const mins = minutes % 60;
                return `${hours}h ${mins}m`;
            },

            //SHOW REPORT FACTURES OF SALE DAY
            showReportDetails(data) {
                this.selectedSell = data;
                const s = data;
                this.load_id = `${s.user?.id || ""}-${s.sale_day?.id || ""}`;
                const baseQuery = new URLSearchParams({
                    id: s.user?.id || "",
                    sale_day_id: s.sale_day?.id || "",
                });
                const filters = new URLSearchParams(window.location.search);
                ["service_type", "emplacement_id", "date_debut", "date_fin"].forEach(
                    (key) => {
                        const value = filters.get(key);
                        if (value) {
                            baseQuery.set(key, value);
                        }
                    }
                );
                const query = baseQuery.toString();

                get(`/report.detail?${query}`)
                    .then(({ data }) => {
                        this.sellFactures = data.factures || [];
                        $(".modal-sell-detail").modal("show");
                    })
                    .finally(() => {
                        this.load_id = "";
                    });
            },
        },

        computed: {
            allReports() {
                return this.reports;
            },
            totalEncaisse() {
                return this.reports.reduce(
                    (sum, item) => sum + parseFloat(item.total_factures || 0),
                    0
                );
            },
            totalFactures() {
                return this.reports.reduce(
                    (sum, item) => sum + parseInt(item.total_factures_count || 0, 10),
                    0
                );
            },
            totalPaiements() {
                return this.reports.reduce(
                    (sum, item) => sum + parseInt(item.total_paiements || 0, 10),
                    0
                );
            },
            totalJours() {
                const ids = new Set(
                    this.reports.map((item) => item.sale_day?.id).filter(Boolean)
                );
                return ids.size;
            },

            allPayment() {
                return (payments) => {
                    return payments.reduce(
                        (sum, item) => sum + parseFloat(item.amount),
                        0
                    );
                };
            },
            formatNumber() {
                return (value) =>
                    Number(value || 0).toLocaleString("fr-FR");
            },
        },
    });
});
