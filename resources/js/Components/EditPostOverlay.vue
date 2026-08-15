<script setup>
import { ref, onMounted, onBeforeUnmount } from "vue";
import { useForm, usePage } from "@inertiajs/vue3";
import Close from "vue-material-design-icons/Close.vue";
import ArrowLeft from "vue-material-design-icons/ArrowLeft.vue";

const user = usePage().props.auth.user;
const emit = defineEmits(["close"]);
const props = defineProps({
    post: Object
});

// Parse existing URLs from the string into an array
const parseUrls = (urlString) => {
    if (!urlString) return [];
    return urlString.split('|').map(u => u.trim()).filter(u => u !== "");
};

// State for URLs
const urlBadges = ref(parseUrls(props.post.url));
const currentUrlInput = ref("");

const form = useForm({
    text: props.post.text,
    url: props.post.url,
});

// Convert input to badge on Ctrl + Enter
const addUrlBadge = () => {
    const val = currentUrlInput.value.trim();
    if (val) {
        // Prevent duplicate badges
        if (!urlBadges.value.includes(val)) {
            urlBadges.value.push(val);
        }
        currentUrlInput.value = "";
        form.errors.url = null;
    }
};

// Remove badge by clicking the cross icon
const removeUrlBadge = (index) => {
    urlBadges.value.splice(index, 1);
};

// Listen for Browser Extension Messages
const handleTabMessage = (event) => {
    if (!event.data || event.data.type !== "tab_info") return;
    
    // Populate the input (User can then press Ctrl+Enter or just click Save)
    currentUrlInput.value = event.data.url || "";
};

const updatePostFunc = () => {
    // 1. Grab all badges
    let finalUrls = [...urlBadges.value];
    
    // 2. Check if user typed something but forgot to hit Ctrl+Enter
    if (currentUrlInput.value.trim()) {
        finalUrls.push(currentUrlInput.value.trim());
    }

    // 3. Join them back into a | separated string for the database
    form.url = finalUrls.join(" | ");

    // 4. Send request
    form.put(`/posts/${props.post.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            emit("close");
        },
    });
};

onMounted(() => {
  // 1. Listen for the extension's reply
    window.addEventListener("message", handleTabMessage);

    // 2. Immediately ask the extension for the current tab's URL
    window.parent.postMessage("request_tab_info", "*");
});

onBeforeUnmount(() => {
    window.removeEventListener("message", handleTabMessage);
});
</script>

<template>
    <div id="EditOverlaySection" class="fixed z-50 top-0 left-0 w-full h-screen bg-[#000000] bg-opacity-60 p-3">
        <button class="absolute right-3 cursor-pointer" @click="$emit('close')">
            <Close :size="27" fillColor="#FFFFFF" />
        </button>

        <div class="max-w-4xl h-[calc(100%-100px)] mx-auto mt-10 bg-white rounded-xl flex flex-col">
            <!-- Header -->
            <div class="flex items-center justify-between w-full rounded-t-xl p-3 border-b border-gray-300">
                <ArrowLeft :size="30" fillColor="#000000" @click="$emit('close')" class="cursor-pointer" />
                <div class="text-lg font-extrabold">Edit Post</div>
                <button @click="updatePostFunc()" class="text-lg text-blue-500 hover:text-gray-900 font-extrabold" :disabled="form.processing">
                    Save
                </button>
            </div>

            <div class="w-full flex-1 rounded-xl overflow-auto p-4">
                <!-- User Info -->
                <div class="flex items-center mb-4">
                    <img class="rounded-full w-[38px] h-[38px]" :src="user.file" />
                    <div class="ml-4 font-extrabold text-[15px]">{{ user.name }}</div>
                </div>

                <!-- Text Area -->
                <div v-if="form.errors.text" class="text-red-500 p-2 font-extrabold">{{ form.errors.text }}</div>
                
                <textarea
                    v-model="form.text"
                    placeholder="Write caption..."
                    rows="8"
                    class="w-full border-gray-300 rounded-lg focus:ring-black text-gray-600 text-[18px] mb-4"
                ></textarea>

                <!-- URL Section -->
                <div class="flex flex-col gap-3 p-4 bg-gray-50 rounded-xl border border-gray-100">
                    <div class="text-lg font-extrabold text-gray-500">URLs</div>

                    <!-- URL Badges Container -->
                    <div v-if="urlBadges.length > 0" class="flex flex-wrap gap-2 mb-1">
                        <div v-for="(badge, index) in urlBadges" :key="index" 
                             class="flex items-center gap-2 px-3 py-1.5 rounded-full bg-gradient-to-r from-[#e0f7fa] to-white border border-[#b2ebf2] shadow-sm group">
                            
                            <span class="text-sm font-semibold text-[#006064] truncate max-w-[250px]">{{ badge }}</span>
                            
                            <button @click="removeUrlBadge(index)" class="text-[#00838f] hover:text-red-500 transition-colors p-0.5 rounded-full hover:bg-white/50">
                                <Close :size="16" />
                            </button>
                        </div>
                    </div>

                    <!-- URL Input -->
                    <input
                        v-model="currentUrlInput"
                        @keydown.enter.ctrl.prevent="addUrlBadge"
                        type="text"
                        placeholder="Type or paste URL here..."
                        class="rounded-md w-full border-gray-300 focus:ring-blue-500 shadow-sm"
                    />
                    
                    <div class="flex justify-between items-center text-xs text-gray-400 font-medium">
                        <span>Press <b>Ctrl + Enter</b> to turn URL into a badge.</span>
                        <span class="italic">Unbadged text will auto-save.</span>
                    </div>

                    <div v-if="form.errors.url" class="text-red-500 p-2 font-extrabold">{{ form.errors.url }}</div>
                </div>
            </div>
        </div>
    </div>
</template>