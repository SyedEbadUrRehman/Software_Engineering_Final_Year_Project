<script setup>
defineProps({
    title: { type: String, default: null },
    noPadding: { type: Boolean, default: false },
});
</script>

<template>
    <div class="glass-card" :class="{ 'no-padding': noPadding }">
        <div v-if="$slots.header" class="glass-card-header-slot">
            <slot name="header" />
        </div>
        <h3 v-else-if="title" class="glass-card-title">{{ title }}</h3>

        <div class="glass-card-body">
            <slot />
        </div>
    </div>
</template>

<style scoped>
.glass-card {
    background: linear-gradient(
        160deg,
        rgba(255, 255, 255, 0.72),
        rgba(255, 255, 255, 0.42)
    );
    backdrop-filter: blur(22px) saturate(160%);
    -webkit-backdrop-filter: blur(22px) saturate(160%);
    border: 1px solid rgba(255, 255, 255, 0.65);
    border-radius: 22px;
    padding: 22px 24px 20px;
    box-shadow: 0 20px 45px -22px rgba(11, 79, 115, 0.35),
        inset 0 1px 0 rgba(255, 255, 255, 0.6);
    position: relative;
    overflow: hidden;
    transition: box-shadow 0.3s ease, transform 0.3s ease;
}
.glass-card:hover {
    box-shadow: 0 26px 55px -22px rgba(11, 79, 115, 0.42),
        inset 0 1px 0 rgba(255, 255, 255, 0.65);
}
.glass-card.no-padding {
    padding: 0;
}
.glass-card::before {
    content: "";
    position: absolute;
    inset: 0;
    background: radial-gradient(
        120% 60% at 0% 0%,
        rgba(14, 165, 233, 0.14),
        transparent 60%
    );
    pointer-events: none;
}
.glass-card-title {
    position: relative;
    z-index: 1;
    font-family: "Outfit", sans-serif;
    font-size: 0.95rem;
    font-weight: 600;
    letter-spacing: 0.01em;
    color: #0b4f73;
    margin: 0 0 14px;
}
.glass-card-header-slot {
    position: relative;
    z-index: 1;
}
.glass-card-body {
    position: relative;
    z-index: 1;
}
</style>
