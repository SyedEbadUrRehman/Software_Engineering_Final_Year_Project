<script setup>
import { computed } from "vue";
import VueApexCharts from "vue3-apexcharts";

const props = defineProps({
    data: { type: Array, default: () => [] },
});

const categories = computed(() => props.data.map((d) => d.label));
const series = computed(() => [
    { name: "Moderations", data: props.data.map((d) => d.total) },
]);

const options = computed(() => ({
    chart: {
        type: "area",
        toolbar: { show: false },
        fontFamily: "Outfit, sans-serif",
        animations: {
            enabled: true,
            easing: "easeinout",
            speed: 750,
            animateGradually: { enabled: true, delay: 60 },
            dynamicAnimation: { enabled: true, speed: 350 },
        },
    },
    colors: ["#0ea5e9"],
    stroke: { curve: "smooth", width: 3 },
    fill: {
        type: "gradient",
        gradient: {
            shadeIntensity: 1,
            opacityFrom: 0.45,
            opacityTo: 0.02,
            stops: [0, 90, 100],
        },
    },
    grid: { borderColor: "rgba(11,79,115,0.08)", strokeDashArray: 4 },
    dataLabels: { enabled: false },
    markers: { size: 0, hover: { size: 6 } },
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
    tooltip: { theme: "light" },
}));
</script>

<template>
    <VueApexCharts type="area" height="260" :options="options" :series="series" />
</template>
