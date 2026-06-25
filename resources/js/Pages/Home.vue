<script setup>
import { ref, onMounted, onUnmounted, toRefs, computed } from "vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import MainLayout from "@/Layouts/MainLayout.vue";

import LikesSection from "@/Components/LikesSection.vue";
import ShowPostOverlay from "@/Components/ShowPostOverlay.vue";

import "vue3-carousel/dist/carousel.css";
import { Carousel, Slide, Navigation } from "vue3-carousel";

import DotsHorizontal from "vue-material-design-icons/DotsHorizontal.vue";
import Close from "vue-material-design-icons/Close.vue";
import ArrowRight from "vue-material-design-icons/ArrowRight.vue";

let wWidth = ref(window.innerWidth);
let currentSlide = ref(0);
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
const props = defineProps({
    posts: Object,
    allUsers: Object,
    myCircleIds: Array,
});
const { posts, allUsers } = toRefs(props);

// real time event listener by echo

// --- HELPER FUNCTION ---
// This finds the post in the list and updates just its likes
const handleReactionUpdate = (eventData) => {
    // Find the post in our current feed
    const post = posts.value.data.find((p) => p.id === eventData.id);

    if (post) {
        // console.log(`Updating likes for Post #${eventData.id}`);
        // Swap the old likes array with the new one from the server
        post.likes = eventData.likes;
    }
};

// --- HELPER FUNCTION: Update Comments ---
const handleCommentUpdate = (eventData) => {
    // Find the post in our feed
    const post = posts.value.data.find((p) => p.id === eventData.id);

    if (post) {
        // Swap the comments array with the new real-time data
        post.comments = eventData.comments;
    }
};

// --- HELPER FUNCTION: Remove Post ---
const handlePostDeletion = (eventData) => {
    // console.log(`Removing Post #${eventData.id}`);
    posts.value.data = posts.value.data.filter((p) => p.id !== eventData.id);
    currentPost.value = null;
    openOverlay.value = false;
};

// --- HELPER FUNCTION: Update Reminder Status ---
const handleReminderSent = (eventData) => {
    // Find the post in our feed
    const post = posts.value.data.find((p) => p.id === eventData.post_id);

    if (post) {
        // Find the specific reminder for the current user
        const reminder = (post.reminders || []).find(
            (r) => r.user_id === user.id,
        );

        if (reminder) {
            // Updating this value will automatically trigger the UI change
            // to show the TimerCheckOutline (sent/due) icon!
            reminder.sent_at = eventData.sent_at;
        }
    }
};

// --- NEW HELPER: Handle Moderation Results ---
const handleModerationUpdate = (eventData) => {
    if (eventData.type === "post") {
        if (eventData.status === "deleted") {
            // Rips the post out of the feed instantly
            handlePostDeletion({ id: eventData.contentId });
        } else if (eventData.status === "flagged") {
            // Find post and mark it flagged so UI can show a warning
            const post = posts.value.data.find(
                (p) => p.id === eventData.contentId,
            );
            if (post) post.status = "flagged";
        }
    } else if (eventData.type === "comment") {
        // Find which post contains this comment
        for (let post of posts.value.data) {
            const commentIndex = post.comments.findIndex(
                (c) => c.id === eventData.contentId,
            );
            if (commentIndex !== -1) {
                if (eventData.status === "deleted") {
                    // Rip the comment out instantly
                    post.comments.splice(commentIndex, 1);
                } else if (eventData.status === "flagged") {
                    post.comments[commentIndex].status = "flagged";
                }
                break; // Stop searching once found
            }
        }
    }
};

