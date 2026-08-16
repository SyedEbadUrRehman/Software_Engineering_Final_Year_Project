<script setup>
import { ref, watch } from "vue";
import { Head, router } from "@inertiajs/vue3";
import MainLayout from "@/Layouts/MainLayout.vue";
import ShareOutline from "vue-material-design-icons/ShareOutline.vue";
import Magnify from "vue-material-design-icons/Magnify.vue";

const props = defineProps({
    users: Array,
    filters: Object,
});

const search = ref(props.filters.search || "");

// Watch the search input and send requests with a slight delay (debounce)
let searchTimeout = null;
watch(search, (value) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(
            "/followers",
            { search: value },
            {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            },
        );
    }, 300); // Waits 300ms after user stops typing
});

const viewUser = (id) => {
    router.visit(`/users/${id}`);
};
</script>

<template>
    <Head title="Manage Follows" />

    <MainLayout>
        <div
            class="max-w-[1000px] lg:min-w-[calc(100%-300px)] md:min-w-[calc(100%-100px)] min-w-full mx-auto px-4 pt-6 pb-20 absolute xl:left-[300px] md:left-[100px] left-0"
        >
          

            <div
                class="bg-white border border-gray-200 rounded-3xl p-6 mb-6 shadow-sm md:w-1/2"
            >
                <h1 class="md:text-4xl text-2xl font-black text-gray-900 mb-2">
                    My Follower
                </h1>
                <div class="flex items-center gap-2">
                    <div class="h-2 w-2 rounded-full bg-blue-500"></div>
                    <p class="text-gray-500 font-bold text-sm">
                         {{ users.length }} Users Following you
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
                <section class="mt-8">
                   

                    <div
                        v-if="users.length"
                        class="space-y-3 [&::-webkit-scrollbar]:hidden flex items-center gap-2 flex-wrap max-h-[350px] overflow-y-scroll mb-2"
                    >
                        <div
                            v-for="user in users"
                            :key="user.id"
                            @click="viewUser(user.id)"
                            class="group relative flex cursor-pointer items-center gap-4 p-4 bg-white border border-gray-200 rounded-2xl shadow-lg transition-all duration-300"
                        >
                            <div class="relative">
                                <img
                                    :src="user.file ?? '/default.png'"
                                    class="w-14 h-14 rounded-full object-cover border-2 border-white ring-1 ring-gray-100"
                                />
                                <div
                                    class="absolute -bottom-1 -right-1 bg-blue-600 text-white rounded-full p-0.5 border-2 border-white opacity-0 group-hover:opacity-100 transition-opacity"
                                >
                                    <ShareOutline :size="14" />
                                </div>
                            </div>
                            <div class="flex-1 min-w-0 pr-4">
                                <p class="font-black text-gray-900 truncate">
                                    {{ user.name }}
                                </p>
                                <p class="text-sm text-gray-400 truncate">
                                    {{ user.email }}
                                </p>
                            </div>
                            <div
                                class="text-blue-500 font-black text-xs uppercase opacity-0 group-hover:opacity-100 tracking-widest transition-opacity pr-2"
                            >
                                View
                            </div>
                        </div>
                    </div>
                    <div
                        v-else
                        class="text-center py-10 bg-gray-50 rounded-3xl border-2 border-dashed border-gray-200"
                    >
                        <p class="text-gray-400 font-medium italic">
                            You aren't any follower yet.
                        </p>
                    </div>
                </section>
            </div>
        </div>
    </MainLayout>
</template>

