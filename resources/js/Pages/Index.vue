<script setup>
import { ref, onMounted, onUnmounted } from "vue";
import Lenis from "lenis";
import gsap from "gsap";
import { ScrollTrigger } from "gsap/ScrollTrigger";

import ProductivityHero from "../Components/Index/ProductivityHero.vue";
import StayOrganize from "../Components/Index/StayOrganize.vue";
import TeamWorkSection from "../Components/Index/TeamWorkSection.vue";
import FeatureSection from "../Components/Index/FeatureSection.vue";
import Footer from "../Components/Index/Footer.vue";

import { Head,Link } from "@inertiajs/vue3";


gsap.registerPlugin(ScrollTrigger);


let lenis;


onMounted(() => {

    lenis = new Lenis({
        smoothWheel: true,
        lerp: 0.08,
    });


    lenis.on("scroll", ScrollTrigger.update);


    function raf(time){
        lenis.raf(time);
        requestAnimationFrame(raf);
    }


    requestAnimationFrame(raf);


    ScrollTrigger.refresh();

});


onUnmounted(() => {
    if(lenis){
        lenis.destroy();
    }
});
</script>

<template>
    <Head title="SiteClip" />
    <header class="flex justify-between items-center my-5 md:mx-16 mx-8">
        <div class="logo">
            <Link :href="route('home.index')">
                <img class="h-16" width="200" src="/SiteClipLogo.png" />
            </Link>
        </div>

        <nav>
            <ul
                class="md:flex hidden list-none justify-between items-center gap-5 shadow-2xl rounded-3xl py-3 px-5"
            >
                <li>
                    <a href="#" class="rounded-full shadow p-2 py-1">Home</a>
                </li>
                <li><a href="">How it Works</a></li>
                <li><a href="">Testimonials</a></li>
                <li><a href="">About Us</a></li>
                <li><a href="">Collaboration</a></li>
            </ul>
        </nav>
        <div class="signupBtn">
            <!-- <button class="myBtn">Signup <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                    viewBox="0 0 24 24">
                    <path fill="#000000"
                        d="m18 12l.707-.707l.707.707l-.707.707zM6 13a1 1 0 1 1 0-2zm8.707-5.707l4 4l-1.414 1.414l-4-4zm4 5.414l-4 4l-1.414-1.414l4-4zM18 13H6v-2h12z" />
                </svg></button> -->
            <button class="btn btn-pill">
                Signup
                <span>→</span>
            </button>
        </div>
    </header>

    <main>
        <section class="hero mb-12 lg:mb-36">
            <div class="textDiv">
                <div class="spanText">
                    <span>Never lose a thought again</span>
                </div>
                <h1 class="font-headingfont">Take control of your digital world With SiteClip</h1>
                <div class="textDiv">
                    <p class="text-[25px] leading-10 mx-2 lg:mx-0">
                        Looking for a cure for digital overload? <br />
                        In a world of constant distractions, Siteclip puts your
                        thinking first
                    </p>
                </div>
                <button class="btn btn-primary">
                    Wherever AI
                    <span>→</span>
                </button>
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
