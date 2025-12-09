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
                message: "",
                status: "",
                payIntervall: null,
                paymentHandled: false, // <==== Flag ajouté,
            };
        },

        mounted() {
            this.checkStatus();
        },

        methods: {
            sendRequest() {
                if (this.code === "") {
                    $.toast({
                        heading: "Code de société requis !",
                        text: "Veuillez entrer le code de la société provenant de l'application comptable !",
                        position: "top-right",
                        loaderBg: "#ff4949ff",
                        icon: "error",
                        hideAfter: 3000,
                        stack: 10,
                    });
                    return;
                }
                this.isLoading = true;
                postJson("/link.request", { code_societe: this.code })
                    .then(({ data }) => {
                        this.isLoading = false;
                        console.log(JSON.stringify(data));
                        /* if (data.status === "success") {
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
                        } */
                    })
                    .catch((e) => {
                        this.isLoading = false;
                        console.error("Erreur de d'envoie => ", e);
                    });
            },

            openConfigModal() {
                $(".config-modal").modal("show");
            },

            checkStatus() {
                // Empêcher de recréer un interval s'il existe déjà
                get(`/link.check`)
                    .then(({ data }) => {
                        if (data.success) {
                            this.message = data.data.message;
                            this.status = data.data.statut;
                        }
                    })
                    .catch((e) => {
                        console.error("Erreur de traitement => ", e);
                    });
            },

            stopInterval() {
                if (this.payIntervall) {
                    clearInterval(this.payIntervall);
                    this.payIntervall = null;
                }
            },
        },

        computed: {
            getMessage() {
                return this.message;
            },
            getStatus() {
                return this.status;
            },
        },
    });
});
