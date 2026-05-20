import { post, get, postJson } from "../modules/http.js";
import Paginator from "../components/pagination.js";
document.querySelectorAll(".AppReservation").forEach((el) => {
    new Vue({
        el: el,
        components: {
            Paginator,
        },
        data() {
            return {
                error: null,
                result: null,
                isLoading: false,
                isDataLoading: false,
                step: 1,
                chambres: [],
                reservations: [],
                pagination: {
                    current_page: 1,
                    last_page: 1,
                    total: 0,
                    per_page: 10,
                },
                form: {
                    chambre_id: "",
                    date_debut: "",
                    date_fin: "",
                    reservation_id: "",
                    paiement: {
                        mode: "",
                        amount: "",
                        devise: "USD",
                        mode_ref: "",
                    },
                    client: {
                        nom: "",
                        telephone: "",
                        email: "",
                        identite: "",
                        identite_type: "",
                    },
                },
                search: "",
                selectedChambre: null,
                selectedMode: null,
                filter: "",
                modes: {
                    cash: { icon: "fa fa-money", label: "Espèces" },
                    mobile: { icon: "fa fa-mobile", label: "Mobile" },
                    cheque: { icon: "fa fa-file-text", label: "Chèque" },
                    virement: { icon: "fa fa-university", label: "Virement" },
                    card: { icon: "fa fa-credit-card", label: "Carte" },
                },
                formPay: {
                    mode: "",
                    amount: "",
                    devise: "",
                    mode_ref: "",
                    reservation_id: "",
                },
                cancel_id: "",
                bed_id: "",
            };
        },
        mounted() {
            this.viewAllReservations();
            this.viewAllChambres();
            this.whenModalHidden();
        },

        methods: {
            //AFFICHE LA LISTE DES Tables
            viewAllChambres() {
                const allowData = true;

                if (allowData) {
                    this.isDataLoading = true;
                    get("/chambres.all")
                        .then(({ data, status }) => {
                            this.isDataLoading = false;
                            this.chambres = data.chambres;

                            if ($("#filter").length) {
                                let val = $("#filter").val();
                                if (val === "reservee") {
                                    this.filter = "réservée";
                                } else if (val === "occupee") {
                                    this.filter = "occupée";
                                } else if (val === "all") {
                                    this.filter = "";
                                } else {
                                    this.filter = val;
                                }
                            }
                        })
                        .catch((err) => {
                            this.isDataLoading = false;
                        });
                }
            },

            viewAllReservations() {
                const allowData = location.pathname === "/reservations";
                if (allowData) {
                    this.isDataLoading = true;
                    get(
                        `/reservations.all?page=${this.pagination.current_page}&per_page=${this.pagination.per_page}`
                    )
                        .then(({ data, status }) => {
                            console.log(data);
                            this.isDataLoading = false;
                            this.reservations = data.reservations.data;
                            this.pagination = {
                                current_page: data.reservations.current_page,
                                last_page: data.reservations.last_page,
                                total: data.reservations.total,
                                per_page: data.reservations.per_page,
                            };
                        })
                        .catch((err) => {
                            this.isDataLoading = false;
                        });
                }
            },

            changePage(page) {
                this.pagination.current_page = page;
                this.viewAllReservations();
            },

            onPerPageChange(perPage) {
                this.pagination.per_page = perPage;
                this.pagination.current_page = 1;
                this.viewAllReservations();
            },

            viewReservation(data) {
                let url = `/reservation.details/${data.facture.id}`;
                document.getElementById("detailIframe").src = url;
                setTimeout(() => {
                    $("#reservationDetailsModal").modal("show");
                }, 500);
            },

            goToStep(num) {
                if (num === 2) {
                    if (
                        this.form.client.identite === "" &&
                        this.form.client.identite_type === "" &&
                        this.form.client.nom === "" &&
                        this.form.client.telephone === "" &&
                        this.form.date_debut === "" &&
                        this.form.date_fin === ""
                    ) {
                        Swal.fire({
                            icon: "warning",
                            title: "Infos incomplète !",
                            text: "Vous devez renseigner tous les champs pour passer à l'étape suivante.",
                            timer: 3000,
                            showConfirmButton: !1,
                        });
                        return;
                    }
                }
                if (num === 2) {
                    let days = this.getDays(
                        this.form.date_debut,
                        this.form.date_fin
                    );
                    if (!isNaN(days)) {
                        if (this.selectedChambre) {
                            let amount =
                                days * parseFloat(this.selectedChambre.prix);
                            this.form.paiement.amount = amount;
                            this.form.paiement.devise =
                                this.selectedChambre.prix_devise;
                        }
                    }
                }
                this.step = num;
            },

            //CREATE RESERVATIN
            createReservation(e) {
                if (this.step === 1) {
                    this.goToStep(2);
                    return;
                }
                const self = this;
                Swal.fire({
                    icon: "question",
                    title: `Confirmez la réservation ${
                        self.form.paiement.mode !== "" ? "avec" : "sans"
                    } paiement !`,
                    text: `Voulez-vous poursuivre la réservation de la chambre ${
                        self.form.paiement.mode !== "" ? "avec" : "sans"
                    }  paiement ??`,
                    confirmButtonText: "Oui,Confirmer",
                    cancelButtonText: "Non, Annuler",
                    showCancelButton: 1,
                    showConfirmButton: 1,
                }).then((res) => {
                    if (res.isConfirmed) {
                        self.isLoading = true;
                        postJson("/reservation.create", self.form)
                            .then(({ data, status }) => {
                                self.isLoading = false;
                                // Gestion des erreurs
                                if (data.errors !== undefined) {
                                    self.error = data.errors;
                                    $.toast({
                                        heading: "Echec de traitement",
                                        text: `${data.errors}`,
                                        position: "top-right",
                                        loaderBg: "#ff4949ff",
                                        icon: "error",
                                        hideAfter: 3000,
                                        stack: 6,
                                    });
                                    return;
                                }
                                if (data.reserved !== undefined) {
                                    self.error = data.reserved;
                                    new Swal({
                                        icon: "warning",
                                        title: "Chambre déjà réservée !",
                                        text: data.reserved,
                                        showConfirmButton: false,
                                        showCancelButton: false,
                                        timer: 3000,
                                    });
                                    return;
                                }
                                if (data.status === "success") {
                                    console.log("success");
                                    self.error = null;
                                    self.result = data.result;

                                    new Swal({
                                        icon: "success",
                                        title: "Réservation effectuéé !",
                                        text: `Nouvelle réservation effectuée !`,
                                        showConfirmButton: false,
                                        showCancelButton: false,
                                        timer: 3000,
                                    });
                                    if (data.facture_url !== undefined) {
                                        document.getElementById(
                                            "factureIframe"
                                        ).src = data.facture_url;
                                        $(".modal-reservation-create").modal(
                                            "hide"
                                        );
                                        setTimeout(() => {
                                            $("#factureModal").modal("show");
                                        }, 500);
                                    } else {
                                        $(".modal-reservation-create").modal(
                                            "hide"
                                        );
                                    }
                                }
                            })
                            .catch((err) => {
                                self.isLoading = false;
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
                    }
                });
            },

            updateReservation(e) {
                if (this.step === 1) {
                    this.goToStep(2);
                    return;
                }
                const self = this;
                Swal.fire({
                    icon: "question",
                    title: `Confirmez la modification de la réservation ${
                        self.form.paiement.mode !== "" ? "avec" : "sans"
                    } paiement !`,
                    text: `Voulez-vous modifier la réservation ${
                        self.form.paiement.mode !== "" ? "avec" : "sans"
                    }  paiement ??`,
                    confirmButtonText: "Oui,Confirmer",
                    cancelButtonText: "Non, Annuler",
                    showCancelButton: 1,
                    showConfirmButton: 1,
                }).then((res) => {
                    if (res.isConfirmed) {
                        self.isLoading = true;
                        postJson("/reservation.update", self.form)
                            .then(({ data, status }) => {
                                self.isLoading = false;
                                // Gestion des erreurs
                                if (data.errors !== undefined) {
                                    self.error = data.errors;
                                    $.toast({
                                        heading: "Echec de traitement",
                                        text: `${data.errors}`,
                                        position: "top-right",
                                        loaderBg: "#ff4949ff",
                                        icon: "error",
                                        hideAfter: 3000,
                                        stack: 6,
                                    });
                                    return;
                                }
                                if (data.reserved !== undefined) {
                                    self.error = data.reserved;
                                    new Swal({
                                        icon: "warning",
                                        title: "Chambre déjà réservée !",
                                        text: data.reserved,
                                        showConfirmButton: false,
                                        showCancelButton: false,
                                        timer: 3000,
                                    });
                                    return;
                                }
                                if (data.status === "success") {
                                    console.log("success");
                                    self.error = null;
                                    self.result = data.result;

                                    new Swal({
                                        icon: "success",
                                        title: "Réservation effectuéé !",
                                        text: `Nouvelle réservation effectuée !`,
                                        showConfirmButton: false,
                                        showCancelButton: false,
                                        timer: 3000,
                                    });
                                    $(".modal-reservation-update").modal(
                                        "hide"
                                    );
                                    self.viewAllReservations();
                                }
                            })
                            .catch((err) => {
                                self.isLoading = false;
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
                    }
                });
            },

            cancelReservation(id) {
                const self = this;
                Swal.fire({
                    icon: "warning",
                    title: `Confirmez l'annulation de la réservation`,
                    text: `Action irréversible, voulez-vous vraiment annuler cette réservation ?`,
                    confirmButtonText: "Oui,Confirmer",
                    cancelButtonText: "Non, Annuler",
                    showCancelButton: 1,
                    showConfirmButton: 1,
                }).then((res) => {
                    if (res.isConfirmed) {
                        self.cancel_id = id;
                        get(`/reservation.cancel/${id}`)
                            .then(({ data, status }) => {
                                self.cancel_id = "";
                                // Gestion des erreurs
                                if (data.errors !== undefined) {
                                    self.error = data.errors;
                                    $.toast({
                                        heading: "Echec de traitement",
                                        text: `${data.errors}`,
                                        position: "top-right",
                                        loaderBg: "#ff4949ff",
                                        icon: "error",
                                        hideAfter: 3000,
                                        stack: 6,
                                    });
                                    return;
                                }

                                if (data.status === "success") {
                                    console.log("success");
                                    self.error = null;
                                    new Swal({
                                        icon: "success",
                                        title: "Annulation effectuéé !",
                                        text: data.message,
                                        showConfirmButton: false,
                                        showCancelButton: false,
                                        timer: 3000,
                                    });

                                    self.viewAllReservations();
                                }
                            })
                            .catch((err) => {
                                self.cancel_id = "";
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
                    }
                });
            },

            occuperChambre(chambre) {
                const self = this;
                let title =
                    chambre.statut === "occupée"
                        ? "Libération de la chambre"
                        : "Occupation de la chambre";

                let msg =
                    chambre.statut === "occupée"
                        ? `Êtes-vous sûr de vouloir libérer la chambre N° ${chambre.numero} ?`
                        : `Êtes-vous sûr de vouloir occuper la chambre N° ${chambre.numero} ?`;
                Swal.fire({
                    icon: "question",
                    title: title,
                    text: msg,
                    confirmButtonText: "Oui,Occuper",
                    cancelButtonText: "Non, Annuler",
                    showCancelButton: 1,
                    showConfirmButton: 1,
                }).then((res) => {
                    if (res.isConfirmed) {
                        self.isLoading = true;
                        self.bed_id = chambre.id;
                        get(`/chambre.occuper/${chambre.id}`)
                            .then(({ data, status }) => {
                                self.isLoading = false;
                                self.bed_id = "";
                                $(".modal-reservation-action").modal("hide");
                                // Gestion des erreurs
                                if (data.errors !== undefined) {
                                    self.error = data.errors;
                                    $.toast({
                                        heading: "Echec de traitement",
                                        text: `${data.errors}`,
                                        position: "top-right",
                                        loaderBg: "#ff4949ff",
                                        icon: "error",
                                        hideAfter: 3000,
                                        stack: 6,
                                    });
                                    return;
                                }

                                if (data.failed !== undefined) {
                                    self.error = data.failed;
                                    new Swal({
                                        icon: "warning",
                                        text: data.failed,
                                        showConfirmButton: false,
                                        showCancelButton: false,
                                        timer: 3000,
                                    });
                                    return;
                                }

                                if (data.status === "success") {
                                    console.log("success");
                                    self.error = null;
                                    new Swal({
                                        icon: "success",
                                        title: "Occupation chambre effectuée !",
                                        text: data.message,
                                        showConfirmButton: false,
                                        showCancelButton: false,
                                        timer: 3000,
                                    });

                                    self.viewAllChambres();
                                    self.viewAllReservations();
                                }
                            })
                            .catch((err) => {
                                self.isLoading = false;
                                self.bed_id = "";
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
                    }
                });
            },

            //Allow to extend existing reservation
            extendReservation(e) {
                if (this.step === 1) {
                    this.goToStep(2);
                    return;
                }
                this.isLoading = true;
                let form = {
                    paiement: this.form.paiement,
                    reservation_id: this.form.reservation_id,
                    new_date_fin: this.form.date_fin,
                };
                postJson("/reservation.extend", form)
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
                            return;
                        }
                        if (data.failed !== undefined) {
                            this.error = data.failed;
                            new Swal({
                                icon: "warning",
                                title: "Echec de traitement!",
                                text: data.failed,
                                showConfirmButton: false,
                                showCancelButton: false,
                                timer: 5000,
                            });
                            return;
                        }
                        if (data.status === "success") {
                            console.log("success");
                            this.error = null;
                            new Swal({
                                icon: "success",
                                title: "Réservation effectuéé !",
                                text: `rolongation effectuée avec succès !`,
                                showConfirmButton: false,
                                showCancelButton: false,
                                timer: 3000,
                            });
                            $(".modal-reservation-create").modal("hide");
                            this.viewAllReservations();
                            if (data.facture_url !== undefined) {
                                document.getElementById("factureIframe").src =
                                    data.facture_url;
                                setTimeout(() => {
                                    $("#factureModal").modal("show");
                                }, 500);
                            }
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

            //CREATE PAYMENT
            payReservation(e) {
                this.formPay.mode = this.selectedMode;
                this.isLoading = true;
                postJson("/reservation.pay", this.formPay)
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
                            const res = data.result;
                            this.viewAllReservations();
                            new Swal({
                                icon: "success",
                                title: "Paiement effectué !",
                                text: `Paiement effectué avec succès`,
                                showConfirmButton: false,
                                showCancelButton: false,
                                timer: 3000,
                            });
                            $("#paymentModal").modal("hide");
                            if (data.facture_url !== undefined) {
                                document.getElementById("factureIframe").src =
                                    data.facture_url;
                                setTimeout(() => {
                                    $("#factureModal").modal("show");
                                }, 500);
                            }
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

            triggerOpenCreateReservationModal(chambre) {
                $(".modal-reservation-action").modal("hide");
                this.selectedChambre = chambre;
                this.form.chambre_id = chambre.id;
                $(".modal-reservation-create").modal("show");
            },

            triggerOpenUpdateReservationModal(data) {
                const reservation = JSON.parse(JSON.stringify(data));
                this.selectedChambre = reservation.chambre;
                this.form = {
                    chambre_id: reservation.chambre_id,
                    date_debut: reservation.date_debut,
                    date_fin: reservation.date_fin,
                    reservation_id: reservation.id,
                    paiement: {
                        mode: "",
                        amount: "",
                        devise: "USD",
                        mode_ref: "",
                    },
                    client: {
                        nom: reservation.client.nom,
                        telephone: reservation.client.telephone,
                        email: reservation.client.email,
                        identite: reservation.client.identite,
                        identite_type: reservation.client.identite_type,
                    },
                };
                $(".modal-reservation-update").modal("show");
            },

            triggerExtendDayModal(data) {
                this.selectedChambre = data.chambre;
                this.form.chambre_id = data.chambre.id;
                this.form.client = data.client;
                this.form.reservation_id = data.id;
                this.form.date_debut = this.parseDate(data.date_fin);
                $(".modal-reservation-create").modal("show");
            },

            triggerActionReservationModal(chambre) {
                this.selectedChambre = chambre;
                this.form.chambre_id = chambre.id;
                $(".modal-reservation-action").modal("show");
            },

            triggerOpenPaymentModal(data) {
                console.log(JSON.stringify(this.selectedMode));
                const days = this.getDays(data.date_debut, data.date_fin);
                this.formPay.amount =
                    parseInt(days) * parseFloat(data.chambre.prix);
                this.formPay.devise = data.chambre.prix_devise;
                this.formPay.reservation_id = data.id;
                $("#paymentModal").modal("show");
            },

            whenModalHidden() {
                const self = this;
                const modals = document.querySelectorAll(".modal");
                modals.forEach((el) => {
                    el.addEventListener("hidden.bs.modal", function (event) {
                        self.cleanField();
                    });
                });
            },

            cleanField() {
                this.form = {
                    chambre_id: "",
                    date_debut: "",
                    date_fin: "",
                    paiement: {
                        mode: "",
                        amount: "",
                        devise: "USD",
                        mode_ref: "",
                    },
                    client: {
                        nom: "",
                        telephone: "",
                        email: "",
                        identite: "",
                        identite_type: "",
                    },
                };
                this.step = 1;
            },
        },

        computed: {
            allChambres() {
                if (this.search) {
                    return this.chambres.filter((el) =>
                        el.numero.includes(this.search.toString())
                    );
                } else if (this.filter) {
                    return this.chambres.filter((el) =>
                        el.statut
                            .toLowerCase()
                            .includes(this.filter.toLowerCase())
                    );
                }
                return this.chambres;
            },

            allReservations() {
                if (this.filter) {
                    return this.reservations.filter((el) =>
                        el.statut
                            .toLowerCase()
                            .includes(this.filter.toLowerCase())
                    );
                }
                return this.reservations;
            },

            getDays() {
                return (start, end) => {
                    let startDate = moment(start, "YYYY-MM-DD");
                    let endDate = moment(end, "YYYY-MM-DD");
                    return endDate.diff(startDate, "days");
                };
                // ex: "03:13 AM"
            },

            formateDate() {
                return (date) =>
                    moment(date, "YYYY-MM-DD HH:mm")
                        .locale("fr")
                        .format("DD MMM YYYY");
                // ex: "14 avril 2021"
            },

            parseDate() {
                return (date) => {
                    const inputDate = moment(date, "YYYY-MM-DD");
                    const today = moment().startOf("day");

                    // Si la date est invalide → retourne aujourd'hui
                    if (!inputDate.isValid()) {
                        return today.format("YYYY-MM-DD");
                    }
                    // Si date < aujourd'hui → retourne aujourd'hui
                    if (inputDate.isBefore(today)) {
                        return today.format("YYYY-MM-DD");
                    }

                    // Sinon, utiliser la date donnée
                    return inputDate.format("YYYY-MM-DD");
                };
            },

            formateTime() {
                return (date) =>
                    moment(date, "YYYY-MM-DD HH:mm")
                        .locale("fr")
                        .format("DD MMMM YYYY hh:mm");
                // ex: "03:13 AM"
            },

            isDateAvailable() {
                return (startedAt, endedAt) => {
                    const now = moment();
                    const start = moment(startedAt, "YYYY-MM-DD");
                    const end = moment(endedAt, "YYYY-MM-DD");
                    return start.isSameOrBefore(now) && end.isSameOrAfter(now);
                };
            },
        },
    });
});
