<script setup>
import { computed } from "vue";
import VueApexCharts from "vue3-apexcharts";

const props = defineProps({
    data: {
        type: Object,
        default: () => ({ approved: 0, flagged: 0, deleted: 0 }),
    },
});

const series = computed(() => [
    props.data.approved ?? 0,
    props.data.flagged ?? 0,
    props.data.deleted ?? 0,
]);

const options = computed(() => ({
    chart: {
        type: "donut",
        fontFamily: "Outfit, sans-serif",
        animations: { enabled: true, speed: 750, easing: "easeinout" },
    },
    labels: ["Allowed", "Flagged", "Deleted"],
    colors: ["#10b981", "#f59e0b", "#fb7185"],
    legend: {
        position: "bottom",
        labels: { colors: "#0b4f73" },
        fontSize: "12px",
        markers: { width: 8, height: 8, radius: 4 },
    },
    dataLabels: {
        enabled: true,
        style: { fontSize: "11px", fontWeight: 700 },
        dropShadow: { enabled: false },
    },
    stroke: { width: 3, colors: ["#ffffff"] },
    plotOptions: {
        pie: {
            donut: {
                size: "68%",
                labels: {
                    show: true,
                    total: {
                        show: true,
                        label: "Total",
                        color: "#0b4f73",
                        fontSize: "13px",
                    },
                },
            },
        },
    },
    tooltip: { theme: "light" },
}));
</script>

<template>
    <VueApexCharts type="donut" height="260" :options="options" :series="series" />
</template>
