<script setup>
import { reactive, toRefs } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";

import MainLayout from "@/Layouts/MainLayout.vue";
import ShowPostOverlay from "@/Components/ShowPostOverlay.vue";
import ContentOverlay from "@/Components/ContentOverlay.vue";
import InformationIcon from "vue-material-design-icons/Information.vue";
const props = defineProps({
    postsByUser: Object,
    user: Object,
    followersCount: Number,
    followingCount: Number,
    isFollowing: Boolean,
    isOwner: Boolean,
    totalLikesReceived: Number,
    totalCommentsReceived: Number,
    mutualFollowers: Array,
    mutualFollowersCount: Number,
});
const { postsByUser, user } = toRefs(props);

/* ---------------------------------------------------------------------- */
/* Existing post/comment/like behavior — unchanged from before            */
/* ---------------------------------------------------------------------- */

let data = reactive({ post: null });
const form = reactive({ file: null });

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
        setTimeout(() => (data.post = null), 100);
    } else {
        url = "/comments/" + object.id;
    }

    router.delete(url, {
        onFinish: () => updatedPost(object),
    });
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
    for (let i = 0; i < postsByUser.value.data.length; i++) {
        const post = postsByUser.value.data[i];
        if (post.id === object.post.id) {
            data.post = post;
        }
    }
};

const getUploadedImage = (e) => {
    form.file = e.target.files[0];
    router.post(`/users`, form, {
        preserveState: false,
    });
};

/* ---------------------------------------------------------------------- */
/* Follow / unfollow directly from the profile page                       */
/* ---------------------------------------------------------------------- */

const followState = reactive({
    isFollowing: props.isFollowing,
    followersCount: props.followersCount,
});

const csrfToken = () =>
    document.querySelector('meta[name="csrf-token"]').getAttribute("content");

const toggleFollow = () => {
    const wasFollowing = followState.isFollowing;

    followState.isFollowing = !wasFollowing;
    followState.followersCount += wasFollowing ? -1 : 1;

    const url = wasFollowing
        ? `/users/${user.value.id}/unfollow`
        : `/users/${user.value.id}/follow`;

    fetch(url, {
        method: wasFollowing ? "DELETE" : "POST",
        headers: { "X-CSRF-TOKEN": csrfToken(), Accept: "application/json" },
    })
        .then((res) => {
            if (!res.ok) {
                followState.isFollowing = wasFollowing;
                followState.followersCount += wasFollowing ? 1 : -1;
            }
        })
        .catch(() => {
            followState.isFollowing = wasFollowing;
            followState.followersCount += wasFollowing ? 1 : -1;
        });
};

/* ---------------------------------------------------------------------- */
/* Inline name editing                                                    */
/* ---------------------------------------------------------------------- */

const nameEdit = reactive({
    active: false,
    value: user.value.name,
    saving: false,
    error: "",
});

const startNameEdit = () => {
    nameEdit.value = user.value.name;
    nameEdit.error = "";
    nameEdit.active = true;
};

const cancelNameEdit = () => {
    nameEdit.active = false;
    nameEdit.error = "";
};

const saveName = () => {
    if (!nameEdit.value || nameEdit.value.trim().length < 2) {
        nameEdit.error = "Name must be at least 2 characters.";
        return;
    }

    nameEdit.saving = true;
    nameEdit.error = "";

    fetch("/users/name", {
        method: "PUT",
        headers: {
            "X-CSRF-TOKEN": csrfToken(),
            "Content-Type": "application/json",
            Accept: "application/json",
        },
        body: JSON.stringify({ name: nameEdit.value.trim() }),
    })
        .then((res) => {
            if (!res.ok) throw new Error();
            return res.json();
        })
        .then((resData) => {
            user.value.name = resData.name;
            nameEdit.active = false;
        })
        .catch(() => {
            nameEdit.error = "Could not save name. Please try again.";
        })
        .finally(() => (nameEdit.saving = false));
};

/* ---------------------------------------------------------------------- */
/* Inline bio editing                                                     */
/* ---------------------------------------------------------------------- */

const bioForm = reactive({
    value: user.value.bio || "",
    saving: false,
    savedFlash: false,
});

