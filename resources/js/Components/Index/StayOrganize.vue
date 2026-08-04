

<script setup>
import { ref } from 'vue';
import { useScrollIconText } from '../../composables/useScrollIconText';

/* ---- generic decorative icons (not brand logos) ---- */
const Icon1 = `<svg width="18" height="18" viewBox="0 0 18 18" fill="none"><rect x="1" y="1" width="6" height="6" rx="1.6" fill="#EF4444"/><rect x="11" y="1" width="6" height="6" rx="1.6" fill="#F59E0B"/><rect x="1" y="11" width="6" height="6" rx="1.6" fill="#10B981"/><rect x="11" y="11" width="6" height="6" rx="1.6" fill="#3B82F6"/></svg>`;
const Icon2 = `<svg width="18" height="18" viewBox="0 0 24 24" fill="none"><g stroke="#16A34A" stroke-width="2.2" stroke-linecap="round"><path d="M12 3V21"/><path d="M4.5 7.5L19.5 16.5"/><path d="M19.5 7.5L4.5 16.5"/></g></svg>`;
const Icon3 = `<svg width="18" height="18" viewBox="0 0 24 24" fill="none"><circle cx="9" cy="12" r="5" stroke="#1E3A8A" stroke-width="2"/><circle cx="15" cy="12" r="5" stroke="#1E3A8A" stroke-width="2"/></svg>`;
const Icon4 = `<svg width="16" height="10" viewBox="0 0 16 10" fill="none"><path d="M1 5C3 1 5 9 8 5C11 1 13 9 15 5" stroke="#1F2937" stroke-width="1.7" stroke-linecap="round"/></svg>`;
const Icon5 = `<svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M3 18L9 8L13 14L16 10L21 18H3Z" stroke="#2563EB" stroke-width="1.8" stroke-linejoin="round" stroke-linecap="round"/></svg>`;
const Icon6 = `<svg width="18" height="18" viewBox="0 0 24 24" fill="none"><g stroke="#F97316" stroke-width="2" stroke-linecap="round"><path d="M12 2V6"/><path d="M12 18V22"/><path d="M2 12H6"/><path d="M18 12H22"/><path d="M4.9 4.9L7.4 7.4"/><path d="M16.6 16.6L19.1 19.1"/><path d="M19.1 4.9L16.6 7.4"/><path d="M7.4 16.6L4.9 19.1"/></g><circle cx="12" cy="12" r="3" fill="#F97316"/></svg>`;
const Icon7 = `<svg width="20" height="14" viewBox="0 0 24 16" fill="none"><path d="M6 14C3.8 14 2 12.2 2 10C2 7.9 3.7 6.1 5.8 6C6.4 3.7 8.5 2 11 2C13.8 2 16.1 4.1 16.4 6.8C18.5 7.1 20 8.8 20 11C20 12.7 18.7 14 17 14H6Z" stroke="#6B7280" stroke-width="1.6" stroke-linejoin="round"/></svg>`;
const Icon8 = `<svg width="18" height="18" viewBox="0 0 24 24" fill="none"><rect x="3" y="4" width="18" height="3" rx="1.5" fill="#3B82F6"/><rect x="3" y="11" width="12" height="3" rx="1.5" fill="#3B82F6"/><rect x="3" y="18" width="15" height="3" rx="1.5" fill="#93C5FD"/></svg>`;
const Icon9 = `<svg width="18" height="18" viewBox="0 0 24 24"><path d="M12 2L21 9L12 13L3 9Z" fill="#F472B6"/><path d="M12 13L21 9L18 18L12 13Z" fill="#FB923C"/><path d="M12 13L3 9L6 18L12 13Z" fill="#C084FC"/></svg>`;
const Icon10 = `<svg width="18" height="18" viewBox="0 0 24 24"><path d="M12 2L21 7L12 12L3 7Z" fill="#FBBF24"/><path d="M12 12L21 7V17L12 22Z" fill="#F97316"/><path d="M12 12L3 7V17L12 22Z" fill="#FB7185"/></svg>`;

