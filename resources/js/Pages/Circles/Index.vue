<script setup>
import { reactive, ref, toRefs } from "vue";
import { Head, router, Link } from "@inertiajs/vue3"; // Added Link for better SPA navigation

import MainLayout from "@/Layouts/MainLayout.vue";

import ShowPostOptionsOverlay from "@/Components/ShowPostOptionsOverlay.vue";

import Plus from "vue-material-design-icons/Plus.vue";
import TrashCanOutline from "vue-material-design-icons/TrashCanOutline.vue";
import AccountGroupOutline from "vue-material-design-icons/AccountGroupOutline.vue";

const props = defineProps({
    circles: Object,
});

const { circles } = toRefs(props);
const error = ref("");
let deleteType = ref(null);
let id = ref(null);

// Form for creating circle
const form = reactive({
    name: "",
});

// Submit circle
const createCircle = () => {
    if (!form.name.trim()) return;

    router.post("/circles", form, {
        preserveScroll: true,
        onError: (errors) => {
            errors && errors.name ? (error.value = errors.name) : "";
        },
        onSuccess: () => {
            form.name = "";
            error.value = "";
        },
    });
};

const openDeleteCircleOverLay = (circleId) => {
    id.value = circleId;
    deleteType.value = "Circle";
};

// Delete circle
const deleteCircle = (circleId) => {
        router.delete(`/circles/${circleId.id}`, {
            preserveScroll: true,
        });
};
</script>

<template>
    <Head title="My Circles" />

    <MainLayout>
        <div class="max-w-[1000px] mx-auto px-4 pt-10 pb-20">
            <div class="mb-10 text-center sm:text-left">
                <h1 class="text-4xl font-black tracking-tighter text-gray-900">
                    Circles
                </h1>
                <p class="text-gray-500 font-medium mt-2">
                    Group your friends and share content privately.
                </p>
            </div>

            <div
                class="bg-white border border-gray-200 rounded-3xl p-2 mb-3 shadow-sm transition-focus-within  ring-slate-500 focus-within:ring-2"
            >
                <div class="flex items-center gap-2">
                    <input
                        v-model="form.name"
                        type="text"
                        placeholder="Name your new circle..."
                        class="w-full border-none bg-transparent rounded-2xl px-5 py-4 text-lg font-semibold focus:ring-0 placeholder:text-gray-400"
                        @keyup.enter="createCircle"
                    />
                    <button
                        @click="createCircle"
                        class="bg-black text-white p-4 rounded-2xl flex items-center justify-center hover:bg-gray-800 transition-all active:scale-95 shadow-lg shadow-gray-200"
                    >
                        <Plus :size="24" />
                        <span class="hidden sm:inline ml-2 pr-2 font-bold"
                            >Create</span
                        >
                    </button>
                </div>
            </div>
            <p class="text-red-500 font-semibold mb-10">{{ error }}</p>
            <div v-if="circles.length > 0">
                <div
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
                >
                    <div
                        v-for="circle in circles"
                        :key="circle.id"
                        class="group relative bg-white border border-gray-300 rounded-[2rem] p-6 hover:shadow-xl hover:shadow-gray-100 hover:border-slate-800 transition-all duration-300 flex flex-col justify-between min-h-[180px]"
                    >
                        <div class="flex justify-between items-start">
                            <div
                                class="bg-gray-100 p-3 rounded-2xl group-hover:bg-black group-hover:text-white transition-colors"
                            >
                                <AccountGroupOutline :size="26" />
                            </div>

                            <button
                                @click="openDeleteCircleOverLay(circle.id)"
                                class="text-gray-300 hover:text-red-500 transition-colors p-2"
                            >
                                <TrashCanOutline :size="20" />
                            </button>
                        </div>

                        <div class="mt-4">
                            <h2 class="text-xl font-black truncate pr-4">
                                {{ circle.name }}
                            </h2>
                            <p
                                class="text-gray-400 text-xs font-bold uppercase tracking-widest mt-1"
                            >
                                {{ circle.created_at }}
                            </p>
                        </div>

                        <Link
                            :href="`/circles/${circle.id}/members`"
                            class="mt-6 flex items-center justify-center w-full py-3 bg-gray-50 rounded-xl text-sm font-black group-hover:bg-black group-hover:text-white transition-all"
                        >
                            Manage Members
                        </Link>
                    </div>
                </div>
            </div>

            <div
                v-else
                class="flex flex-col items-center justify-center py-20 bg-gray-50 rounded-[3rem] border-2 border-dashed border-gray-200"
            >
                <div class="bg-gray-200 p-5 rounded-full mb-4 text-gray-400">
                    <AccountGroupOutline :size="40" />
                </div>
                <h3 class="text-xl font-bold text-gray-800">
                    No circles found
                </h3>
                <p class="text-gray-500 mt-1">
                    Create your first circle to get started.
                </p>
            </div>
        </div>
    </MainLayout>
    <ShowPostOptionsOverlay
        v-if="deleteType"
        :deleteType="deleteType"
        :id="id"
        @deleteSelected="
            deleteCircle( $event);
            deleteType = null;
            id = null;
        "
        @close="
            deleteType = null;
            id = null;
        "
    />
</template>

<style scoped>
.transition-focus-within {
    transition: all 0.2s ease-in-out;
}
</style>
