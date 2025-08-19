import { post, get, postJson } from "../modules/http.js";
new Vue({
    el: "#AppAdmin",
    data() {
        return {
            error: null,
            result: null,
            isLoading: false,
            isDataLoading: false,
            users: [],
            selectedUserPermissions: null,
            selectedPermissionIds: [],
            permissions: [],
            selectedUserId: null,
            form: {
                password: "",
                name: "",
                emplacement_id: "",
                role: "",
                salaire: "",
                id: "",
            },
        };
    },

    mounted() {
        this.whenModalHidden();
        this.viewAllUsers();
        this.getAllPermissions();
    },

    methods: {
        //AFFICHE LA LISTE DES USERS
        viewAllUsers() {
            const validPath = location.pathname === "/users";
            if (validPath) {
                this.isDataLoading = true;
                get("/users.all")
                    .then(({ data, status }) => {
                        this.isDataLoading = false;
                        this.users = data.users;
                    })
                    .catch((err) => {
                        this.isDataLoading = false;
                    });
            }
        },

        //RENVOI TOUTES LES PERMISSIONS
        getAllPermissions() {
            const validPath = location.pathname === "/users";
            if (validPath) {
                get("/permissions")
                    .then(({ data, status }) => {
                        this.permissions = data.permissions;
                    })
                    .catch((err) => {
                        console.error(err);
                    });
            }
        },

        //Editing user
        editUser(user) {
            this.form.password = user.password;
            this.form.name = user.name;
            this.form.id = user.id;
            this.form.emplacement_id = user.emplacement_id;
            this.form.role = user.role;
            this.form.salaire = user.salaire;
        },

        //A L'OUVERTURE DU MODAL
        openPermissionsModal(userData) {
            // garder les objets si tu veux
            this.selectedUserPermissions = userData.permissions;

            /* console.log(userData.roles[0].permissions.length); */

            // tableau d'IDs pour v-model
            this.selectedPermissionIds = userData.permissions.map((p) => p.id);
            this.selectedUserId = userData.id;
        },

        //PERMET D'ACCORD OU RESTREINDRE LES PERMESSIONS
        togglePermission(perm) {
            const index = this.selectedUserPermissions.findIndex(
                (p) => p.id === perm.id
            );
            if (index > -1) {
                // retirer
                this.selectedUserPermissions.splice(index, 1);
            } else {
                // ajouter
                this.selectedUserPermissions.push(perm);
            }
        },

        //METTRE A JOUR LES PERMISSIONS
        submitPermissions() {
            this.isLoading = true;
            const data = {
                permissions: this.selectedPermissionIds,
                user_id: this.selectedUserId,
            };
            console.log(JSON.stringify(data));

            postJson("/user.give.access", data)
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
                            heading: "Mise à jour effectué",
                            text: `Les accès utilisateurs ont été modifiés avec succès !`,
                            position: "top-right",
                            loaderBg: "#49ff5eff",
                            icon: "success",
                            hideAfter: 3000,
                            stack: 6,
                        });
                        this.viewAllUsers();
                        $("#modalPermissions").modal("hide");
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

        //COMMENCER UNE JOURNEE DES VENTES
        startDay(event) {
            const formData = new FormData(event.target);
            const url = event.target.getAttribute("action");
            this.isLoading = true;
            post(url, formData)
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
                            heading: "Journée ouverte",
                            text: `Nouvelle journée de vente ouverte avec succès`,
                            position: "top-right",
                            loaderBg: "#49ff5eff",
                            icon: "success",
                            hideAfter: 3000,
                            stack: 6,
                        });
                        $("body").toggleClass("right-bar-toggle");
                        setInterval(() => {
                            location.reload();
                        }, 1000);
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

        //METTRE A JOUR LES PERMISSIONS
        createOrUpdateUser() {
            this.isLoading = true;
            postJson("/user.create", this.form)
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
                            heading: "Opération effectué",
                            text: `L'utilisateur créé avec succès !`,
                            position: "top-right",
                            loaderBg: "#49ff5eff",
                            icon: "success",
                            hideAfter: 3000,
                            stack: 6,
                        });
                        this.viewAllUsers();
                        $("#myModal").modal("hide");
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
            this.form = {
                password: "",
                name: "",
                emplacement_id: "",
                role: "",
                salaire: "",
                id: "",
            };
        },

        whenModalHidden() {
            const self = this;
            const modals = document.querySelectorAll(".modal");
            modals.forEach((el) => {
                el.addEventListener("hidden.bs.modal", function (event) {
                    self.resetAll();
                });
            });
        },
    },

    computed: {
        allUsers() {
            return this.users;
        },

        allPermissions() {
            return this.permissions;
        },

        formateDate() {
            return (date) =>
                moment(date, "DD/MM/YYYY HH:mm")
                    .locale("fr")
                    .format("DD MMMM YYYY");
            // ex: "14 avril 2021"
        },
        formateTime() {
            return (date) =>
                moment(date, "DD/MM/YYYY HH:mm").locale("fr").format("hh:mm");
            // ex: "03:13 AM"
        },
    },
});
