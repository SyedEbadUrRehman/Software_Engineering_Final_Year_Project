<script setup>
import { computed } from "vue";
import VueApexCharts from "vue3-apexcharts";

const props = defineProps({
    data: { type: Array, default: () => [] },
});

const categories = computed(() => props.data.map((d) => d.label));
const series = computed(() => [
    { name: "Posts", data: props.data.map((d) => d.posts) },
    { name: "Comments", data: props.data.map((d) => d.comments) },
]);

const options = computed(() => ({
    chart: {
        type: "line",
        toolbar: { show: false },
        fontFamily: "Outfit, sans-serif",
        animations: {
            enabled: true,
            easing: "easeinout",
            speed: 750,
            animateGradually: { enabled: true, delay: 80 },
        },
    },
    colors: ["#0ea5e9", "#0b4f73"],
    stroke: { curve: "smooth", width: [3, 3], dashArray: [0, 4] },
    grid: { borderColor: "rgba(11,79,115,0.08)", strokeDashArray: 4 },
    markers: { size: 0, hover: { size: 5 } },
    legend: {
        position: "top",
        horizontalAlign: "right",
        labels: { colors: "#0b4f73" },
        markers: { width: 8, height: 8, radius: 4 },
    },
    xaxis: {
        categories: categories.value,
        labels: { style: { colors: "#0b4f73", fontSize: "11px" } },
        axisBorder: { show: false },
        axisTicks: { show: false },
    },
    yaxis: {
        min: 0,
        labels: { style: { colors: "#0b4f73", fontSize: "11px" } },
    },
    tooltip: { theme: "light", shared: true },
}));
</script>

<template>
    <VueApexCharts type="line" height="280" :options="options" :series="series" />
</template>
