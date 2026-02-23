<script setup>
import { ref, onMounted, toRefs, computed, watch } from "vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import { debounce } from "lodash";
import MainLayout from "@/Layouts/MainLayout.vue";

import LikesSection from "@/Components/LikesSection.vue";
import ShowPostOverlay from "@/Components/ShowPostOverlay.vue";

import DotsHorizontal from "vue-material-design-icons/DotsHorizontal.vue";
import Close from "vue-material-design-icons/Close.vue";

let wWidth = ref(window.innerWidth);
let currentPost = ref(null);
let openOverlay = ref(false);
let showDeleteConfirm = ref(false);
let deletePosId = ref(null);
const circles = ref([]); // all circles
const searchQuery = ref(""); // Tracks the text in the search box
const openShareId = ref(null); // Tracks which post ID has the share div open
const openReminderId = ref(null); // Tracks which post ID has the reminder div open
const reminderDate = ref("");

const user = usePage().props.auth.user;
const props = defineProps({ posts: Object, searchQuery: String });
const { posts } = toRefs(props);

// search feature

const search = ref(props.searchQuery || "");

// ✅ Debounced Search Request
watch(
    search,
    debounce((value) => {
        router.get(
            route("search.index"),
            { q: value },
            {
                preserveState: true,
                replace: true,
            },
        );
    }, 500),
);

const toggleShare = async (postId) => {
    if (openShareId.value === postId) {
        openShareId.value = null;
        return;
    }

    openShareId.value = postId;

    // Fetch circles from backend
    const res = await fetch("/my-circles");
    circles.value = await res.json();
};

const filteredCircles = computed(() => {
    if (!searchQuery.value) {
        return [];
    }
    return circles.value.filter((circle) =>
        circle.name.toLowerCase().includes(searchQuery.value.toLowerCase()),
    );
});
// Share Post Into Circle
const sharePost = (post, circleId) => {
    router.post(
        `/posts/${post.id}/share`,
        { circle_id: circleId },
        {
            onFinish: () => {
                toggleShare(post.id);
                searchQuery.value = null;
            },
            preserveScroll: true,
        },
    );
};
// Remove post from Share
const unsharePost = (shareId) => {
    router.delete(`/post-circle-shares/${shareId}`, {
        onFinish: () => {
            openShareId.value = null;
            searchQuery.value = null;
        },
        preserveScroll: true,
    });
};

onMounted(() => {
    window.addEventListener("resize", () => {
        wWidth.value = window.innerWidth;
    });
});

const openOverlayToggler = (post) => {
    currentPost.value = post;
    openOverlay.value = true;
};

const addComment = (object) => {
    router.post(
        "/comments",
        {
            post_id: object.post.id,
            user_id: object.user.id,
            comment: object.comment,
        },
        {
            onFinish: () => updatedPost(object),
        },
    );
};

const deleteFunc = (object) => {
    let url = "";
    if (object.deleteType === "Post") {
        url = "/posts/" + object.id;
    } else {
        url = "/comments/" + object.id;
    }

    router.delete(url, {
        onFinish: () => updatedPost(object),
    });

    if (object.deleteType === "Post") {
        openOverlay.value = false;
    }
};

const updateLike = (object) => {
    let deleteLike = false;
    let id = null;

    for (let i = 0; i < object.post.likes.length; i++) {
        const like = object.post.likes[i];
        if (
            like.user_id === object.user.id &&
            like.post_id === object.post.id
        ) {
            deleteLike = true;
            id = like.id;
        }
    }

    if (deleteLike) {
        router.delete("/likes/" + id, {
            onFinish: () => updatedPost(object),
        });
    } else {
        router.post(
            "/likes",
            {
                post_id: object.post.id,
            },
            {
                onFinish: () => updatedPost(object),
            },
        );
    }
};

const updatedPost = (object) => {
    for (let i = 0; i < posts.value.data.length; i++) {
        const post = posts.value.data[i];
        if (post.id === object.post.id) {
            currentPost.value = post;
        }
    }
};

