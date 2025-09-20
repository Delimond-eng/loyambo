import { post, postJson, get } from "../modules/http.js";

const Store = Vue.observable({
    counts: {},
});

document.querySelectorAll(".AppDashboard").forEach((el) => {
    new Vue({
        el: el,
        data() {
            return {
                error: null,
                result: null,
                isLoading: false,
                isDataLoading: false,
                store: Store,
                search: "",
            };
        },

        mounted() {
            /* setInterval(() => {
                this.loadCounter();
            }, 3000); */
            this.loadCounter();
        },

        methods: {
            loadCounter() {
                get("/counts.all")
                    .then(({ data, status }) => {
                        console.log(data);
                        this.store.counts = data.counts;
                    })
                    .catch((err) => {
                        console.log(err);
                    });
            },
        },

        computed: {
            counts() {
                return this.store.counts;
            },

            formateDate() {
                return (date) =>
                    moment(date, "YYYY-MM-DD")
                        .locale("fr")
                        .format("DD MMMM YYYY");
                // ex: "14 avril 2021"
            },

            formateTime() {
                return (date) => moment(date).locale("fr").format("hh:mm");
                // ex: "03:13 AM"
            },
        },
    });
});
