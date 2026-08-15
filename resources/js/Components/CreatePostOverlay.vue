<script setup>
import { ref, onMounted, onBeforeUnmount } from "vue";
import { useForm, usePage } from "@inertiajs/vue3";
import axios from "axios";

import Close from "vue-material-design-icons/Close.vue";
import ArrowLeft from "vue-material-design-icons/ArrowLeft.vue";
import ChevronDown from "vue-material-design-icons/ChevronDown.vue";

const user = usePage().props.auth.user;

const emit = defineEmits(["close"]);

/*
|--------------------------------------------------------------------------
| Form
|--------------------------------------------------------------------------
*/

const form = useForm({
    text: null,
    file: null,
    url: null,
});

/*
|--------------------------------------------------------------------------
| General State
|--------------------------------------------------------------------------
*/

const isValidFile = ref(null);
const fileDisplay = ref("");

const textarea = ref("");
const url = ref("");

const error = ref({
    text: null,
    file: null,
    url: null,
});

const isOpenUrl = ref(false);

/*
|--------------------------------------------------------------------------
| AI State
|--------------------------------------------------------------------------
*/

const isGeneratingAI = ref(false);

const activeTrackingToken = ref(null);

const aiStatus = ref("idle");

const aiStatusMessage = ref("");

const aiElapsedSeconds = ref(0);

/*
|--------------------------------------------------------------------------
| AI Timer
|--------------------------------------------------------------------------
*/

let aiTimer = null;
let aiStartedAt = null;

/*
|--------------------------------------------------------------------------
| Echo
|--------------------------------------------------------------------------
*/

let echoChannel = null;

/*
|--------------------------------------------------------------------------
| URL Section
|--------------------------------------------------------------------------
*/

const toggleUrl = () => {
    isOpenUrl.value = !isOpenUrl.value;
};

/*
|--------------------------------------------------------------------------
| IMPORTANT:
| This function is used by BOTH:
|
| 1. User manually changing the URL
| 2. Browser extension dispatching:
|
|    input.dispatchEvent(new Event("change"))
|
|--------------------------------------------------------------------------
*/

const urlChange = () => {
    if (!url.value) {
        form.url = null;
        return;
    }

    form.url = url.value.value.trim();

    /*
    |--------------------------------------------------------------------------
    | Clear old URL error when URL changes
    |--------------------------------------------------------------------------
    */

    error.value.url = null;
};

/*
|--------------------------------------------------------------------------
| Clear URL
|--------------------------------------------------------------------------
*/

const clearUrl = () => {
    form.url = null;

    if (url.value) {
        url.value.value = "";
    }

    error.value.url = null;
};

/*
|--------------------------------------------------------------------------
| AI Timer
|--------------------------------------------------------------------------
*/

const startAiTimer = () => {
    stopAiTimer();

    aiStartedAt = Date.now();

    aiElapsedSeconds.value = 0;

    aiTimer = setInterval(() => {
        if (!aiStartedAt) {
            return;
        }

        aiElapsedSeconds.value = Math.floor(
            (Date.now() - aiStartedAt) / 1000
        );
    }, 1000);
};

const stopAiTimer = () => {
    if (aiTimer) {
        clearInterval(aiTimer);

        aiTimer = null;
    }
};

const resetAiTimer = () => {
    stopAiTimer();

    aiStartedAt = null;

    aiElapsedSeconds.value = 0;
};

const formattedAiTime = () => {
    const seconds = aiElapsedSeconds.value;

    const minutes = Math.floor(seconds / 60);

    const remainingSeconds = seconds % 60;

    return `${String(minutes).padStart(2, "0")}:${String(
        remainingSeconds
    ).padStart(2, "0")}`;
};

/*
|--------------------------------------------------------------------------
| AI Status
|--------------------------------------------------------------------------
*/

const setAiStatus = (status, message = null) => {
    aiStatus.value = status;

    if (message !== null) {
        aiStatusMessage.value = message;
    }

    if (
        status === "completed" ||
        status === "failed"
    ) {
        stopAiTimer();
    }
};

