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
                serveurs: [],
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
            this.viewAllServeurs();
        },

        methods: {
            viewAllServeurs() {
                get("/serveurs.all")
                    .then(({ data, status }) => {
                        this.isDataLoading = false;
                        this.serveurs = data.users;
                    })
                    .catch((err) => {
                        this.isDataLoading = false;
                    });
            },

            goToUserOrderSession(data) {
                const chambre = this.selectedBed;
                localStorage.removeItem("table");
                localStorage.removeItem("chambre");
                localStorage.setItem("chambre", JSON.stringify(chambre));
                localStorage.setItem("user", JSON.stringify(data));
                location.href = "/orders.interface";
            },

            reserverChambreView(bed) {
                this.selectedBed = bed;
                if (bed.statut !== "libre") {
                    $(".modal-chambre-infos").modal("show");
                } else {
                    $(".modal-reservation").modal("show");
                }
            },

            libererEtOccuperChambre() {
                const chambre = this.selectedBed;
                postJson(`/chambre.status`, { chambre_id: chambre.id })
                    .then(({ data, status }) => {
                        $(".modal-chambre-infos").modal("hide");
                        $.toast({
                            heading: "Opération effectuée",
                            text: data.message,
                            position: "top-right",
                            loaderBg: "#52ff49ff",
                            icon: "success",
                            hideAfter: 3000,
                            stack: 6,
                        });
                        if (data.status === "success") {
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

            addNewReservation() {
                $(".modal-chambre-infos").modal("hide");
                $(".modal-reservation").modal("show");
            },

            addCommande() {
                $(".modal-serveurs-list").modal("show");
            },

            editReservation(data) {
                this.form = {
                    client: {
                        nom: data.client.nom,
                        telephone: data.client.telephone,
                        email: data.client.email,
                        identite: data.client.identite,
                        identite_type: data.client.identite_type,
                    },
                    date_debut: data.date_debut,
                    date_fin: data.date_fin,
                    id: data.id,
                };
                $(".modal-chambre-infos").modal("hide");
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

            allServeurs() {
                return this.serveurs;
            },

            allChambres() {
                return this.chambres;
            },
            formateDate() {
                return (date) =>
                    moment(date).locale("fr").format("DD MMMM YYYY");
                // ex: "14 avril 2021"
            },

            joursRestants() {
                return (dateDebut, dateFin) => {
                    const debut = moment(dateDebut, "YYYY-MM-DD");
                    const fin = moment(dateFin, "YYYY-MM-DD");
                    const aujourdHui = moment().startOf("day");
                    // Si la fin est passée → 0
                    if (fin.isBefore(aujourdHui)) {
                        return 0;
                    }
                    // Si on est avant le début → on compte tout l'intervalle
                    const pointDeDepart = aujourdHui.isBefore(debut)
                        ? debut
                        : aujourdHui;
                    const diff = fin.diff(pointDeDepart, "days") + 1; // +1 si tu veux inclure le jour courant
                    return diff > 0 ? "+" + diff : 0;
                };
            },
        },
    });
});
