import { post, postJson, get } from "../modules/http.js";

const Store = Vue.observable({
    counts: {},
});

document.querySelectorAll(".ConfigApp").forEach((el) => {
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
                code: "",
                payIntervall: null,
                paymentHandled: false, // <==== Flag ajouté
            };
        },

        methods: {
            activeApp() {
                if (this.code === "") {
                    $.toast({
                        heading: "Code de société requis !",
                        text: "Veuillez entrer le code de la société provenant de l'application comptable !",
                        position: "top-right",
                        loaderBg: "#ff4949ff",
                        icon: "error",
                        hideAfter: 3000,
                        stack: 6,
                    });
                    return;
                }
                if (this.isLoading) return;
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