const toggleSave = (post) => {
    post.saves = post.saves ?? [];

    let alreadySaved = false;
    let saveId = null;

    // Check if user already saved
    for (let i = 0; i <= post.saves.length; i++) {
        const save = post.saves[i];

        if (save.user_id === user.id) {
            alreadySaved = true;
            saveId = save.id;
        }
    }

    if (alreadySaved) {
        // ✅ Unsave
        router.delete("/saves/" + saveId, {
            preserveScroll: true,

            // ✅ IMPORTANT: Refresh overlay post
            onFinish: () => {
                updatedPost({ post });
                openOverlay.value = false;
            },
        });
    } else {
        // ✅ Save
        router.post(
            "/saves",
            { post_id: post.id },
            {
                preserveScroll: true,

                // ✅ IMPORTANT: Refresh overlay post
                onFinish: () => updatedPost({ post }),
            },
        );
    }
};
// --- NEW REMINDER FUNCTIONS ---

const toggleReminder = (postId) => {
    // If it's already open, close it
    if (openReminderId.value === postId) {
        openReminderId.value = null;
        return;
    }

    // Open this post's reminder box
    openReminderId.value = postId;
    openShareId.value = null; // Optional: close share box if it's open so they don't overlap

    // Pre-fill the date if the user already has a reminder set
    const post = posts.value.data.find((p) => p.id === postId);
    const existingReminder = (post.reminders || []).find(
        (r) => r.user_id === user.id,
    );
    reminderDate.value = existingReminder ? existingReminder.due_at : "";
};

const hasReminder = (post) => {
    return (post.reminders || []).some((r) => r.user_id === user.id);
};

// 1. SET (Create)
const submitReminder = (postId) => {
    if (!reminderDate.value) return;

    router.post(
        "/post-reminders",
        {
            post_id: postId,
            due_at: reminderDate.value,
        },
        {
            preserveScroll: true,
            onFinish: () => {
                openReminderId.value = null;
                reminderDate.value = "";
            },
        },
    );
};

// 2. UPDATE
const updateReminder = (postId) => {
    if (!reminderDate.value) return;

    router.put(
        `/post-reminders/${postId}`,
        {
            due_at: reminderDate.value,
        },
        {
            preserveScroll: true,
            onFinish: () => {
                openReminderId.value = null;
                reminderDate.value = "";
            },
        },
    );
};

// 3. DELETE
const deleteReminder = (postId) => {
    router.delete(`/post-reminders/${postId}`, {
        preserveScroll: true,
        onFinish: () => {
            openReminderId.value = null;
            reminderDate.value = "";
        },
    });
};
</script>

