<script setup>
import { ref, onMounted, nextTick, onUnmounted } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import MainLayout from '@/Layouts/MainLayout.vue';
import Magnify from "vue-material-design-icons/Magnify.vue";
import SendOutline from "vue-material-design-icons/Send.vue";
import ArrowLeft from "vue-material-design-icons/ArrowLeft.vue";

const user = usePage().props.auth.user;
const contacts = ref([]);
const searchResults = ref([]);
const searchQuery = ref('');
const activeUser = ref(null);
const messages = ref([]);
const newMessage = ref('');
const messagesContainer = ref(null);
const textarea = ref(null);

// --- Scroll Lock State ---
const isUserHovering = ref(false);

// --- Mobile Overlay State ---
const showMobileChat = ref(false);

// --- Typing Indicator State ---
const isTyping = ref(false);
let typingTimer = null;
let activeConversationChannel = null;

const fetchContacts = async () => {
    const res = await axios.get('/chat/contacts');
    contacts.value = res.data;
};

const searchUsers = async () => {
    if (!searchQuery.value) {
        searchResults.value = [];
        return;
    }
    const res = await axios.get(`/chat/search?q=${searchQuery.value}`);
    searchResults.value = res.data;
};

const scrollToBottom = async (force = false) => {
    await nextTick();
    if (messagesContainer.value) {
        // If force is true (e.g. sending a message or initial load), scroll regardless of hover state.
        // Otherwise, respect hover state and do not scroll if the user is hovering.
        if (force || !isUserHovering.value) {
            messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
        }
    }
};

const selectUser = async (selectedUser) => {
    activeUser.value = selectedUser;
    searchQuery.value = '';
    searchResults.value = [];
    showMobileChat.value = true;

    const contact = contacts.value.find(c => c.id === selectedUser.id);
    if (contact) contact.unread_count = 0;

    const res = await axios.get(`/chat/${selectedUser.id}/messages`);
    messages.value = res.data;
    
    scrollToBottom(true);

    if (activeConversationChannel) {
        window.Echo.leave(activeConversationChannel);
    }

    const minId = Math.min(user.id, selectedUser.id);
    const maxId = Math.max(user.id, selectedUser.id);
    activeConversationChannel = `conversation.${minId}.${maxId}`;

    window.Echo.private(activeConversationChannel)
        .listenForWhisper('typing', (e) => {
            if (e.userID === selectedUser.id) {
                isTyping.value = true;
                scrollToBottom(false);

                clearTimeout(typingTimer);
                typingTimer = setTimeout(() => {
                    isTyping.value = false;
                }, 2000);
            }
        });
};

const closeMobileChat = () => {
    showMobileChat.value = false;
    activeUser.value = null;
    if (activeConversationChannel) {
        window.Echo.leave(activeConversationChannel);
        activeConversationChannel = null;
    }
};

const sendTypingEvent = () => {
    if (!activeConversationChannel) return;
    window.Echo.private(activeConversationChannel)
        .whisper('typing', {
            userID: user.id
        });
};

const textareaInput = (e) => {
    if (textarea.value) {
        textarea.value.style.height = "auto";
        textarea.value.style.height = `${e.target.scrollHeight}px`;
    }
};

const sendMessage = async () => {
    if (!newMessage.value.trim() || !activeUser.value) return;

    const tempBody = newMessage.value;
    newMessage.value = ''; 
    isTyping.value = false;

    if (textarea.value) {
        textarea.value.style.height = "auto";
    }

    const res = await axios.post(`/chat/${activeUser.value.id}/messages`, {
        body: tempBody
    });

    messages.value.push(res.data);
    scrollToBottom(true);

    if (!contacts.value.find(c => c.id === activeUser.value.id)) {
        contacts.value.unshift(activeUser.value);
    }
};

const markAsRead = (senderId) => {
    axios.patch(`/chat/${senderId}/read`);
};

