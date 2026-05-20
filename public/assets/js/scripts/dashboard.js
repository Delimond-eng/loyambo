import { post, postJson, get } from "../modules/http.js";

const Store = Vue.observable({
    counts: {},
    stats: null,
});

const Charts = {
    adminDaily: null,
    adminServices: null,
    adminModes: null,
    adminEmplacements: null,
    cashierDaily: null,
    cashierModes: null,
};

const formatNumber = (value) =>
    Number(value || 0).toLocaleString("fr-FR");

const renderLineChart = (el, seriesData, categories, chartRefKey) => {
    if (typeof ApexCharts === "undefined") return;
    if (!el) return;
    const options = {
        series: [{ name: "Recettes", data: seriesData }],
        chart: { height: 300, type: "area", toolbar: { show: false } },
        dataLabels: { enabled: false },
        stroke: { curve: "smooth", width: 2 },
        xaxis: { categories },
        colors: ["#0d6efd"],
    };
    if (Charts[chartRefKey]) {
        Charts[chartRefKey].updateOptions({ xaxis: { categories } });
        Charts[chartRefKey].updateSeries([{ name: "Recettes", data: seriesData }]);
    } else {
        Charts[chartRefKey] = new ApexCharts(el, options);
        Charts[chartRefKey].render();
    }
};

const renderDonutChart = (el, seriesData, labels, colors, chartRefKey) => {
    if (typeof ApexCharts === "undefined") return;
    if (!el) return;
    const options = {
        series: seriesData,
        chart: { height: 300, type: "donut" },
        labels,
        legend: { position: "bottom" },
        colors,
    };
    if (Charts[chartRefKey]) {
        Charts[chartRefKey].updateOptions({ labels, colors });
        Charts[chartRefKey].updateSeries(seriesData);
    } else {
        Charts[chartRefKey] = new ApexCharts(el, options);
        Charts[chartRefKey].render();
    }
};

const renderBarChart = (el, seriesData, categories, chartRefKey) => {
    if (typeof ApexCharts === "undefined") return;
    if (!el) return;
    const options = {
        series: [{ name: "Recettes", data: seriesData }],
        chart: { height: 300, type: "bar", toolbar: { show: false } },
        plotOptions: { bar: { columnWidth: "50%", borderRadius: 4 } },
        dataLabels: { enabled: false },
        xaxis: { categories },
        colors: ["#20c997"],
    };
    if (Charts[chartRefKey]) {
        Charts[chartRefKey].updateOptions({ xaxis: { categories } });
        Charts[chartRefKey].updateSeries([{ name: "Recettes", data: seriesData }]);
    } else {
        Charts[chartRefKey] = new ApexCharts(el, options);
        Charts[chartRefKey].render();
    }
};

document.querySelectorAll(".AppDashboard").forEach((el) => {
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
            };
        },

        mounted() {
            setInterval(() => {
                this.loadCounter();
            }, 3000);
            this.loadStats();
        },

        methods: {
            loadCounter() {
                get("/counts.all")
                    .then(({ data, status }) => {
                        console.log(data);
                        this.store.counts = data.counts;
                    })
                    .catch((err) => {
                        console.log(err);
                    });
            },
            loadStats() {
                get("/dashboard.stats")
                    .then(({ data }) => {
                        this.store.stats = data;
                        this.renderDashboardCharts(data);
                    })
                    .catch((err) => {
                        console.log(err);
                    });
            },
            renderDashboardCharts(data) {
                if (!data || data.status !== "success") {
                    return;
                }

                if (data.summary) {
                    const isAdmin = data.role === "admin" || data.role === "manager";
                    const today = formatNumber(data.summary.today);
                    const week = formatNumber(data.summary.week);
                    const month = formatNumber(data.summary.month);
                    if (isAdmin) {
                        const elToday = document.getElementById("admin-total-today");
                        const elWeek = document.getElementById("admin-total-week");
                        const elMonth = document.getElementById("admin-total-month");
                        const elServices = document.getElementById("admin-total-services");
                        if (elToday) elToday.textContent = today;
                        if (elWeek) elWeek.textContent = week;
                        if (elMonth) elMonth.textContent = month;
                        if (elServices) {
                            elServices.textContent = data.services?.labels?.length || 0;
                        }
                    } else if (data.role === "caissier") {
                        const elToday = document.getElementById("cashier-total-today");
                        const elWeek = document.getElementById("cashier-total-week");
                        const elMonth = document.getElementById("cashier-total-month");
                        if (elToday) elToday.textContent = today;
                        if (elWeek) elWeek.textContent = week;
                        if (elMonth) elMonth.textContent = month;
                    }
                }

                if (data.daily) {
                    renderLineChart(
                        document.querySelector("#chart-admin-daily"),
                        data.daily.series || [],
                        data.daily.labels || [],
                        "adminDaily"
                    );
                    renderLineChart(
                        document.querySelector("#chart-cashier-daily"),
                        data.daily.series || [],
                        data.daily.labels || [],
                        "cashierDaily"
                    );
                }

                if (data.modes) {
                    renderDonutChart(
                        document.querySelector("#chart-admin-modes"),
                        data.modes.series || [],
                        data.modes.labels || [],
                        ["#0d6efd", "#20c997", "#ffc107", "#dc3545", "#6f42c1"],
                        "adminModes"
                    );
                    renderDonutChart(
                        document.querySelector("#chart-cashier-modes"),
                        data.modes.series || [],
                        data.modes.labels || [],
                        ["#0d6efd", "#20c997", "#ffc107", "#dc3545", "#6f42c1"],
                        "cashierModes"
                    );
                }

                if (data.services) {
                    renderDonutChart(
                        document.querySelector("#chart-admin-services"),
                        data.services.series || [],
                        data.services.labels || [],
                        ["#198754", "#0dcaf0", "#ffc107", "#dc3545"],
                        "adminServices"
                    );
                }

                if (data.top_emplacements) {
                    renderBarChart(
                        document.querySelector("#chart-admin-emplacements"),
                        data.top_emplacements.series || [],
                        data.top_emplacements.labels || [],
                        "adminEmplacements"
                    );
                }
            },
        },

        computed: {
            counts() {
                return this.store.counts;
            },

            formateDate() {
                return (date) =>
                    moment(date, "YYYY-MM-DD")
                        .locale("fr")
                        .format("DD MMMM YYYY");
                // ex: "14 avril 2021"
            },

            formateTime() {
                return (date) => moment(date).locale("fr").format("hh:mm");
                // ex: "03:13 AM"
            },
        },
    });
});
