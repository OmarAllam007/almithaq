<script setup lang="ts">
import { useLang } from '@/composables/useLang';
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const { trans } = useLang();
interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}
interface PaginationData {
    current_page: number;
    first_page_url: string;
    from: number | null;
    last_page: number;
    last_page_url: string;
    links: PaginationLink[];
    next_page_url: string | null;
    path: string;
    per_page: number;
    prev_page_url: string | null;
    to: number | null;
    total: number;
}
const props = defineProps<{ data: PaginationData }>();
const hasPages = computed(() => props.data.last_page > 1);
const numberLinks = computed(() => props.data.links.slice(1, -1));
const pageLabel = (label: string) => label.replace(/&hellip;|&middot;/g, '…').replace(/&amp;/g, '&');
</script>

<template>
    <nav v-if="hasPages" class="pagination-shell" :aria-label="trans('sidebar.Pagination')">
        <div class="pagination-summary">
            <span class="summary-mark" aria-hidden="true">✦</span
            ><span>{{ trans('sidebar.pagination_showing', { from: data.from ?? 0, to: data.to ?? 0, total: data.total }) }}</span>
        </div>
        <div class="pagination-controls">
            <Link
                v-if="data.prev_page_url"
                :href="data.prev_page_url"
                class="page-direction"
                preserve-scroll
                preserve-state
                :aria-label="trans('sidebar.Previous')"
                ><i class="ki-outline ki-arrow-left" aria-hidden="true"></i><span>{{ trans('sidebar.Previous') }}</span></Link
            >
            <span v-else class="page-direction disabled" aria-disabled="true"
                ><i class="ki-outline ki-arrow-left" aria-hidden="true"></i><span>{{ trans('sidebar.Previous') }}</span></span
            >
            <div class="page-numbers">
                <template v-for="(link, index) in numberLinks" :key="`${index}-${link.label}`">
                    <Link
                        v-if="link.url && !link.active"
                        :href="link.url"
                        class="page-number"
                        :class="{ 'page-number-near': Math.abs(Number(link.label) - data.current_page) <= 1 }"
                        preserve-scroll
                        preserve-state
                        :aria-label="`${trans('sidebar.pagination_page')} ${pageLabel(link.label)}`"
                        >{{ pageLabel(link.label) }}</Link
                    >
                    <span
                        v-else
                        class="page-number"
                        :class="{
                            active: link.active,
                            ellipsis: !link.url,
                            'page-number-near': Math.abs(Number(link.label) - data.current_page) <= 1,
                        }"
                        :aria-current="link.active ? 'page' : undefined"
                        >{{ pageLabel(link.label) }}</span
                    >
                </template>
            </div>
            <Link
                v-if="data.next_page_url"
                :href="data.next_page_url"
                class="page-direction"
                preserve-scroll
                preserve-state
                :aria-label="trans('sidebar.Next')"
                ><span>{{ trans('sidebar.Next') }}</span
                ><i class="ki-outline ki-arrow-right" aria-hidden="true"></i
            ></Link>
            <span v-else class="page-direction disabled" aria-disabled="true"
                ><span>{{ trans('sidebar.Next') }}</span
                ><i class="ki-outline ki-arrow-right" aria-hidden="true"></i
            ></span>
        </div>
    </nav>
</template>

<style scoped>
.pagination-shell {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 18px;
    width: 100%;
    margin-top: 28px;
    padding: 15px 18px;
    border: 1px solid #eee0e4;
    border-radius: 16px;
    background: #fff;
    box-shadow: 0 7px 25px #5a2a400d;
}
.pagination-summary {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #927d88;
    font-size: 11px;
    font-weight: 700;
    white-space: nowrap;
}
.summary-mark {
    color: #ba7892;
    font-size: 17px;
}
.pagination-controls {
    display: flex;
    align-items: center;
    gap: 7px;
}
.page-numbers {
    display: flex;
    align-items: center;
    gap: 5px;
}
.page-direction,
.page-number {
    min-width: 35px;
    min-height: 35px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    border: 1px solid transparent;
    border-radius: 9px;
    color: #745965;
    background: #faf5f6;
    text-decoration: none;
    font-size: 11px;
    font-weight: 800;
    line-height: 1;
    transition:
        background 0.2s,
        color 0.2s,
        transform 0.2s;
}
.page-direction {
    padding: 9px 12px;
}
.page-number {
    width: 35px;
}
.page-direction:hover,
a.page-number:hover {
    color: #933b60;
    background: #f3e2e9;
    transform: translateY(-2px);
}
.page-number.active {
    color: #fff;
    background: #913d60;
    box-shadow: 0 4px 12px #913d6038;
}
.page-number.ellipsis {
    background: transparent;
    color: #a9949d;
}
.page-direction.disabled {
    color: #c7b7bf;
    background: #faf8f8;
    cursor: default;
}
.page-direction i {
    font-size: 14px;
}
[dir='rtl'] .page-direction i {
    transform: rotate(180deg);
}
@media (max-width: 900px) {
    .pagination-shell {
        flex-direction: column;
        align-items: stretch;
    }
    .pagination-controls {
        justify-content: space-between;
    }
    .pagination-summary {
        justify-content: center;
    }
}
@media (max-width: 550px) {
    .pagination-shell {
        padding: 13px 10px;
        gap: 10px;
    }
    .page-numbers .page-number:not(.active):not(.page-number-near) {
        display: none;
    }
    .page-direction {
        min-width: 35px;
        padding: 8px;
    }
    .page-direction span {
        display: none;
    }
    .page-numbers {
        gap: 3px;
    }
    .page-number {
        width: 31px;
        min-width: 31px;
        min-height: 33px;
    }
}
@media (prefers-reduced-motion: reduce) {
    .page-direction,
    .page-number {
        transition: none;
    }
}
</style>