onMounted(() => {
    // 1. MY USER CHANNEL
    window.Echo.private(`App.Models.User.${user.id}`)
        // A. Post Created (Sync other tabs)
        .listen(".post.created", (e) => {
            // console.log("My new post (other tab):", e);
            posts.value.data.unshift(e);
        })
        // B. Post Deleted (Sync other tabs)
        .listen(".post.deleted", (e) => {
            handlePostDeletion(e);
        })
        .listen(".post.like.updated", (e) => {
            // <--- UPDATED NAME
            handleReactionUpdate(e);
        })
        .listen(".reminder.sent", (e) => {
            handleReminderSent(e);
        })
        // NEW: Listen for comments on my posts
        .listen(".post.comment.updated", (e) => {
            handleCommentUpdate(e);
        })
        // NEW: Listen for Moderation API results
        .listen(".content.moderated", (e) => {
            handleModerationUpdate(e);
        })
        .listen(".follower.post.shared", (e) => {
            // Find if the post already exists in this user's current feed array
            const existingPostIndex = posts.value.data.findIndex(
                (p) => p.id === e.id,
            );

            if (existingPostIndex !== -1) {
                // SCENARIO A: The user already has this post in their UI (likely the Post Owner)
                // BUG FIX: Preserve the author's optimistic is_shared_with_followers state.
                // When the author clicks "Share", they optimistically set is_shared_with_followers=true.
                // Previously, the spread `...e` would overwrite it with the (broken) event payload value.
                // Now the event correctly sends is_shared_with_followers=true, but we still protect
                // against race conditions by not letting the event downgrade the author's state.
                const existingPost = posts.value.data[existingPostIndex];
                const isAuthor = existingPost.user.id === user.id;

                // For the author: preserve their optimistic state on is_shared_with_followers
                // For other users: use the event's value
                const sharedState = isAuthor
                    ? existingPost.is_shared_with_followers
                    : e.is_shared_with_followers;

                posts.value.data[existingPostIndex] = {
                    ...existingPost,
                    ...e,
                    is_shared_with_followers: sharedState,
                };

                // If this post is currently active inside the comment/detail overlay, refresh it too
                if (currentPost.value && currentPost.value.id === e.id) {
                    currentPost.value = posts.value.data[existingPostIndex];
                }
            } else {
                // SCENARIO B: The user doesn't have the post yet (the Follower)
                // Add the brand new post to the very top of their timeline feed
                posts.value.data.unshift(e);
            }
        })
        // NEW: Listen for real-time unshares
        .listen(".follower.post.unshared", (e) => {
            // Locate the post to inspect who owns it before taking action
            const postIndex = posts.value.data.findIndex((p) => p.id === e.id);

            if (postIndex !== -1) {
                const targetPost = posts.value.data[postIndex];

                if (targetPost.user.id === user.id) {
                    // SCENARIO A: The current user is the OWNER of the post
                    // Do NOT erase it from their screen! Just update the toggle flag back to false
                    targetPost.is_shared_with_followers = false;

                    if (currentPost.value && currentPost.value.id === e.id) {
                        currentPost.value.is_shared_with_followers = false;
                    }
                } else {
                    // SCENARIO B: The current user is a FOLLOWER
                    // Completely strip the post out of their feed layout instantly
                    posts.value.data = posts.value.data.filter(
                        (p) => p.id !== e.id,
                    );

                    if (currentPost.value && currentPost.value.id === e.id) {
                        currentPost.value = null;
                        openOverlay.value = false;
                    }
                }
            }
        })
        // NEW: Authoritative, author-only, single-fire confirmation of share state.
        // Sent ONLY to the author's channel, exactly once per share/unshare action
        // (never broadcast to followers, never fired N times).
        .listen(".post.share.status.updated", (e) => {
            const post = posts.value.data.find((p) => p.id === e.id);

            if (post) {
                post.is_shared_with_followers = e.is_shared_with_followers;
            }

            if (currentPost.value && currentPost.value.id === e.id) {
                currentPost.value.is_shared_with_followers =
                    e.is_shared_with_followers;
            }
        });

    // 2. Listen for "Post Shared" in my Circles
    if (props.myCircleIds && props.myCircleIds.length) {
        props.myCircleIds.forEach((circleId) => {
            window.Echo.private(`circle.${circleId}`)
                .listen(".post.shared", (e) => {
                    // 'e' is now the clean object from AllPostsCollection
                    // console.log(`Real-time post shared in Circle ${circleId}:`, e);

                    // To add to feed dynamically:
                    posts.value.data.unshift(e);
                })
                // B. Handle Unshared Post (Remove)
                .listen(".post.unshared", (e) => {
                    // console.log(`Unshared from Circle ${circleId}:`, e);

                    // Logic: Remove post ONLY if I am NOT the owner.
                    // (Owners should still see their own posts even if unshared)
                    const postIndex = posts.value.data.findIndex(
                        (post) => post.id === e.id,
                    );

                    if (postIndex !== -1) {
                        const post = posts.value.data[postIndex];

                        // If I am NOT the owner, remove it from my view
                        if (post.user.id !== user.id) {
                            posts.value.data = posts.value.data.filter(
                                (p) => p.id !== e.id,
                            );
                        }
                    }
                })
                // C. Post Deleted (If a shared post is deleted by owner)
                .listen(".post.deleted", (e) => {
                    handlePostDeletion(e); // <--- REMOVE FROM FEED
                })
                .listen(".post.like.updated", (e) => {
                    // <--- UPDATED NAME
                    handleReactionUpdate(e);
                })
                // NEW: Listen for comments on shared posts
                .listen(".post.comment.updated", (e) => {
                    handleCommentUpdate(e);
                })
                .listen(".content.moderated", (e) => {
                    handleModerationUpdate(e);
                })
                .error((error) => {
                    console.error("Channel Error:", error);
                });
        });
    }
});

