<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginationData {
    current_page: number;
    first_page_url: string;
    from: number;
    last_page: number;
    last_page_url: string;
    links: PaginationLink[];
    next_page_url: string | null;
    path: string;
    per_page: number;
    prev_page_url: string | null;
    to: number;
    total: number;
}

interface Props {
    data: PaginationData;
}

const props = defineProps<Props>();

const hasPages = computed(() => {
    return props.data.last_page > 1;
});

const currentPage = computed(() => props.data.current_page);
const lastPage = computed(() => props.data.last_page);

const getPageLabel = (label: string): string => {
    if (label === '&laquo; Previous') {
        return 'Previous';
    }
    if (label === 'Next &raquo;') {
        return 'Next';
    }
    return label;
};
</script>

<template>
    <div v-if="hasPages" class="d-flex flex-stack flex-wrap pt-10">
        <div class="fs-6 fw-semibold text-gray-700">
            Showing {{ data.from }} to {{ data.to }} of {{ data.total }} entries
        </div>

        <ul class="pagination">
            <li
                v-for="(link, index) in data.links"
                :key="index"
                class="page-item"
                :class="{
                    active: link.active,
                    disabled: !link.url,
                    previous: index === 0,
                    next: index === data.links.length - 1,
                }"
            >
                <Link
                    v-if="link.url"
                    :href="link.url"
                    class="page-link"
                    :class="{
                        'active': link.active,
                    }"
                    preserve-scroll
                    preserve-state
                >
                    <span v-html="getPageLabel(link.label)"></span>
                </Link>
                <span v-else class="page-link">
                    <span v-html="getPageLabel(link.label)"></span>
                </span>
            </li>
        </ul>
    </div>
</template>

<style scoped>
.pagination {
    margin-bottom: 0;
}

.page-item.active .page-link {
    background-color: var(--bs-primary);
    border-color: var(--bs-primary);
    color: white;
}

.page-item.disabled .page-link {
    cursor: not-allowed;
    opacity: 0.5;
}
</style>
