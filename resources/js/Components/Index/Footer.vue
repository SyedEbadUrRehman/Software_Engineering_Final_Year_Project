<script setup>
import { ref, onMounted } from "vue";
import { useScrollIconText } from "../../composables/useScrollIconText";
import { Link } from "@inertiajs/vue3";
import gsap from "gsap";

const sectionRef = ref(null);
const circularRef = ref(null);

useScrollIconText(sectionRef, {
    start: "top 50%",
    end: "80% bottom",
    scrub: 2,
});

const icons = [
    { src: "/icons/calander.png", cls: "left-[3%] top-[30%] rotate-[-8deg]" },
    { src: "/icons/check.png", cls: "left-[18%] top-[12%] rotate-[-12deg]" },
    { src: "/icons/flag.png", cls: "left-[12%] top-[55%] rotate-[12deg]" },
    { src: "/icons/stopwatch.png", cls: "left-[36%] top-[38%] rotate-[8deg]" },

    { src: "/icons/arrow.png", cls: "left-[50%] top-[10%]" },
    { src: "/icons/dates.png", cls: "left-[60%] top-[56%] rotate-[-10deg]" },

    {
        src: "/icons/sandwatch.png",
        cls: "right-[24%] top-[12%] rotate-[-5deg]",
    },
    { src: "/icons/bulb.png", cls: "right-[18%] top-[45%] rotate-[10deg]" },
    { src: "/icons/clock.png", cls: "right-[4%] top-[15%] rotate-[15deg]" },
    { src: "/icons/arrow.png", cls: "right-[2%] top-[52%]" },
];

onMounted(() => {
    if (!circularRef.value) {
        return;
    }

    const masker = circularRef.value.querySelector("#masker");
    const rotateGroup = circularRef.value.querySelector("svg");

    if (masker) {
        gsap.to(masker, {
            duration: 2,
            attr: { r: 200 },
            ease: "power1.inOut",
        });
    }

    if (rotateGroup) {
        gsap.to(rotateGroup, {
            duration: 10,
            rotation: -360,
            ease: "none",
            repeat: -1,
        });
    }
});
const scrollToTop = () => {
   window.scrollTo({ top: 0, behavior: "smooth",duration: 1.2 });
};

</script>

<template>
    <section
        ref="sectionRef"
        class="relative overflow-hidden bg-[#fafafa] border-t border-gray-100"
    >
        <div class="m-5">
            <Link :href="route('home.index')">
                <img class="h-16" width="200" src="/SiteClipLogo.png" />
            </Link>
        </div>

        <div
            class="relative max-w-7xl mx-auto sm:min-h-[350px] px-6 flex items-center justify-center"
        >
            <!-- Floating Icons -->
            <div
                v-for="(icon, i) in icons"
                :key="i"
                class="absolute hidden lg:flex floating"
                :class="icon.cls"
            >
                <div
                    class="w-16 h-16 rounded-2xl bg-white shadow-lg border border-gray-100 flex items-center justify-center"
                >
                    <img :src="icon.src" class="w-14 h-14 object-contain" />
                </div>
            </div>
        </div>
        <!-- <div class="flex justify-between w-full "> -->
            <p
                class="ml-3 md:-mt-10 mb-3 text-lg text-gray-500 leading-8 max-w-2xl"
            >
                Stop forcing your workflow into rigid templates. Our automation
                adapts to your team's habits and helps everyone collaborate
                naturally. <br>
                <a :href="route('extension.download')"  target="blank" class="hover:text-black  text-blue-400 transition"
                    >Get free Extension </a>
            </p>

            <div 
                id="circular-motion-area"
                @click="scrollToTop()"
                ref="circularRef"
                class="relative overflow-hidden cursor-pointer rounded-3xl md:ml-auto md:-mt-[130px] p-6 flex items-center justify-center"
            >
                <svg
                    viewBox="0 0 400 400"
                    xmlns="http://www.w3.org/2000/svg"
                    class="max-w-[200px] h-auto"
                >
                    <defs>
                        <path
                            id="somePath"
                            fill="none"
                            stroke="black"
                            d="M200,50c82.8,0,150,67.2,150,150s-67.2,150-150,150S50,282.8,50,200S117.2,50,200,50z"
                        />
                        <clipPath id="theClipPath">
                            <circle
                                id="masker"
                                r="0"
                                fill="purple"
                                cx="200"
                                cy="200"
                            />
                        </clipPath>
                    </defs>
                    <g
                        id="clipPathReveal"
                        class="rotate-group"
                        clip-path="url(#theClipPath)"
                    >
                        <circle
                            r="100"
                            fill="transparent"
                            stroke="red"
                            cx="200"
                            cy="200"
                        />

                        <text class="circular-text">
                            <textPath href="#somePath">
                                Round and round we go. siteclip.com is a
                                really cool website.
                            </textPath>
                        </text>
                    </g>
                </svg>
            </div>
        
        <!-- </div> -->
        <footer
            class="border-t border-gray-200 py-6 px-6 max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between text-sm text-gray-500"
        >
            <p>© 2026. All rights reserved.</p>

            <div class="flex gap-8 mt-4 md:mt-0">
                <a :href="route('contact.index')" class="hover:text-black transition"
                    >Contact Us</a
                >
                <a :href="route('privacy.policy')" class="hover:text-black transition"
                    >Privacy Policy</a
                >
                <a :href="route('privacy.policy')" class="hover:text-black transition"
                    >Terms of Service</a
                >
            </div>
        </footer>
    </section>
</template>

<style scoped>
.floating {
    animation: float 5s ease-in-out infinite;
}

.floating:nth-child(2n) {
    animation-duration: 6s;
}

.floating:nth-child(3n) {
    animation-duration: 7s;
}

@keyframes float {
    0%,
    100% {
        transform: translateY(0px);
    }
    50% {
        transform: translateY(-12px);
    }
}

#circular-motion-area {
    max-height: 200px;
    max-width: 300px;
    
}

.circular-text {
    font-size: 24px;
    fill: black;
    font-weight: bold;
}


</style>
