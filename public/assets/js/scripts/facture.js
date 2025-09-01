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

            printInvoice(facture, place) {
                const content = `
            <div class="ticket">
                <h2>${place.libelle}</h2>
                <p><strong>Facture N°:</strong> ${facture.numero_facture}</p>
                <p><strong>Date:</strong> ${this.formateDate(
                    facture.date_facture
                )}</p>
                <hr/>
                <table>
                    <thead>
                        <tr>
                            <th>Désignation</th>
                            <th>Qté</th>
                            <th class="right">PU</th>
                            <th class="right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${facture.details
                            .map(
                                (d) => `
                            <tr>
                                <td>${d.produit.libelle}</td>
                                <td>${d.quantite}</td>
                                <td class="right">${d.prix_unitaire}</td>
                                <td class="right">${d.total_ligne}</td>
                            </tr>
                        `
                            )
                            .join("")}
                    </tbody>
                </table>
                <hr/>
                <p class="right">Total HT: ${facture.total_ht}</p>
                <p class="right">Remise: ${facture.remise}%</p>
                <p class="right">TVA: 0</p>
                <h3 class="right">TOTAL TTC: ${facture.total_ttc}</h3>
                <hr/>
                <p class="center">Merci pour votre visite !</p>
            </div>
        `;

                const style = `
            <style>
                *, *::before, *::after {
                    box-sizing: border-box;
                }
                body {
                    font-family: 'Courier New', monospace;
                    font-size: 14px;
                    margin: 0;
                    padding: 0;
                    -webkit-print-color-adjust: exact;
                    color-adjust: exact;
                }
                .ticket {
                    width: 240px;
                    max-width: 100%;
                    margin: 0 auto;
                    padding: 10px;
                }
                h2 {
                    text-align: center;
                    margin: 0 0 10px;
                    font-size: 16px;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-bottom: 5px;
                }
                th, td {
                    padding: 2px 0;
                    font-size: 12px;
                }
                th {
                    border-bottom: 1px dashed #000;
                    text-align: left;
                }
                td {
                    vertical-align: top;
                }
                .right {
                    text-align: right;
                }
                .center {
                    text-align: center;
                }
                hr {
                    border: none;
                    border-top: 1px dashed #000;
                    margin: 5px 0;
                }
                @media print {
                    body {
                        margin: 0;
                        padding: 0;
                    }
                    .ticket {
                        width: 100%;
                        max-width: 240px;
                        margin: 0 auto;
                    }
                    table, th, td {
                        border: none;
                    }
                }
            </style>
        `;

                const printWindow = window.open("", "", "height=600,width=400");
                printWindow.document.write(`
            <html>
                <head>
                    <title>Facture ${facture.numero_facture}</title>
                    ${style}
                </head>
                <body>${content}</body>
            </html>
        `);
                printWindow.document.close();
                printWindow.focus();
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
