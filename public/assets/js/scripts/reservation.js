import { post, get, postJson } from "../modules/http.js";
document.querySelectorAll(".AppReservation").forEach((el) => {
    new Vue({
        el: el,
        data() {
            return {
                error: null,
                result: null,
                isLoading: false,
                isDataLoading: false,
                chambres: [],
                search: "",
                selectedChambre: null,
            };
        },

        mounted() {
            this.viewAllChambres();
            this.whenModalHidden();
        },

        methods: {
            //AFFICHE LA LISTE DES Tables
            viewAllChambres() {
                this.isDataLoading = true;
                get("/chambres.all")
                    .then(({ data, status }) => {
                        this.isDataLoading = false;
                        this.chambres = data.chambres;
                    })
                    .catch((err) => {
                        this.isDataLoading = false;
                    });
            },

            triggerOpenCreateReservationModal(chambre) {
                this.selectedChambre = chambre;
                $(".modal-reservation-create").modal("show");
            },

            whenModalHidden() {
                const self = this;
                const modals = document.querySelectorAll(".modal");
                modals.forEach((el) => {
                    el.addEventListener("hidden.bs.modal", function (event) {});
                });
            },
        },

        computed: {
            allChambres() {
                if (this.search) {
                    return this.chambres.filter((el) =>
                        el.numero.includes(this.search.toString())
                    );
                }
                return this.chambres;
            },
        },
    });
});
