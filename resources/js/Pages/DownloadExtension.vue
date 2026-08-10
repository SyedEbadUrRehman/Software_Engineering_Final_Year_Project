<script setup>
import { ref, computed, onMounted } from "vue";
import Header from "@/Components/Index/Header.vue";
import Footer from "../Components/Index/Footer.vue";
import { Head } from "@inertiajs/vue3";

/* ---------------------------------------------------------
   1. Browser detection
--------------------------------------------------------- */
const browsers = [
    {
        id: "chrome",
        label: "Chrome",
        mode: "sideload",
        logoUrl: "https://www.google.com/chrome/static/images/chrome-logo.svg",
    },
    {
        id: "brave",
        label: "Brave",
        mode: "sideload",
        logoUrl: "https://brave.com/static-assets/images/brave-logo-sans-text.svg",
    },
    {
        id: "edge",
        label: "Edge",
        mode: "store",
        storeUrl:
            "https://microsoftedge.microsoft.com/addons/detail/YOUR_EXTENSION_ID",
        logoUrl:
            "https://upload.wikimedia.org/wikipedia/commons/9/98/Microsoft_Edge_logo_%282019%29.svg",
    },
    {
        id: "firefox",
        label: "Firefox",
        mode: "store",
        storeUrl:
            "https://addons.mozilla.org/firefox/addon/YOUR_EXTENSION_SLUG",
        logoUrl:
            "https://upload.wikimedia.org/wikipedia/commons/a/a0/Firefox_logo%2C_2019.svg",
    },
    {
        id: "opera",
        label: "Opera / Vivaldi",
        mode: "sideload",
        logoUrl:
            "https://upload.wikimedia.org/wikipedia/commons/4/49/Opera_2015_icon.svg",
    },
    {
        id: "safari",
        label: "Safari",
        mode: "unsupported",
        logoUrl:
            "https://developer.apple.com/assets/elements/icons/safari/safari-128x128_2x.png",
    },
];

const selectedId = ref(null);
const detectedId = ref(null);
const hasDownloaded = ref(false);
const openStep = ref(0);

function detectBrowser() {
    const ua = navigator.userAgent;
    if (ua.includes("Edg/")) return "edge";
    if (ua.includes("OPR/") || ua.includes("Opera")) return "opera";
    if (ua.includes("Firefox")) return "firefox";
    if (ua.includes("Safari") && !ua.includes("Chrome")) return "safari";
    if (ua.includes("Chrome")) return "chrome";
    return null;
}

onMounted(async () => {
    // navigator.brave.isBrave() is the reliable Brave check when available
    if (navigator.brave && (await navigator.brave.isBrave?.())) {
        detectedId.value = "brave";
    } else {
        detectedId.value = detectBrowser();
    }
    selectedId.value = detectedId.value;
});

const selectedBrowser = computed(
    () => browsers.find((b) => b.id === selectedId.value) || null,
);

const extensionDownloadUrl = "/downloads/siteclip-extension.zip";
const extensionVersion = "v1.0.0";

function selectBrowser(id) {
    selectedId.value = id;
    hasDownloaded.value = false;
    openStep.value = 0;
}

function markDownloaded() {
    hasDownloaded.value = true;
}

/* ---------------------------------------------------------
   2. Step guide content
--------------------------------------------------------- */
const steps = [
    {
        title: "Extract the zip file",
        desc: "Right-click the downloaded file and choose 'Extract All' (Windows) or double-click it (Mac). Note where the extracted folder is saved.",
    },
    {
        title: "Open the extensions page",
        desc: "Go to chrome://extensions (Chrome/Brave) or type it into the address bar directly — the '://' is required.",
    },
    {
        title: "Turn on Developer mode",
        desc: "Flip the 'Developer mode' toggle in the top-right corner of the page.",
    },
    {
        title: "Click 'Load unpacked'",
        desc: "Three new buttons appear top-left. Click 'Load unpacked'.",
    },
    {
        title: "Select the extracted folder",
        desc: "Choose the folder from step 1 — the one that contains manifest.json — then confirm.",
    },
    {
        title: "Pin it to your toolbar",
        desc: "Click the puzzle-piece icon in the toolbar and pin SiteClip for one-click access.",
    },
];

function toggleStep(i) {
    openStep.value = openStep.value === i ? -1 : i;
}
</script>

