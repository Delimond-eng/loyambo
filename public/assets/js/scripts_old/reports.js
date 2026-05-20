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
                get("/reports.all")
                    .then(({ data, status }) => {
                        this.isDataLoading = false;
                        this.reports = data.reports;
                    })
                    .catch((err) => {
                        this.isDataLoading = false;
                    });
            },

            //SHOW REPORT FACTURES OF SALE DAY
            showReportDetails(data) {
                this.selectedSell = data;
                const s = data;
                this.load_id = s.user.id;
                get(`/report.detail?id=${s.user.id}`).then(
                    ({ data, status }) => {
                        this.load_id = "";
                        this.sellFactures = data.factures;
                        $(".modal-sell-detail").modal("show");
                    }
                );
            },
        },

        computed: {
            allReports() {
                return this.reports;
            },

            allPayment() {
                return (payments) => {
                    return payments.reduce(
                        (sum, item) => sum + parseFloat(item.amount),
                        0
                    );
                };
            },
        },
    });
});
