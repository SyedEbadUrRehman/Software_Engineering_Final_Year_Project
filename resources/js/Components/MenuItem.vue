<script setup>
import { toRefs } from 'vue';
import { usePage } from '@inertiajs/vue3';

import HomeOutline from 'vue-material-design-icons/HomeOutline.vue';
import Magnify from 'vue-material-design-icons/Magnify.vue';
import HeartOutline from 'vue-material-design-icons/HeartOutline.vue';
import Plus from 'vue-material-design-icons/Plus.vue';
import AccountOutline from 'vue-material-design-icons/AccountOutline.vue';
import Menu from 'vue-material-design-icons/Menu.vue';
import AccountGroupOutline from "vue-material-design-icons/AccountGroupOutline.vue";
import BookmarkOutline from 'vue-material-design-icons/BookmarkOutline.vue';
import BellOutline from "vue-material-design-icons/BellOutline.vue";
import Logout from "vue-material-design-icons/Logout.vue";

const props = defineProps({ iconString: String })
const { iconString } = toRefs(props)

const user = usePage().props.auth.user

let icon = null

if (iconString.value === 'Home') icon = HomeOutline
if (iconString.value === 'Search') icon = Magnify
if (iconString.value === 'Circles') icon = AccountGroupOutline
if (iconString.value === 'Saved') icon = BookmarkOutline
if (iconString.value === 'Notifications') icon = BellOutline
if (iconString.value === 'Create') icon = Plus
if (iconString.value === 'Profile') icon = AccountOutline
if (iconString.value === 'Log out') icon = Logout

</script>

<template>
    <div class="w-full xl:inline-block xl:hover:bg-gray-100 p-2 rounded-full transition duration-300 ease-in-out cursor-pointer">
        <div class="flex items-center">
            <img
                v-if="iconString === 'Profile'"
                :class="{'mr-1': iconString === 'Profile'}"
                class="rounded-full ml-[2px] w-[30px] h-[30px] cursor-pointer"
                :src="user.file"
            >
            <component v-else :is="icon" fillColor="#000000" :size="36" />
            <span class="xl:block hidden text-black font-extrabold text-[18px] pl-2 mt-0.5">
                {{ iconString }}
            </span>
        </div>
    </div>
</template>