<template>
    <Head title="Download Extension - SiteClip" />
    <Header />

    <main>
        <!-- ============ Hero ============ -->
        <section class="downloadHero mb-12 lg:mb-20">
            <div class="textDiv text-center max-w-3xl mx-auto">
                <div class="mx-auto">
                    <h2 class="text-2xl">Browser Extension</h2>
                </div>
                <h1 class="font-headingfont text-4xl font-extrabold">
                    Get the SiteClip Extension
                </h1>
                <p
                    class="text-[20px] leading-8 mx-2 lg:mx-0 mt-4 text-gray-500"
                >
                    Pick your browser below, we'll show you the fastest way to
                    install it.
                </p>
            </div>
        </section>

        <!-- ============ Browser picker ============ -->
        <section class="browserPicker mb-12 lg:mb-20">
            <div
                class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 max-w-4xl mx-auto px-4"
            >
                <button
                    v-for="b in browsers"
                    :key="b.id"
                    @click="selectBrowser(b.id)"
                    class="browserCard relative flex flex-col items-center gap-3 p-4 rounded-xl border transition-all duration-200"
                    :class="
                        selectedId === b.id
                            ? 'border-primary bg-primary/5 shadow-sm'
                            : 'border-gray-200 hover:border-gray-300 hover:bg-gray-50'
                    "
                >
                    <!-- Browser logo -->
                    <div class="w-10 h-10 flex items-center justify-center">
                        <img
                            :src="b.logoUrl"
                            :alt="`${b.label} logo`"
                            class="w-9 h-9 object-contain"
                            loading="lazy"
                        />
                    </div>

                    <!-- Browser name -->
                    <span class="font-medium text-sm text-center">
                        {{ b.label }}
                    </span>

                    <!-- Detected indicator -->
                    <span
                        v-if="detectedId === b.id"
                        class="text-[11px] text-primary font-medium"
                    >
                        Detected
                    </span>
                </button>
            </div>
        </section>

        <!-- ============ Store-link flow: Firefox / Edge ============ -->
        <section
            v-if="selectedBrowser && selectedBrowser.mode === 'store'"
            class="storeFlow mb-16 lg:mb-36"
        >
            <div class="textDiv text-center max-w-xl mx-auto">
                <h2 class="font-headingfont text-[28px] lg:text-[34px]">
                    Install on {{ selectedBrowser.label }}
                </h2>
                <p class="text-gray-500 mt-3 leading-7">
                    SiteClip is published on the official
                    {{ selectedBrowser.label }} add-ons store, so it installs in
                    one click with no extra setup.
                </p>
                <a
                    :href="selectedBrowser.storeUrl"
                    target="_blank"
                    rel="noopener"
                    class="btn btn-primary mt-6 flex justify-center items-center"
                >
                    Get it on {{ selectedBrowser.label }}
                    <span>→</span>
                </a>
            </div>
        </section>

        <!-- ============ Unsupported: Safari ============ -->
        <section
            v-else-if="
                selectedBrowser && selectedBrowser.mode === 'unsupported'
            "
            class="unsupportedFlow mb-16 lg:mb-36"
        >
            <div class="textDiv text-center max-w-xl mx-auto">
                <h2 class="font-headingfont text-[28px] lg:text-[34px]">
                    Safari isn't supported yet
                </h2>
                <p class="text-gray-500 mt-3 leading-7">
                    Safari uses a different extension format from Chrome and
                    Firefox, so SiteClip isn't available there right now. We're
                    looking into adding support — check back soon.
                </p>
            </div>
        </section>

        <!-- ============ Sideload flow: Chrome / Brave / Opera ============ -->
        <section
            v-else-if="selectedBrowser && selectedBrowser.mode === 'sideload'"
            class="sideloadFlow mb-16 lg:mb-36"
        >
            <div class="textDiv text-center max-w-xl mx-auto">
                <template v-if="!hasDownloaded">
                    <h2 class="font-headingfont text-[28px] lg:text-[34px]">
                        Download for {{ selectedBrowser.label }}
                    </h2>
                    <p class="text-gray-500 mt-3 leading-7">
                        {{ selectedBrowser.label }} isn't on the extension store
                        yet, so you'll add it manually — takes about a minute.
                    </p>
                    <a
                        :href="extensionDownloadUrl"
                        download
                        class="btn btn-primary mt-6 flex justify-center items-center"
                        @click="markDownloaded"
                    >
                        Download the extension
                        <span>↓</span>
                    </a>
                    <div class="text-sm text-gray-400 mt-2">
                        {{ extensionVersion }}
                    </div>
                </template>

                <template v-else>
                    <h2 class="font-headingfont text-[28px] lg:text-[34px]">
                        Thanks for downloading! 🎉
                    </h2>
                    <p class="text-gray-500 mt-3 leading-7">
                        One more step — follow the guide below to add it to
                        {{ selectedBrowser.label }}.
                    </p>
                </template>
            </div>

            <!-- Accordion step guide, only shown once downloaded -->
            <div
                v-if="hasDownloaded"
                class="stepsAccordion max-w-2xl mx-auto mt-10 flex flex-col gap-3"
            >
                <div
                    v-for="(step, i) in steps"
                    :key="i"
                    class="stepItem rounded-xl border overflow-hidden"
                    :class="
                        openStep === i ? 'border-primary' : 'border-gray-200'
                    "
                >
                    <button
                        class="stepHeader w-full flex items-center gap-4 p-4 text-left"
                        @click="toggleStep(i)"
                    >
                        <span
                            class="stepNumber shrink-0 w-8 h-8 rounded-full flex items-center justify-center text-sm font-semibold"
                            :class="
                                openStep === i
                                    ? 'bg-primary text-white'
                                    : 'bg-gray-100 text-gray-500'
                            "
                        >
                            {{ i + 1 }}
                        </span>
                        <span class="font-medium flex-1">{{ step.title }}</span>
                        <span
                            class="transition-transform"
                            :class="openStep === i ? 'rotate-180' : ''"
                        >
                            ⌄
                        </span>
                    </button>

                    <div v-show="openStep === i" class="stepBody px-4 pb-5">
                        <div
                            class="flex flex-col sm:flex-row gap-4 items-center"
                        >
                            <!-- Custom illustration per step -->
                            <div class="stepIllustration shrink-0">
                                <!-- Step 1: extract zip -->
                                <svg
                                    v-if="i === 0"
                                    width="140"
                                    height="100"
                                    viewBox="0 0 140 100"
                                    fill="none"
                                >
                                    <rect
                                        x="10"
                                        y="20"
                                        width="60"
                                        height="55"
                                        rx="4"
                                        fill="#F3F4F6"
                                        stroke="#D1D5DB"
                                    />
                                    <path d="M10 30h60" stroke="#D1D5DB" />
                                    <text
                                        x="40"
                                        y="27"
                                        font-size="7"
                                        text-anchor="middle"
                                        fill="#9CA3AF"
                                    >
                                        .zip
                                    </text>
                                    <path
                                        d="M85 47h35"
                                        stroke="#D97757"
                                        stroke-width="2"
                                        marker-end="url(#arrow)"
                                    />
                                    <rect
                                        x="90"
                                        y="20"
                                        width="45"
                                        height="55"
                                        rx="4"
                                        fill="#FFF7F5"
                                        stroke="#D97757"
                                    />
                                    <path
                                        d="M100 35h25M100 45h25M100 55h15"
                                        stroke="#D97757"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                    />
                                    <defs>
                                        <marker
                                            id="arrow"
                                            markerWidth="6"
                                            markerHeight="6"
                                            refX="5"
                                            refY="3"
                                            orient="auto"
                                        >
                                            <path
                                                d="M0 0L6 3L0 6Z"
                                                fill="#D97757"
                                            />
                                        </marker>
                                    </defs>
                                </svg>

                                <!-- Step 2: extensions URL bar -->
                                <svg
                                    v-else-if="i === 1"
                                    width="140"
                                    height="100"
                                    viewBox="0 0 140 100"
                                    fill="none"
                                >
                                    <rect
                                        x="10"
                                        y="15"
                                        width="120"
                                        height="70"
                                        rx="6"
                                        fill="#F9FAFB"
                                        stroke="#D1D5DB"
                                    />
                                    <rect
                                        x="18"
                                        y="25"
                                        width="104"
                                        height="16"
                                        rx="4"
                                        fill="white"
                                        stroke="#D97757"
                                        stroke-width="1.5"
                                    />
                                    <text
                                        x="24"
                                        y="36"
                                        font-size="8"
                                        fill="#D97757"
                                    >
                                        chrome://extensions
                                    </text>
                                </svg>

                                <!-- Step 3: developer mode toggle -->
                                <svg
                                    v-else-if="i === 2"
                                    width="140"
                                    height="100"
                                    viewBox="0 0 140 100"
                                    fill="none"
                                >
                                    <rect
                                        x="10"
                                        y="15"
                                        width="120"
                                        height="70"
                                        rx="6"
                                        fill="#F9FAFB"
                                        stroke="#D1D5DB"
                                    />
                                    <text
                                        x="20"
                                        y="35"
                                        font-size="8"
                                        fill="#9CA3AF"
                                    >
                                        Developer mode
                                    </text>
                                    <rect
                                        x="95"
                                        y="26"
                                        width="28"
                                        height="14"
                                        rx="7"
                                        fill="#D97757"
                                    />
                                    <circle
                                        cx="116"
                                        cy="33"
                                        r="5"
                                        fill="white"
                                    />
                                    <circle
                                        cx="116"
                                        cy="33"
                                        r="9"
                                        fill="none"
                                        stroke="#D97757"
                                        stroke-width="1.5"
                                        stroke-dasharray="2 2"
                                    />
                                </svg>

                                <!-- Step 4: Load unpacked button -->
                                <svg
                                    v-else-if="i === 3"
                                    width="140"
                                    height="100"
                                    viewBox="0 0 140 100"
                                    fill="none"
                                >
                                    <rect
                                        x="10"
                                        y="15"
                                        width="120"
                                        height="70"
                                        rx="6"
                                        fill="#F9FAFB"
                                        stroke="#D1D5DB"
                                    />
                                    <rect
                                        x="18"
                                        y="24"
                                        width="70"
                                        height="18"
                                        rx="4"
                                        fill="#D97757"
                                    />
                                    <text
                                        x="53"
                                        y="36"
                                        font-size="7"
                                        text-anchor="middle"
                                        fill="white"
                                    >
                                        Load unpacked
                                    </text>
                                    <rect
                                        x="14"
                                        y="19"
                                        width="78"
                                        height="28"
                                        rx="6"
                                        fill="none"
                                        stroke="#D97757"
                                        stroke-width="1.5"
                                        stroke-dasharray="3 2"
                                    />
                                </svg>

                                <!-- Step 5: folder picker -->
                                <svg
                                    v-else-if="i === 4"
                                    width="140"
                                    height="100"
                                    viewBox="0 0 140 100"
                                    fill="none"
                                >
                                    <rect
                                        x="15"
                                        y="15"
                                        width="110"
                                        height="70"
                                        rx="6"
                                        fill="white"
                                        stroke="#D1D5DB"
                                    />
                                    <rect
                                        x="15"
                                        y="15"
                                        width="110"
                                        height="16"
                                        rx="6"
                                        fill="#F3F4F6"
                                    />
                                    <path
                                        d="M28 45l8 10 20-16"
                                        stroke="#9CA3AF"
                                        fill="none"
                                    />
                                    <rect
                                        x="25"
                                        y="55"
                                        width="30"
                                        height="20"
                                        rx="2"
                                        fill="#FFF7F5"
                                        stroke="#D97757"
                                        stroke-width="1.5"
                                    />
                                    <path d="M25 58h30" stroke="#D97757" />
                                    <rect
                                        x="88"
                                        y="63"
                                        width="30"
                                        height="14"
                                        rx="3"
                                        fill="#D97757"
                                    />
                                    <text
                                        x="103"
                                        y="73"
                                        font-size="6.5"
                                        text-anchor="middle"
                                        fill="white"
                                    >
                                        Select
                                    </text>
                                </svg>

                                <!-- Step 6: pin extension -->
                                <svg
                                    v-else
                                    width="140"
                                    height="100"
                                    viewBox="0 0 140 100"
                                    fill="none"
                                >
                                    <rect
                                        x="10"
                                        y="15"
                                        width="120"
                                        height="24"
                                        rx="4"
                                        fill="#F9FAFB"
                                        stroke="#D1D5DB"
                                    />
                                    <circle
                                        cx="105"
                                        cy="27"
                                        r="7"
                                        fill="none"
                                        stroke="#9CA3AF"
                                    />
                                    <path
                                        d="M100 27a5 3 0 0110 0"
                                        stroke="#9CA3AF"
                                        fill="none"
                                    />
                                    <circle
                                        cx="122"
                                        cy="27"
                                        r="8"
                                        fill="#FFF7F5"
                                        stroke="#D97757"
                                        stroke-width="1.5"
                                    />
                                    <path
                                        d="M122 23v8M118 27h8"
                                        stroke="#D97757"
                                        stroke-width="1.5"
                                    />
                                </svg>
                            </div>

                            <p
                                class="text-gray-500 leading-7 text-sm sm:text-base"
                            >
                                {{ step.desc }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============ Nothing selected yet ============ -->
        <section v-else class="pickPrompt mb-16 lg:mb-36">
            <p class="text-center text-gray-400">
                Choose your browser above to see install instructions.
            </p>
        </section>
    </main>

    <footer>
        <Footer />
    </footer>
</template>
