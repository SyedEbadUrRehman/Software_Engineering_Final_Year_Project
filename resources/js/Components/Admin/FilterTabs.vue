<script setup>
const props = defineProps({
    active: { type: String, default: "all" },
    counts: {
        type: Object,
        default: () => ({ approved: 0, flagged: 0, deleted: 0 }),
    },
});
const emit = defineEmits(["select"]);

const tabs = [
    { value: "all", label: "All" },
    { value: "approved", label: "Allowed" },
    { value: "flagged", label: "Flagged" },
    { value: "deleted", label: "Deleted" },
];

function totalFor(value) {
    if (value === "all") {
        return (
            (props.counts?.approved ?? 0) +
            (props.counts?.flagged ?? 0) +
            (props.counts?.deleted ?? 0)
        );
    }
    return props.counts?.[value] ?? 0;
}
</script>

<template>
    <div class="filter-tabs">
        <button
            v-for="tab in tabs"
            :key="tab.value"
            class="tab"
            :class="{ active: active === tab.value }"
            @click="emit('select', tab.value)"
        >
            {{ tab.label }}
            <span class="count">{{ totalFor(tab.value) }}</span>
        </button>
    </div>
</template>

<style scoped>
.filter-tabs {
    display: flex;
    gap: 8px;
    padding: 18px 22px;
    flex-wrap: wrap;
    border-bottom: 1px solid rgba(11, 79, 115, 0.08);
}
.tab {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    border: 1px solid rgba(11, 79, 115, 0.14);
    background: rgba(255, 255, 255, 0.5);
    color: #0b4f73;
    padding: 7px 14px;
    border-radius: 999px;
    font-size: 0.82rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
}
.tab:hover {
    background: rgba(14, 165, 233, 0.12);
    transform: translateY(-1px);
}
.tab.active {
    background: linear-gradient(135deg, #0ea5e9, #0b4f73);
    color: #fff;
    border-color: transparent;
    box-shadow: 0 10px 22px -10px rgba(14, 165, 233, 0.6);
}
.tab .count {
    font-size: 0.72rem;
    font-weight: 700;
    background: rgba(11, 79, 115, 0.1);
    padding: 1px 7px;
    border-radius: 999px;
}
.tab.active .count {
    background: rgba(255, 255, 255, 0.25);
}
</style>
