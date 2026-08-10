<script setup>
import StatusBadge from "./StatusBadge.vue";

const props = defineProps({
    logs: { type: Object, required: true },
    loading: { type: Boolean, default: false },
});
const emit = defineEmits(["edit", "paginate"]);

function truncate(text, len = 90) {
    if (!text) return "—";
    return text.length > len ? text.slice(0, len) + "…" : text;
}

function formatDate(iso) {
    if (!iso) return "—";
    return new Date(iso).toLocaleString(undefined, {
        month: "short",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
}
</script>

<template>
    <div class="mod-table-wrap" :class="{ loading }">
        <table class="mod-table">
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Author</th>
                    <th>Content</th>
                    <th>Status</th>
                    <th>Reviewed</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="log in logs.data" :key="log.id">
                    <td>
                        <span class="type-pill" :class="log.type">{{
                            log.type
                        }}</span>
                    </td>
                    <td class="author-cell">{{ log.author }}</td>
                    <td class="content-cell">{{ truncate(log.text) }}</td>
                    <td><StatusBadge :status="log.status" /></td>
                    <td class="muted">{{ formatDate(log.createdAt) }}</td>
                    <td class="action-cell">
                        <button class="edit-btn" @click="emit('edit', log)">
                            Review
                        </button>
                    </td>
                </tr>
                <tr v-if="!logs.data.length">
                    <td colspan="6" class="empty-row">
                        Nothing here yet — this filter has no moderated
                        content.
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="pagination" v-if="logs.links && logs.links.length > 3">
            <button
                v-for="(link, i) in logs.links"
                :key="i"
                class="page-btn"
                :class="{ active: link.active, disabled: !link.url }"
                :disabled="!link.url"
                v-html="link.label"
                @click="emit('paginate', link.url)"
            />
        </div>
    </div>
</template>

<style scoped>
.mod-table-wrap {
    transition: opacity 0.25s ease;
}
.mod-table-wrap.loading {
    opacity: 0.55;
    pointer-events: none;
}

.mod-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.88rem;
}
.mod-table thead th {
    text-align: left;
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: rgba(11, 79, 115, 0.55);
    font-weight: 700;
    padding: 10px 22px;
}
.mod-table tbody td {
    padding: 14px 22px;
    border-top: 1px solid rgba(11, 79, 115, 0.07);
    vertical-align: middle;
    color: #0b4f73;
}
.mod-table tbody tr {
    transition: background 0.2s ease;
}
.mod-table tbody tr:hover {
    background: rgba(14, 165, 233, 0.06);
}

.author-cell {
    font-weight: 600;
    white-space: nowrap;
}
.content-cell {
    max-width: 360px;
    color: rgba(11, 79, 115, 0.8);
}
.muted {
    color: rgba(11, 79, 115, 0.55);
    font-size: 0.8rem;
    white-space: nowrap;
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

.action-cell {
    text-align: right;
}
.edit-btn {
    border: 1px solid rgba(14, 165, 233, 0.4);
    background: rgba(14, 165, 233, 0.08);
    color: #0b4f73;
    font-weight: 600;
    font-size: 0.8rem;
    padding: 6px 14px;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.2s ease;
}
.edit-btn:hover {
    background: #0ea5e9;
    color: #fff;
    transform: translateY(-1px);
}

.empty-row {
    text-align: center;
    padding: 40px 20px !important;
    color: rgba(11, 79, 115, 0.5);
    border-top: none !important;
}

.pagination {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    padding: 16px 22px 22px;
    border-top: 1px solid rgba(11, 79, 115, 0.07);
}
.page-btn {
    min-width: 32px;
    height: 32px;
    padding: 0 8px;
    border-radius: 9px;
    border: 1px solid rgba(11, 79, 115, 0.14);
    background: rgba(255, 255, 255, 0.6);
    color: #0b4f73;
    font-size: 0.8rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
}
.page-btn:hover:not(.disabled) {
    background: rgba(14, 165, 233, 0.14);
}
.page-btn.active {
    background: linear-gradient(135deg, #0ea5e9, #0b4f73);
    color: #fff;
    border-color: transparent;
}
.page-btn.disabled {
    opacity: 0.35;
    cursor: default;
}
</style>