// Renamed these to lowercase to match your template requirements
const googleDot = `<svg width="14" height="14" viewBox="0 0 24 24"><path d="M12 2A10 10 0 0 1 22 12H12Z" fill="#4285F4"/><path d="M12 12L22 12A10 10 0 0 1 12 22Z" fill="#34A853"/><path d="M2 12A10 10 0 0 1 12 2V12Z" fill="#FBBC05"/><path d="M2 12A10 10 0 0 0 12 22V12Z" fill="#EA4335"/></svg>`;
const starDot = `<svg width="13" height="13" viewBox="0 0 24 24" fill="#00B67A"><path d="M12 2L14.9 8.6L22 9.3L16.7 14.1L18.2 21.2L12 17.6L5.8 21.2L7.3 14.1L2 9.3L9.1 8.6Z"/></svg>`;
const checkIcon = `<svg width="8" height="8" viewBox="0 0 24 24" fill="none"><path d="M5 13L9.5 17.5L19 7" stroke="white" stroke-width="3.4" stroke-linecap="round" stroke-linejoin="round"/></svg>`;
const dotsIcon = `<svg width="3" height="13" viewBox="0 0 4 16" fill="#D1D5DB"><circle cx="2" cy="2" r="1.7"/><circle cx="2" cy="8" r="1.7"/><circle cx="2" cy="14" r="1.7"/></svg>`;
const mailIcon = `<svg width="16" height="16" viewBox="0 0 24 24" fill="none"><rect x="3" y="5" width="18" height="14" rx="2.5" stroke="#9CA3AF" stroke-width="1.6"/><path d="M3.5 6.5L12 13L20.5 6.5" stroke="#9CA3AF" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>`;

const badges = [
  { top: '17%', left: '23%', size: 46, bg: 'bg-white', icon: Icon1, delay: '0.05s' },
  { top: '35%', left: '13%', size: 42, bg: 'bg-white', icon: Icon2, delay: '0.15s' },
  { top: '47%', left: '27%', size: 46, bg: 'bg-white', icon: Icon3, delay: '0.25s' },
  { top: '56%', left: '9%', size: 42, bg: 'bg-amber-400', icon: Icon4, delay: '0.35s' },
  { top: '70%', left: '28%', size: 42, bg: 'bg-white', icon: Icon5, delay: '0.45s' },
  { top: '17%', left: '77%', size: 46, bg: 'bg-white', icon: Icon6, delay: '0.1s' },
  { top: '35%', left: '87%', size: 46, bg: 'bg-white', icon: Icon7, delay: '0.2s' },
  { top: '47%', left: '73%', size: 42, bg: 'bg-white', icon: Icon8, delay: '0.3s' },
  { top: '56%', left: '91%', size: 46, bg: 'bg-white', icon: Icon9, delay: '0.4s' },
  { top: '71%', left: '79%', size: 46, bg: 'bg-white', icon: Icon10, delay: '0.5s' },
];

const logos = [
  { name: 'Google', cls: 'font-medium' },
  { name: 'Airbnb', cls: 'font-semibold' },
  { name: 'coinbase', cls: 'font-medium' },
  { name: 'Notion', cls: 'font-semibold' },
  { name: 'Gumroad', cls: 'italic font-medium' },
  { name: 'PayPal', cls: 'font-bold' },
  { name: 'Upwork', cls: 'italic font-medium lowercase' },
  { name: 'shopify', cls: 'font-semibold lowercase' },
  { name: 'stripe', cls: 'font-medium tracking-wide' },
  { name: 'zoom', cls: 'font-bold lowercase' },
];

// Note: In Vue 3 <script setup>, the keyword 'this' doesn't exist.
// Reference your `logos` variable directly.
const marqueeLogos = [...logos, ...logos];



const sectionRef = ref(null);

useScrollIconText(sectionRef, {
  start: "top 50%",
  end: "bottom 85%",
  scrub: 2,
});


