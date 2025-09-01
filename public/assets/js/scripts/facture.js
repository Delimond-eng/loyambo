import { post, postJson, get } from "../modules/http.js";

const Store = Vue.observable({
    cart: [],
});

document.querySelectorAll(".AppFacture").forEach((el) => {
    new Vue({
        el: el,
        data() {
            return {
                error: null,
                result: null,
                isLoading: false,
                isDataLoading: false,
                factures: [],
                selectedFacture: null,
                store: Store,
                search: "",
            };
        },

        mounted() {
            this.viewAllFactures();
        },

        methods: {
            viewAllFactures() {
                this.isDataLoading = true;
                get("/factures.all")
                    .then(({ data, status }) => {
                        this.isDataLoading = false;
                        this.factures = data.factures;
                    })
                    .catch((err) => {
                        this.isDataLoading = false;
                    });
            },

            printInvoice() {
                // Récupérer uniquement la zone à imprimer
                const content =
                    this.$el.querySelector(".printableArea").innerHTML;

                // Ouvrir une fenêtre d’impression
                const printWindow = window.open("", "", "height=400,width=600");
                printWindow.document.write(`
                    <html>
                        <head>
                            <title>Facture</title>
                            <link rel="stylesheet" href="assets/css/style.css">
                            <link rel="stylesheet" href="assets/css/vendors_css.css">
                        </head>
                        <body>${content}</body>
                    </html>
                `);
                printWindow.document.close();
                printWindow.focus();

                // Lancer l’impression
                printWindow.print();
                printWindow.close();
            },
        },

        computed: {
            cart() {
                return this.store.cart;
            },
            allFactures() {
                return this.factures;
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
