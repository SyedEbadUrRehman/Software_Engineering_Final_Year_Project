<script setup>
import { ref, computed, toRefs } from "vue";
import { Head, router } from "@inertiajs/vue3";
import MainLayout from "@/Layouts/MainLayout.vue";
import Close from "vue-material-design-icons/Close.vue";
import AccountPlusOutline from "vue-material-design-icons/AccountPlusOutline.vue";
import Magnify from "vue-material-design-icons/Magnify.vue";
import ChevronDown from "vue-material-design-icons/ChevronDown.vue";

const props = defineProps({
    following: Array,
    suggestions: Array,
});

const { following, suggestions } = toRefs(props);
const isAddMembersOpen = ref(true);
const search = ref("");

const filteredFollowing = computed(() => {
    return following.value.filter((u) =>
        (u.name + u.email).toLowerCase().includes(search.value.toLowerCase()),
    );
});
const filteredSuggestions = computed(() => {
    return suggestions.value.filter((u) =>
        (u.name + u.email).toLowerCase().includes(search.value.toLowerCase()),
    );
});

const unfollowUser = (userId) => {
    router.delete(`/users/${userId}/unfollow`, { preserveScroll: true });
};
const followUser = (userId) => {
    router.post(`/users/${userId}/follow`, {}, { preserveScroll: true });
};
</script>

<template>
    <Head title="Manage Follows" />

    <MainLayout>
        <div
            class="max-w-[1000px] lg:min-w-[calc(100%-300px)] md:min-w-[calc(100%-100px)] min-w-full mx-auto px-4 pt-6 pb-20 absolute xl:left-[300px] md:left-[100px] left-0"
        >
            <div class="flex items-center gap-4 mb-6">
                <h1 class="text-2xl font-black tracking-tight">
                    Manage Network
                </h1>
            </div>

            <div
                class="bg-white border border-gray-200 rounded-3xl p-6 mb-6 shadow-sm md:w-1/2"
            >
                <h1 class="md:text-4xl text-2xl font-black text-gray-900 mb-2">
                    My Network
                </h1>
                <div class="flex items-center gap-2">
                    <div class="h-2 w-2 rounded-full bg-blue-500"></div>
                    <p class="text-gray-500 font-bold text-sm">
                        Following {{ following.length }} Users
                    </p>
                </div>
            </div>

            <div class="relative mb-8 group">
                <div
                    class="absolute inset-y-0 left-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-black transition-colors"
                >
                    <Magnify :size="22" />
                </div>
                <input
                    v-model="search"
                    placeholder="Search people to follow or unfollow..."
                    class="w-full bg-white border border-gray-200 rounded-2xl pl-12 pr-4 py-4 focus:outline-none focus:ring-2 focus:ring-black focus:border-transparent transition shadow-sm"
                />
            </div>

            <div>
                <section>
                    <div class="flex items-center justify-between mb-5 px-1">
                        <div class="inline-flex gap-5 items-center">
                            <h2
                                class="text-lg font-black italic uppercase tracking-tighter text-gray-400"
                            >
                                Discover People
                            </h2>
                            <span
                                class="bg-blue-50 text-blue-600 text-xs font-bold px-2 py-1 rounded-md"
                                >{{ filteredSuggestions.length }}</span
                            >
                        </div>
                        <ChevronDown
                            class="transition cursor-pointer delay-[0.1s] p-1 rounded-full hover:bg-gray-100"
                            :class="[
                                isAddMembersOpen ? 'rotate-180' : 'rotate-0',
                            ]"
                            @click="isAddMembersOpen = !isAddMembersOpen"
                        />
                    </div>
                    <div v-if="isAddMembersOpen">
                        <div
                            v-if="filteredSuggestions.length"
                            class="[&::-webkit-scrollbar]:hidden space-y-3 flex items-center gap-2 flex-wrap max-h-[350px] overflow-y-scroll mb-2"
                        >
                            <div
                                v-for="user in filteredSuggestions"
                                :key="user.id"
                                @click="followUser(user.id)"
                                class="group cursor-pointer flex items-center gap-4 px-4 py-1 bg-gray-50 border border-transparent rounded-2xl hover:bg-white hover:border-blue-200 hover:shadow-md transition-all duration-300"
                            >
                                <div class="relative">
                                    <img
                                        :src="user.file ?? '/default.png'"
                                        class="w-14 h-14 rounded-full object-cover transition-all shadow-sm"
                                    />
                                    <div
                                        class="absolute -bottom-1 -right-1 bg-blue-600 text-white rounded-full p-0.5 border-2 border-white opacity-0 group-hover:opacity-100 transition-opacity"
                                    >
                                        <AccountPlusOutline :size="14" />
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0 pr-4">
                                    <p
                                        class="font-black text-gray-600 group-hover:text-black transition-colors truncate"
                                    >
                                        {{ user.name }}
                                    </p>
                                    <p class="text-sm text-gray-400 truncate">
                                        {{ user.email }}
                                    </p>
                                </div>
                                <div
                                    class="text-blue-500 font-black text-xs uppercase opacity-0 group-hover:opacity-100 tracking-widest transition-opacity pr-2"
                                >
                                    Follow
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="mt-8">
                    <div class="flex items-center gap-5 mb-5 px-1">
                        <h2
                            class="text-lg font-black italic uppercase tracking-tighter"
                        >
                            Following
                        </h2>
                        <span
                            class="bg-gray-100 text-gray-600 text-xs font-bold px-2 py-1 rounded-md"
                            >{{ filteredFollowing.length }}</span
                        >
                    </div>

                    <div
                        v-if="filteredFollowing.length"
                        class="space-y-3 [&::-webkit-scrollbar]:hidden flex items-center gap-2 flex-wrap max-h-[350px] overflow-y-scroll mb-2"
                    >
                        <div
                            v-for="user in filteredFollowing"
                            :key="user.id"
                            class="group relative flex cursor-pointer items-center gap-4 p-4 bg-white border border-gray-100 rounded-2xl hover:shadow-md transition-all duration-300"
                        >
                            <img
                                :src="user.file ?? '/default.png'"
                                class="w-14 h-14 rounded-full object-cover border-2 border-white ring-1 ring-gray-100"
                            />
                            <div class="flex-1 min-w-0 pr-4">
                                <p class="font-black text-gray-900 truncate">
                                    {{ user.name }}
                                </p>
                                <p class="text-sm text-gray-400 truncate">
                                        {{ user.email }}
                                    </p>
                            </div>
                            <button
                                @click="unfollowUser(user.id)"
                                class="px-4 py-2 bg-gray-100 text-gray-600 hover:text-white hover:bg-red-500 rounded-xl transition-colors font-bold text-xs"
                                title="Unfollow"
                            >
                                Unfollow
                            </button>
                        </div>
                    </div>
                    <div
                        v-else
                        class="text-center py-10 bg-gray-50 rounded-3xl border-2 border-dashed border-gray-200"
                    >
                        <p class="text-gray-400 font-medium italic">
                            You aren't following anyone yet.
                        </p>
                    </div>
                </section>
            </div>
        </div>
    </MainLayout>
</template>