/*
|--------------------------------------------------------------------------
| Generate AI Idea
|--------------------------------------------------------------------------
*/

const generateWithAi = async () => {
    /*
    |--------------------------------------------------------------------------
    | Validate URL
    |--------------------------------------------------------------------------
    */

    if (!form.url || !form.url.trim()) {
        error.value.url =
            "Please enter a valid URL first to generate an idea.";

        return;
    }

    /*
    |--------------------------------------------------------------------------
    | Reset previous AI state
    |--------------------------------------------------------------------------
    */

    error.value.url = null;
    error.value.text = null;

    form.text = null;

    isGeneratingAI.value = true;

    activeTrackingToken.value = null;

    /*
    |--------------------------------------------------------------------------
    | Initial status
    |--------------------------------------------------------------------------
    */

    setAiStatus(
        "queued",
        "Waiting for AI worker..."
    );

    /*
    |--------------------------------------------------------------------------
    | Start timer immediately
    |--------------------------------------------------------------------------
    */

    startAiTimer();

    try {
        const response = await axios.post(
            "/posts/generate-ai-idea",
            {
                url: form.url,
            }
        );

        /*
        |--------------------------------------------------------------------------
        | Store tracking token
        |--------------------------------------------------------------------------
        */

        activeTrackingToken.value =
            response.data.trackingToken;

        /*
        |--------------------------------------------------------------------------
        | Backend may return initial status
        |--------------------------------------------------------------------------
        */

        setAiStatus(
            response.data.status || "queued",
            response.data.message ||
                "Waiting for AI worker..."
        );
    } catch (err) {
        console.error(
            "AI generation request failed:",
            err
        );

        setAiStatus(
            "failed",
            "Could not start AI analysis."
        );

        error.value.url =
            "Failed to communicate with AI service.";

        isGeneratingAI.value = false;

        activeTrackingToken.value = null;
    }
};

/*
|--------------------------------------------------------------------------
| AI Echo Event
|--------------------------------------------------------------------------
*/

const handleAiEvent = (e) => {
    /*
    |--------------------------------------------------------------------------
    | Ignore events from another AI request
    |--------------------------------------------------------------------------
    */

    if (
        e.trackingToken !==
        activeTrackingToken.value
    ) {
        return;
    }

    const aiResponse = e.aiResponse || {};

    const status =
        aiResponse.status || "analyzing";

    const message =
        aiResponse.message ||
        "AI is processing your webpage...";

    /*
    |--------------------------------------------------------------------------
    | Update status
    |--------------------------------------------------------------------------
    */

    setAiStatus(
        status,
        message
    );

    /*
    |--------------------------------------------------------------------------
    | Completed
    |--------------------------------------------------------------------------
    */

    if (status === "completed") {
        form.text =
            aiResponse.description || "";

        isGeneratingAI.value = false;

        activeTrackingToken.value = null;

        return;
    }

    /*
    |--------------------------------------------------------------------------
    | Failed
    |--------------------------------------------------------------------------
    */

    if (status === "failed") {
        isGeneratingAI.value = false;

        error.value.url =
            aiResponse.message ||
            "Unable to generate an AI description.";

        activeTrackingToken.value = null;

        return;
    }

    /*
    |--------------------------------------------------------------------------
    | Still processing
    |--------------------------------------------------------------------------
    */

    isGeneratingAI.value = true;
};

/*
|--------------------------------------------------------------------------
| Create Post
|--------------------------------------------------------------------------
*/

const createPostFunc = () => {
    error.value.text = null;
    error.value.file = null;
    error.value.url = null;

    form.post("/posts", {
        forceFormData: true,
        preserveScroll: true,

        onError: (errors) => {
            if (errors?.text) {
                error.value.text =
                    errors.text;
            }

            if (errors?.file) {
                error.value.file =
                    errors.file;
            }

            if (errors?.url) {
                error.value.url =
                    errors.url;
            }
        },

        onSuccess: () => {
            closeOverlay();
        },
    });
};

