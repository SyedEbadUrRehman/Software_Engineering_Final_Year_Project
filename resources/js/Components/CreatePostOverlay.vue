<script setup>
import { ref, onMounted } from "vue";
import { useForm, usePage } from "@inertiajs/vue3";
import axios from "axios";

import Close from "vue-material-design-icons/Close.vue";
import ArrowLeft from "vue-material-design-icons/ArrowLeft.vue";
import ChevronDown from "vue-material-design-icons/ChevronDown.vue";

const user = usePage().props.auth.user;
const emit = defineEmits(["close"]);

const form = useForm({
    text: null,
    file: null,
    url: null,
});

let isValidFile = ref(null);
let fileDisplay = ref("");
let url = ref("");
let error = ref({
    text: null,
    file: null,
    url: null,
});

const isOpenUrl = ref(false);

// AI Generation State
const isGeneratingAI = ref(false);
const activeTrackingToken = ref(null);

const toggleUrl = () => {
    isOpenUrl.value = !isOpenUrl.value;
};

const urlChange = () => {
    form.url = url.value.value;
};

const clearUrl = () => {
    form.url = null;
    if (url.value) url.value.value = null;
};

// Trigger AI Generation
const generateWithAi = async () => {
    if (!form.url) {
        error.value.url = "Please enter a valid URL first to generate an idea.";
        return;
    }
    
    isGeneratingAI.value = true;
    error.value.url = null;

    try {
        const response = await axios.post('/posts/generate-ai-idea', {
            url: form.url
        });
        
        activeTrackingToken.value = response.data.trackingToken;
    } catch (err) {
        error.value.url = "Failed to communicate with AI service.";
        isGeneratingAI.value = false;
    }
};

const createPostFunc = () => {
    error.value.text = null;
    error.value.file = null;
    error.value.url = null;

    form.post("/posts", {
        forceFormData: true,
        preserveScroll: true,
        onError: (errors) => {
            errors && errors.text ? (error.value.text = errors.text) : "";
            errors && errors.file ? (error.value.file = errors.file) : "";
            errors && errors.url ? (error.value.url = errors.url) : "";
        },
        onSuccess: () => {
            closeOverlay();
        },
    });
};

const closeOverlay = () => {
    form.reset();
    fileDisplay.value = "";
    activeTrackingToken.value = null;
    isGeneratingAI.value = false;
    emit("close");
};

// Reverb / Echo Listener for AI Response
onMounted(() => {
    window.Echo.private(`App.Models.User.${user.id}`)
        .listen('.ai.idea.generated', (e) => {
            if (e.trackingToken === activeTrackingToken.value) {
                form.text = e.aiResponse.description;
                isGeneratingAI.value = false;
                activeTrackingToken.value = null;
            }
        });
});

window.addEventListener("message", (event) => {
    if (event.data && event.data.type === "tab_info") {
        const input = document.getElementById("postUrl");
        if (input) {
            input.value = event.data.url;
            input.dispatchEvent(new Event("change"));
        }
    }
});
</script>

<template>
    <div id="OverlaySection" class="fixed z-50 top-0 left-0 w-full h-screen bg-[#000000] bg-opacity-60 p-3">
        <button class="absolute right-3 cursor-pointer" @click="closeOverlay()">
            <Close :size="27" fillColor="#FFFFFF" />
        </button>

        <div class="max-w-4xl h-[calc(100%-100px)] mx-auto mt-10 bg-white rounded-xl">
            <div class="flex items-center justify-between w-full rounded-t-xl p-3 border-b border-b-gray-300">
                <ArrowLeft :size="30" fillColor="#000000" @click="closeOverlay()" class="cursor-pointer" />
                <div class="text-lg font-extrabold">New Idea</div>
                <button @click="createPostFunc()" class="text-lg text-blue-500 hover:text-gray-900 font-extrabold">
                    Post
                </button>
            </div>

            <div class="w-full md:flex h-[calc(100%-55px)] rounded-xl overflow-auto">
                <div id="TextAreaSection" class="w-full relative">
                    <div class="flex items-center justify-between p-3">
                        <div class="flex items-center">
                            <img class="rounded-full w-[38px] h-[38px]" :src="user.file" />
                            <div class="ml-4 font-extrabold text-[15px]">
                                {{ user.name }}
                            </div>
                        </div>
                    </div>

                    <!-- URL Section -->
                    <div class="flex flex-col gap-4 border-b p-3 bg-gray-50">
                        <div class="flex items-center justify-between cursor-pointer" @click="toggleUrl">
                            <div class="text-lg font-extrabold text-gray-700">Source URL</div>
                            <ChevronDown :size="27" class="transition-transform duration-300" :class="{ 'rotate-180': isOpenUrl }" />
                        </div>
                        <div :class="{ 'opacity-0 hidden': !isOpenUrl, 'opacity-100 block': isOpenUrl }" class="transition-opacity">
                            <div class="flex items-center justify-between gap-3">
                                <input id="postUrl" ref="url" type="text" placeholder="https://..." class="rounded-md w-full border-gray-300 focus:ring-blue-500" @change="urlChange" />
                                <Close :size="27" class="p-2 hover:bg-slate-200 hover:text-red-400 rounded-full cursor-pointer" @click="clearUrl" />
                            </div>
                            <div v-if="error && error.url" class="text-red-500 p-2 text-sm font-bold">
                                {{ error.url }}
                            </div>
                            
                            <!-- Generate with AI Button -->
                            <button 
                                @click="generateWithAi" 
                                :disabled="isGeneratingAI || !form.url"
                                class="mt-3 w-full py-2 px-4 rounded-lg text-white font-bold transition-all flex justify-center items-center gap-2"
                                :class="isGeneratingAI || !form.url ? 'bg-gray-400 cursor-not-allowed' : 'bg-gradient-to-r from-purple-500 to-blue-500 hover:opacity-90 active:scale-98'"
                            >
                                <span v-if="isGeneratingAI">Grok is analyzing webpage...</span>
                                <span v-else>✨ Generate Idea with AI</span>
                            </button>
                        </div>
                    </div>

                    <div v-if="error && error.text" class="text-red-500 p-2 font-extrabold">
                        {{ error.text }}
                    </div>

                    <!-- Description / Text Area -->
                    <div class="flex w-full max-h-[300px] bg-white border-b px-3">
                        <textarea
                            v-model="form.text"
                            placeholder="Write caption or generate one with AI..."
                            rows="10"
                            class="placeholder-gray-500 w-full border-0 mt-2 mb-2 z-50 focus:ring-0 text-gray-600 text-[18px] bg-transparent resize-none"
                            :class="{'animate-pulse bg-gray-50 rounded': isGeneratingAI}"
                            :disabled="isGeneratingAI"
                        ></textarea>
                    </div>

                    <div class="text-gray-500 mt-3 p-3 text-sm md:text-lg">
                        Your post will be shared only with the circles you choose and will appear in the feeds of members within those circles.[cite: 11]
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>