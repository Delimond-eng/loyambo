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
        },

        computed: {
            allReports() {
                return this.reports;
            },
        },
    });
});