/*
|--------------------------------------------------------------------------
| Uploaded Image
|--------------------------------------------------------------------------
*/

const getUploadedImage = (e) => {
    form.file = e.target.files[0];

    if (!form.file) {
        return;
    }

    const extension =
        form.file.name.substring(
            form.file.name.lastIndexOf(".") + 1
        );

    if (
        extension === "png" ||
        extension === "jpg" ||
        extension === "jpeg"
    ) {
        isValidFile.value = true;
    } else {
        isValidFile.value = false;

        return;
    }

    fileDisplay.value =
        URL.createObjectURL(
            e.target.files[0]
        );

    setTimeout(() => {
        document
            .getElementById("TextAreaSection")
            ?.scrollIntoView({
                behavior: "smooth",
            });
    }, 300);
};

/*
|--------------------------------------------------------------------------
| Close Overlay
|--------------------------------------------------------------------------
*/

const closeOverlay = () => {
    resetAiTimer();

    form.text = null;
    form.file = null;
    form.url = null;

    fileDisplay.value = "";

    activeTrackingToken.value = null;

    isGeneratingAI.value = false;

    aiStatus.value = "idle";
    aiStatusMessage.value = "";

    emit("close");
};

/*
|--------------------------------------------------------------------------
| IMPORTANT:
| Browser Extension Integration
|--------------------------------------------------------------------------
|
| Your extension sends:
|
| window.parent.postMessage({
|     type: "tab_info",
|     url: currentUrl
| }, "*");
|
| This listener receives it.
|--------------------------------------------------------------------------
*/

const handleTabMessage = (event) => {
    if (
        !event.data ||
        event.data.type !== "tab_info"
    ) {
        return;
    }

    const input =
        document.getElementById("postUrl");

    if (!input) {
        return;
    }

    /*
    |--------------------------------------------------------------------------
    | Put extension URL into the actual input
    |--------------------------------------------------------------------------
    */

    input.value = event.data.url || "";

    /*
    |--------------------------------------------------------------------------
    | IMPORTANT:
    | Trigger Vue's existing URL change logic.
    |
    | This keeps your original extension behavior.
    |--------------------------------------------------------------------------
    */

    input.dispatchEvent(
        new Event("change", {
            bubbles: true,
        })
    );
};

/*
|--------------------------------------------------------------------------
| Mounted
|--------------------------------------------------------------------------
*/

onMounted(() => {
    /*
    |--------------------------------------------------------------------------
    | Laravel Echo
    |--------------------------------------------------------------------------
    */

    echoChannel = window.Echo
        .private(
            `App.Models.User.${user.id}`
        )
        .listen(
            ".ai.idea.generated",
            handleAiEvent
        );

    /*
    |--------------------------------------------------------------------------
    | Browser Extension
    |--------------------------------------------------------------------------
    */

    window.addEventListener(
        "message",
        handleTabMessage
    );
});

/*
|--------------------------------------------------------------------------
| Before Unmount
|--------------------------------------------------------------------------
*/

