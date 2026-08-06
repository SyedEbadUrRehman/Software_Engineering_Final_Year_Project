<script setup>
import { computed, ref, onUnmounted } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    status: String,
});

const form = useForm({});

// Local status/error states, cooldown timer, and timeout reference for hiding
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
    if (hideTimeout) clearTimeout(hideTimeout);
    resendStatus.value = "";
    resendError.value = "";

    form.post(route('verification.send'), {
        preserveScroll: true,
        onSuccess: () => {
            resendStatus.value = "A new verification link has been sent to your email address.";
            startCooldown();

            hideTimeout = setTimeout(() => {
                resendStatus.value = "";
            }, 3000);
        },
        onError: () => {
            resendError.value = "Failed to resend verification link. Please try again.";

            hideTimeout = setTimeout(() => {
                resendError.value = "";
            }, 3000);
        },
    });
};

const verificationLinkSent = computed(() => props.status === 'verification-link-sent');

onUnmounted(() => {
    if (timer) clearInterval(timer);
    if (hideTimeout) clearTimeout(hideTimeout);
});
</script>

<template>
    <Head title="Email Verification" />

    <!-- Clean, Crisp White & Ocean Blue Aesthetic matching previous screens -->
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
            <!-- Ocean Blue Minimal Logo / Icon Link -->
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
                Email Verification
            </h2>
            
            <p class="text-sm text-slate-500 mb-8 leading-relaxed">
                Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn't receive the email, we will gladly send you another.
            </p>

            <!-- Status Alert (Success from props or local resend) -->
            <div
                v-if="verificationLinkSent || resendStatus"
                class="mb-6 font-medium text-xs text-emerald-700 bg-emerald-50 border border-emerald-200/60 p-3.5 rounded-xl text-center shadow-sm transition-all duration-300"
            >
                {{ resendStatus || 'A new verification link has been sent to the email address you provided during registration.' }}
            </div>

            <!-- Error Alert -->
            <div
                v-if="resendError"
                class="mb-6 font-medium text-xs text-rose-700 bg-rose-50 border border-rose-200/60 p-3.5 rounded-xl text-center shadow-sm transition-all duration-300"
            >
                {{ resendError }}
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <div class="flex items-center justify-between pt-2">
                    <!-- Logout Link -->
                    <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="text-xs font-bold text-slate-500 hover:text-sky-400 transition-colors"
                    >
                        Log Out
                    </Link>

                    <!-- Resend Button with Cooldown & Processing State -->
                    <button
                        type="submit"
                        class="bg-sky-400 hover:bg-sky-500 text-white text-sm font-semibold px-6 py-3 rounded-2xl shadow-lg shadow-sky-600/20 hover:shadow-sky-600/30 transition-all duration-200 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed"
                        :disabled="form.processing || countdown > 0"
                    >
                        <span v-if="form.processing">Sending...</span>
                        <span v-else-if="countdown > 0">Resend in {{ countdown }}s</span>
                        <span v-else>Resend Verification Email</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>