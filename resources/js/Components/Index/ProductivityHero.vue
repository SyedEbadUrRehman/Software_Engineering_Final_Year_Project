
<script setup>
import { h,ref } from 'vue'
import FolderIntegrationCard from './FolderIntegrationCard.vue'
import { useScrollIconText } from '../../composables/useScrollIconText';
// tiny inline clock icon used inside the reminder card
const ClockIcon = () =>
  h(
    'svg',
    { viewBox: '0 0 24 24', width: 12, height: 12, xmlns: 'http://www.w3.org/2000/svg' },
    [
      h('circle', { cx: 12, cy: 12, r: 9, fill: 'none', stroke: '#2f6fed', 'stroke-width': 2 }),
      h('path', { d: 'M12 7v5l4 2', fill: 'none', stroke: '#2f6fed', 'stroke-width': 2, 'stroke-linecap': 'round' })
    ]
  )

// gentle per-prop drift, staggered so the desk feels alive but calm
function floatStyle(index) {
  return {
    '--delay': `${index * 0.45}s`,
    '--dur': `${6 + (index % 3)}s`
  }
}


const sectionRef = ref(null);

useScrollIconText(sectionRef, {
  start: "top 70%",
  end: "bottom bottom",
  scrub: 2,
});
</script>



<template>
  <section class="hero " ref="sectionRef">
    <div class="grid-bg " aria-hidden="true"></div>

    <div class="stage gsap-item">
      <!-- Sticky note -->
      <div class="prop sticky-note rounded-none " :style="floatStyle(0)">
        <span class="pin " aria-hidden="true"></span>
        <p class="gsap-para font-handwriting">Take notes to keep track of crucial details, and accomplish more tasks with ease.</p>
      </div>

      <!-- Checklist app icon -->
      <div class="folder chkFolder">
        <div class="prop tile checklist" :style="floatStyle(1)">
          <svg viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
            <rect x="4" y="4" width="40" height="40" rx="9" fill="#2f6fed" />
            <path d="M14 24.5l6 6 14-14" stroke="#ffffff" stroke-width="4.5" fill="none" stroke-linecap="round"
              stroke-linejoin="round" />
          </svg>
        </div>
      </div>


      <!-- App grid / dial icon -->
      <div class="prop tile dial" :style="floatStyle(2)">
        <svg viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
          <circle cx="16" cy="16" r="6" fill="#0ea5e9" />
          <circle cx="32" cy="16" r="6" fill="#1f2330" />
          <circle cx="16" cy="32" r="6" fill="#1f2330" />
          <circle cx="32" cy="32" r="6" fill="#1f2330" />
        </svg>
      </div>

      <!-- Stopwatch icon -->
      <div class="prop tile stopwatch" :style="floatStyle(3)">
        <svg viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
          <rect x="19" y="3" width="10" height="5" rx="2" fill="#1f2330" />
          <circle cx="24" cy="27" r="16" fill="none" stroke="#1f2330" stroke-width="3" />
          <path d="M24 27V17" stroke="#1f2330" stroke-width="3" stroke-linecap="round" />
          <path d="M33 12l3-3" stroke="#1f2330" stroke-width="3" stroke-linecap="round" />
        </svg>
      </div>

      <!-- Tilted reminder cards stack -->
      <div class="prop reminder-stack" :style="floatStyle(4)">
      
        <div class="folder rcard front">
          <span class="rcard-title">Reminders</span>
          <span class="rcard-sub">Purchase Subscription</span>
          <span class="rcard-detail">Call with marketing team</span>
          <span class="rcard-time">
            <ClockIcon /> 13:00 – 13:45
          </span>
        </div>
          <div class="folder rcard back">
          <!-- <span class="rcard-title">Meetings</span> -->
        </div>
      </div>

      <!-- Folder integration card (provided component) -->
      <div class="prop folder-slot" :style="floatStyle(5)">
        <FolderIntegrationCard />
      </div>
    </div>

    <div class="copy gsap-item">
      <h1 class="font-headingfont">
        <span class="line strong gsap-heading">Think, plan, and track</span>
        <span class="line soft gsap-heading">all in one place</span>
      </h1>
      <p class="subtext gsap-para">Efficiently manage your ideas and boost productivity.</p>
      <div class="cta-row gsap-text">
        <button class="btn primary">Get started free</button>
        <!-- <button class="btn ghost">See how it works</button> -->
      </div>
    </div>
  </section>
