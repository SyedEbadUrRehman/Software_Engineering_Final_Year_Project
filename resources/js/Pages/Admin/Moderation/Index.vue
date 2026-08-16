<script setup>
import { computed, ref, watch } from "vue";
import { Head, router, usePage ,Link} from "@inertiajs/vue3";

import GlassCard from "@/Components/Admin/GlassCard.vue";
import FilterTabs from "@/Components/Admin/FilterTabs.vue";
import DailyModerationsChart from "@/Components/Admin/DailyModerationsChart.vue";
import StatusRatioChart from "@/Components/Admin/StatusRatioChart.vue";
import PostsCommentsChart from "@/Components/Admin/PostsCommentsChart.vue";
import ModerationTable from "@/Components/Admin/ModerationTable.vue";
import EditModerationModal from "@/Components/Admin/EditModerationModal.vue";

const props = defineProps({
    filters: { type: Object, required: true },
    logs: { type: Object, required: true },
    stats: { type: Object, required: true },
});

const page = usePage();

const activeLog = ref(null);
const showModal = ref(false);
const isBusy = ref(false);

const toastMessage = ref(null);
watch(
    () => page.props.flash?.success,
    (message) => {
        if (!message) return;
        toastMessage.value = message;
        setTimeout(() => {
            toastMessage.value = null;
        }, 3500);
    }
);

function applyFilter(status) {
    isBusy.value = true;
    router.get(
        route("admin.moderation.index"),
        { status },
        {
            preserveScroll: true,
            preserveState: true,
            replace: true,
            onFinish: () => (isBusy.value = false),
        }
    );
}

function goToPage(url) {
    if (!url) return;
    isBusy.value = true;
    router.get(
        url,
        {},
        {
            preserveScroll: true,
            preserveState: true,
            onFinish: () => (isBusy.value = false),
        }
    );
}

function openEdit(log) {
    activeLog.value = log;
    showModal.value = true;
}

function closeModal() {
    showModal.value = false;
    activeLog.value = null;
}
</script>

<template>
    <Head title="Moderation · Admin" />

    <div class="admin-shell">
        <div class="bg-orb orb-a" aria-hidden="true"></div>
        <div class="bg-orb orb-b" aria-hidden="true"></div>

        <transition name="toast">
            <div v-if="toastMessage" class="toast">{{ toastMessage }}</div>
        </transition>

        <header class="admin-header">
            <div class="header-wave" aria-hidden="true"></div>
            <Link :href="route('home.index')">
               <img
                   class="w-[150px] cursor-pointer"
                   src="/SiteClipLogo.png"
               />
           </Link>
            <div class="header-content">
                <div>
                    <p class="eyebrow">Content Moderation</p>
                    <h1>Moderation Control Room</h1>
                    <p class="subtitle">
                        Live status of every post and comment reviewed this
                        month.
                    </p>
                </div>
                <div class="admin-chip">
                    <span class="dot"></span>
                    {{ page.props.auth?.user?.name ?? "Admin" }}
                </div>
            </div>
        </header>

        <main class="admin-main">
            <section class="charts-grid">
                <GlassCard title="Daily moderations this month" class="span-2">
                    <DailyModerationsChart :data="stats.dailyModerations" />
                </GlassCard>

                <GlassCard title="Allow / Flag / Delete">
                    <StatusRatioChart :data="stats.statusRatio" />
                </GlassCard>

                <GlassCard
                    title="Posts vs. comments this month"
                    class="span-3"
                >
                    <PostsCommentsChart :data="stats.postsVsComments" />
                </GlassCard>
            </section>

            <section class="table-section">
                <GlassCard no-padding>
                    <template #header>
                        <FilterTabs
                            :active="filters.status"
                            :counts="stats.statusRatio"
                            @select="applyFilter"
                        />
                    </template>

                    <ModerationTable
                        :logs="logs"
                        :loading="isBusy"
                        @edit="openEdit"
                        @paginate="goToPage"
                    />
                </GlassCard>
            </section>
        </main>

        <EditModerationModal
            :show="showModal"
            :log="activeLog"
            @close="closeModal"
        />
    </div>
</template>

<style>
@import url("https://fonts.googleapis.com/css2?family=Outfit:wght@500;600;700&display=swap");

