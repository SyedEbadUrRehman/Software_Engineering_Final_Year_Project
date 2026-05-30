<script setup>
import { ref, onMounted, computed } from "vue"; // Import computed
import { Link, usePage } from "@inertiajs/vue3"; // Import usePage

import Magnify from "vue-material-design-icons/Magnify.vue";
import HeartOutline from "vue-material-design-icons/HeartOutline.vue";
import HomeOutline from "vue-material-design-icons/HomeOutline.vue";
import Compass from "vue-material-design-icons/Compass.vue";
// import SendOutline from 'vue-material-design-icons/SendOutline.vue';
import Plus from "vue-material-design-icons/Plus.vue";
import AccountOutline from "vue-material-design-icons/AccountOutline.vue";
import ChevronLeft from "vue-material-design-icons/ChevronLeft.vue";
import AccountPlusOutline from "vue-material-design-icons/AccountPlusOutline.vue";
import AccountGroupOutline from "vue-material-design-icons/AccountGroupOutline.vue";
import BookmarkOutline from "vue-material-design-icons/BookmarkOutline.vue";
import BellOutline from "vue-material-design-icons/BellOutline.vue";
import Logout from "vue-material-design-icons/Logout.vue";

import MenuItem from "@/Components/MenuItem.vue";
import CreatePostOverlay from "@/Components/CreatePostOverlay.vue";

let showCreatePost = ref(false);

// 1. Get initial count from Middleware
const page = usePage();
// We use a ref so we can increment it in real-time without reloading
const unreadCount = ref(page.props.auth.unreadNotificationCount || 0);

onMounted(() => {
    // 2. Listen for Real-Time Notifications
    // Echo has a special helper .notification() for Laravel Notifications
    window.Echo.private(
        `App.Models.User.${page.props.auth.user.id}`,
    ).notification((notification) => {
        console.log("Notification received:", notification);
        unreadCount.value++;

        // Optional: Play a sound
        // new Audio('/notification.mp3').play();
    });
});

function createNoteTogglerFun() {
    window.parent.postMessage("request_tab_info", "*");
}
</script>

