<script setup>
import { Head, Link } from "@inertiajs/vue3";
import MainLayout from "@/Layouts/MainLayout.vue";
import moment from "moment";

const props = defineProps({
    newNotifications: Array,
    earlierNotifications: Array,
});
</script>

<template>
    <Head title="Notifications" />

    <MainLayout>
        <div
            class="md:min-w-[600px] w-full h-full min-h-screen pt-4 relative"
        >
            <div class="sticky md:top-0 top-[61px] sm:text-left bg-white px-4 mb-6 border-b border-gray-200 pb-4">
                <div class="text-3xl font-extrabold">Notifications</div>
            </div>

            <div v-if="newNotifications.length > 0">
                <div class="px-4 py-2 font-bold text-lg text-black">New</div>

                <div v-for="note in newNotifications" :key="note.id">
                    <Link
                        :href="`/index#${note.data.post_id}`"
                        class="flex items-center p-4 bg-blue-50 border-l-4 border-blue-500 mb-1"
                    >
                        <div class="mr-4">
                            <img
                                class="rounded-full w-10 h-10 object-cover border border-gray-200"
                                :src="
                                    note.data.notifier_file ||
                                    '/user-placeholder.png'
                                "
                            />
                        </div>

                        <div class="flex-1">
                            <div class="text-sm text-gray-800">
                                <span class="font-bold mr-1">{{
                                    note.data.notifier_name
                                }}</span>

                                <span>{{ note.data.message }}</span>
                            </div>

                            <div class="text-xs text-gray-500 mt-1">
                                {{ moment(note.created_at).fromNow() }}
                            </div>
                        </div>

                        <div class="ml-2">
                            <span v-if="note.data.type === 'like'">❤️</span>
                            <span v-if="note.data.type === 'comment'">💬</span>
                            <span v-if="note.data.type === 'share'">📢</span>
                            <span v-if="note.data.type === 'reminder'">⏰</span>
                        </div>
                    </Link>
                </div>
            </div>

            <div v-if="earlierNotifications.length > 0" class="mt-6">
                <div class="px-4 py-2 font-bold text-lg text-gray-800">
                    Earlier
                </div>

                <div v-for="note in earlierNotifications" :key="note.id">
                    <Link
                        :href="`/index#${note.data.post_id}`"
                        class="flex items-center p-4 hover:bg-gray-50 transition-colors border-b border-gray-100"
                    >
                        <div class="mr-4">
                            <img
                                class="rounded-full w-10 h-10 object-cover border border-gray-200"
                                :src="
                                    note.data.notifier_file ||
                                    '/user-placeholder.png'
                                "
                            />
                        </div>

                        <div class="flex-1">
                            <div class="text-sm text-gray-800">
                                <span class="font-bold mr-1">{{
                                    note.data.notifier_name
                                }}</span>

                                <span>{{ note.data.message }}</span>
                            </div>

                            <div class="text-xs text-gray-500 mt-1">
                                {{ moment(note.created_at).fromNow() }}
                            </div>
                        </div>

                        <div class="ml-2">
                            <span v-if="note.data.type === 'like'">❤️</span>
                            <span v-if="note.data.type === 'comment'">💬</span>
                            <span v-if="note.data.type === 'share'">📢</span>
                        </div>
                    </Link>
                </div>
            </div>

            <div
                v-if="
                    newNotifications.length === 0 &&
                    earlierNotifications.length === 0
                "
                class="text-center mt-20 text-gray-500 font-bold"
            >
                No notifications yet.
            </div>
        </div>
    </MainLayout>
</template>