.admin-shell {
    --ocean-950: #04263a;
    --ocean-800: #0b4f73;
    --ocean-600: #0ea5e9;
    --ocean-400: #7dd3fc;
    --foam: #eaf6ff;

   
    background: radial-gradient(
            120% 100% at 100% 0%,
            #eafcff 0%,
            transparent 55%
        ),
        radial-gradient(120% 100% at 0% 100%, #e4f3ff 0%, transparent 55%),
        linear-gradient(180deg, #f5fbff 0%, #eaf6ff 100%);
    font-family: "Inter", system-ui, sans-serif;
    color: var(--ocean-950);
    position: relative;
    overflow: hidden;
    padding-bottom: 64px;
}

.bg-orb {
    position: absolute;
    border-radius: 50%;
    filter: blur(70px);
    opacity: 0.5;
    pointer-events: none;
    z-index: 0;
}
.orb-a {
    width: 520px;
    height: 520px;
    top: -220px;
    right: -160px;
    background: radial-gradient(circle, #7dd3fc, transparent 70%);
}
.orb-b {
    width: 420px;
    height: 420px;
    bottom: -180px;
    left: -140px;
    background: radial-gradient(circle, #bae6fd, transparent 70%);
}

.admin-header {
    position: relative;
    z-index: 1;
    padding: 48px 48px 30px;
    overflow: hidden;
}
.header-wave {
    position: absolute;
    inset: 0;
    background: linear-gradient(
        100deg,
        rgba(14, 165, 233, 0.12),
        rgba(125, 211, 252, 0.05) 40%,
        transparent 70%
    );
    background-size: 200% 200%;
    animation: wave-drift 12s ease-in-out infinite;
    pointer-events: none;
}
@keyframes wave-drift {
    0%,
    100% {
        background-position: 0% 50%;
    }
    50% {
        background-position: 100% 50%;
    }
}
.header-content {
    position: relative;
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 24px;
    flex-wrap: wrap;
}
.eyebrow {
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    color: var(--ocean-600);
    margin: 0 0 6px;
}
.admin-header h1 {
    font-family: "Outfit", sans-serif;
    font-size: clamp(1.7rem, 2.6vw, 2.4rem);
    font-weight: 700;
    color: var(--ocean-950);
    margin: 0 0 6px;
}
.subtitle {
    color: var(--ocean-800);
    opacity: 0.75;
    margin: 0;
    font-size: 0.95rem;
}
.admin-chip {
    display: flex;
    align-items: center;
    gap: 8px;
    background: rgba(255, 255, 255, 0.65);
    backdrop-filter: blur(14px);
    border: 1px solid rgba(255, 255, 255, 0.7);
    padding: 9px 16px;
    border-radius: 999px;
    font-size: 0.82rem;
    font-weight: 600;
    color: var(--ocean-800);
    box-shadow: 0 10px 25px -14px rgba(11, 79, 115, 0.4);
}
.admin-chip .dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #22c55e;
    box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.25);
}

.admin-main {
    position: relative;
    z-index: 1;
    padding: 8px 48px 0;
    display: flex;
    flex-direction: column;
    gap: 22px;
}

.charts-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
}
.charts-grid .span-2 {
    grid-column: span 2;
}
.charts-grid .span-3 {
    grid-column: span 3;
}
@media (max-width: 1024px) {
    .charts-grid {
        grid-template-columns: 1fr;
    }
    .charts-grid .span-2,
    .charts-grid .span-3 {
        grid-column: span 1;
    }
    .admin-header,
    .admin-main {
        padding-left: 20px;
        padding-right: 20px;
    }
}

.toast {
    position: fixed;
    top: 24px;
    right: 24px;
    z-index: 60;
    background: rgba(11, 79, 115, 0.92);
    color: #eafcff;
    padding: 12px 20px;
    border-radius: 14px;
    font-size: 0.88rem;
    font-weight: 500;
    box-shadow: 0 20px 40px -16px rgba(4, 38, 58, 0.6);
    backdrop-filter: blur(10px);
}
.toast-enter-active,
.toast-leave-active {
    transition: all 0.35s cubic-bezier(0.22, 1, 0.36, 1);
}
.toast-enter-from,
.toast-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}
</style>