onBeforeUnmount(() => {
    /*
    |--------------------------------------------------------------------------
    | Stop timer
    |--------------------------------------------------------------------------
    */

    stopAiTimer();

    /*
    |--------------------------------------------------------------------------
    | Remove Echo
    |--------------------------------------------------------------------------
    */

    if (window.Echo) {
        window.Echo.leave(
            `App.Models.User.${user.id}`
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Remove extension listener
    |--------------------------------------------------------------------------
    */

    window.removeEventListener(
        "message",
        handleTabMessage
    );
});
</script>

<template>
    <div
        id="OverlaySection"
        class="fixed z-50 top-0 left-0 w-full h-screen bg-[#000000] bg-opacity-60 p-3"
    >
        <!-- Close -->
        <button
            class="absolute right-3 cursor-pointer"
            @click="closeOverlay()"
        >
            <Close
                :size="27"
                fillColor="#FFFFFF"
            />
        </button>

        <!-- Main Card -->
        <div
            class="max-w-4xl h-[calc(100%-100px)] mx-auto mt-10 bg-white rounded-xl"
        >
            <!-- Header -->
            <div
                class="flex items-center justify-between w-full rounded-t-xl p-3 border-b border-b-gray-300"
            >
                <ArrowLeft
                    :size="30"
                    fillColor="#000000"
                    @click="closeOverlay()"
                    class="cursor-pointer"
                />

                <div class="text-lg font-extrabold">
                    New Post
                </div>

                <button
                    @click="createPostFunc()"
                    class="text-lg text-blue-500 hover:text-gray-900 font-extrabold"
                >
                    Share
                </button>
            </div>

            <div
                class="w-full md:flex h-[calc(100%-55px)] rounded-xl overflow-auto"
            >
                <div
                    id="TextAreaSection"
                    class="w-full relative"
                >
                    <!-- User -->
                    <div
                        class="flex items-center justify-between p-3"
                    >
                        <div
                            class="flex items-center"
                        >
                            <img
                                class="rounded-full w-[38px] h-[38px]"
                                :src="user.file"
                            />

                            <div
                                class="ml-4 font-extrabold text-[15px]"
                            >
                                {{ user.name }}
                            </div>
                        </div>
                    </div>

                    <!-- Text Error -->
                    <div
                        v-if="error && error.text"
                        class="text-red-500 p-2 font-extrabold"
                    >
                        {{ error.text }}
                    </div>

                    <!-- Text -->
                    <div
                        class="flex w-full max-h-[200px] bg-white border-b"
                    >
                        <textarea
                            ref="textarea"
                            v-model="form.text"
                            placeholder="Write caption or generate one with AI..."
                            rows="10"
                            class="placeholder-gray-500 w-full border-0 mt-2 mb-2 z-50 focus:ring-0 text-gray-600 text-[18px]"
                            :disabled="isGeneratingAI"
                        ></textarea>
                    </div>

                    <!-- ================================================== -->
                    <!-- URL SECTION -->
                    <!-- ================================================== -->

                    <div
                        class="flex flex-col gap-4 border-b p-3 bg-gray-50"
                    >
                        <!-- URL Header -->
                        <div
                            class="flex items-center justify-between"
                        >
                            <div
                                class="text-lg font-extrabold text-gray-500"
                            >
                                URL
                            </div>

                            <ChevronDown
                                :size="27"
                                class="transition-transform duration-300 cursor-pointer"
                                :class="{
                                    'rotate-180':
                                        isOpenUrl
                                }"
                                @click="toggleUrl"
                            />
                        </div>

                        <!-- URL Content -->
                        <div
                            v-show="isOpenUrl"
                        >
                            <div
                                class="flex items-center justify-between gap-3"
                            >
                                <input
                                    id="postUrl"
                                    ref="url"
                                    type="text"
                                    placeholder="URL of the site"
                                    class="rounded-md w-full border-gray-300 focus:ring-blue-500"
                                    @change="urlChange"
                                />

                                <Close
                                    :size="27"
                                    class="p-2 hover:bg-slate-200 hover:text-red-400 rounded-full cursor-pointer"
                                    @click="clearUrl"
                                />
                            </div>

                            <!-- URL Error -->
                            <div
                                v-if="error && error.url"
                                class="text-red-500 p-2 font-extrabold"
                            >
                                {{ error.url }}
                            </div>
                        </div>

                        <!-- AI BUTTON -->
                        <div
                            class="flex justify-start w-full"
                        >
                            <button
                                type="button"
                                @click="generateWithAi"
                                :disabled="
                                    isGeneratingAI ||
                                    !form.url?.trim()
                                "
                                :title="
                                    isGeneratingAI
                                        ? 'AI is generating your idea...'
                                        : !form.url?.trim()
                                            ? 'Enter a URL first'
                                            : 'Generate AI Idea'
                                "
                                class="flex items-center justify-center gap-2 rounded-lg font-bold transition-all active:scale-95
                                       
                                       sm:w-auto sm:h-auto sm:px-3 sm:py-2"
                                :class="
                                    isGeneratingAI ||
                                    !form.url?.trim()
                                        ? 'bg-gray-200 text-gray-400 cursor-not-allowed'
                                        : 'bg-purple-50 text-purple-600 hover:bg-purple-100 hover:text-purple-700'
                                "
                            >
                                <span
                                    v-if="isGeneratingAI"
                                    class="inline-block w-4 h-4 border-2 border-purple-400 border-t-transparent rounded-full animate-spin"
                                ></span>

                                <span
                                    v-else
                                    class="text-base"
                                >
                                    ✨
                                </span>

                                <!-- Desktop only -->
                                <span
                                    class=""
                                >
                                    {{
                                        isGeneratingAI
                                            ? "Generating..."
                                            : "Generate AI Idea"
                                    }}
                                </span>
                            </button>
                        </div>
                    </div>

                    <!-- ================================================== -->
                    <!-- AI STATUS -->
                    <!-- Outside collapsible URL content -->
                    <!-- ================================================== -->

                    <div
                        v-if="
                            isGeneratingAI ||
                            aiStatus === 'completed' ||
                            aiStatus === 'failed'
                        "
                        class="mx-3 mt-3 rounded-xl border bg-white overflow-hidden shadow-sm"
                    >
                        <!-- Status -->
                        <div
                            class="flex items-center justify-between px-4 py-3"
                        >
                            <div
                                class="flex items-center gap-3"
                            >
                                <!-- Working -->
                                <div
                                    v-if="isGeneratingAI"
                                    class="relative flex items-center justify-center w-9 h-9"
                                >
                                    <div
                                        class="absolute inset-0 rounded-full bg-purple-100 animate-ping opacity-60"
                                    ></div>

                                    <div
                                        class="relative flex items-center justify-center w-9 h-9 rounded-full bg-gradient-to-r from-purple-500 to-blue-500 text-white"
                                    >
                                        ✨
                                    </div>
                                </div>

                                <!-- Complete -->
                                <div
                                    v-else-if="
                                        aiStatus ===
                                        'completed'
                                    "
                                    class="flex items-center justify-center w-9 h-9 rounded-full bg-green-100 text-green-600 text-lg"
                                >
                                    ✓
                                </div>

                                <!-- Failed -->
                                <div
                                    v-else
                                    class="flex items-center justify-center w-9 h-9 rounded-full bg-red-100 text-red-500 text-lg"
                                >
                                    !
                                </div>

                                <div>
                                    <div
                                        class="font-bold text-gray-800"
                                    >
                                        <span
                                            v-if="isGeneratingAI"
                                        >
                                            AI is working
                                        </span>

                                        <span
                                            v-else-if="
                                                aiStatus ===
                                                'completed'
                                            "
                                        >
                                            AI analysis complete
                                        </span>

                                        <span
                                            v-else
                                        >
                                            AI analysis failed
                                        </span>
                                    </div>

                                    <div
                                        class="text-sm text-gray-500"
                                    >
                                        {{
                                            aiStatusMessage
                                        }}
                                    </div>
                                </div>
                            </div>

                            <!-- Timer -->
                            <div
                                class="font-mono text-sm font-bold text-gray-600"
                            >
                                {{
                                    formattedAiTime()
                                }}
                            </div>
                        </div>

                        <!-- Progress -->
                        <div
                            v-if="isGeneratingAI"
                            class="h-1 bg-gray-100 overflow-hidden"
                        >
                            <div
                                class="h-full w-1/3 bg-gradient-to-r from-purple-500 to-blue-500 animate-[ai-progress_1.5s_ease-in-out_infinite]"
                            ></div>
                        </div>
                    </div>

                    <!-- Information -->
                    <div
                        class="text-gray-500 mt-3 p-3 text-sm md:text-lg"
                    >
                        Your post will be shared only with
                        the circles you choose and will appear
                        in the feeds of members within those
                        circles.
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
@keyframes ai-progress {
    0% {
        transform: translateX(-100%);
    }

    50% {
        transform: translateX(100%);
    }

    100% {
        transform: translateX(300%);
    }
}
</style>