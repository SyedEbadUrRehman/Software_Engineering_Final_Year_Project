<script setup>
import { computed, ref, toRefs } from "vue";
import { router, usePage } from "@inertiajs/vue3";

import Heart from "vue-material-design-icons/Heart.vue";
import HeartOutline from "vue-material-design-icons/HeartOutline.vue";
import CommentOutline from "vue-material-design-icons/CommentOutline.vue";
import BookmarkOutline from "vue-material-design-icons/BookmarkOutline.vue";
import ShareOutline from "vue-material-design-icons/ShareOutline.vue";
import Bookmark from "vue-material-design-icons/Bookmark.vue";
import ClockTimeFive from "vue-material-design-icons/ClockTimeFive.vue";
import ClockTimeFiveOutline from "vue-material-design-icons/ClockTimeFiveOutline.vue";

const props = defineProps(["post"]);
const { post } = toRefs(props);

const emit = defineEmits(["like", "share", "comment", "saved", "openReminder"]);

const user = usePage().props.auth.user;

const isHeartActiveComputed = computed(() => {
    let isTrue = false;

    for (let i = 0; i < post.value.likes.length; i++) {
        const like = post.value.likes[i];
        if (like.user_id === user.id && like.post_id === post.value.id) {
            isTrue = true;
        }
    }

    return isTrue;
});
const userReminder = computed(() => {
    return (post.value.reminders || []).find((r) => r.user_id === user.id);
});
</script>

<template>
    <div class="flex z-20 items-center justify-between">
        <div class="flex items-center">
            <button @click="$emit('like', { post, user })" class="-mt-[14px]">
                <HeartOutline
                    v-if="!isHeartActiveComputed"
                    class="pl-3 cursor-pointer"
                    :size="30"
                />
                <Heart
                    v-else
                    class="pl-3 cursor-pointer"
                    fillColor="#FF0000"
                    :size="30"
                />
            </button>
            <CommentOutline
                @click="$emit('comment')"
                class="pl-3 pt-[10px] cursor-pointer"
                :size="30"
            />
            <ShareOutline
                v-if="post.user.id === user.id"
                @click="$emit('share', post.id)"
                class="pl-3 pt-[5px] cursor-pointer"
                :size="33"
            />
            <button
                @click="$emit('openReminder', post.id)"
                class="pl-3 pt-[5px] cursor-pointer"
            >
                <ClockTimeFive
                    v-if="userReminder"
                    :size="27"
                    class="cursor-pointer text-blue-600"
                />
                <ClockTimeFiveOutline
                    v-else
                    :size="27"
                    class="cursor-pointer hover:text-blue-500"
                />
            </button>
        </div>

        <!-- <BookmarkOutline class="pl-3 pt-[10px] cursor-pointer" :size="30" /> -->
        <button @click="$emit('saved', post)" class="ml-3">
            <!-- Filled Icon if Saved -->

            <Bookmark
                v-if="(post.saves ?? []).some((s) => s.user_id === user.id)"
                :size="28"
                class="cursor-pointer text-blue-600"
            />

            <!-- Outline Icon if Not Saved -->
            <BookmarkOutline v-else :size="28" class="cursor-pointer" />
        </button>
    </div>
</template>