const saveBio = () => {
    bioForm.saving = true;

    fetch("/users/bio", {
        method: "PUT",
        headers: {
            "X-CSRF-TOKEN": csrfToken(),
            "Content-Type": "application/json",
            Accept: "application/json",
        },
        body: JSON.stringify({ bio: bioForm.value }),
    })
        .then((res) => res.json())
        .then((resData) => {
            user.value.bio = resData.bio;
            bioForm.savedFlash = true;
            setTimeout(() => (bioForm.savedFlash = false), 1800);
        })
        .finally(() => (bioForm.saving = false));
};

/* ---------------------------------------------------------------------- */
/* Two-factor toggle (UI only)                                            */
/* ---------------------------------------------------------------------- */

const twoFactor = reactive({
    enabled: user.value.two_factor_enabled,
    saving: false,
});

const toggleTwoFactor = () => {
    const previous = twoFactor.enabled;
    twoFactor.enabled = !previous;
    twoFactor.saving = true;

    fetch("/users/two-factor", {
        method: "POST",
        headers: { "X-CSRF-TOKEN": csrfToken(), Accept: "application/json" },
    })
        .then((res) => res.json())
        .then((resData) => {
            twoFactor.enabled = resData.two_factor_enabled;
        })
        .catch(() => {
            twoFactor.enabled = previous;
        })
        .finally(() => (twoFactor.saving = false));
};

/* ---------------------------------------------------------------------- */
/* Inline account deletion (expands within the page, no overlay)          */
/* ---------------------------------------------------------------------- */

const deleteForm = reactive({
    expanded: false,
    password: "",
    error: "",
    submitting: false,
});

const submitDeleteAccount = () => {
    deleteForm.error = "";
    deleteForm.submitting = true;

    fetch("/users/account", {
        method: "DELETE",
        headers: {
            "X-CSRF-TOKEN": csrfToken(),
            "Content-Type": "application/json",
            Accept: "application/json",
        },
        body: JSON.stringify({ password: deleteForm.password }),
    })
        .then((res) => {
            if (res.redirected) {
                window.location.href = res.url;
                return;
            }
            if (!res.ok) {
                return res.json().then((body) => {
                    deleteForm.error =
                        body?.errors?.password?.[0] ||
                        "Something went wrong. Please try again.";
                });
            }
        })
        .finally(() => {
            deleteForm.submitting = false;
        });
};
</script>

