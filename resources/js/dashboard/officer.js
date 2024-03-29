import { initDashboard } from "../dashboard";

initDashboard().then((res) => {
    const body = res;
    const userRole = body.authentication.data.role;

    if (userRole === "officer") {
        // Response JSON
        const confessionGenders = body.chart.data.confessionGenders;
        const yourResponses = body.chart.data.yourResponses;
        const yourComments = body.chart.data.yourComments;
        const yourLikes = body.chart.data.yourLikes;
        const yourHistoryLogins = body.chart.data.yourHistoryLogins;

        // Set options
        const optionsYourStatistics = {
            series: [
                {
                    name: "Tanggapan",
                    data: yourResponses.yAxis,
                },
                {
                    name: "Komentar",
                    data: yourComments.yAxis,
                },
                {
                    name: "Suka",
                    data: yourLikes.yAxis,
                },
                {
                    name: "Log-in",
                    data: yourHistoryLogins.yAxis,
                },
            ],
            chart: {
                height: 350,
                type: "area",
                zoom: {
                    enabled: false,
                },
                stacked: false,
            },
            dataLabels: {
                enabled: false,
            },
            stroke: {
                curve: "smooth",
            },
            xaxis: {
                categories: yourResponses.xAxis,
                type: "datetime",
            },
            yaxis: {
                labels: {
                    formatter: function (value) {
                        return Math.round(value);
                    },
                },
                max:
                    Math.max(
                        ...yourResponses.yAxis,
                        ...yourComments.yAxis,
                        ...yourLikes.yAxis,
                        ...yourHistoryLogins.yAxis
                    ) + 1,
            },
            tooltip: {
                x: {
                    format: "dd/MM/yy",
                },
            },
        };
        const optionsConfessionGenders = {
            series: [confessionGenders.male, confessionGenders.female],
            labels: ["Male", "Female"],
            colors: ["#435ebe", "#55c6e8"],
            chart: {
                type: "donut",
                width: "100%",
                height: "350px",
            },
            legend: {
                position: "bottom",
            },
            plotOptions: {
                pie: {
                    donut: {
                        size: "30%",
                    },
                },
            },
        };

        // Instance chart
        const chartYourStatistics = new ApexCharts(
            document.getElementById("chart-your-statistics"),
            optionsYourStatistics
        );
        const chartConfessionGenders = new ApexCharts(
            document.getElementById("chart-confession-genders"),
            optionsConfessionGenders
        );

        // Render
        chartYourStatistics.render();
        chartConfessionGenders.render();
    }
});