<template>
    <div id="MainLayout" class="w-full h-screen">
        <div
            v-if="$page.url === '/'"
            id="TopNavHome"
            class="fixed z-30 md:hidden block w-full bg-white h-[61px] border-b border-b-gray-300"
        >
            <div class="flex items-center justify-between h-full">
                <Link href="/">
                    <img
                        class="w-[100px] ml-6 cursor-pointer"
                        src="/SiteClipLogo.png"
                    />
                </Link>

                <div class="flex items-center">
                    <Link :href="route('search.index')">
                        <Magnify fillColor="#000000" :size="27" />
                    </Link>
                    <!-- <input
                            type="text"
                            placeholder="Search"
                            class="bg-transparent w-full placeholder-[#8E8E8E] border-0 ring-0 focus:ring-0"
                        /> -->
                    <div class="relative pl-4 pr-3 cursor-pointer">
                        <Link :href="route('notifications.index')">
                            <BellOutline fillColor="#000000" :size="27" />
                            <div
                                v-if="unreadCount > 0"
                                class="absolute top-0 right-2 bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full border-2 border-white"
                            >
                                {{ unreadCount }}
                            </div>
                        </Link>
                    </div>
                    <!-- <BellOutline
                        class="pl-4 pr-3"
                        fillColor="#000000"
                        :size="27"
                    /> -->
                </div>
            </div>
        </div>

        <div
            v-if="$page.url !== '/'"
            id="TopNavUser"
            class="md:hidden fixed flex items-center justify-between z-30 w-full bg-white h-[61px] border-b border-b-gray-300"
        >
            <Link href="/" class="px-4">
                <ChevronLeft :size="30" class="cursor-pointer" />
            </Link>
            <div class="font-extrabold text-lg">
                {{ $page.props.auth.user.name }}
            </div>
            <AccountPlusOutline :size="30" class="cursor-pointer px-4" />
        </div>

        <div
            id="SideNav"
            class="fixed h-full bg-white xl:w-[280px] w-[80px] md:block hidden border-r border-r-gray-300"
        >
            <Link href="/">
                <img
                    class="xl:hidden block w-[60px] mt-10 ml-[5px] mb-10 cursor-pointer"
                    src="/SiteClipLogo.png"
                />
                <img
                    class="xl:block hidden w-[120px] mt-10 ml-6 mb-10 cursor-pointer"
                    src="/SiteClipLogo.png"
                />
            </Link>

            <div class="px-3">
                <Link href="/">
                    <MenuItem iconString="Home" class="mb-4" />
                </Link>
                <Link :href="route('search.index')">
                    <MenuItem iconString="Search" class="mb-4" />
                </Link>

                <Link href="/circles">
                    <MenuItem iconString="Circles" class="mb-4" />
                </Link>
                <Link href="/saved">
                    <MenuItem iconString="Saved" class="mb-4" />
                </Link>
                <!-- <Link href="/circles">
                    <MenuItem iconString="Notifications" class="mb-4" />
                </Link> -->

                <Link
                    :href="route('notifications.index')"
                    class="relative block"
                >
                    <MenuItem iconString="Notifications" class="mb-4" />
                    <div
                        v-if="unreadCount > 0"
                        class="absolute top-2 left-[35px] bg-red-500 text-white text-[11px] font-bold px-1.5 rounded-full"
                    >
                        {{ unreadCount }}
                    </div>
                </Link>

                <MenuItem
                    @click="showCreatePost = true"
                    iconString="Create"
                    class="mb-4"
                />
                <Link
                    :href="
                        route('users.show', { id: $page.props.auth.user.id })
                    "
                >
                    <MenuItem iconString="Profile" class="mb-4" />
                </Link>
            </div>

            <Link
                :href="route('logout')"
                as="button"
                method="post"
                class="absolute bottom-0 px-3 w-full"
            >
                <MenuItem iconString="Log out" class="mb-4" />
            </Link>
        </div>

        <div
            class="flex lg:justify-between bg-white h-full w-[100%-280px] xl:pl-[280px] lg:pl-[100px] overflow-auto"
        >
            <div
                class="mx-auto md:pt-6 pt-20"
                :class="
                    $page.url === '/' ? 'lg:w-8/12 w-full' : 'max-w-[1200px]'
                "
            >
                <main>
                    <slot />
                </main>
            </div>

            <div
                v-if="
                    !$page.url.includes('/circles') &&
                    !$page.url.includes('/follow') &&
                    !$page.url.includes('/users')
                "
                id="SuggestionsSection"
                class="lg:w-4/12 lg:block hidden text-black mt-10"
            >
                <Link
                    :href="
                        route('users.show', { id: $page.props.auth.user.id })
                    "
                    class="flex items-center justify-between max-w-[300px]"
                >
                    <div class="flex items-center">
                        <img
                            class="rounded-full z-10 w-[58px] h-[58px]"
                            :src="$page.props.auth.user.file"
                        />
                        <div class="pl-4">
                            <div class="text-black font-extrabold">
                                {{ $page.props.auth.user.name }}
                            </div>
                            <div class="text-gray-500 text-extrabold text-sm">
                                {{ $page.props.auth.user.name }}
                            </div>
                        </div>
                    </div>
                    <button
                        class="text-blue-500 hover:text-gray-900 text-xs font-extrabold"
                    >
                        Switch
                    </button>
                </Link>

                <div
                    class="max-w-[300px] flex items-center justify-between py-3"
                >
                    <div class="text-gray-500 font-extrabold">
                        Suggestions for you
                    </div>
                    <button
                        class="text-blue-500 hover:text-gray-900 text-xs font-extrabold"
                    >
                        See All
                    </button>
                </div>

                <div
                    v-for="randUser in $page.props.randomUsers"
                    :key="randUser"
                >
                    <Link
                        :href="route('users.show', { id: randUser.id })"
                        class="flex items-center justify-between max-w-[300px] pb-2"
                    >
                        <div class="flex items-center">
                            <img
                                class="rounded-full z-10 w-[37px] h-[37px]"
                                :src="randUser.file"
                            />
                            <div class="pl-4">
                                <div class="text-black font-extrabold">
                                    {{ randUser.name }}
                                </div>
                                <div
                                    class="text-gray-500 text-extrabold text-sm"
                                >
                                    Suggested for you
                                </div>
                            </div>
                        </div>
                        <button
                            class="text-blue-500 hover:text-gray-900 text-xs font-extrabold"
                        >
                            Follow
                        </button>
                    </Link>
                </div>

                <div class="max-w-[300px] mt-5">
                    <div class="text-sm text-gray-400">
                        About Help Press API Jobs Privacy Terms Locations
                        Language Verified
                    </div>
                    <div class="text-left text-gray-400 mt-4">
                        © Thought Cliper by SiteClip.
                    </div>
                </div>
            </div>
        </div>

        <div
            id="BottomNav"
            class="fixed z-30 bottom-0 w-full md:hidden flex items-center justify-around bg-white border-t py-2 border-t-gray-300"
        >
            <Link href="/">
                <HomeOutline
                    fillColor="#000000"
                    :size="33"
                    class="cursor-pointer"
                />
            </Link>
            <Link href="/circles">
                <AccountGroupOutline
                    fillColor="#000000"
                    :size="33"
                    class="cursor-pointer"
                />
            </Link>
            <Link href="/saved">
                <BookmarkOutline
                    fillColor="#000000"
                    :size="33"
                    class="cursor-pointer"
                />
            </Link>
            <Plus
                @click="
                    showCreatePost = true;
                    createNoteTogglerFun();
                "
                fillColor="#000000"
                :size="33"
                class="cursor-pointer"
            />

            <Link :href="route('users.show', { id: $page.props.auth.user.id })">
                <img
                    class="rounded-[50%] w-[30px] h-[30px] cursor-pointer"
                    :src="$page.props.auth.user.file"
                />
            </Link>
            <Link :href="route('logout')" as="button" method="post">
                <Logout fillColor="#000000" :size="30" class="cursor-pointer" />
            </Link>
        </div>
    </div>

    <CreatePostOverlay v-if="showCreatePost" @close="showCreatePost = false" />
</template>
<style>
:root {
    font-size: 1vw;
}
body {
    font-variation-settings: "wght" 500;
}

@media screen and (max-width: 900px) {
    svg {
        width:26px;
        height:26px;
    }
    :root {
        font-size: 93%;
    }
}@media screen and (max-width: 450px) {
    svg {
        width: 20px;
        height: 20px;
    }
    :root {
        font-size: 80%;
    }
}
</style>
