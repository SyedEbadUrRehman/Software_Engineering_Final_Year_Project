<script setup>
import { ref  } from 'vue';
import { useScrollIconText } from '../../composables/useScrollIconText';
import { Link } from "@inertiajs/vue3";


const sectionRef = ref(null);

useScrollIconText(sectionRef, {
  start: "top 50%",
  end: "80% bottom",
  scrub: 2,
});

</script>
<template>
    <section ref="sectionRef" class="relative w-full overflow-hidden bg-white" id="using-siteclip">
        <div class="gsap-item relative max-w-7xl mx-auto px-6 pt-20 pb-28 sm:pt-24 sm:pb-32 lg:pt-28 lg:pb-36">

            <!-- Floating avatars: left side -->
            <div class="hidden lg:block">
                <!-- top avatar -->
                <div class="absolute left-[12%] top-[22%] avatar-pop" style="animation-delay:0.1s">
                    <img src="https://i.pravatar.cc/80?img=12" alt=""
                        class="w-14 h-14 rounded-full ring-4 ring-white shadow-md object-cover" />
                </div>
                <!-- speech bubble + avatar -->
                <div class="absolute left-[2%] top-[42%] flex items-end gap-2 avatar-pop" style="animation-delay:0.25s">
                    <img src="https://i.pravatar.cc/80?img=51" alt=""
                        class="w-12 h-12 rounded-full ring-4 ring-white shadow-md object-cover" />
                    <div
                        class="bg-white rounded-2xl rounded-bl-sm shadow-lg border border-gray-100 px-4 py-2.5 text-sm text-gray-700 whitespace-nowrap -translate-y-1">
                        Awesome! Let's jump in 🎉
                    </div>
                </div>
                <div class="absolute left-[20%] top-[50%] avatar-pop" style="animation-delay:0.4s">
                    <img src="https://i.pravatar.cc/80?img=33" alt=""
                        class="w-14 h-14 rounded-full ring-4 ring-white shadow-md object-cover" />
                </div>
                <!-- bottom avatar + dots -->
                <div class="absolute left-[9%] top-[60%] flex flex-col-reverse items-center avatar-pop"
                    style="animation-delay:0.55s">
                    <img src="https://i.pravatar.cc/80?img=14" alt=""
                        class="w-16 h-16 rounded-full ring-4 ring-white shadow-md object-cover" />
                    <div
                        class="bg-white ms-15 rounded-full shadow-md border border-gray-100 w-9 h-9 flex items-center justify-center text-gray-400">
                        <svg width="16" height="16" viewBox="0 0 16 4" fill="currentColor">
                            <circle cx="2" cy="2" r="2" />
                            <circle cx="8" cy="2" r="2" />
                            <circle cx="14" cy="2" r="2" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Floating avatars: right side -->
            <div class="hidden lg:block">
                <div class="absolute right-[14%] top-[20%] avatar-pop" style="animation-delay:0.15s">
                    <img src="https://i.pravatar.cc/80?img=68" alt=""
                        class="w-14 h-14 rounded-full ring-4 ring-white shadow-md object-cover" />
                </div>
                <div class="absolute right-[8%] top-[48%] flex items-end gap-2 avatar-pop" style="animation-delay:0.3s">
                    <img src="https://i.pravatar.cc/80?img=60" alt=""
                        class="w-12 h-12 rounded-full ring-4 ring-white shadow-md object-cover" />
                    <div
                        class="bg-white rounded-2xl mb-15 rounded-br-sm shadow-lg border border-gray-100 px-4 py-2.5 text-sm text-gray-700 whitespace-nowrap -translate-y-1">
                        Looks great 👍
                    </div>
                </div>
                <div class="absolute right-[2%] top-[24%] avatar-pop" style="animation-delay:0.45s">
                    <img src="https://i.pravatar.cc/80?img=15" alt=""
                        class="w-14 h-14 rounded-full ring-4 ring-white shadow-md object-cover" />
                </div>
                <div class="absolute right-[5%] top-[55%] avatar-pop" style="animation-delay:0.6s">
                    <img src="https://i.pravatar.cc/80?img=32" alt=""
                        class="w-16 h-16 rounded-full ring-4 ring-white shadow-md object-cover" />
                </div>
            </div>

            <!-- Center content -->
            <div class="relative z-10 flex flex-col items-center text-center max-w-2xl mx-auto">
                <!-- Badge -->
                <div
                    class="gsap-text inline-flex items-center gap-2 bg-gray-50 border border-gray-200 rounded-full pl-1.5 pr-4 py-1.5 mb-8 text-sm text-gray-600">
                    <span
                        class="bg-orange-500 text-white w-5 h-5 rounded-full flex items-center justify-center text-[11px] font-bold">Y</span>
                    Backed by Browser Extention
                </div>

                <!-- Heading -->
                <h1
                    class="gsap-heading font-headingfont text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight text-gray-900 leading-[1.1] mb-6">
                    Built around how your<br class="hidden sm:block" />
                    team actually works
                </h1>

                <!-- Subtext -->
                <p class="gsap-para text-gray-500 text-base sm:text-lg leading-relaxed mb-9 max-w-xl">
                    Built around how your team actually works. Stop forcing your process into rigid templates. Our
                    automation adapts to your team's real habits.
                </p>

                <!-- CTAs -->
                <div class="gsap-text flex flex-col sm:flex-row items-center gap-3">
                    <Link :href="route('home.index')"
                        class="btn btn-primary">
                      Lets Collaborate
                    </Link>
                   
                </div>
            </div>

            <!-- Mobile avatars row (shown only on small screens, simplified) -->
            <div class="flex lg:hidden justify-center items-center gap-3 mt-5">
                <img src="https://i.pravatar.cc/80?img=12" alt=""
                    class="w-10 h-10 rounded-full ring-4 ring-white shadow-md object-cover -mr-5" />
                <img src="https://i.pravatar.cc/80?img=51" alt=""
                    class="w-10 h-10 rounded-full ring-4 ring-white shadow-md object-cover -mr-5" />
                <img src="https://i.pravatar.cc/80?img=33" alt=""
                    class="w-10 h-10 rounded-full ring-4 ring-white shadow-md object-cover -mr-5" />
                <img src="https://i.pravatar.cc/80?img=68" alt=""
                    class="w-10 h-10 rounded-full ring-4 ring-white shadow-md object-cover -mr-5" />
                <img src="https://i.pravatar.cc/80?img=60" alt=""
                    class="w-10 h-10 rounded-full ring-4 ring-white shadow-md object-cover" />
            </div>
        </div>
    </section>




</template>

<style scoped>
.avatar-pop {
    animation: pop 0.5s ease both;
}

@keyframes pop {
    from {
        opacity: 0;
        transform: scale(0.7) translateY(10px);
    }

    to {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}

</style>
