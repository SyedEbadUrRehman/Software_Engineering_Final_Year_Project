<script setup>
import { ref, reactive } from "vue";
import { router, usePage } from "@inertiajs/vue3";

import Close from "vue-material-design-icons/Close.vue";
import ArrowLeft from "vue-material-design-icons/ArrowLeft.vue";
import MapMarkerOutline from "vue-material-design-icons/MapMarkerOutline.vue";
import ChevronDown from "vue-material-design-icons/ChevronDown.vue";

const user = usePage().props.auth.user;

const emit = defineEmits(["close"]);

const form = reactive({
    text: null,
    file: null,
    url: null,
});

let isValidFile = ref(null);
let fileDisplay = ref("");
let textarea = ref("");
let url = ref("");
let error = ref({
    text: null,
    file: null,
    url: null,
});

const isOpenUrl = ref(false);

const toggleUrl = () => {
    isOpenUrl.value = !isOpenUrl.value;
};

const urlChange=()=>{
    form.url=url.value.value
}
const clearUrl=()=>{
  form.url=null
  url.value.value=null  
}
const createPostFunc = () => {
    error.value.text = null;
    error.value.file = null;
    error.value.url = null;

    router.post("/posts", form, {
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

const getUploadedImage = (e) => {
    form.file = e.target.files[0];
    let extention = form.file.name.substring(
        form.file.name.lastIndexOf(".") + 1,
    );

    // console.log(extention)

    if (extention == "png" || extention == "jpg" || extention == "jpeg") {
        isValidFile.value = true;
    } else {
        isValidFile.value = false;
        return;
    }

    fileDisplay.value = URL.createObjectURL(e.target.files[0]);
    setTimeout(() => {
        document
            .getElementById("TextAreaSection")
            .scrollIntoView({ behavior: "smooth" });
    }, 300);
};

const closeOverlay = () => {
    form.text = null;
    form.file = null;
    form.url = null;
    fileDisplay.value = "";
    emit("close");
};
</script>

<template>
    <div
        id="OverlaySection"
        class="fixed z-50 top-0 left-0 w-full h-screen bg-[#000000] bg-opacity-60 p-3"
    >
        <button class="absolute right-3 cursor-pointer" @click="closeOverlay()">
            <Close :size="27" fillColor="#FFFFFF" />
        </button>

        <div
            class="max-w-4xl h-[calc(100%-100px)] mx-auto mt-10 bg-white rounded-xl"
        >
            <div
                class="flex items-center justify-between w-full rounded-t-xl p-3 border-b border-b-gray-300"
            >
                <ArrowLeft
                    :size="30"
                    fillColor="#000000"
                    @click="closeOverlay()"
                />
                <div class="text-lg font-extrabold">New Post</div>
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
                <!-- <div class="flex items-center bg-gray-100 w-full h-full overflow-hidden">
                    <div v-if="!fileDisplay" class="flex flex-col items-center mx-auto">
                        <label
                            for="file"
                            class="hover:bg-blue-700 bg-blue-500 rounded-lg p-2.5 text-white font-extrabold cursor-pointer"
                        >
                            Select From Computer
                        </label>
                        <input
                            id="file"
                            class="hidden"
                            type="file"
                            @input="getUploadedImage($event)"
                        >
                        <div v-if="error && error.file" class="text-red-500 text-center p-2 font-extrabold">{{ error.file }}</div>
                        <div v-if="!fileDisplay && isValidFile === false" class="text-red-500 text-center p-2 font-extrabold">
                            File not accepted
                        </div>
                    </div>
                    <img v-if="fileDisplay && isValidFile === true" class=" min-w-[400px] p-4 mx-auto" :src="fileDisplay">
                </div> -->

                <div id="TextAreaSection" class="w-full relative">
                    <div class="flex items-center justify-between p-3">
                        <div class="flex items-center">
                            <img
                                class="rounded-full w-[38px] h-[38px]"
                                :src="user.file"
                            />
                            <div class="ml-4 font-extrabold text-[15px]">
                                {{ user.name }}
                            </div>
                        </div>
                    </div>

                    <div
                        v-if="error && error.text"
                        class="text-red-500 p-2 font-extrabold"
                    >
                        {{ error.text }}
                    </div>

                    <div class="flex w-full max-h-[200px] bg-white border-b">
                        <textarea
                            ref="textarea"
                            v-model="form.text"
                            placeholder="Write caption..."
                            rows="10"
                            class="placeholder-gray-500 w-full border-0 mt-2 mb-2 z-50 focus:ring-0 text-gray-600 text-[18px]"
                        ></textarea>
                    </div>

                    <div class="flex flex-col gap-4 border-b p-3">
                        <div class="flex items-center justify-between">
                            <div class="text-lg font-extrabold text-gray-500">
                                URL
                            </div>
                            <ChevronDown
                                :size="27"
                                class="transition-transform duration-300 cursor-pointer"
                                :class="{ 'rotate-180': isOpenUrl }"
                                @click="toggleUrl"
                            />
                        </div>
                        <div :class="{ 'opacity-0': !isOpenUrl }">
                            <div
                                class="flex items-center justify-between gap-3"
                            >
                                <input
                                id="postUrl"
                                    ref="url"
                                    type="text"
                                    placeholder="URL of the site"
                                    class="rounded-md w-full"
                                    @change="urlChange"
                                />
                                <Close
                                    :size="27"
                                    class="p-2 hover:bg-slate-200 hover:text-red-400 rounded-full cursor-pointer"
                                    @click="clearUrl"
                                    />
                            </div>
                            <div
                                v-if="error && error.url"
                                class="text-red-500 p-2 font-extrabold"
                            >
                                {{ error.url }}
                            </div>
                        </div>
                    </div>

                    <!-- <div class="flex items-center justify-between border-b p-3">
                        <div class="text-lg font-extrabold text-gray-500">Add Location</div>
                        <MapMarkerOutline :size="27"/>
                    </div>

                    <div class="flex items-center justify-between border-b p-3">
                        <div class="text-lg font-extrabold text-gray-500">Accesibility</div>
                        <ChevronDown :size="27"/>
                    </div>

                    <div class="flex items-center justify-between border-b p-3">
                        <div class="text-lg font-extrabold text-gray-500">Advanced Settings</div>
                        <ChevronDown :size="27"/>
                    </div> -->

                    <div class="text-gray-500 mt-3 p-3 text-sm md:text-lg">
                        Your post will be shared only with the circles you
                        choose and will appear in the feeds of members within
                        those circles.
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>



<!-- const input = document.getElementById("postUrl");
input.value = "hi";
input.dispatchEvent(new Event("change")); -->