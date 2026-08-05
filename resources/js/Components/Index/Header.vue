<script setup>
import { Head, Link, usePage } from "@inertiajs/vue3";

const page = usePage();

</script>

<template>
   
    <header class="flex justify-between items-center my-5 md:mx-16 mx-8 flex-col sm:flex-row">
        <div class="logo">
            <Link :href="route('home.index')">
                <img class="h-16" src="/SiteClipLogo.png" />
            </Link>
        </div>

        <nav>
            <ul
                class="md:flex hidden list-none justify-between items-center gap-5 shadow-2xl rounded-3xl py-3 px-6"
            >
                <li>
                    <a href="/" class="rounded-full shadow p-2 py-1">Home</a>
                </li>
                <li><a href="#using-siteclip">Using SiteClip</a></li>
                <li><a href="#how-it-works">How it Works</a></li>
                <!-- <li><a href="">About Us</a></li> -->
                <li><a href="#collaboration">Collaboration</a></li>
            </ul>
        </nav>
        <div v-if="!$page.props.auth.user" class="signupBtn flex gap-3">
            <!-- <button class="myBtn">Signup <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                    viewBox="0 0 24 24">
                    <path fill="#000000"
                        d="m18 12l.707-.707l.707.707l-.707.707zM6 13a1 1 0 1 1 0-2zm8.707-5.707l4 4l-1.414 1.414l-4-4zm4 5.414l-4 4l-1.414-1.414l4-4zM18 13H6v-2h12z" />
                </svg></button> -->

            <Link :href="route('register')" class="btn btn-pill">
                Signup
                <span>→</span>
            </Link>
            <Link :href="route('login')" class="btn btn-pill">
                Signin
                <span>→</span>
            </Link>
        </div>
        <div v-if="$page.props.auth.user">
            <Link :href="route('home.index')" class="btn btn-primary scale-75 md:scale-100 hover:scale-75 md:hover:scale-100">
                Dashboard
            </Link>
        </div>
    </header>



</template>