</template>

<style scoped>
.hero {
  --bg: #eceef1;
  --ink: #1f2330;
  --ink-soft: #c7cad1;
  --paper: #ffffff;
  --note-yellow: #FFF088;
  --note-yellow-edge: #f3df8c;
  --accent-blue: #2f6fed;
  --shadow-soft: rgba(30, 30, 60, 0.14);

  position: relative;
  min-height: 100vh;
  background: var(--bg);
  overflow: hidden;
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 80px 24px 96px;
}

.folder {
  --folder-bg: #f4f4f6;
  --text-dark: #1f2330;
  --tile-bg: #ffffff;
  --tile-shadow: rgba(20, 20, 40, 0.10);
  --card-shadow: rgba(30, 30, 60, 0.14);

  position: relative;
  width: 320px;
  height: 210px;
  background: var(--folder-bg);
  border-radius: 0px 18px 18px 18px;
  box-shadow:
    0 30px 60px -15px var(--card-shadow),
    0 10px 20px -10px rgba(30, 30, 60, 0.10);
}

.folder::before {
  content: "";
  position: absolute;
  top: -22px;
  left: 0px;
  width: 90px;
  height: 28px;
  background: var(--folder-bg);
  border-radius: 12px 12px 0 0;
}

.chkFolder {
  transform: rotate(20deg);
  opacity: 0.8;
  width: 200px;
  height: 130px;
  top: 35%;
  left: 0;
}

.grid-bg {
  position: absolute;
  inset: 0;
  background-image: radial-gradient(circle, rgba(31, 35, 48, 0.14) 1.4px, transparent 1.4px);
  background-size: 22px 22px;
  mask-image: radial-gradient(ellipse 70% 60% at 50% 30%, black 40%, transparent 85%);
  pointer-events: none;
}

/* ---------- Stage holding floating props ---------- */
.stage {
  position: relative;
  width: 100%;
  max-width: 1180px;
  height: 340px;
  margin-bottom: 8px;
}

.prop {
  position: absolute;
  animation: drift var(--dur, 7s) ease-in-out var(--delay, 0s) infinite;
}

@keyframes drift {

  0%,
  100% {
    transform: translateY(0) rotate(var(--rot, 0deg));
  }

  50% {
    transform: translateY(-8px) rotate(var(--rot, 0deg));
  }
}

@media (prefers-reduced-motion: reduce) {
  .prop {
    animation: none;
  }
}

/* Sticky note */
.sticky-note {
  --rot: 6deg;
  top: 0;
  left: 8%;
  width: 200px;
  height: 230px;
  background: var(--note-yellow);
  border: 1px solid var(--note-yellow-edge);
  /* border-radius: 4px 4px 24px 4px; */
  padding: 22px 18px 26px;
  box-shadow: 0 18px 36px -12px var(--shadow-soft);
  transform: rotate(-4deg);
 
}

.sticky-note p {
  margin: 0;
  font-size: 18.5px;
  line-height: 1.5;
  font-weight: 500;
  color: #000000;
  /* transform: rotate(2deg); */
   /* font-family: 'Chiller' !important; */
   
}

