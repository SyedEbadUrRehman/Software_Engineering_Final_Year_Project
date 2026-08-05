<script setup>
import { ref, onMounted, onUnmounted } from "vue";
import Lenis from "lenis";
import gsap from "gsap";
import { ScrollTrigger } from "gsap/ScrollTrigger";

import Header from "@/Components/Index/Header.vue";
import ProductivityHero from "../Components/Index/ProductivityHero.vue";
import StayOrganize from "../Components/Index/StayOrganize.vue";
import TeamWorkSection from "../Components/Index/TeamWorkSection.vue";
import FeatureSection from "../Components/Index/FeatureSection.vue";
import Footer from "../Components/Index/Footer.vue";

import { Head, Link, usePage } from "@inertiajs/vue3";

const page = usePage();
gsap.registerPlugin(ScrollTrigger);

let lenis;
let anchorLinks = [];
let onAnchorClick;
let onHashChange;

onMounted(() => {
    lenis = new Lenis({
        smoothWheel: true,
        lerp: 0.08,
    });

    lenis.on("scroll", ScrollTrigger.update);

    const scrollToHash = (hash) => {
        const target = document.querySelector(hash);
        if (target) {
            lenis.scrollTo(target, {
                duration: 1.2,
            });
        }
    };

    onAnchorClick = (event) => {
        const href = event.currentTarget.getAttribute("href");
        if (!href || !href.startsWith("#")) {
            return;
        }

        const hash = href;
        const target = document.querySelector(hash);
        if (target) {
            event.preventDefault();
            scrollToHash(hash);
            history.pushState(null, "", hash);
        }
    };

    anchorLinks = Array.from(document.querySelectorAll("a[href^='#']"));
    anchorLinks.forEach((link) => {
        link.addEventListener("click", onAnchorClick);
    });

    onHashChange = () => {
        if (window.location.hash) {
            scrollToHash(window.location.hash);
        }
    };

    window.addEventListener("hashchange", onHashChange);

    if (window.location.hash) {
        scrollToHash(window.location.hash);
    }

    function raf(time) {
        lenis.raf(time);
        requestAnimationFrame(raf);
    }

    requestAnimationFrame(raf);

    ScrollTrigger.refresh();
});

onUnmounted(() => {
    if (lenis) {
        lenis.destroy();
    }

    if (anchorLinks.length && onAnchorClick) {
        anchorLinks.forEach((link) => {
            link.removeEventListener("click", onAnchorClick);
        });
    }

    if (onHashChange) {
        window.removeEventListener("hashchange", onHashChange);
    }
});
</script>

<template>
    <Head title="SiteClip" />
 <Header/>

    <main>
        <section class="hero mb-12 lg:mb-36">
            <div class="textDiv">
                <div class="spanText">
                    <span>Never lose a thought again</span>
                </div>
                <h1 class="font-headingfont">
                    Take control of your digital world With SiteClip
                </h1>
                <div class="textDiv">
                    <p class="text-[25px] leading-10 mx-2 lg:mx-0">
                        Looking for a cure for digital overload? <br />
                        In a world of constant distractions, Siteclip puts your
                        thinking first
                    </p>
                </div>
                <Link :href="route('home.index')" class="btn btn-primary">
                    Get Started
                    <span>→</span>
                </Link>
            </div>
            <div class="img">
                <img src="/icons/heroimg.png" alt="" />
            </div>
        </section>

        <TeamWorkSection />
        <ProductivityHero />
        <FeatureSection />
        <StayOrganize />
    </main>

    <footer>
        <Footer />
    </footer>
</template>
