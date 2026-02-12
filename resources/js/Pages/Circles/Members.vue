<script setup>
import { reactive, ref, computed, toRefs } from "vue";
import { Head, router } from "@inertiajs/vue3";

import MainLayout from "@/Layouts/MainLayout.vue";
import Close from "vue-material-design-icons/Close.vue";
import ShareOutline from "vue-material-design-icons/ShareOutline.vue";
import Magnify from "vue-material-design-icons/Magnify.vue"; // Added for search icon
import ChevronDown from "vue-material-design-icons/ChevronDown.vue";
import Cog from "vue-material-design-icons/Cog.vue";
import ShowPostOptionsOverlay from "@/Components/ShowPostOptionsOverlay.vue";

const props = defineProps({
    circle: Object,
    members: Array,
    nonMembers: Array,
});

const { circle, members, nonMembers } = toRefs(props);
const isAddMembersOpen = ref(true);
// open update UI toggler
const isMenuOpen = ref(false);
const isEditing = ref(false);
const error = ref(false);
// for delete circle 
let deleteType = ref(null);
let id = ref(null);



// Delete circle
const deleteCircle = (circleId) => {
        router.delete(`/circles/${circleId.id}`, {
           onSuccess: () => {
                router.visit('/circles');
            },
        });
};


const enableEdit = () => {
    isEditing.value = true;
    isMenuOpen.value = false; // Close menu when editing starts
};

/* Logic remains exactly as provided */
const circleForm = reactive({ name: circle.value.name });
const updateCircleName = () => {
    router.put(
        `/circles/${circle.value.id}`,
        { name: circleForm.name },
        {
            onError: (errors) => {
                errors && errors.name ? (error.value = errors.name) : "";
            },
            onSuccess: () => {
                isEditing.value = false;
                isMenuOpen.value = false;
                error.value = "";
            },
        },
    );
};

const search = ref("");
const filteredMembers = computed(() => {
    return members.value.filter((u) =>
        (u.name + u.email).toLowerCase().includes(search.value.toLowerCase()),
    );
});
const filteredNonMembers = computed(() => {
    return nonMembers.value.filter((u) =>
        (u.name + u.email).toLowerCase().includes(search.value.toLowerCase()),
    );
});

const removeMember = (userId) => {
    router.delete(`/circles/${circle.value.id}/members/${userId}`, {
        preserveScroll: true,
    });
};
const addMember = (userId) => {
    router.post(
        `/circles/${circle.value.id}/members`,
        { user_id: userId },
        { preserveScroll: true },
    );
};
</script>