onUnmounted(() => {
    // Cleanup
    window.Echo.leave(`App.Models.User.${user.id}`);
    props.myCircleIds.forEach((id) => window.Echo.leave(`circle.${id}`));
});

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

const shareToFollowers = (post) => {
    const targetPost = posts.value.data.find((p) => p.id === post.id);

    if (targetPost) {
        targetPost.is_shared_with_followers = true;
    }

    openShareId.value = null;

    // IMPORTANT: Use fetch() here instead of router.post().
    // router.post() triggers a full Inertia visit, which re-runs
    // HomeController@index and overwrites posts.value.data with fresh
    // server props. Since the share job is queued (QUEUE_CONNECTION=database),
    // it almost never finishes before this response comes back, so the
    // fresh props would say is_shared_with_followers = false and stomp the
    // optimistic flip we just made above. fetch() avoids touching Inertia's
    // page props entirely — the websocket event (post.share.status.updated)
    // is now the single source of truth that confirms/corrects this state.
    fetch(`/posts/${post.id}/share-followers`, {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": document
                .querySelector('meta[name="csrf-token"]')
                .getAttribute("content"),
            Accept: "application/json",
        },
    })
        .then((res) => {
            if (!res.ok && targetPost) {
                // Server responded but rejected the request (e.g. 403/422). Roll back.
                targetPost.is_shared_with_followers = false;
            }
        })
        .catch(() => {
            // Network-level failure (server unreachable, etc). Roll back.
            if (targetPost) {
                targetPost.is_shared_with_followers = false;
            }
        });
};

const unshareFromFollowers = (post) => {
    const targetPost = posts.value.data.find((p) => p.id === post.id);

    if (targetPost) {
        targetPost.is_shared_with_followers = false;
    }

    openShareId.value = null;

    // Same reasoning as shareToFollowers() above: fetch() instead of
    // router.delete() so the Inertia page props are never touched here.
    fetch(`/posts/${post.id}/unshare-followers`, {
        method: "DELETE",
        headers: {
            "X-CSRF-TOKEN": document
                .querySelector('meta[name="csrf-token"]')
                .getAttribute("content"),
            Accept: "application/json",
        },
    })
        .then((res) => {
            if (!res.ok && targetPost) {
                targetPost.is_shared_with_followers = true;
            }
        })
        .catch(() => {
            if (targetPost) {
                targetPost.is_shared_with_followers = true;
            }
        });
};

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
    // 1. Capture the values into simple variables immediately
    // This "saves" them so they aren't lost when the 'object' is deleted
    const id = object.id;
    const type = object.deleteType;
    const postReference = object.post; // If it's a comment, save its parent post info

    let url = type === "Post" ? `/posts/${id}` : `/comments/${id}`;

    router.delete(url, {
        // 2. Pass a "clean" reconstruction to updatedPost
        // This avoids the 'undefined' error because we aren't relying on the original reactive object
        onFinish: () => {
            if (type === "Comment") {
                // If you're deleting a comment, you likely need to
                // refresh the parent post's comment count
                updatedPost({ id: id, deleteType: type, post: postReference });
            } else {
                // If the post itself is deleted, updatedPost might
                // not even be necessary anymore, but if it is:
                updatedPost({ id: id, deleteType: type });
            }
        },
    });

    if (type === "Post") {
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
            preserveScroll: true,
        });
    } else {
        router.post(
            "/likes",
            {
                post_id: object.post.id,
            },
            {
                onFinish: () => updatedPost(object),
                preserveScroll: true,
            },
        );
    }
};

const updatedPost = (object) => {
    // 1. Safety Guard: If there's no object, or no parent post (like when deleting a Post), stop.
    if (!object || !object.post) {
        return;
    }

    // 2. Loop through the fresh data from Inertia
    for (let i = 0; i < posts.value.data.length; i++) {
        const post = posts.value.data[i];

        // 3. Safe comparison using the captured parent post ID
        if (post.id === object.post.id) {
            currentPost.value = post;
            break; // Stop looping once found
        }
    }
};