.pin {
  position: absolute;
  top: -8px;
  left: 50%;
  transform: translateX(-50%);
  width: 12px;
  height: 12px;
  border-radius: 50%;
  background: radial-gradient(circle at 35% 30%, #ff7a6e, #c81e3a);
  box-shadow: 0 3px 5px rgba(0, 0, 0, 0.25);
}

.pin::after {
  content: "";
  position: absolute;
  top: 10px;
  left: 50%;
  width: 2px;
  height: 16px;
  background: #b4b8c0;
  transform: translateX(-50%);
}

/* Generic square tiles */
.tile {
  width: 64px;
  height: 64px;
  background: var(--paper);
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
box-shadow: 9px 13px 22px 9px rgb(39 39 40 / 14%);
}

.tile svg {
  width: 36px;
  height: 36px;
}

.checklist {
  --rot: -2deg;
  bottom: 20px;
  left: 45%;
}

.dial {
  --rot: 0deg;
  top: 28px;
  left: 47%;
}

.stopwatch {
  --rot: 3deg;
  top: 96px;
    right: 12%;
  width: 56px;
  height: 56px;
  z-index: 5;
}

.stopwatch svg {
  width: 30px;
  height: 30px;
}

/* Reminder card stack */
.reminder-stack {
  top: -8px;
  left: 86%;
  width: 168px;
  height: 130px;
}

.rcard {
  position: absolute;
  width: 150px;
  background: var(--paper);
 border-radius: 18px ;
  box-shadow: 0 14px 26px -8px var(--shadow-soft);
  padding: 14px 16px;
}

.rcard.back {
     top: 120px;
    left: 85px;
    transform: rotate(166deg);
  height: 110px;
  font-size: 12px;
  font-weight: 600;
 opacity: 0.8;
}

.rcard.front {
  top: 18px;
  left: -6px;
  transform: rotate(-6deg);
  display: flex;
  flex-direction: column;
  align-items: self-start;
  gap: 4px;
  height: 160px;
  width: 200px;
  background-color: #fff;
  border-radius: 0 18px 18px;
  box-shadow: 0 2px 5px #2c2828b2;
}
.front.rcard.folder::before {
  background:#fff;
   box-shadow: -1px -1px 0px #2c282821;
}
.back.rcard.folder::before {
  width: 0;
}

.rcard-title {
  font-size: 13px;
  font-weight: 700;
  color: var(--ink);
}

.rcard-sub {
  font-size: 11.5px;
  font-weight: 600;
  color: var(--ink);
  margin-top: 2px;
}

.rcard-detail {
  font-size: 10.5px;
  color: #8a8f9c;
}

.rcard-time {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  margin-top: 4px;
  font-size: 10.5px;
  font-weight: 600;
  color: var(--accent-blue);
}

/* Folder integration slot */
.folder-slot {
  --rot: 3deg;
     bottom: -40%;
    right: 2%;
  transform: scale(0.58) rotate(2deg);
  transform-origin: top left;
}

/* ---------- Copy ---------- */
.copy {
  position: absolute;
  z-index: 2;
  top:50%;
  text-align: center;
  max-width: 720px;
}

h1 {
  margin: 0 0 18px;
  font-weight: 700;
  letter-spacing: -0.01em;
  line-height: 1.15;
}

h1 .line {
  display: block;
  font-size: clamp(34px, 5vw, 52px);
}

h1 .strong {
  color: var(--ink);
}

h1 .soft {
  color: var(--ink-soft);
}

.subtext {
  margin: 0 0 32px;
  font-size: 17px;
  color: #6b7080;
}

.cta-row {
  display: flex;
  justify-content: center;
  gap: 14px;
}

.btn {
  font-size: 15px;
  font-weight: 600;
  padding: 13px 26px;
  border-radius: 12px;
  border: none;
  cursor: pointer;
  transition: transform 0.15s ease, box-shadow 0.15s ease;
}

.btn:focus-visible {
  outline: 3px solid var(--accent-blue);
  outline-offset: 2px;
}

.btn.primary {
  background: var(--ink);
  color: #fff;
  box-shadow: 0 10px 20px -8px rgba(31, 35, 48, 0.45);
}

.btn.primary:hover {
  transform: translateY(-2px);
}

.btn.ghost {
  background: transparent;
  color: var(--ink);
  border: 1.5px solid #d6d9e0;
}

.btn.ghost:hover {
  background: rgba(31, 35, 48, 0.04);
}

/* ---------- Responsive ---------- */
@media (max-width: 880px) {
  .hero{
      min-height: unset;
  }
  .stage {
    height: 300px;
  }

  .sticky-note {
   display: none;
  }

  .dial {
    left: 10%;
    top: 10px;
  }

  .stopwatch {
    right: 0;
    top: 150px;
  }

  .reminder-stack {
    left: 58%;
    top: 0;
  }

  .checklist {
    top: 210px;
    left: 4%;
  }

  .folder-slot {
    display: none;
  }
}

@media (max-width: 560px) {
  .hero {
    padding: 56px 16px 72px;
  }

  .stage {
    height: 460px;
  }

  h1 .line {
    font-size: 30px;
  }
}
</style>
