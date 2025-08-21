import { post, get, postJson } from "../modules/http.js";
document.querySelectorAll(".AppPlace").forEach((el) => {
    new Vue({
        el: el,
        data() {
            return {
                error: null,
                result: null,
                isLoading: false,
                isDataLoading: false,
                emplacements: [],
                tables: [],
                formEmplacement: {
                    libelle: "",
                    type: "",
                },
                formTable: [{ numero: "", emplacement_id: "", id: "" }],
                selectedEmplacement: null,
            };
        },

        mounted() {
            this.viewAllEmplacements();
            this.viewAllTables();
            this.whenModalHidden();
        },

        methods: {
            //AFFICHE LA LISTE DES Tables
            viewAllTables() {
                const validPath = location.pathname === "/tables";
                if (validPath) {
                    this.isDataLoading = true;
                    get("/tables.all")
                        .then(({ data, status }) => {
                            this.isDataLoading = false;
                            this.tables = data.tables;
                        })
                        .catch((err) => {
                            this.isDataLoading = false;
                        });
                }
            },

            //AFFICHE LA LISTE DES EMPLACEMENTS
            viewAllEmplacements() {
                this.isDataLoading = true;
                get("/emplacements.all")
                    .then(({ data, status }) => {
                        this.isDataLoading = false;
                        this.emplacements = data.emplacements;
                    })
                    .catch((err) => {
                        this.isDataLoading = false;
                    });
            },

            //CREATE CATEGORIE
            submitEmplacement() {
                this.isLoading = true;
                postJson("/emplacement.create", this.formEmplacement)
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
                            this.viewAllEmplacements();
                            this.resetAll();
                            $("#emplacementModal").modal("hide");
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

            //CREATE PRODUCT
            submitTables() {
                this.isLoading = true;
                postJson("/table.create", { tables: this.formTable })
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
                            this.viewAllTables();
                            this.viewAllEmplacements();
                            this.resetAll();
                            if ($("#tableModal").length) {
                                $("#tableModal").modal("hide");
                            }
                            if ($("#addTablesModal").length) {
                                $("#addTablesModal").modal("hide");
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

            resetAll() {
                this.formEmplacement = {
                    libelle: "",
                    type: "",
                };

                this.formTable = [{ numero: "", emplacement_id: "" }];
            },

            whenModalHidden() {
                const self = this;
                const modals = document.querySelectorAll(".modal");
                modals.forEach((el) => {
                    el.addEventListener("hidden.bs.modal", function (event) {
                        self.resetAll();
                        self.selectedEmplacement = null;
                    });
                });
            },
        },

        computed: {
            allTables() {
                return this.tables;
            },

            allEmplacements() {
                return this.emplacements;
            },

            bedPendingsCount() {
                return this.emplacements
                    .filter((emp) => emp.type === "hôtel")
                    .reduce((count, emp) => {
                        return (
                            count +
                            emp.items.filter((i) => i.statut !== "libre").length
                        );
                    }, 0);
            },

            tablePendingsCount() {
                return this.emplacements
                    .filter((emp) => emp.type !== "hôtel")
                    .reduce((count, emp) => {
                        return (
                            count +
                            emp.items.filter((i) => i.statut !== "libre").length
                        );
                    }, 0);
            },
        },
    });
});