<template>
    <Head title="SiteClip" />

    <MainLayout>
        <div
            class="md:min-w-[600px] w-full mx-auto lg:pl-0 md:pl-[80px] pl-0 relative"
        >
            <div
                class="sticky md:top-0 top-[60px] w-full text-left bg-white flex gap-5 flex-col md:pt-10 pt-4"
            >
                <h1
                    class="md:text-4xl text-3xl font-black tracking-tighter text-gray-900"
                >
                    Search Here
                </h1>

                <!-- Search Input -->
                <input
                    v-model="search"
                    type="text"
                    placeholder="Search posts by text, name or email..."
                    class="border rounded-lg p-3 mb-6 mx-0"
                />

                <!-- Results -->
            </div>

            <div class="w-full">
                <div
                    v-if="posts.data.length === 0"
                    class="flex flex-col items-center justify-center px-3 text-center"
                >
                    <div class="bg-gray-100 p-6 rounded-full mb-4">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="w-12 h-12 text-gray-400"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.5"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                            />
                        </svg>
                    </div>

                    <h3 class="text-xl font-bold text-gray-900 mb-1">
                        No posts found
                    </h3>
                    <p class="text-gray-500 max-w-xs mx-auto mb-6">
                        We couldn't find any results for
                        <span class="font-semibold text-gray-800"
                            >"{{ search }}"</span
                        >. Try checking for typos or using broader keywords.
                    </p>

                    <button
                        @click="search = ''"
                        class="px-5 py-2.5 bg-black text-white text-sm font-bold rounded-xl hover:bg-gray-800 transition-all active:scale-95"
                    >
                        Clear Search
                    </button>
                </div>
                <div
                    id="Posts"
                    class="md:max-w-[600px] w-full mx-auto mt-10"
                    v-for="post in posts.data"
                    :key="post"
                >
                    <div class="flex items-center justify-between py-2">
                        <div class="flex items-center">
                            <Link
                                :href="
                                    route('users.show', { id: post.user.id })
                                "
                                class="flex items-center"
                            >
                                <img
                                    class="rounded-full w-[38px] h-[38px]"
                                    :src="post.user.file"
                                />

                                <div class="ml-4 font-extrabold text-[15px]">
                                    {{ post.user.name }}
                                </div>
                            </Link>
                            <div
                                class="flex items-center text-[15px] text-gray-500"
                            >
                                <span class="-mt-5 ml-2 mr-[5px] text-[35px]"
                                    >.</span
                                >
                                <div>{{ post.created_at }}</div>
                            </div>
                        </div>

                        <DotsHorizontal
                            v-if="user.id === post.user.id"
                            class="cursor-pointer"
                            :size="27"
                            @click="
                                showDeleteConfirm = true;
                                deletePosId = post.id;
                            "
                        />
                    </div>
                    <div class="text-lg my-4">
                        {{ post.text }}
                    </div>

                    <LikesSection
                        :post="post"
                        @like="updateLike($event)"
                        @share="toggleShare"
                        @comment="openOverlayToggler(post)"
                        @saved="toggleSave"
                        @reminder="toggleReminder($event)"
                    />

                    <div class="text-black font-extrabold py-1">
                        {{ post.likes.length }} likes
                    </div>

                    <div class="flex justify-between gap-1">
                        <button
                            @click="
                                currentPost = post;
                                openOverlay = true;
                            "
                            class="text-gray-500 font-extrabold py-1"
                        >
                            View all {{ post.comments.length }} comments
                        </button>

                        <button
                            class="text-gray-500 font-extrabold py-1"
                            v-if="post.user.id === user.id"
                        >
                            Shared in {{ post.shared_circles_count }} circles
                        </button>
                    </div>

                    <div
                        v-if="openShareId === post.id"
                        class="mt-4 p-5 pt-7 bg-white rounded-2xl border border-gray-100 transition-all"
                    >
                        <div class="relative flex items-center mb-4">
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Search circles to share..."
                                class="w-full pl-4 pr-4 py-2.5 bg-gray-50 border-none rounded-xl text-sm focus:ring-2 focus:ring-black transition"
                            />
                            <Close
                                @click="toggleShare(post.id)"
                                class="p-1 rounded-full hover:text-[red] transition-colors hover:bg-gray-400 hover:bg-opacity-30 cursor-pointer -translate-y-6"
                            />
                        </div>
                        <div>
                            <h4
                                v-if="filteredCircles.length != 0"
                                class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3 px-1"
                            >
                                Add To Circle
                            </h4>

                            <div
                                class="flex flex-wrap gap-2 max-h-[120px] overflow-y-auto custom-scrollbar"
                            >
                                <button
                                    v-for="circle in filteredCircles"
                                    :key="circle.id"
                                    @click="sharePost(post, circle.id)"
                                    class="px-4 py-2 bg-gray-50 border border-gray-100 rounded-xl text-xs font-black text-gray-600 hover:bg-white hover:border-black hover:text-black hover:shadow-lg hover:shadow-gray-100 transition-all active:scale-95"
                                >
                                    + {{ circle.name }}
                                </button>
                            </div>
                        </div>
                        <div class="mb-5" v-if="post.shared_circles.length > 0">
                            <h4
                                class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3 px-1"
                            >
                                Currently Shared In
                            </h4>

                            <div
                                v-if="post.shared_circles.length === 0"
                                class="text-xs text-gray-400 italic px-1"
                            >
                                Not shared in any circle yet.
                            </div>

                            <div class="flex flex-wrap gap-2">
                                <div
                                    v-for="circle in post.shared_circles"
                                    :key="circle.id"
                                    class="flex items-center gap-2 px-3 py-1.5 bg-black text-white rounded-full shadow-sm hover:shadow-md transition-all group"
                                >
                                    <span class="text-xs font-bold">{{
                                        circle.name
                                    }}</span>

                                    <button
                                        @click="unsharePost(circle.share_id)"
                                        class="hover:text-red-400 transition-colors"
                                    >
                                        <span class="text-[20px] leading-none"
                                            >×</span
                                        >
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div
                        v-if="openReminderId === post.id"
                        class="px-4 pb-4 border-t border-gray-100 pt-3 transition-all duration-300"
                    >
                        <div
                            class="bg-gray-50 p-3 rounded-lg border border-gray-200"
                        >
                            <div class="flex items-center justify-between">
                                <div
                                    class="text-xs font-bold mb-2 text-gray-700"
                                >
                                    Set Due Date
                                    <span class="text-gray-400 font-normal"
                                        >(Reminder sent 12h before)</span
                                    >
                                </div>
                                <Close
                                    @click="toggleReminder(post.id)"
                                    class="p-1 rounded-full hover:text-[red] transition-colors hover:bg-gray-400 hover:bg-opacity-30 cursor-pointer -translate-y-2"
                                />
                            </div>
                            <div class="flex gap-2 items-center">
                                <input
                                    type="date"
                                    v-model="reminderDate"
                                    class="flex-1 border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500"
                                />
                                <div
                                    v-if="!hasReminder(post)"
                                    class="flex gap-3 items-center justify-end"
                                >
                                    <button
                                        @click="submitReminder(post.id)"
                                        class="bg-blue-500 hover:bg-blue-600 transition-colors text-white text-sm font-bold px-4 py-2 rounded-md"
                                    >
                                        Set
                                    </button>
                                </div>
                                <div
                                    v-else
                                    class="flex gap-3 items-center justify-end"
                                >
                                    <button
                                        @click="deleteReminder(post.id)"
                                        class="bg-red-500 hover:bg-red-600 transition-colors text-white text-sm font-bold px-4 py-2 rounded-md"
                                    >
                                        Delete
                                    </button>
                                    <button
                                        @click="updateReminder(post.id)"
                                        class="bg-blue-500 hover:bg-blue-600 transition-colors text-white text-sm font-bold px-4 py-2 rounded-md"
                                    >
                                        Update
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="pb-20"></div>
        </div>
    </MainLayout>

    <ShowPostOverlay
        v-if="openOverlay"
        :post="currentPost"
        @addComment="addComment($event)"
        @updateLike="updateLike($event)"
        @deleteSelected="deleteFunc($event)"
        @updateSave="toggleSave($event)"
        @updateShare="
            toggleShare($event);
            openOverlay = false;
        "
        @closeOverlay="openOverlay = false"
        @openReminder="
            toggleReminder($event);
            openOverlay = false;
        "
    />

    <div
        v-if="showDeleteConfirm"
        id="ShowPostOptionsOverlay"
        class="fixed flex items-center z-50 top-0 left-0 w-full h-screen bg-[#000000] bg-opacity-60 p-3"
    >
        <div
            class="max-w-sm w-full mx-auto mt-10 bg-white rounded-xl text-center"
        >
            <button
                @click="
                    deleteFunc({ id: deletePosId, deleteType: 'Post' });
                    showDeleteConfirm = false;
                    deletePosId = null;
                "
                class="font-extrabold w-full text-red-600 p-3 text-lg border-b border-b-gray-300 cursor-pointer"
            >
                Delete Post
            </button>
            <div
                class="p-3 text-lg cursor-pointer"
                @click="
                    showDeleteConfirm = false;
                    deletePosId = null;
                "
            >
                Cancel
            </div>
        </div>
    </div>
</template>
