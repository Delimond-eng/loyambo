import { identity } from "lodash";
import { post, postJson, get } from "../modules/http.js";
document.querySelectorAll(".AppHotel").forEach((el) => {
    new Vue({
        el: el,
        data() {
            return {
                error: null,
                result: null,
                isLoading: false,
                isDataLoading: false,
                chambres: [],
                selectedBed: null,
                modes: [
                    { value: "cash", label: "CASH", icon: "fa fa-money" },
                    {
                        value: "mobile",
                        label: "MOBILE MONEY",
                        icon: "fa fa-mobile-phone",
                    },
                    {
                        value: "card",
                        label: "BANQUE/CARTE",
                        icon: "fa fa-credit-card",
                    },
                ],
                selectedMode: null,
                selectedModeRef: "",
                operation: "",
                form: {
                    client: {
                        nom: "",
                        telephone: "",
                        email: "",
                        identite: "",
                        identite_type: "",
                    },
                    date_debut: "",
                    date_fin: "",
                },
            };
        },

        mounted() {
            this.viewAllChambres();
        },

        methods: {
            reserverChambreView(bed) {
                this.selectedBed = bed;
                $(".modal-reservation").modal("show");
            },
            viewAllChambres() {
                let url = "/chambres.all";
                this.isDataLoading = true;
                get(url)
                    .then(({ data, status }) => {
                        this.isDataLoading = false;
                        this.chambres = data.chambres;
                    })
                    .catch((err) => {
                        this.isDataLoading = false;
                    });
            },

            //===========Reservation de la chambre=============//
            makeReservation() {
                this.form.chambre_id = this.selectedBed.id;
                const form = this.form;
                this.isLoading = true;
                postJson("/reservation.action", form)
                    .then(({ data, status }) => {
                        this.isLoading = false;
                        // Gestion des erreurs
                        if (data.errors !== undefined) {
                            this.error = data.errors;
                            $.toast({
                                heading: "Echec de traitement",
                                text: `${data.errors}`,
                                position: "top-right",
                                loaderBg: "#ff4949ff",
                                icon: "error",
                                hideAfter: 3000,
                                stack: 6,
                            });
                        }
                        if (data.status === "success") {
                            this.error = null;
                            this.result = data.result;
                            $.toast({
                                heading: "Opération effectuée",
                                text: data.message,
                                position: "top-right",
                                loaderBg: "#49ff5eff",
                                icon: "success",
                                hideAfter: 3000,
                                stack: 6,
                            });
                            this.viewAllChambres();
                        }
                    })
                    .catch((err) => {
                        this.isLoading = false;
                        $.toast({
                            heading: "Echec de traitement",
                            text: "Veuillez réessayer plutard !",
                            position: "top-right",
                            loaderBg: "#ff4949ff",
                            icon: "error",
                            hideAfter: 3000,
                            stack: 6,
                        });
                    });
            },
        },

        computed: {
            getTableOperationColorClass() {
                let borderClass = "border-primary";
                switch (this.operation) {
                    case "transfert":
                        borderClass = "border-info";
                        break;
                    case "combiner":
                        borderClass = "border-success";
                        break;
                    case "reservation":
                        borderClass = "border-info";
                        break;
                    default:
                        borderClass = "border-primary";
                        break;
                }
                return borderClass;
            },
            allChambres() {
                return this.chambres;
            },
        },
    });
});
