import { post } from "../modules/http.js";
new Vue({
    el: "#App",
    data() {
        return {
            error: null,
            result: null,
            isLoading: false,
            isDataLoading: false,
        };
    },

    mounted() {
        $("input,select,textarea").not("[type=submit]").jqBootstrapValidation();
    },

    methods: {
        login(event) {
            const formData = new FormData(event.target);
            const url = event.target.getAttribute("action");
            this.isLoading = true;
            post(url, formData)
                .then(({ data, status }) => {
                    console.log(data, status);
                    this.isLoading = false;
                    // Gestion des erreurs
                    if (data.errors !== undefined) {
                        this.startAnimated();
                        this.error = data.errors;
                        $.toast({
                            heading: "Identifiants erronés.",
                            text: "Nom d'utilisateur ou mot de passe non reconnu",
                            position: "top-right",
                            loaderBg: "#ff4949ff",
                            icon: "error",
                            hideAfter: 3000,
                            stack: 6,
                        });
                    }
                    if (data.alerts !== undefined) {
                        this.startAnimated();
                        this.error = data.alerts;
                        $.toast({
                            heading: "Permission non accordé",
                            text: data.alerts,
                            position: "top-right",
                            loaderBg: "#ff4949ff",
                            icon: "error",
                            hideAfter: 3000,
                            stack: 6,
                        });
                    }
                    if (data.user) {
                        console.log(data.user);
                        this.error = null;
                        this.result = data.user;
                        $.toast({
                            heading: "Connexion reussi",
                            text: `Bienvenue ${data.user.name}`,
                            position: "top-right",
                            loaderBg: "#49ff5eff",
                            icon: "success",
                            hideAfter: 3000,
                            stack: 6,
                        });
                        // Rediriger l'utilisateur
                        window.location.href = data.redirect;
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

        register(event) {
            const formData = new FormData(event.target);
            const url = event.target.getAttribute("action");
            this.isLoading = true;
            post(url, formData)
                .then(({ data, status }) => {
                    console.log(data, status);
                    this.isLoading = false;
                    // Gestion des erreurs
                    if (data.errors !== undefined) {
                        this.error = data.errors;
                        $.toast({
                            heading: "Echec de traitement",
                            text: "Une erreur est survenue lors de l'envoi de la requête",
                            position: "top-right",
                            loaderBg: "#ff4949ff",
                            icon: "error",
                            hideAfter: 3000,
                            stack: 6,
                        });
                    }

                    if (data.user) {
                        console.log(data.user);
                        this.error = null;
                        this.result = data.user;
                        $.toast({
                            heading: "Opération reussi",
                            text: `Veuillez vous connecter avec vos identifiants.`,
                            position: "top-right",
                            loaderBg: "#49ff5eff",
                            icon: "success",
                            hideAfter: 3000,
                            stack: 6,
                        });
                        // Rediriger l'utilisateur
                        window.location.href = "/login";
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

        startAnimated() {
            $(".loginBox")
                .addClass("animated tada")
                .one(
                    "webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend",
                    function () {
                        $(this).removeClass("animated tada");
                    }
                );
        },
    },
});
