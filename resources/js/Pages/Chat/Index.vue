<script setup>
import { ref, onMounted, nextTick, onUnmounted } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import MainLayout from '@/Layouts/MainLayout.vue';
import Magnify from "vue-material-design-icons/Magnify.vue";
import SendOutline from "vue-material-design-icons/Send.vue";
import ArrowLeft from "vue-material-design-icons/ArrowLeft.vue";
import Pencil from "vue-material-design-icons/Pencil.vue";
import TrashCan from "vue-material-design-icons/TrashCan.vue";

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

// --- Edit Message State ---
const editingMessageId = ref(null);
const editMessageText = ref('');

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
    editingMessageId.value = null; // reset edit state

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
    editingMessageId.value = null;
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

// --- Edit & Delete Functions ---
const startEdit = (msg) => {
    editingMessageId.value = msg.id;
    editMessageText.value = msg.body;
};

const cancelEdit = () => {
    editingMessageId.value = null;
    editMessageText.value = '';
};

const saveEdit = async (msg) => {
    if (!editMessageText.value.trim()) return;

    // Optimistic UI update
    const previousBody = msg.body;
    msg.body = editMessageText.value;
    msg.updated_at = new Date().toISOString(); 
    editingMessageId.value = null;

    try {
        const res = await axios.put(`/chat/messages/${msg.id}`, {
            body: editMessageText.value
        });
        // Sync with server format
        const index = messages.value.findIndex(m => m.id === msg.id);
        if (index !== -1) messages.value[index] = res.data;
    } catch (error) {
        msg.body = previousBody; // Revert on failure
        console.error("Failed to edit message");
    }
};

const deleteMsg = async (msgId) => {
    // Optimistic UI update
    messages.value = messages.value.filter(m => m.id !== msgId);
    try {
        await axios.delete(`/chat/messages/${msgId}`);
    } catch (error) {
        console.error("Failed to delete message");
        // For production, you could re-fetch the conversation here to ensure sync
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
        })
        .listen('.message.edited', (e) => {
            if (activeUser.value && activeUser.value.id === e.message.sender_id) {
                const index = messages.value.findIndex(m => m.id === e.message.id);
                if (index !== -1) {
                    messages.value[index] = e.message;
                }
            }
        })
        .listen('.message.deleted', (e) => {
            if (activeUser.value) {
                messages.value = messages.value.filter(m => m.id !== e.messageId);
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
                        <div v-for="msg in messages" :key="msg.id" class="flex flex-col group" :class="msg.sender_id === user.id ? 'items-end' : 'items-start'">
                            
                            <div class="flex items-center gap-2 max-w-[85%] sm:max-w-[70%]">
                                <!-- Action Buttons (Hidden by default, appear on hover for sender) -->
                                <div v-if="msg.sender_id === user.id && editingMessageId !== msg.id" class="hidden group-hover:flex items-center gap-2 text-gray-400 mx-1">
                                    <button @click="startEdit(msg)" class="hover:text-blue-500 transition"><Pencil :size="16"/></button>
                                    <button @click="deleteMsg(msg.id)" class="hover:text-red-500 transition"><TrashCan :size="16"/></button>
                                </div>

                                <div class="px-4 py-2 rounded-2xl text-[15px] whitespace-pre-wrap break-words w-full shadow-sm" 
                                    :class="msg.sender_id === user.id ? 'bg-black text-white rounded-br-sm' : 'bg-white border border-gray-200 text-gray-800 rounded-bl-sm'">
                                    
                                    <!-- Edit Mode Interface -->
                                    <div v-if="editingMessageId === msg.id" class="flex flex-col gap-2 min-w-[200px]">
                                        <textarea 
                                            v-model="editMessageText" 
                                            class="text-white bg-transparent focus:ring-0 border-0 no-scrollbar rounded p-2 w-full text-sm resize-none"
                                            rows="2"
                                            @keydown.enter.ctrl.exact.prevent="saveEdit(msg)"
                                        ></textarea>
                                        <div class="flex justify-end gap-3 mt-1">
                                            <button @click="cancelEdit" class="text-xs font-bold text-gray-400 hover:text-white transition">Cancel</button>
                                            <button @click="saveEdit(msg)" class="text-xs font-bold text-green-400 hover:text-green-300 transition">Save</button>
                                        </div>
                                    </div>

                                    <!-- Standard Message Display -->
                                    <template v-else>
                                        {{ msg.body }}
                                        <!-- <span v-if="msg.created_at !== msg.updated_at" class="text-[10px] opacity-60 italic ml-2 block mt-1" :class="msg.sender_id === user.id ? 'text-right' : 'text-left'">(edited)</span> -->
                                    </template>
                                </div>
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