<template>
    <Head title="Circle Management" />

    <MainLayout>
        <div
            class="max-w-[1000px] lg:min-w-[calc(100%-300px)] md:min-w-[calc(100%-100px)] min-w-full mx-auto px-4 pt-6 pb-20 absolute xl:left-[300px] md:left-[100px] left-0 "
        >
            <div class="flex items-center gap-4 mb-6">
                <h1 class="text-2xl font-black tracking-tight">
                    Manage Circle
                </h1>
            </div>

            <div
                class="bg-white border border-gray-200 rounded-3xl p-6 mb-6 shadow-sm md:w-1/2"
            >
                <label
                    class="text-xs font-bold text-gray-400 uppercase tracking-widest ml-1 mb-2 block"
                    >Circle Name</label
                >
                <!-- <div class="flex flex-col sm:flex-row items-center gap-3">
                    <input
                        v-model="circleForm.name"
                        placeholder="Enter circle name..."
                        class="w-full bg-gray-50 border-none rounded-2xl px-5 py-4 text-xl font-bold focus:ring-2 focus:ring-black transition"
                    />
                    <button
                        @click="updateCircleName"
                        class="w-full sm:w-auto bg-black text-white px-8 py-4 rounded-2xl font-bold hover:bg-gray-800 active:scale-95 transition-all shadow-lg shadow-gray-200"
                    >
                        Update
                    </button>
                </div> -->

                <div class="relative inline-block w-full max-w-lg">
                    <div
                        v-if="!isEditing"
                        class="flex items-center justify-between bg-white rounded-2xl"
                    >
                        <h1
                            class="md:text-4xl text-2xl font-black text-gray-900"
                        >
                            {{ circle.name }}
                        </h1>

                        <button
                            @click="isMenuOpen = !isMenuOpen"
                            class="p-2 hover:bg-gray-100 rounded-full transition-colors relative"
                        >
                            <Cog class="w-6 h-6 text-gray-600" />

                            <div
                                v-if="isMenuOpen"
                                class="absolute right-0 top-12 w-32 bg-white shadow-xl rounded-xl border border-gray-100 py-2 z-10"
                            >
                                <h2
                                    @click="enableEdit"
                                    class="px-4 py-2 hover:bg-gray-50 cursor-pointer font-semibold text-sm text-gray-700"
                                >
                                    Edit
                                </h2>
                                <button
                                    class="px-4 py-2 hover:bg-gray-50 cursor-pointer font-semibold text-sm text-red-600 border-t border-gray-50"
                                @click=" id = circle.id; deleteType = 'Circle';"
                                    >
                                    Delete
                                </button>
                            </div>
                        </button>
                    </div>
                    <div v-else>
                        <div
                            class="flex flex-col sm:flex-row items-center gap-3 animate-in fade-in zoom-in duration-200"
                        >
                            <input
                                v-model="circleForm.name"
                                placeholder="Enter circle name..."
                                class="w-full bg-white rounded-xl p-2 shadow text-xl font-bold focus:ring-0 transition"
                                auto-focus
                            />

                            <div class="flex gap-2 w-full sm:w-auto">
                                <button
                                    @click="updateCircleName"
                                    class="flex-1 sm:flex-none bg-gray-700 text-white px-6 py-[0.6rem] rounded-xl font-bold hover:bg-gray-800 active:scale-95 transition-all"
                                >
                                    Update
                                </button>
                                <button
                                    @click="
                                        isEditing = false;
                                        isMenuOpen = false;
                                        circleForm.name=circle.name;
                                        error='';
                                    "
                                    class="bg-gray-200 text-gray-700 px-4 py-[0.6rem] rounded-xl font-bold hover:bg-gray-300 transition"
                                >
                                    Cancel
                                </button>
                            </div>
                        </div>
                        <p v-if="error" class="text-red-500 mr-4 mt-2">{{error}}</p>
                    </div>
                </div>

                <!-- hcomment  -->

                <div class="mt-4 flex items-center gap-2">
                    <div class="h-2 w-2 rounded-full bg-green-500"></div>
                    <p class="text-gray-500 font-bold text-sm">
                        {{ members.length }} Active Members
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
                    placeholder="Search people to add or remove..."
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
                                Add People
                            </h2>
                            <span
                                class="bg-blue-50 text-blue-600 text-xs font-bold px-2 py-1 rounded-md"
                                >{{ filteredNonMembers.length }}</span
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
                            v-if="filteredNonMembers.length"
                            class="[&::-webkit-scrollbar]:hidden space-y-3 flex items-center gap-2 flex-wrap max-h-[350px] overflow-y-scroll mb-2"
                        >
                            <div
                                v-for="user in filteredNonMembers"
                                :key="user.id"
                                @click="addMember(user.id)"
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
                                        <ShareOutline :size="14" />
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
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
                                    Add
                                </div>
                            </div>
                        </div>
                        <div
                            v-else
                            class="text-center py-10 bg-gray-50 rounded-3xl border-2 border-dashed border-gray-200"
                        >
                            <p class="text-gray-400 font-medium italic">
                                Everyone is already in the circle!
                            </p>
                        </div>
                    </div>
                </section>

                <section>
                    <div class="flex items-center gap-5 mb-5 px-1">
                        <h2
                            class="text-lg font-black italic uppercase tracking-tighter"
                        >
                            Current Members
                        </h2>
                        <span
                            class="bg-gray-100 text-gray-600 text-xs font-bold px-2 py-1 rounded-md"
                            >{{ filteredMembers.length }}</span
                        >
                    </div>

                    <div
                        v-if="filteredMembers.length"
                        class="space-y-3 [&::-webkit-scrollbar]:hidden flex items-center gap-2 flex-wrap max-h-[350px] overflow-y-scroll mb-2"
                    >
                        <div
                            v-for="user in filteredMembers"
                            :key="user.id"
                            class="group relative flex items-center gap-4 p-4 bg-white border border-gray-100 rounded-2xl hover:shadow-md transition-all duration-300"
                        >
                            <img
                                :src="user.file ?? '/default.png'"
                                class="w-14 h-14 rounded-full object-cover border-2 border-white ring-1 ring-gray-100"
                            />
                            <div class="flex-1 min-w-0">
                                <p class="font-black text-gray-900 truncate">
                                    {{ user.name }}
                                </p>
                                <p class="text-sm text-gray-500 truncate">
                                    {{ user.email }}
                                </p>
                            </div>
                            <button
                                @click="removeMember(user.id)"
                                class="p-2 text-gray-300 hover:text-red-500 hover:bg-red-50 rounded-xl transition-colors"
                                title="Remove Member"
                            >
                                <Close :size="24" />
                            </button>
                        </div>
                    </div>
                    <div
                        v-else
                        class="text-center py-10 bg-gray-50 rounded-3xl border-2 border-dashed border-gray-200"
                    >
                        <p class="text-gray-400 font-medium italic">
                            No members found matching your search.
                        </p>
                    </div>
                </section>
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
