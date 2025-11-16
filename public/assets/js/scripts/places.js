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
                chambres: [],
                formEmplacement: {
                    libelle: "",
                    type: "",
                },
                formTable: {
                    numero: "",
                    emplacement_id: "",
                    id: "",
                    prix: "",
                    prix_devise: "CDF",
                    capacite: "",
                    type: "simple",
                },
                selectedEmplacement: null,
                operation: null,
                isHotel: false,
            };
        },

        mounted() {
            const modal = document.getElementById("tableModal");
            if (modal) {
                modal.addEventListener("hidden.bs.modal", () => {
                    // Reset form ou autres actions
                    this.formTable = {
                        numero: "",
                        emplacement_id: "",
                        id: "",
                        prix: "",
                        prix_devise: "CDF",
                        capacite: "",
                    };
                    this.isHotel = false;
                });
            }
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
                            console.log(JSON.stringify(data.chambres));

                            this.tables = data.tables;
                            this.chambres = data.chambres;
                        })
                        .catch((err) => {
                            this.isDataLoading = false;
                        });
                }
            },

            setOperation(op) {
                this.operation = op;
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
                console.log(JSON.stringify(this.formTable));

                postJson("/table.create", this.formTable)
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

                this.formTable = {
                    numero: "",
                    emplacement_id: "",
                    id: "",
                    prix: "",
                    prix_devise: "CDF",
                };
            },

            supprimerEmplacement(id) {
                if (
                    confirm("Voulez-vous vraiment supprimer cet emplacement ?")
                ) {
                    this.isLoading = true;
                    postJson(`/emplacement/delete/${id}`)
                        .then(({ data, status }) => {
                            this.isLoading = false;
                            if (data.status === "success") {
                                $.toast({
                                    heading: "Suppression réussie",
                                    text: data.message,
                                    position: "top-right",
                                    loaderBg: "#49ff5eff",
                                    icon: "success",
                                    hideAfter: 3000,
                                    stack: 6,
                                });
                                this.viewAllEmplacements(); // Rafraîchir la liste
                            } else {
                                $.toast({
                                    heading: "Erreur",
                                    text:
                                        data.message || "Échec de suppression",
                                    position: "top-right",
                                    loaderBg: "#ff4949ff",
                                    icon: "error",
                                    hideAfter: 3000,
                                    stack: 6,
                                });
                            }
                        })
                        .catch((err) => {
                            this.isLoading = false;
                            $.toast({
                                heading: "Erreur",
                                text: "Une erreur s'est produite. Réessayez plus tard.",
                                position: "top-right",
                                loaderBg: "#ff4949ff",
                                icon: "error",
                                hideAfter: 3000,
                                stack: 6,
                            });
                        });
                }
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

        watch: {
            "formTable.emplacement_id"(val) {
                var emp = this.allEmplacements.find((e) => e.id === val);
                this.isHotel = emp.type === "hôtel";
            },
        },
        computed: {
            allTables() {
                return this.tables;
            },

            allChambres() {
                return this.chambres;
            },

            allEmplacements() {
                return this.emplacements;
            },

            bedPendingsCount() {
                return this.emplacements.reduce((count, emp) => {
                    return (
                        count +
                        emp.chambres.filter((i) => i.statut !== "libre").length
                    );
                }, 0);
            },

            tablePendingsCount() {
                return this.emplacements.reduce((count, emp) => {
                    return (
                        count +
                        emp.tables.filter((i) => i.statut !== "libre").length
                    );
                }, 0);
            },
        },
    });
});
