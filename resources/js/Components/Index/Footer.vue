<script setup>
import { ref } from "vue";
import { useScrollIconText } from "../../composables/useScrollIconText";
import { Link } from "@inertiajs/vue3";
const sectionRef = ref(null);

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

  { src: "/icons/sandwatch.png", cls: "right-[24%] top-[12%] rotate-[-5deg]" },
  { src: "/icons/bulb.png", cls: "right-[18%] top-[45%] rotate-[10deg]" },
  { src: "/icons/clock.png", cls: "right-[4%] top-[15%] rotate-[15deg]" },
  { src: "/icons/arrow.png", cls: "right-[2%] top-[52%]" },
];
</script>

<template>
  <section
    ref="sectionRef"
    class="relative overflow-hidden bg-[#fafafa] border-t border-gray-100"
  >
    <div class="m-5">
      <Link :href="route('home.index')">
            <img
                class="h-16"
                width="200"
                src="/SiteClipLogo.png"
            />
        </Link>
    </div>
    <div
      class="relative max-w-7xl mx-auto min-h-[350px] px-6 flex items-center justify-center"
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
    <p class="ml-3 -mt-10 mb-3 text-lg text-gray-500 leading-8 max-w-2xl">
      Stop forcing your workflow into rigid templates. Our automation adapts to
      your team's habits and helps everyone collaborate naturally.
    </p>
    <!-- Footer -->
    <footer
      class="border-t border-gray-200 py-6 px-6 max-w-7xl mx-auto flex items-center justify-between text-sm text-gray-500"
    >
      <p>© 2026. All rights reserved.</p>

      <div class="flex gap-8">
        <a href="#" class="hover:text-black transition"> Privacy Policy </a>

        <a href="#" class="hover:text-black transition"> Terms of Service </a>
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
</style>
