<script setup>
import { toRefs } from 'vue'

defineEmits(['close', 'deleteSelected', 'editSelected'])
const props = defineProps({ 
    deleteType: String, 
    id: Number,
    postObj: Object // Added this optional object
})

const { deleteType, id, postObj } = toRefs(props)
</script>

<template>
    <div
        id="ShowPostOptionsOverlay"
        class="fixed flex items-center z-50 top-0 left-0 w-full h-screen bg-[#000000] bg-opacity-60 p-3"
    >
        <div class="max-w-sm w-full mx-auto mt-10 bg-white rounded-xl text-center overflow-hidden">
            <!-- Edit Button only shows for posts -->
            <button
                v-if="deleteType === 'Post'"
                @click="$emit('editSelected', postObj)"
                class="font-extrabold w-full text-black hover:bg-gray-50 p-3 text-lg border-b border-b-gray-300 cursor-pointer transition"
            >
                Edit Post
            </button>
            <button
                @click="$emit('deleteSelected', { deleteType, id, post: postObj });"
                class="font-extrabold w-full text-red-600 hover:bg-red-50 p-3 text-lg border-b border-b-gray-300 cursor-pointer transition"
            >
                Delete {{ deleteType }}
            </button>
            <div class="p-3 text-lg cursor-pointer hover:bg-gray-50 transition" @click="$emit('close')">Cancel</div>
        </div>
    </div>
</template>