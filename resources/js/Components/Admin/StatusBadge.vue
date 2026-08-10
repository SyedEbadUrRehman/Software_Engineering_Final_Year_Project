<script setup>
const props = defineProps({
    status: { type: String, required: true },
});

const meta = {
    approved: { label: "Allowed", color: "#0f9d78", bg: "rgba(16,185,129,0.14)" },
    flagged: { label: "Flagged", color: "#b45309", bg: "rgba(251,191,36,0.18)" },
    deleted: { label: "Deleted", color: "#be123c", bg: "rgba(251,113,133,0.18)" },
};
</script>

<template>
    <span
        class="status-badge"
        :style="{
            color: meta[status]?.color ?? '#0b4f73',
            background: meta[status]?.bg ?? 'rgba(11,79,115,0.1)',
        }"
    >
        <span
            class="status-dot"
            :style="{ background: meta[status]?.color ?? '#0b4f73' }"
        ></span>
        {{ meta[status]?.label ?? status }}
    </span>
</template>

<style scoped>
.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 11px;
    border-radius: 999px;
    font-size: 0.76rem;
    font-weight: 700;
    letter-spacing: 0.01em;
    white-space: nowrap;
}
.status-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
}
</style>
