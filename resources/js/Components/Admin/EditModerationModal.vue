<script setup>
import { ref } from "vue";
import { router } from "@inertiajs/vue3";
import StatusBadge from "./StatusBadge.vue";

const props = defineProps({
    show: { type: Boolean, default: false },
    log: { type: Object, default: null },
});
const emit = defineEmits(["close"]);

const pendingStatus = ref(null);
const saving = ref(false);

function submit(status) {
    if (!props.log || saving.value) return;
    pendingStatus.value = status;
    saving.value = true;

    router.patch(
        `/admin/moderation/${props.log.type}/${props.log.itemId}`,
        { status },
        {
            preserveScroll: true,
            onFinish: () => (saving.value = false),
            onSuccess: () => emit("close"),
        },
    );
}
</script>

<template>
    <Teleport to="body">
        <transition name="modal-fade">
            <div
                v-if="show && log"
                class="modal-backdrop"
                @click.self="emit('close')"
            >
                <transition name="modal-pop" appear>
                    <div class="modal-card">
                        <button class="modal-close" @click="emit('close')">
                            ×
                        </button>

                        <div class="modal-header">
                            <span class="type-pill" :class="log.type">{{
                                log.type
                            }}</span>
                            <StatusBadge :status="log.status" />
                        </div>

                        <p class="modal-meta">
                            By <strong>{{ log.author }}</strong> · last review:
                            {{ log.actionTaken }}
                        </p>
                        <span class="type-pill" :class="log.type">{{
                               'Reason : '+ log.aiReason
                            }}
                        </span>
                        <div class="flex gap-3 text-sm">
                           <p>Toxic Score:  {{log.toxicScore}}</p> <p>Hate Score:  {{log.hateScore}}</p> 
                        </div>
                        <div class="modal-content-block">
                            <p class="block-label">
                                {{
                                    log.type === "comment" ? "Comment" : "Post"
                                }}
                            </p>
                            <p class="block-text">
                                {{ log.text ?? "No content." }}
                            </p>

                            <template v-if="log.context">
                                <p class="block-label">On post</p>
                                <p class="block-text muted">
                                    {{ log.context }}
                                </p>
                            </template>
                        </div>

                        <div class="modal-actions">
                            <button
                                class="action-btn allow"
                                :disabled="saving"
                                @click="submit('approved')"
                            >
                                {{
                                    saving && pendingStatus === "approved"
                                        ? "Saving…"
                                        : "Allow"
                                }}
                            </button>
                            <button
                                class="action-btn flag"
                                :disabled="saving"
                                @click="submit('flagged')"
                            >
                                {{
                                    saving && pendingStatus === "flagged"
                                        ? "Saving…"
                                        : "Flag"
                                }}
                            </button>
                            <button
                                class="action-btn delete"
                                :disabled="saving"
                                @click="submit('deleted')"
                            >
                                {{
                                    saving && pendingStatus === "deleted"
                                        ? "Saving…"
                                        : "Delete"
                                }}
                            </button>
                        </div>
                    </div>
                </transition>
            </div>
        </transition>
    </Teleport>
</template>

<style scoped>
.modal-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(4, 38, 58, 0.45);
    backdrop-filter: blur(6px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 100;
    padding: 20px;
}

.modal-card {
    width: 100%;
    max-width: 520px;
    max-height: 88vh;
    overflow-y: auto;
    background: linear-gradient(
        165deg,
        rgba(255, 255, 255, 0.92),
        rgba(240, 249, 255, 0.88)
    );
    backdrop-filter: blur(26px) saturate(180%);
    border: 1px solid rgba(255, 255, 255, 0.8);
    border-radius: 24px;
    padding: 26px 28px 24px;
    position: relative;
    box-shadow: 0 40px 80px -30px rgba(4, 38, 58, 0.55);
}

.modal-close {
    position: absolute;
    top: 16px;
    right: 16px;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    border: none;
    background: rgba(11, 79, 115, 0.08);
    color: #0b4f73;
    font-size: 1.1rem;
    line-height: 1;
    cursor: pointer;
    transition: all 0.2s ease;
}
.modal-close:hover {
    background: rgba(11, 79, 115, 0.16);
    transform: rotate(90deg);
}

.modal-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 8px;
}
.type-pill {
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    padding: 3px 9px;
    border-radius: 8px;
    background: rgba(11, 79, 115, 0.08);
    color: #0b4f73;
}
.type-pill.comment {
    background: rgba(14, 165, 233, 0.12);
    color: #0369a1;
}

.modal-meta {
    font-size: 0.8rem;
    color: rgba(11, 79, 115, 0.6);
    margin: 0 0 16px;
}

.modal-content-block {
    background: rgba(11, 79, 115, 0.04);
    border: 1px solid rgba(11, 79, 115, 0.08);
    border-radius: 14px;
    padding: 14px 16px;
    margin-bottom: 20px;
}
.block-label {
    font-size: 0.68rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    font-weight: 700;
    color: rgba(11, 79, 115, 0.5);
    margin: 10px 0 4px;
}
.block-label:first-child {
    margin-top: 0;
}
.block-text {
    font-size: 0.9rem;
    line-height: 1.5;
    color: #0b4f73;
    margin: 0;
    white-space: pre-wrap;
}
.block-text.muted {
    color: rgba(11, 79, 115, 0.55);
}

.modal-actions {
    display: flex;
    gap: 10px;
}
.action-btn {
    flex: 1;
    padding: 10px 0;
    border-radius: 12px;
    border: none;
    font-weight: 700;
    font-size: 0.85rem;
    cursor: pointer;
    transition:
        transform 0.15s ease,
        box-shadow 0.15s ease,
        opacity 0.15s;
}
.action-btn:disabled {
    opacity: 0.6;
    cursor: default;
}
.action-btn:not(:disabled):hover {
    transform: translateY(-1px);
}
.action-btn.allow {
    background: linear-gradient(135deg, #34d399, #0f9d78);
    color: #fff;
    box-shadow: 0 12px 24px -12px rgba(15, 157, 120, 0.55);
}
.action-btn.flag {
    background: linear-gradient(135deg, #fbbf24, #b45309);
    color: #fff;
    box-shadow: 0 12px 24px -12px rgba(180, 83, 9, 0.5);
}
.action-btn.delete {
    background: linear-gradient(135deg, #fb7185, #be123c);
    color: #fff;
    box-shadow: 0 12px 24px -12px rgba(190, 18, 60, 0.5);
}

.modal-fade-enter-active,
.modal-fade-leave-active {
    transition: opacity 0.25s ease;
}
.modal-fade-enter-from,
.modal-fade-leave-to {
    opacity: 0;
}
.modal-pop-enter-active {
    transition: all 0.3s cubic-bezier(0.22, 1, 0.36, 1);
}
.modal-pop-enter-from {
    opacity: 0;
    transform: translateY(16px) scale(0.97);
}
</style>
