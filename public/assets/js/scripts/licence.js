import { post, postJson, get } from "../modules/http.js";

const Store = Vue.observable({
    counts: {},
});

document.querySelectorAll(".LicenceApp").forEach((el) => {
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
                months: "",
                payIntervall: null,
                paymentHandled: false, // <==== Flag ajouté
            };
        },

        methods: {
            activeApp() {
                if (this.months === "") {
                    $.toast({
                        heading: "Nombre des mois requis",
                        text: "Veuillez renseigner le nombre des mois à activer !",
                        position: "top-right",
                        loaderBg: "#ff4949ff",
                        icon: "error",
                        hideAfter: 3000,
                        stack: 6,
                    });
                    return;
                }
                if (this.isLoading) return;

                this.paymentHandled = false; // <==== reset flag

                this.isLoading = true;
                get(`/licence.payment?months=${this.months}`)
                    .then(({ data }) => {
                        this.isLoading = false;
                        $(".app-active-modal").modal("hide");

                        Swal.fire({
                            title: `${data.amount} $`,
                            text: `A payer pour ${data.months} mois`,
                            icon: "success",
                            showCancelButton: true,
                            confirmButtonText: "Continuer le paiement",
                            cancelButtonText: "Annuler",
                        }).then((res) => {
                            if (res.isConfirmed) {
                                if (data.status === "success") {
                                    window.open(data.url, "_blank");
                                    this.checkPayStatus(data.uuid);
                                } else {
                                    Swal.fire(
                                        "Erreur",
                                        "Impossible de lancer le paiement",
                                        "error"
                                    );
                                }
                            }
                        });
                    })
                    .catch(() => {
                        this.isLoading = false;
                        Swal.fire("Erreur", "Une erreur est survenue", "error");
                    });
            },

            openActiveAppModal() {
                $(".app-active-modal").modal("show");
            },

            checkPayStatus(uuid) {
                // Empêcher de recréer un interval s'il existe déjà
                if (this.payIntervall !== null) return;

                this.payIntervall = setInterval(() => {
                    get(
                        `https://testled.milleniumhorizon.com/check_paie.php?uuid=${uuid}`
                    )
                        .then(({ data }) => {
                            if (
                                data?.flexpay_raw &&
                                data.flexpay_raw.transaction
                            ) {
                                const status =
                                    data.flexpay_raw.transaction.status;
                                // Paiement OK
                                if (status === "0") {
                                    this.paymentHandled = true;
                                    this.stopInterval();
                                    this.confirmPayment(uuid);
                                    return;
                                } else {
                                    Swal.fire({
                                        icon: "error",
                                        title: "Échec du paiement",
                                        text: "Le paiement a échoué. Veuillez réessayer.",
                                    });
                                    this.paymentHandled = true;
                                    this.stopInterval();
                                    setTimeout(() => {
                                        location.reload();
                                    }, 2000);
                                    return;
                                }
                            }
                        })
                        .catch(() => {
                            if (!this.paymentHandled) {
                                this.paymentHandled = true;
                                this.stopInterval();
                                Swal.fire({
                                    icon: "error",
                                    title: "Erreur",
                                    text: "Impossible de vérifier le statut du paiement.",
                                });
                                setTimeout(() => {
                                    location.reload();
                                }, 1500);
                            }
                        });
                }, 3000);
            },

            stopInterval() {
                if (this.payIntervall) {
                    clearInterval(this.payIntervall);
                    this.payIntervall = null;
                }
            },

            confirmPayment(uuid) {
                postJson("/licence.payment.confirm", { uuid })
                    .then(({ data }) => {
                        if (!this.paymentHandled) this.paymentHandled = true;

                        if (data.status === "success") {
                            Swal.fire({
                                icon: "success",
                                title: "Succès",
                                text: "Licence activée avec succès !",
                                confirmButtonText: "Ok",
                            });
                            setTimeout(() => {
                                location.reload();
                            }, 1500);
                        } else {
                            Swal.fire(
                                "Erreur",
                                "Activation impossible",
                                "error"
                            );
                            setTimeout(() => {
                                location.reload();
                            }, 1500);
                        }
                    })
                    .catch(() => {
                        Swal.fire("Erreur", "Erreur de confirmation", "error");
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    });
            },
        },
    });
});