const toggleSave = (post) => {
    post.saves = post.saves ?? [];

    let alreadySaved = false;
    let saveId = null;

    // Check if user already saved
    for (let i = 0; i < post.saves.length; i++) {
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
            onFinish: () => updatedPost({ post }),
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
        <div class="mx-auto lg:pl-0 md:pl-[80px] pl-0">
            <Carousel
                v-model="currentSlide"
                class="max-w-[700px] mx-auto"
                :items-to-show="wWidth >= 768 ? 8 : 6"
                :items-to-scroll="4"
                :wrap-around="true"
                :transition="500"
                snapAlign="start"
            >
                <Slide v-for="slide in allUsers" :key="slide">
                    <Link
                        :href="route('users.show', { id: slide.id })"
                        class="relative mx-auto text-center mt-4 px-2 cursor-pointer"
                    >
                        <div
                            class="absolute z-[-1] -top-[5px] left-[4px] rounded-full rotate-45 w-[64px] h-[64px] contrast-[1.3] bg-gradient-to-t from-yellow-300 to-purple-500 via-red-500"
                        >
                            <div
                                class="rounded-full ml-[3px] mt-[3px] w-[58px] h-[58px] bg-white"
                            />
                        </div>
                        <img
                            class="rounded-full w-[56px] h-[56px] -mt-[1px] ml-[2px]"
                            :src="slide.file"
                        />
                        <div
                            class="text-xs mt-2 w-[60px] truncate text-ellipsis overflow-hidden"
                        >
                            {{ slide.name }}
                        </div>
                    </Link>
                </Slide>

                <template #addons>
                    <Navigation />
                </template>
            </Carousel>

            <div
                class="px-4 max-w-[600px] mx-auto mt-10"
                v-for="post in posts.data"
                :key="post.id"
                :id="post.id"
            >
                <div class="flex items-center justify-between py-2">
                    <div class="flex items-center">
                        <Link
                            :href="route('users.show', { id: post.user.id })"
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
                    <!-- @click="deleteFunc({id:post.id,deleteType : 'Post'})" -->
                </div>
                <div class="postControllerOverlay relative">
                    <div
                        v-if="post.status === 'flagged'"
                        class="absolute backdrop-blur-md shadow-md bg-white/30 rounded-sm w-full h-full flex items-center justify-center flex-col"
                    >
                        <p class="font-bold text-lg">
                            This Post may contains sensitive Content
                        </p>
                        <p
                            class="flex gap-2 items-center text-lg my-4 text-blue-500 hover:text-gray-900 cursor-pointer"
                            @click="post.status = 'allowed'"
                        >
                            See Anyway
                        </p>
                    </div>
                    <div class="text-lg my-4">
                        {{ post.text }}
                    </div>
                    <a
                        :href="post.url"
                        class="flex gap-2 items-center text-lg my-4 text-blue-500 hover:text-gray-900 cursor-pointer"
                        target="blank"
                    >
                        <div>Visit Site</div>
                        <ArrowRight :size="22" />
                    </a>

                    <!-- <div class="bg-black rounded-lg w-full min-h-[400px] flex items-center">
                    <img class="mx-auto w-full" :src="post.file" />
                </div> -->

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
                    <!-- <div>
                    <span class="text-black font-extrabold">{{ post.user.name }}</span>
                    {{ post.text }}
                </div> -->
                    <div class="flex justify-between">
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
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <button
                            v-if="post.is_shared_with_followers"
                            @click="unshareFromFollowers(post)"
                            class="w-full py-2.5 bg-red-500 hover:bg-red-600 text-white rounded-xl text-sm font-bold transition shadow-sm active:scale-95"
                        >
                            Unshare from All My Followers
                        </button>

                        <button
                            v-else
                            @click="shareToFollowers(post)"
                            class="w-full py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-bold transition shadow-sm active:scale-95"
                        >
                            Share to All My Followers
                        </button>
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
                            <div class="text-xs font-bold mb-2 text-gray-700">
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

<style>
.carousel__prev,
.carousel__next,
.carousel__prev:hover,
.carousel__next:hover {
    color: rgb(49, 49, 49);
    background-color: rgb(255, 255, 255);
    border-radius: 100%;
    margin: 0 20px;
}
</style>
