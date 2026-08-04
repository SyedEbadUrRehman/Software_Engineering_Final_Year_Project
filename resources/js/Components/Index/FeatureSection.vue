<style scoped>
.dot-grid {
    background-image: radial-gradient(circle, #d4d4d8 1px, transparent 1px);
    background-size: 14px 14px;
}

.read-more svg {
    transition: transform 0.2s ease;
}

.read-more:hover svg {
    transform: translateX(3px);
}

</style>




<script setup>
import { ref } from 'vue';
import { useScrollIconText } from '../../composables/useScrollIconText';

const sectionRef = ref(null);

useScrollIconText(sectionRef, {
start: "top 50%",
end: "bottom 85%",
  scrub: 2,
},
[{
    indexNo:0,
    start:"top 70%",
    end: "top 10%"
}
]);


const arrowIcon = `<svg width="14" height="14" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3.5 8H12.5M12.5 8L8.5 4M12.5 8L8.5 12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>`;



const cards = [
    {
        title: 'Network with Members',
        desc: 'Share your ideas and boost your collaboration with info that gets responses.',
    },
    {
        title: 'Manage resources',
        desc: 'Take actions on top of all your web-based documents, applications and media',
    },
    {
        title: 'Send outreach',
        desc: "Create genuine, down-to-earth outreach that feels like it's coming from a real person.",
    },
];



</script>
<template>


    <section ref="sectionRef" class="relative w-full bg-white overflow-hidden py-20 px-6">
        <!-- decorative gradient blob -->
        <div
            class="pointer-events-none absolute -top-24 -left-24 w-96 h-96 rounded-full bg-gradient-to-br from-gray-200/60 to-transparent blur-3xl">
        </div>

        <div class="relative max-w-6xl mx-auto">
            <!-- Heading -->
            <div class="gsap-item text-center max-w-2xl mx-auto mb-14">
                <h2 class="gsap-heading font-headingfont text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight leading-[1.15] mb-4">
                    One simple workflow for all<br class="hidden sm:block" /> your outbound moves
                </h2>
                <p class="gsap-para text-gray-500 text-sm sm:text-[15.1px] leading-relaxed">
                    The process revolves around a simple, effective cycle: first, identifying a promising lead, and then
                    securing a meeting to move things forward.
                </p>
            </div>

            <!-- Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">

                <!-- Card 1: Find contacts -->
                <div class="gsap-item bg-gray-50 rounded-2xl p-5 flex flex-col border border-gray-100/80 h-full">
                    <div class="relative h-48 mb-2 rounded-xl overflow-hidden">
                        <div class="absolute inset-0 dot-grid opacity-70"></div>
                        <div class="absolute top-2 left-8 w-20 h-20 rounded-full bg-gray-200/60 blur-2xl"></div>
                        <div class="absolute bottom-0 right-4 w-24 h-24 rounded-full bg-gray-200/60 blur-2xl"></div>

                        <div class="relative w-[200px] h-[176px] mx-auto">
                            <img src="https://i.pravatar.cc/100?img=13"
                                class="gsap-icon absolute top-0 left-3 w-12 h-12 rounded-full ring-4 ring-white object-cover shadow-sm"
                                alt="" />
                            <img src="https://i.pravatar.cc/100?img=14"
                                class="gsap-icon absolute top-0 right-1 w-14 h-14 rounded-full ring-4 ring-white object-cover shadow-sm"
                                alt="" />
                            <img src="https://i.pravatar.cc/100?img=15"
                                class="gsap-icon absolute top-16 left-0 w-11 h-11 rounded-full ring-4 ring-white object-cover shadow-sm"
                                alt="" />
                            <img src="https://i.pravatar.cc/100?img=16"
                                class="gsap-icon absolute top-9 left-1/2 -translate-x-1/2 w-[72px] h-[72px] rounded-full ring-4 ring-white object-cover shadow-md z-10"
                                alt="" />
                            <img src="https://i.pravatar.cc/100?img=17"
                                class="gsap-icon absolute -bottom-3 left-10 w-14 h-14 rounded-full ring-4 ring-white object-cover shadow-sm"
                                alt="" />
                            <img src="https://i.pravatar.cc/100?img=18"
                                class="gsap-icon absolute bottom-1 right-0 w-12 h-12 rounded-full ring-4 ring-white object-cover shadow-sm"
                                alt="" />
                        </div>
                    </div>

                    <div class="border-t border-gray-200 pt-4 mt-auto">
                        <h3 class=" gsap-text font-semibold text-gray-900 mb-1.5">{{ cards[0].title }}</h3>
                        <p class=" gsap-text text-sm text-gray-500 leading-relaxed mb-4">{{ cards[0].desc }}</p>
                        <a href="#"
                            class=" gsap-text read-more inline-flex items-center gap-1.5 text-sm font-medium text-gray-900">
                            Read More <span v-html="arrowIcon"></span>
                        </a>
                    </div>
                </div>

                <!-- Card 2: Enrich data -->
                <div class="gsap-item bg-gray-50 rounded-2xl p-5 flex flex-col border border-gray-100/80 h-full">
                    <div class="gsap-icon bg-white rounded-xl border border-gray-100 shadow-sm px-3.5 mb-6">

                        <div class="flex items-start gap-2.5 py-3 border-b border-gray-100">
                            <img src="https://i.pravatar.cc/100?img=33"
                                class="gsap-text w-8 h-8 rounded-full object-cover mt-0.5 flex-shrink-0" alt="" />
                            <div class="min-w-0 pt-0.5">
                                <p class=" gsap-text text-[13px] text-gray-800 leading-snug"><span class="font-semibold">Wei
                                        Chen</span> joined to <span class="font-semibold">Final Presentation</span></p>
                                <p class=" gsap-text text-xs text-gray-400 mt-1">8 min ago · @divitheam</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-2.5 py-3 border-b border-gray-100">
                            <div
                                class="gsap-text w-8 h-8 rounded-lg bg-white border border-gray-200 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                                    <path d="M3 6.5L12 13L21 6.5" stroke="#EA4335" stroke-width="1.6"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                    <rect x="3" y="5" width="18" height="14" rx="2" stroke="#9CA3AF"
                                        stroke-width="1.4" />
                                </svg>
                            </div>
                            <div class="min-w-0 pt-0.5">
                                <p class=" gsap-text text-[13px] font-semibold text-gray-800">Terry Lipshutz</p>
                                <p class=" gsap-text text-xs text-gray-400 mt-1 truncate">Approved the design of the iOS app...</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-2.5 py-3">
                            <img src="https://i.pravatar.cc/100?img=53"
                                class="gsap-text w-8 h-8 rounded-full object-cover mt-0.5 flex-shrink-0" alt="" />
                            <div class="min-w-0 pt-0.5">
                                <p class=" gsap-text text-[13px] font-semibold text-gray-800">Matthew Johnson</p>
                                <p class=" gsap-text text-xs text-gray-400 mt-1">Content Writer · @fiftyworld</p>
                            </div>
                        </div>

                    </div>

                    <div class=" border-t border-gray-200 pt-4 mt-auto">
                        <h3 class="gsap-text font-semibold text-gray-900 mb-1.5">{{ cards[1].title }}</h3>
                        <p class="gsap-text text-sm text-gray-500 leading-relaxed mb-4">{{ cards[1].desc }}</p>
                        <a href="#"
                            class="gsap-text read-more inline-flex items-center gap-1.5 text-sm font-medium text-gray-900">
                            Read More <span v-html="arrowIcon"></span>
                        </a>
                    </div>
                </div>

                <!-- Card 3: Send outreach -->
                <div class="gsap-item bg-gray-50 rounded-2xl p-5 flex flex-col border border-gray-100/80 h-full">
                    <div class="gsap-icon bg-white rounded-xl border border-gray-100 shadow-sm p-4 mb-6 text-left">

                        <p class="text-[10px] font-semibold tracking-wider text-gray-400 uppercase mb-2.5">Payment
                            Method</p>

                        <div class="flex items-center gap-1 text-[13px] font-medium text-gray-700 mb-2">
                            Select Payment Method
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" class="text-gray-300">
                                <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.6" />
                                <path d="M12 11V16.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" />
                                <circle cx="12" cy="8" r="0.9" fill="currentColor" />
                            </svg>
                        </div>

                        <div class="flex items-center justify-between border border-gray-200 rounded-lg px-3 py-2 mb-2">
                            <span class="text-sm text-gray-700">Wire</span>
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" class="text-gray-400">
                                <path d="M6 9L12 15L18 9" stroke="currentColor" stroke-width="1.8"
                                    stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </div>

                        <p class="text-[11px] text-gray-400 mb-3">Same-day transfer, no fees.</p>

                        <div class="border-t border-gray-100 pt-3">
                            <p class="text-[10px] font-semibold tracking-wider text-gray-400 uppercase mb-2.5">
                                Recipient's Bank Details</p>
                            <div class="flex items-center justify-between gap-2">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div
                                        class="w-8 h-8 rounded-md bg-gray-50 border border-gray-100 flex items-center justify-center shrink-0">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
                                            class="text-gray-500">
                                            <path d="M3 10L12 4L21 10" stroke="currentColor" stroke-width="1.6"
                                                stroke-linecap="round" stroke-linejoin="round" />
                                            <path d="M5 10V19M9.5 10V19M14.5 10V19M19 10V19" stroke="currentColor"
                                                stroke-width="1.6" stroke-linecap="round" />
                                            <path d="M3 19H21" stroke="currentColor" stroke-width="1.6"
                                                stroke-linecap="round" />
                                        </svg>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-xs font-semibold text-gray-800 truncate">Summit Finance
                                            International</p>
                                        <p class="text-[11px] text-gray-400">Account ••9876 · Routing ••5432</p>
                                    </div>
                                </div>
                                <a href="#" class="text-xs text-blue-600 font-medium shrink-0">Edit</a>
                            </div>
                        </div>

                    </div>

                    <div class="border-t border-gray-200 pt-4 mt-auto">
                        <h3 class="gsap-text  font-semibold text-gray-900 mb-1.5">{{ cards[2].title }}</h3>
                        <p class="gsap-text text-sm text-gray-500 leading-relaxed mb-4">{{ cards[2].desc }}</p>
                        <a href="#"
                            class="gsap-text read-more inline-flex items-center gap-1.5 text-sm font-medium text-gray-900">
                            Read More <span v-html="arrowIcon"></span>
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </section>

</template>