<template>
    <Head title="SiteClip" />

    <MainLayout>
        <div class="relative">
            <!-- Decorative background blobs -->
            <div
                class="pointer-events-none absolute inset-0 overflow-hidden -z-10"
            >
                <svg
                    class="absolute -top-24 -left-24 w-[420px] h-[420px] opacity-60"
                    viewBox="0 0 200 200"
                    xmlns="http://www.w3.org/2000/svg"
                >
                    <defs>
                        <linearGradient
                            id="blobBlue"
                            x1="0%"
                            y1="0%"
                            x2="100%"
                            y2="100%"
                        >
                            <stop offset="0%" stop-color="#bfe4ff" />
                            <stop offset="100%" stop-color="#eaf6ff" />
                        </linearGradient>
                    </defs>
                    <path
                        fill="url(#blobBlue)"
                        d="M45.5,-58.6C58.9,-49.5,69.8,-34.9,73.6,-18.5C77.4,-2.1,74.1,16.1,64.9,30.6C55.7,45.1,40.6,55.8,23.7,62.1C6.8,68.4,-11.9,70.3,-28.4,64.9C-44.9,59.5,-59.2,46.8,-66.7,30.7C-74.2,14.6,-74.9,-4.9,-68.1,-20.9C-61.3,-36.9,-47,-49.4,-31.5,-58C-16,-66.6,0.7,-71.3,17.1,-68.6C33.5,-65.9,32.1,-67.7,45.5,-58.6Z"
                        transform="translate(100 100)"
                    />
                </svg>
                <svg
                    class="absolute top-40 right-0 w-[360px] h-[360px] opacity-50"
                    viewBox="0 0 200 200"
                    xmlns="http://www.w3.org/2000/svg"
                >
                    <path
                        fill="#dff1ff"
                        d="M39.6,-51.2C50.7,-41.6,58.6,-27.7,61.1,-12.7C63.6,2.3,60.7,18.4,52.2,31.6C43.7,44.8,29.6,55.1,13.6,60.6C-2.4,66.1,-20.3,66.8,-35.1,59.8C-49.9,52.8,-61.6,38.1,-66.1,21.2C-70.6,4.3,-67.9,-14.8,-58.6,-29.6C-49.3,-44.4,-33.4,-54.9,-17.1,-62.1C-0.8,-69.3,16,-73.2,28.5,-66.9C41,-60.6,28.5,-60.8,39.6,-51.2Z"
                        transform="translate(100 100)"
                    />
                </svg>
            </div>

            <div class="pt-6 md:pt-10"></div>

            <!-- Hero / profile card -->
            <div
                class="max-w-[880px] lg:ml-0 md:ml-[80px] md:pl-20 px-4 w-full"
            >
                <div
                    class="relative rounded-3xl border border-white/60 bg-white/70 backdrop-blur-xl shadow-[0_8px_30px_rgba(59,130,246,0.12)] p-5 md:p-8"
                >
                    <div
                        class="flex items-start md:items-center md:justify-between gap-5 flex-wrap"
                    >
                        <div class="flex items-center gap-5">
                            <!-- Avatar -->
                            <label
                                :for="isOwner ? 'fileUser' : null"
                                class="relative block shrink-0"
                            >
                                <div
                                    class="rounded-full p-[3px] bg-gradient-to-br from-sky-300 via-sky-400 to-blue-500 shadow-lg shadow-sky-200/60"
                                >
                                    <img
                                        class="rounded-full object-cover md:w-[130px] w-[92px] md:h-[130px] h-[92px] border-4 border-white cursor-pointer bg-white"
                                        :src="user.file"
                                    />
                                </div>

                                <span
                                    v-if="isOwner"
                                    class="absolute -bottom-1 -right-1 flex items-center justify-center w-8 h-8 rounded-full bg-sky-500 border-2 border-white shadow-md cursor-pointer hover:bg-sky-600 transition-colors"
                                >
                                    <svg
                                        viewBox="0 0 24 24"
                                        class="w-4 h-4 fill-white"
                                    >
                                        <path
                                            d="M9 2l-1.83 2H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-3.17L15 2H9zm3 15a5 5 0 1 1 0-10 5 5 0 0 1 0 10zm0-2a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"
                                        />
                                    </svg>
                                </span>
                            </label>
                            <input
                                v-if="isOwner"
                                id="fileUser"
                                class="hidden"
                                type="file"
                                @input="getUploadedImage($event)"
                            />

                            <div class="min-w-0">
                                <!-- Name: static display, or inline edit form -->
                                <div
                                    v-if="!nameEdit.active"
                                    class="flex items-center gap-2"
                                >
                                    <h1
                                        class="text-xl md:text-2xl font-black text-black truncate"
                                    >
                                        {{ user.name }}
                                    </h1>

                                    <svg
                                        viewBox="0 0 24 24"
                                        class="w-5 h-5 fill-sky-500 shrink-0"
                                    >
                                        <path
                                            d="M12 2l2.4 2.1 3.1-.5 1 3 2.9 1.4-.6 3.1 1.9 2.5-1.9 2.5.6 3.1-2.9 1.4-1 3-3.1-.5L12 25l-2.4-2.1-3.1.5-1-3-2.9-1.4.6-3.1L1.3 13l1.9-2.5-.6-3.1 2.9-1.4 1-3 3.1.5L12 2z"
                                            transform="translate(0 -1)"
                                        />
                                        <path
                                            d="M9.5 12.5l1.8 1.8 3.6-4"
                                            fill="none"
                                            stroke="white"
                                            stroke-width="1.6"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        />
                                    </svg>

                                    <button
                                        v-if="isOwner"
                                        @click="startNameEdit"
                                        class="w-6 h-6 flex items-center justify-center rounded-full hover:bg-sky-50 transition-colors shrink-0"
                                    >
                                        <svg
                                            viewBox="0 0 24 24"
                                            class="w-3.5 h-3.5 fill-gray-400"
                                        >
                                            <path
                                                d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04a1 1 0 0 0 0-1.41l-2.34-2.34a1 1 0 0 0-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"
                                            />
                                        </svg>
                                    </button>
                                </div>

                                <div v-else class="flex items-center gap-2">
                                    <input
                                        v-model="nameEdit.value"
                                        type="text"
                                        maxlength="50"
                                        class="text-lg font-black text-black rounded-xl border border-sky-200 bg-white/80 px-3 py-1 focus:outline-none focus:ring-2 focus:ring-sky-300 w-full max-w-[220px]"
                                        @keyup.enter="saveName"
                                        @keyup.esc="cancelNameEdit"
                                    />
                                    <button
                                        @click="saveName"
                                        :disabled="nameEdit.saving"
                                        class="w-7 h-7 flex items-center justify-center rounded-full bg-sky-500 hover:bg-sky-600 disabled:opacity-50 transition-colors shrink-0"
                                    >
                                        <svg
                                            viewBox="0 0 24 24"
                                            class="w-3.5 h-3.5 fill-white"
                                        >
                                            <path
                                                d="M9 16.2L4.8 12l-1.4 1.4L9 19 21 7l-1.4-1.4z"
                                            />
                                        </svg>
                                    </button>
                                    <button
                                        @click="cancelNameEdit"
                                        class="w-7 h-7 flex items-center justify-center rounded-full bg-gray-100 hover:bg-gray-200 transition-colors shrink-0"
                                    >
                                        <svg
                                            viewBox="0 0 24 24"
                                            class="w-3.5 h-3.5 fill-gray-500"
                                        >
                                            <path
                                                d="M18.3 5.71a1 1 0 0 0-1.41 0L12 10.59 7.11 5.7A1 1 0 0 0 5.7 7.11L10.59 12 5.7 16.89a1 1 0 1 0 1.41 1.41L12 13.41l4.89 4.89a1 1 0 0 0 1.41-1.41L13.41 12l4.89-4.89a1 1 0 0 0 0-1.4z"
                                            />
                                        </svg>
                                    </button>
                                </div>
                                <p
                                    v-if="nameEdit.error"
                                    class="text-xs text-red-500 mt-1"
                                >
                                    {{ nameEdit.error }}
                                </p>

                                <p
                                    class="text-sm text-gray-500 mt-1 max-w-md leading-snug"
                                    v-if="user.email"
                                >
                                    {{ user.email }}
                                </p>
                                <!-- <p class="text-sm text-gray-300 italic mt-1" v-else-if="isOwner">
                                    Add a bio below to tell people about yourself.
                                </p> -->

                                <!-- Stats row -->
                                <div
                                    class="flex items-center gap-5 md:gap-6 mt-3 text-sm"
                                >
                                    <div>
                                        <span
                                            class="font-extrabold text-black"
                                            >{{ postsByUser.data.length }}</span
                                        >
                                        <span class="text-gray-400 ml-1"
                                            >posts</span
                                        >
                                    </div>
                                    <div>
                                        <span
                                            class="font-extrabold text-black"
                                            >{{
                                                followState.followersCount
                                            }}</span
                                        >
                                        <span class="text-gray-400 ml-1"
                                            >followers</span
                                        >
                                    </div>
                                    <Link :href="route('follow.index')">
                                        <span
                                            class="font-extrabold text-black"
                                            >{{ followingCount }}</span
                                        >
                                        <span class="text-gray-400 ml-1"
                                            >following</span
                                        >
                                    </Link>
                                </div>
                                <div
                                    v-if="isOwner"
                                    class="mt-2 flex flex-col gap-1.5 selection:bg-transparent"
                                >
                                    <!-- Streak Styling for the Scores -->
                                    <div
                                        class="flex items-center gap-2 text-sm"
                                    >
                                        <div
                                            class="flex items-center gap-1 bg-gradient-to-r from-amber-500 to-orange-500 text-white font-black px-2.5 py-0.5 rounded-full shadow-sm animate-pulse-slow"
                                        >
                                            <span>🔥</span>
                                            <span>{{ user.owner_score }}</span>
                                            <span
                                                class="text-xs opacity-75 font-medium"
                                                >({{
                                                    user.owner_score_count
                                                }})</span
                                            >
                                        </div>

                                        <span
                                            class="text-gray-400 italic text-xs font-medium tracking-wide"
                                            >Ideation Score</span >

                                        <div
                                            class="relative group flex items-center cursor-help"
                                        >
                                            <InformationIcon
                                                :size="16"
                                                class="text-gray-400 group-hover:text-purple-400 transition-colors duration-200"
                                            />
                                            <div
                                                class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 hidden group-hover:flex flex-col items-center pointer-events-none z-50 transition-all duration-200"
                                            >
                                                <div
                                                    class="bg-gray-900 text-white text-xs rounded-md py-1 px-2.5 whitespace-nowrap shadow-xl border border-gray-800 text-center font-normal non-italic"
                                                >
                                                    Calculated by user
                                                    interaction & activity
                                                    metrics showing how much
                                                    others love your ideas!.
                                                </div>
                                                <div
                                                    class="w-2 h-2 bg-gray-900 rotate-45 -mt-1 border-r border-b border-gray-800"
                                                ></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Follow button, non-owner only -->
                        <div
                            v-if="!isOwner"
                            class="flex items-center gap-2 ml-auto md:ml-0"
                        >
                            <button
                                @click="toggleFollow"
                                class="flex items-center gap-2 px-5 py-2 rounded-xl text-sm font-bold transition-all shadow-sm"
                                :class="
                                    followState.isFollowing
                                        ? 'bg-white/80 border border-gray-200 text-black hover:bg-gray-50'
                                        : 'bg-gradient-to-r from-sky-400 to-blue-500 text-white hover:from-sky-500 hover:to-blue-600 shadow-sky-200'
                                "
                            >
                                <svg
                                    v-if="followState.isFollowing"
                                    viewBox="0 0 24 24"
                                    class="w-4 h-4 fill-black"
                                >
                                    <path
                                        d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"
                                    />
                                </svg>
                                <svg
                                    v-else
                                    viewBox="0 0 24 24"
                                    class="w-4 h-4 fill-white"
                                >
                                    <path
                                        d="M15 12c1.93 0 3.5-1.57 3.5-3.5S16.93 5 15 5s-3.5 1.57-3.5 3.5S13.07 12 15 12zm-6.5 0c1.93 0 3.5-1.57 3.5-3.5S10.43 5 8.5 5 5 6.57 5 8.5 6.57 12 8.5 12zM8.5 14c-2.67 0-8 1.34-8 4v2h10v-2c0-.83.24-1.63.65-2.34C10.29 15.24 9.43 14 8.5 14zm6.5 0c-.35 0-.72.03-1.11.08 1.36.99 2.36 2.34 2.36 3.92v2h8v-2c0-2.66-5.33-4-9.25-4zm3-4h-2V8h-2V6h2V4h2v2h2v2h-2v2z"
                                    />
                                </svg>
                                {{
                                    followState.isFollowing
                                        ? "Following"
                                        : "Follow"
                                }}
                            </button>
                        </div>
                    </div>

                    <!-- Mutual followers: "Followed by X, Y +N others" -->
                    <div
                        v-if="!isOwner && mutualFollowersCount > 0"
                        class="mt-5 pt-4 border-t border-sky-50 flex items-center gap-3"
                    >
                        <div class="flex -space-x-2 shrink-0">
                            <img
                                v-for="mutual in mutualFollowers"
                                :key="mutual.id"
                                :src="mutual.file"
                                class="w-7 h-7 rounded-full border-2 border-white object-cover bg-white"
                            />
                        </div>
                        <p class="text-xs text-gray-500">
                            Followed by
                            <span class="font-bold text-black">{{
                                mutualFollowers.map((m) => m.name).join(", ")
                            }}</span>
                            <span
                                v-if="
                                    mutualFollowersCount >
                                    mutualFollowers.length
                                "
                            >
                                and
                                {{
                                    mutualFollowersCount -
                                    mutualFollowers.length
                                }}
                                other{{
                                    mutualFollowersCount -
                                        mutualFollowers.length >
                                    1
                                        ? "s"
                                        : ""
                                }}
                                you follow
                            </span>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile stats bar -->
        <div class="md:hidden">
            <div
                class="w-full flex items-center justify-around border-t border-t-gray-100 mt-6"
            >
                <div class="text-center p-3">
                    <div class="font-extrabold">
                        {{ postsByUser.data.length }}
                    </div>
                    <div class="text-gray-400 font-semibold -mt-1.5 text-xs">
                        posts
                    </div>
                </div>
                <div class="text-center p-3">
                    <div class="font-extrabold">
                        {{ followState.followersCount }}
                    </div>
                    <div class="text-gray-400 font-semibold -mt-1.5 text-xs">
                        followers
                    </div>
                </div>
                <div class="text-center p-3">
                    <div class="font-extrabold">{{ followingCount }}</div>
                    <div class="text-gray-400 font-semibold -mt-1.5 text-xs">
                        following
                    </div>
                </div>
            </div>
        </div>

        <!-- =================================================================
             ABOUT + ENGAGEMENT STATS — visible to everyone, makes a light
             profile feel content-heavy even with few/no posts or bio.
        ================================================================== -->
        <div
            class="max-w-[880px] lg:ml-0 md:ml-[80px] md:pl-20 px-4 w-full mt-6"
        >
            <div class="grid md:grid-cols-5 gap-4">
                <!-- About card -->
                <div
                    class="md:col-span-3 rounded-3xl border border-white/60 bg-white/70 backdrop-blur-xl shadow-[0_8px_30px_rgba(59,130,246,0.08)] p-5 md:p-6"
                >
                    <h2
                        class="text-sm font-black text-black uppercase tracking-wide flex items-center gap-2 mb-4"
                    >
                        <svg viewBox="0 0 24 24" class="w-4 h-4 fill-sky-500">
                            <path
                                d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20zm0 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3zm2 12h-4v-1h1v-5h-1v-1h3v6h1v1z"
                            />
                        </svg>
                        About
                    </h2>

                    <p
                        v-if="user.bio"
                        class="text-sm text-gray-600 leading-relaxed"
                    >
                        {{ user.bio }}
                    </p>
                    <p v-else class="text-sm text-gray-300 italic">
                        {{
                            isOwner
                                ? "You haven't added a bio yet — add one in Account Settings below."
                                : "This user hasn't added a bio yet."
                        }}
                    </p>

                    <div
                        class="flex items-center gap-2 mt-4 pt-4 border-t border-sky-50 text-xs text-gray-400"
                    >
                        <svg viewBox="0 0 24 24" class="w-4 h-4 fill-gray-300">
                            <path
                                d="M19 4h-1V2h-2v2H8V2H6v2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2zm0 16H5V10h14v10zm0-12H5V6h14v2z"
                            />
                        </svg>
                        Joined {{ user.joined_at }}
                    </div>
                </div>

                <!-- Engagement stats card -->
                <div
                    class="md:col-span-2 rounded-3xl border border-white/60 bg-white/70 backdrop-blur-xl shadow-[0_8px_30px_rgba(59,130,246,0.08)] p-5 md:p-6"
                >
                    <h2
                        class="text-sm font-black text-black uppercase tracking-wide flex items-center gap-2 mb-4"
                    >
                        <svg viewBox="0 0 24 24" class="w-4 h-4 fill-sky-500">
                            <path
                                d="M16 6l2.29 2.29-4.88 4.88-4-4L2 16.59 3.41 18l6-6 4 4 6.3-6.29L22 12V6z"
                            />
                        </svg>
                        Engagement
                    </h2>

                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span
                                class="flex items-center gap-2 text-sm text-gray-500"
                            >
                                <svg
                                    viewBox="0 0 24 24"
                                    class="w-4 h-4 fill-red-400"
                                >
                                    <path
                                        d="M12 21s-6.7-4.35-9.3-8.2C.8 9.7 1.7 6 5 4.7 7.3 3.8 9.7 4.7 11 6.5 12.3 4.7 14.7 3.8 17 4.7c3.3 1.3 4.2 5 2.3 8.1C18.7 16.65 12 21 12 21z"
                                    />
                                </svg>
                                Likes received
                            </span>
                            <span class="text-sm font-extrabold text-black">{{
                                totalLikesReceived
                            }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span
                                class="flex items-center gap-2 text-sm text-gray-500"
                            >
                                <svg
                                    viewBox="0 0 24 24"
                                    class="w-4 h-4 fill-sky-400"
                                >
                                    <path
                                        d="M20 2H4a2 2 0 0 0-2 2v18l4-4h14a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2z"
                                    />
                                </svg>
                                Comments received
                            </span>
                            <span class="text-sm font-extrabold text-black">{{
                                totalCommentsReceived
                            }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span
                                class="flex items-center gap-2 text-sm text-gray-500"
                            >
                                <svg
                                    viewBox="0 0 24 24"
                                    class="w-4 h-4 fill-blue-400"
                                >
                                    <path
                                        d="M4 4h16v2H4zm0 7h16v2H4zm0 7h16v2H4z"
                                    />
                                </svg>
                                Total posts
                            </span>
                            <span class="text-sm font-extrabold text-black">{{
                                postsByUser.data.length
                            }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- =================================================================
             ACCOUNT SETTINGS — all inline on the page now, owner only.
             No modals/popups: name, bio, 2FA, and delete all live here.
        ================================================================== -->
        <div
            v-if="isOwner"
            class="max-w-[880px] lg:ml-0 md:ml-[80px] md:pl-20 px-4 w-full mt-6"
        >
            <div
                class="rounded-3xl border border-white/60 bg-white/70 backdrop-blur-xl shadow-[0_8px_30px_rgba(59,130,246,0.08)] p-5 md:p-7 space-y-6"
            >
                <h2
                    class="text-sm font-black text-black uppercase tracking-wide flex items-center gap-2"
                >
                    <svg viewBox="0 0 24 24" class="w-4 h-4 fill-sky-500">
                        <path
                            d="M19.14 12.94c.04-.31.06-.62.06-.94 0-.32-.02-.63-.07-.94l2.03-1.58a.5.5 0 0 0 .12-.64l-1.92-3.32a.5.5 0 0 0-.61-.22l-2.39.96a7.3 7.3 0 0 0-1.63-.94l-.36-2.54a.5.5 0 0 0-.5-.42h-3.84a.5.5 0 0 0-.5.42l-.36 2.54c-.59.24-1.14.56-1.63.94l-2.39-.96a.5.5 0 0 0-.61.22L1.62 8.84a.5.5 0 0 0 .12.64l2.03 1.58c-.05.31-.09.63-.09.94s.02.63.07.94L1.72 14.52a.5.5 0 0 0-.12.64l1.92 3.32c.14.24.42.32.61.22l2.39-.96c.5.38 1.04.7 1.63.94l.36 2.54c.05.24.25.42.5.42h3.84c.25 0 .46-.18.5-.42l.36-2.54c.59-.24 1.13-.56 1.63-.94l2.39.96c.24.1.51 0 .61-.22l1.92-3.32a.5.5 0 0 0-.12-.64l-2.01-1.58zM12 15.6a3.6 3.6 0 1 1 0-7.2 3.6 3.6 0 0 1 0 7.2z"
                        />
                    </svg>
                    Account Settings
                </h2>

                <!-- Bio -->
                <div>
                    <label
                        class="text-xs font-bold text-gray-400 uppercase tracking-wide"
                        >Bio</label
                    >
                    <textarea
                        v-model="bioForm.value"
                        rows="3"
                        maxlength="500"
                        placeholder="Tell people about yourself..."
                        class="mt-2 w-full rounded-2xl border border-sky-100 bg-white/70 p-3 text-sm text-black focus:outline-none focus:ring-2 focus:ring-sky-300 resize-none"
                    ></textarea>
                    <div class="flex items-center justify-between mt-2">
                        <span class="text-xs text-gray-300"
                            >{{ bioForm.value?.length || 0 }}/500</span
                        >
                        <button
                            @click="saveBio"
                            :disabled="bioForm.saving"
                            class="px-4 py-1.5 rounded-xl text-sm font-bold bg-gradient-to-r from-sky-400 to-blue-500 text-white hover:from-sky-500 hover:to-blue-600 disabled:opacity-50 transition-all"
                        >
                            <span v-if="bioForm.saving">Saving...</span>
                            <span v-else-if="bioForm.savedFlash">Saved ✓</span>
                            <span v-else>Save Bio</span>
                        </button>
                    </div>
                </div>

                <!-- Two-factor toggle -->
                <div class="rounded-2xl border border-sky-100 bg-sky-50/60 p-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <svg
                                viewBox="0 0 24 24"
                                class="w-6 h-6 fill-sky-500 shrink-0"
                            >
                                <path
                                    d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 10.99h7c-.53 4.12-3.28 7.79-7 8.94V12H5V6.3l7-3.11v8.8z"
                                />
                            </svg>
                            <div>
                                <div class="text-sm font-bold text-black">
                                    Two-Factor Authentication
                                </div>
                                <div class="text-xs text-gray-400">
                                    Coming soon — this switch doesn't enforce
                                    2FA yet.
                                </div>
                            </div>
                        </div>

                        <button
                            @click="toggleTwoFactor"
                            :disabled="twoFactor.saving"
                            class="relative w-12 h-7 rounded-full transition-colors shrink-0"
                            :class="
                                twoFactor.enabled ? 'bg-sky-500' : 'bg-gray-300'
                            "
                        >
                            <span
                                class="absolute top-0.5 left-0.5 w-6 h-6 rounded-full bg-white shadow transition-transform"
                                :class="
                                    twoFactor.enabled
                                        ? 'translate-x-5'
                                        : 'translate-x-0'
                                "
                            ></span>
                        </button>
                    </div>
                </div>

                <!-- Danger zone: delete account, expands inline, no overlay -->
                <div class="rounded-2xl border border-red-100 bg-red-50/60 p-4">
                    <div class="flex items-center gap-3 mb-3">
                        <svg
                            viewBox="0 0 24 24"
                            class="w-5 h-5 fill-red-500 shrink-0"
                        >
                            <path
                                d="M12 2L1 21h22L12 2zm0 5.5L18.53 19H5.47L12 7.5zM11 11v4h2v-4h-2zm0 5v2h2v-2h-2z"
                            />
                        </svg>
                        <div class="text-sm font-bold text-red-600">
                            Danger Zone
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mb-3">
                        Deleting your account is permanent and cannot be undone.
                        All your posts, comments, and data will be removed.
                    </p>

                    <button
                        v-if="!deleteForm.expanded"
                        @click="deleteForm.expanded = true"
                        class="w-full py-2 rounded-xl text-sm font-bold bg-red-500 text-white hover:bg-red-600 transition-colors flex items-center justify-center gap-2"
                    >
                        <svg viewBox="0 0 24 24" class="w-4 h-4 fill-white">
                            <path
                                d="M6 7h12l-1 13H7L6 7zm2-3h8l1 2H7l1-2zM9 9v9h2V9H9zm4 0v9h2V9h-2z"
                            />
                        </svg>
                        Delete Account
                    </button>

                    <div v-else class="space-y-2">
                        <p class="text-xs text-gray-600">
                            Enter your password to confirm — this cannot be
                            undone.
                        </p>
                        <input
                            v-model="deleteForm.password"
                            type="password"
                            placeholder="Your password"
                            class="w-full rounded-xl border border-red-200 bg-white p-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-300"
                        />
                        <p v-if="deleteForm.error" class="text-xs text-red-500">
                            {{ deleteForm.error }}
                        </p>

                        <div class="flex items-center gap-2">
                            <button
                                @click="
                                    deleteForm.expanded = false;
                                    deleteForm.password = '';
                                    deleteForm.error = '';
                                "
                                class="flex-1 py-2 rounded-xl text-sm font-bold bg-gray-100 text-black hover:bg-gray-200 transition-colors"
                            >
                                Cancel
                            </button>
                            <button
                                @click="submitDeleteAccount"
                                :disabled="
                                    deleteForm.submitting ||
                                    !deleteForm.password
                                "
                                class="flex-1 py-2 rounded-xl text-sm font-bold bg-red-500 text-white hover:bg-red-600 disabled:opacity-50 transition-colors"
                            >
                                {{
                                    deleteForm.submitting
                                        ? "Deleting..."
                                        : "Delete Forever"
                                }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Posts grid — tab row removed, this is the only content shown -->
        <div id="ContentSection" class="md:pr-1.5 lg:pl-0 md:pl-[90px] mt-8">
            <div
                v-if="postsByUser.data.length === 0"
                class="max-w-[600px] mx-auto flex flex-col items-center text-center py-16 px-4"
            >
                <svg viewBox="0 0 24 24" class="w-16 h-16 fill-sky-200 mb-4">
                    <path
                        d="M4 5h13l3 4v10a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2zm0 2v12h14V9.83L15.17 5H4zm8 3a4 4 0 1 1 0 8 4 4 0 0 1 0-8zm0 2a2 2 0 1 0 0 4 2 2 0 0 0 0-4z"
                    />
                </svg>
                <h3 class="text-base font-black text-black">
                    {{
                        isOwner
                            ? "You haven't posted anything yet"
                            : `${user.name} hasn't posted anything yet`
                    }}
                </h3>
                <p class="text-sm text-gray-400 mt-1">
                    {{
                        isOwner
                            ? "Once you share something, it'll show up here."
                            : "Check back later for updates."
                    }}
                </p>
            </div>

            <div v-else class="grid md:gap-4 gap-1 grid-cols-3 relative">
                <div
                    v-for="postByUser in postsByUser.data"
                    :key="postByUser.id"
                >
                    <ContentOverlay
                        :postByUser="postByUser"
                        @selectedPost="data.post = $event"
                    />
                </div>
            </div>

            <div class="pb-20"></div>
        </div>
    </MainLayout>

    <ShowPostOverlay
        v-if="data.post"
        :post="data.post"
        @addComment="addComment($event)"
        @updateLike="updateLike($event)"
        @deleteSelected="deleteFunc($event)"
        @closeOverlay="data.post = null"
    />
</template>