onMounted(() => {
    fetchContacts();

    window.Echo.private(`chat.${user.id}`)
        .listen('.message.sent', (e) => {
            if (activeUser.value && activeUser.value.id === e.message.sender_id) {
                messages.value.push(e.message);
                isTyping.value = false;
                scrollToBottom(false);
                markAsRead(e.message.sender_id);
            } else {
                let contact = contacts.value.find(c => c.id === e.message.sender_id);
                if (contact) {
                    contact.unread_count++;
                } else {
                    fetchContacts();
                }
            }
        });
});

onUnmounted(() => {
    if (activeConversationChannel) {
        window.Echo.leave(activeConversationChannel);
    }
});
</script>

<template>
    <Head title="Direct Messages" />

    <MainLayout>
        <!-- WhatsApp Style Split Pane Container -->
        <div class="flex w-screen md:w-[calc(100vw-300px)] h-[calc(100vh-130px)] sm:h-[calc(100vh-100px)] bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden sm:mt-4 relative">

            <!-- Left Pane: Search & Contacts -->
            <div class="w-full md:w-1/3 border-r border-gray-200 flex flex-col h-full bg-gray-50">
                <div class="p-4 bg-white border-b border-gray-200">
                    <h2 class="text-2xl font-black mb-4">Messages</h2>
                    <div class="relative flex items-center bg-gray-100 rounded-xl px-3 py-2">
                        <Magnify :size="22" class="text-gray-400" />
                        <input 
                            v-model="searchQuery" 
                            @input="searchUsers"
                            type="text" 
                            placeholder="Search users..." 
                            class="w-full bg-transparent border-none focus:ring-0 text-sm py-1 placeholder-gray-500 font-semibold"
                        />
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto custom-scrollbar bg-white">
                    <!-- Search Results -->
                    <div v-if="searchQuery" class="p-2">
                        <div class="text-xs font-bold text-gray-400 uppercase tracking-widest px-2 pb-2">Search Results</div>
                        <div v-for="result in searchResults" :key="result.id" @click="selectUser(result)" class="flex items-center gap-3 p-3 hover:bg-gray-50 rounded-xl cursor-pointer transition">
                            <img :src="result.file || '/user-placeholder.png'" class="w-12 h-12 rounded-full object-cover border" />
                            <div>

                                <div class="font-bold text-gray-900">{{ result.name }}</div>
                                <div class="font-bold text-gray-900">{{ result.email }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Contacts -->
                    <div v-else class="p-2">
                        <div class="text-xs font-bold text-gray-400 uppercase tracking-widest px-2 pb-2 mt-2">Recent Chats</div>
                        <div v-for="contact in contacts" :key="contact.id" @click="selectUser(contact)" 
                            class="flex items-center justify-between p-3 hover:bg-gray-50 rounded-xl cursor-pointer transition border-b border-gray-50"
                            :class="{'bg-blue-50': activeUser && activeUser.id === contact.id}"
                        >
                            <div class="flex items-center gap-3">
                                <img :src="contact.file || '/user-placeholder.png'" class="w-12 h-12 rounded-full object-cover border" />
                                <div>
                                    <div class="font-bold text-gray-900">{{ contact.name }}</div>
                                    <div class="font-bold text-gray-900">{{ contact.email }}</div>
                                </div>
                            </div>

                            <!-- Light Blue Unread Badge -->
                            <div v-if="contact.unread_count > 0" class="w-6 h-6 bg-blue-400 text-white flex items-center justify-center rounded-full text-xs font-black shadow-sm">
                                {{ contact.unread_count }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Pane: Active Chat (Acts as Overlay on Mobile, Side-by-Side on Desktop) -->
            <div 
                class="flex flex-col h-full bg-gray-50 absolute inset-0 z-30 md:relative md:inset-auto md:w-2/3"
                :class="showMobileChat ? 'block' : 'hidden md:flex'"
            >
                <template v-if="activeUser">
                    <!-- Chat Header -->
                    <div class="p-4 bg-white border-b border-gray-200 flex items-center gap-3">
                        <!-- Back Button (Visible only on mobile) -->
                        <button @click="closeMobileChat" class="md:hidden p-1 mr-1 text-gray-600 hover:text-black transition">
                            <ArrowLeft :size="24" />
                        </button>
                        <img :src="activeUser.file || '/user-placeholder.png'" class="w-10 h-10 rounded-full object-cover" />
                        <div>
                           <h3 class="font-black text-lg">{{ activeUser.name }}</h3> 
                           <h4 class="font-black text-sm">{{ activeUser.email }}</h4>
                        </div>
                    </div>

                   <!-- Messages Area -->
                    <div 
                        ref="messagesContainer" 
                        @mouseenter="isUserHovering = true"
                        @mouseleave="isUserHovering = false"
                        class="flex-1 overflow-y-auto p-4 flex flex-col gap-3 custom-scrollbar"
                    >
                        <div v-for="msg in messages" :key="msg.id" class="flex" :class="msg.sender_id === user.id ? 'justify-end' : 'justify-start'">
                            <div class="max-w-[70%] px-4 py-2 rounded-2xl text-[15px] whitespace-pre-wrap break-words" 
                                :class="msg.sender_id === user.id ? 'bg-black text-white rounded-br-sm' : 'bg-white border border-gray-200 text-gray-800 rounded-bl-sm'">
                                {{ msg.body }}
                            </div>
                        </div>

                        <!-- Normal Flow Typing Indicator Bubble -->
                        <div v-if="isTyping" class="flex justify-start mt-1">
                            <div class="px-4 py-3 bg-blue-50 border-2 border-white shadow-sm text-black rounded-2xl rounded-bl-sm flex items-center gap-1 w-max">
                                <span class="w-1.5 h-1.5 bg-black rounded-full typing-dot"></span>
                                <span class="w-1.5 h-1.5 bg-black rounded-full typing-dot animation-delay-150"></span>
                                <span class="w-1.5 h-1.5 bg-black rounded-full typing-dot animation-delay-300"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Input Area -->
                    <div class="p-4 bg-white border-t border-gray-200">
                        <form @submit.prevent="sendMessage" class="flex items-end gap-2">
                            <textarea 
                                ref="textarea"
                                v-model="newMessage" 
                                @input="(e) => { textareaInput(e); sendTypingEvent(); }"
                                @keydown.enter.ctrl.exact.prevent="sendMessage"
                                rows="1"
                                placeholder="Type a message (Press Ctrl + Enter to send)..." 
                                class="w-full focus:ring-0 bg-gray-100 border-none rounded-xl px-4 py-3 transition resize-none max-h-32 no-scrollbar"
                            ></textarea>
                            <button type="submit" class="p-3 bg-black text-white rounded-xl hover:bg-gray-800 transition shadow-md active:scale-95 mb-0.5">
                                <SendOutline :size="20" />
                            </button>
                        </form>
                    </div>
                </template>

                <template v-else>
                    <div class="flex-1 flex flex-col items-center justify-center text-gray-400">
                        <div class="bg-gray-200 p-6 rounded-full mb-4"><SendOutline :size="40" /></div>
                        <h3 class="text-xl font-bold text-gray-800">Your Messages</h3>
                        <p class="mt-1">Select a user or search to start a conversation.</p>
                    </div>
                </template>
            </div>
        </div>
    </MainLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background-color: #e5e7eb; border-radius: 20px; }

/* Hide scrollbar for Chrome, Safari, Opera and Edge/Firefox */
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}

/* Typing Animation Styles */
.typing-dot {
    animation: typingBounce 1.4s infinite ease-in-out both;
}
.animation-delay-150 { animation-delay: -0.32s; }
.animation-delay-300 { animation-delay: -0.16s; }

@keyframes typingBounce {
    0%, 80%, 100% { transform: translateY(0); }
    40% { transform: translateY(-4px); }
}
</style>