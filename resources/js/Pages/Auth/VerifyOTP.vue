<script setup>
import { Head, useForm, Link } from "@inertiajs/vue3";
import { ref, onUnmounted } from "vue";

const props = defineProps({
    email: String,
    status: String,
});

const form = useForm({
    code: "",
});

// Local status/error messages, cooldown timer, and timeout references
const resendStatus = ref("");
const resendError = ref("");
const countdown = ref(0);
let timer = null;
let hideTimeout = null;

const startCooldown = () => {
    countdown.value = 60;
    timer = setInterval(() => {
        countdown.value--;
        if (countdown.value <= 0) {
            clearInterval(timer);
        }
    }, 1000);
};

const submit = () => {
    form.post(route("2fa.verify"));
};

const resendCode = () => {
    // Clear any existing active hiding timeout
    if (hideTimeout) clearTimeout(hideTimeout);

    resendStatus.value = "";
    resendError.value = "";

    form.post(route("2fa.resend"), {
        preserveScroll: true,
        onSuccess: () => {
            resendStatus.value = "A new verification code has been sent to your email.";
            startCooldown();

            // Automatically hide the status message after 3000ms
            hideTimeout = setTimeout(() => {
                resendStatus.value = "";
            }, 3000);
        },
        onError: () => {
            resendError.value = "Failed to resend verification code. Please try again.";

            // Automatically hide the error message after 3000ms
            hideTimeout = setTimeout(() => {
                resendError.value = "";
            }, 3000);
        },
    });
};

onUnmounted(() => {
    if (timer) clearInterval(timer);
    if (hideTimeout) clearTimeout(hideTimeout);
});
</script>

<template>
    <Head title="Two-Factor Verification" />

    <!-- Clean, Crisp White & Ocean Blue Aesthetic -->
    <div
        class="min-h-screen flex flex-col justify-center items-center p-6 bg-gradient-to-b from-sky-50 via-white to-blue-50/50 relative overflow-hidden"
    >
        <!-- Subtle Floating Ocean Orbs -->
        <div
            class="absolute w-[500px] h-[500px] bg-sky-200/40 rounded-full blur-[120px] -top-32 -right-32 pointer-events-none"
        ></div>
        <div
            class="absolute w-[400px] h-[400px] bg-blue-200/30 rounded-full blur-[100px] -bottom-32 -left-32 pointer-events-none"
        ></div>

        <!-- Glassmorphism Container with Sleek Frost Effect -->
        <div
            class="w-full max-w-md p-8 sm:p-10 bg-white/80 backdrop-blur-2xl border border-sky-100 shadow-[0_20px_40px_-15px_rgba(14,116,144,0.08)] rounded-3xl relative z-10 transition-all duration-300"
        >
            <!-- Ocean Blue Minimal Logo / Icon -->
            <div
                class="flex items-center justify-center my-3"
            >
                <Link :href="route('home.index')">
                    <img
                        class="mx-auto"
                        width="200"
                        src="/SiteClipLogo.png"
                    />
                </Link>
            </div>

            <h2
                class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900 mb-2"
            >
                Authentication Code
            </h2>
            <p class="text-sm text-slate-500 mb-2 leading-relaxed">
                We've sent a secure 6-digit code to
                <span class="font-semibold text-sky-900">{{ email }}</span>. Enter it below to proceed.
            </p>

            <!-- OTP Expiration Note -->
            <p class="text-xs font-medium text-sky-700/80 mb-8 bg-sky-50/80 border border-sky-100 py-1.5 px-3 rounded-lg inline-block">
                🕒 OTP code expires in 10 minutes
            </p>

            <!-- Status Alert (Success) -->
            <div
                v-if="status || resendStatus"
                class="mb-6 font-medium text-xs text-emerald-700 bg-emerald-50 border border-emerald-200/60 p-1 px-3 rounded-xl text-center shadow-sm transition-all duration-300"
            >
                {{ resendStatus || status }}
            </div>

            <!-- Error Alert (Resend Error) -->
            <div
                v-if="resendError"
                class="mb-6 font-medium text-xs text-rose-700 bg-rose-50 border border-rose-200/60 p-3.5 rounded-xl text-center shadow-sm transition-all duration-300"
            >
                {{ resendError }}
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <div>
                    <label
                        class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2"
                    >
                        Verification Code
                    </label>
                    <input
                        v-model="form.code"
                        type="text"
                        maxlength="6"
                        class="w-full text-center text-3xl tracking-[0.4em] font-black bg-white/70 border border-sky-200 text-slate-800 placeholder-slate-300 focus:bg-white focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 rounded-2xl shadow-sm py-2 transition-all duration-200 outline-none"
                        placeholder="000000"
                        required
                        autofocus
                        :disabled="form.processing"
                    />
                    <div
                        v-if="form.errors.code"
                        class="text-rose-500 text-xs mt-2 font-medium text-center bg-rose-50 border border-rose-100 p-2 rounded-xl"
                    >
                        {{ form.errors.code }}
                    </div>
                </div>

                <div class="flex items-center justify-between pt-2">
                    <!-- Resend Code Button with Cooldown Logic -->
                    <button
                        type="button"
                        @click="resendCode"
                        class="text-xs font-bold text-slate-500 hover:text-sky-400 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        :disabled="form.processing || countdown > 0"
                    >
                        <span v-if="countdown > 0">Resend in {{ countdown }}s</span>
                        <span v-else>Resend Code</span>
                    </button>

                    <!-- Verify Button (Disabled when processing either action) -->
                    <button
                        type="submit"
                        class="bg-sky-400 hover:bg-sky-500 text-white text-sm font-semibold px-6 py-3 rounded-2xl shadow-lg shadow-sky-600/20 hover:shadow-sky-600/30 transition-all duration-200 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed"
                        :disabled="form.processing"
                    >
                        <span v-if="form.processing">Verifying...</span>
                        <span v-else>Verify Account</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>