</script>
<template>


  <section ref="sectionRef" class="min-h-screen w-full flex items-center justify-center p-3 sm:p-6 lg:p-10">
    <div
      class="relative w-full max-w-6xl bg-white rounded-[24px] sm:rounded-[32px] shadow-[0_4px_28px_rgba(0,0,0,0.06)] overflow-hidden">

      <!-- decorative concentric rings -->
      <div class="absolute inset-0 hidden md:block pointer-events-none overflow-hidden">
        <div v-for="s in [380, 560, 740, 920, 1100]" :key="s" class="absolute left-1/2 rounded-full border border-gray-100"
          :style="{ top: '235px', width: s + 'px', height: s + 'px', transform: 'translate(-50%, -50%)' }"></div>
        <svg class="absolute inset-0 w-full h-full" viewBox="0 0 1152 700" fill="none" preserveAspectRatio="none">
          <path d="M170 180 C 260 250, 300 320, 400 410" stroke="#93C5FD" stroke-width="1.2" opacity="0.55" />
          <path d="M982 180 C 892 250, 852 320, 752 410" stroke="#93C5FD" stroke-width="1.2" opacity="0.55" />
        </svg>
      </div>

      <!-- floating integration badges -->
      <div class="absolute inset-0 hidden lg:block pointer-events-none">
        <div v-for="(b, i) in badges" :key="i"
          :class="['badge-in absolute rounded-full shadow-md ring-1 ring-gray-100 flex items-center justify-center', b.bg]"
          :style="{ top: b.top, left: b.left, width: b.size + 'px', height: b.size + 'px', animationDelay: b.delay }"
          v-html="b.icon"></div>
      </div>

      <!-- main content -->
      <div class=" relative px-6 pt-14 pb-12 sm:pt-20 sm:pb-16 lg:pt-24 lg:pb-20">
        <div class="gsap-item max-w-xl mx-auto text-center">

          <!-- rating pills -->
          <div class="flex items-center justify-center gap-2.5 mb-7">
            <div
              class="gsap-text inline-flex items-center gap-1.5 bg-white border border-gray-200 rounded-full pl-2.5 pr-3 py-1.5 shadow-sm text-[13px] text-gray-500">
              <span v-html="googleDot"></span>
              <span class="text-gray-900 font-medium">4.6</span> Trusted
            </div>
            <div
              class="gsap-text inline-flex items-center gap-1.5 bg-white border border-gray-200 rounded-full pl-2.5 pr-3 py-1.5 shadow-sm text-[13px] text-gray-500">
              <span v-html="starDot"></span>
              <span class="text-gray-900 font-medium">4.9</span> Users
            </div>
          </div>

          <!-- headline -->
          <h1
            class="gsap-heading font-headingfont text-3xl sm:text-4xl lg:text-[44px] font-extrabold text-gray-900 tracking-tight leading-[1.15] mb-4">
            AI-powered tools to<br /> stay organized
          </h1>

          <!-- subtext -->
          <p class="gsap-para text-gray-500 text-sm sm:text-[15px] leading-relaxed max-w-md mx-auto mb-8">
            From small tasks to complex projects, manage everything in one place and keep your team moving forward.
          </p>

          <!-- CTAs -->
          <div class="gsap-text flex flex-col sm:flex-row items-center justify-center gap-3 mb-14">
            <button
              class=" cta-btn cursor-pointer bg-gray-900 hover:bg-gray-800 text-white text-sm font-medium rounded-full px-5 py-3 w-full sm:w-auto">Get
              started now</button>
          </div>


        </div>
        <div class="max-w-xl mx-auto text-center">
          <!-- notification card stack -->
          <div class="gsap-item relative lg:max-w-105 max-w-75 mx-auto mb-4">
            <div
              class="absolute -bottom-5 left-1/2 -translate-x-1/2 w-2/3 h-10 bg-blue-300/40 blur-2xl rounded-full -z-10">
            </div>

            <div class="gsap-text relative bg-white rounded-2xl shadow-md border border-gray-100 px-3.5 py-3 mx-3">
              <div class="flex items-start gap-2.5 text-left">
                <div class="relative flex-shrink-0">
                  <img src="https://i.pravatar.cc/80?img=8" class="w-9 h-9 rounded-full object-cover" alt="" />
                  <span
                    class="absolute -bottom-0.5 -right-0.5 w-3.5 h-3.5 rounded-full bg-blue-500 border-2 border-white flex items-center justify-center"
                    v-html="checkIcon"></span>
                </div>
                <div class="min-w-0 pt-0.5">
                  <p class="text-[13px] text-gray-800 leading-snug"><span class="font-semibold">Wei Chen</span> joined
                    to <span class="font-semibold">Final Presentation</span></p>
                  <p class="text-xs text-gray-400 mt-0.5">8 min ago · Orixcreative Dribbble</p>
                </div>
              </div>
            </div>

            <div
              class="gsap-text relative bg-white rounded-2xl shadow-lg border border-gray-100 px-3.5 pt-3 pb-1 -mt-3">
              <div class="flex items-start gap-2.5 text-left pb-2.5 border-b border-gray-100">
                <div class="relative flex-shrink-0">
                  <img src="https://i.pravatar.cc/80?img=45" class="w-9 h-9 rounded-full object-cover" alt="" />
                  <span
                    class="absolute -bottom-0.5 -right-0.5 w-3.5 h-3.5 rounded-full bg-emerald-500 border-2 border-white flex items-center justify-center"
                    v-html="checkIcon"></span>
                </div>
                <div class="min-w-0 flex-1 pt-0.5">
                  <p class="text-[13px] font-semibold text-gray-800">Matthew Johnson</p>
                  <p class="text-xs text-gray-400 mt-0.5">Content Writer · @orixcreative</p>
                </div>
                <span class="pt-1.5" v-html="dotsIcon"></span>
              </div>
              <div class="flex items-start gap-2.5 text-left py-2.5">
                <div
                  class="w-9 h-9 rounded-lg bg-white border border-gray-200 flex items-center justify-center flex-shrink-0"
                  v-html="mailIcon"></div>
                <div class="min-w-0 pt-0.5">
                  <p class="text-[13px] font-semibold text-gray-800">Terry Lipshutz</p>
                  <p class="text-xs text-gray-400 mt-0.5 truncate">Approved the design of the iOS app...</p>
                </div>
              </div>
            </div>
          </div>

        </div>

        <!-- trusted by -->
        <p class="text-center text-xs text-gray-400 mt-12 mb-6">Worked on 200,000+ website worldwide</p>
        <div class="relative marquee-mask overflow-hidden">
          <div class="flex items-center gap-10 w-max marquee-track">
            <span v-for="(logo, i) in marqueeLogos" :key="i"
              :class="['text-gray-400 text-base whitespace-nowrap shrink-0', logo.cls]">{{ logo.name }}</span>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
<style scoped>
@keyframes floatIn {
  from {
    opacity: 0;
    transform: translate(-50%, -50%) scale(0.6);
  }

  to {
    opacity: 1;
    transform: translate(-50%, -50%) scale(1);
  }
}

.badge-in {
  animation: floatIn 0.6s ease both;
}

.cta-btn {
  transition: background-color .2s ease, transform .15s ease;
}

.cta-btn:active {
  transform: scale(0.97);
}

@keyframes marquee {
  from {
    transform: translateX(0);
  }

  to {
    transform: translateX(-50%);
  }
}

.marquee-track {
  animation: marquee 24s linear infinite;
}

.marquee-track:hover {
  animation-play-state: paused;
}

.marquee-mask {
  -webkit-mask-image: linear-gradient(to right, transparent 0%, black 10%, black 90%, transparent 100%);
  mask-image: linear-gradient(to right, transparent 0%, black 10%, black 90%, transparent 100%);
}
</style>