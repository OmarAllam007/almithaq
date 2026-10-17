<script setup lang="ts">
import Pagination from '@/components/Pagination.vue';
import type { AdminPagination } from '@/pages/Admin/types';
import { router, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { route } from 'ziggy-js';
interface Country {
    id: number;
    name: string;
    ar_name: string | null;
    flag: string;
    is_arab: boolean;
    cities_count?: number;
}
interface City {
    id: number;
    name: string;
    ar_name: string | null;
    country_id: number;
    country?: Country;
}
type Item = Country & City;
const props = defineProps<{ kind: 'countries' | 'cities'; items: AdminPagination<Item>; countries?: Country[]; search: string }>();
const page = usePage();
const isRtl = computed(() => page.props.locale === 'ar');
const isCity = computed(() => props.kind === 'cities');
const query = ref(props.search);
const editing = ref<Item | null | undefined>(undefined);
const deleting = ref<Item | null>(null);
const busy = ref(false);
const form = useForm({ name: '', ar_name: '', flag: '', is_arab: false as boolean, country_id: '' as string | number });
let timer: ReturnType<typeof setTimeout>;
const search = () => {
    clearTimeout(timer);
    timer = setTimeout(
        () => router.get(route(`admin.${props.kind}.index`), { search: query.value || undefined }, { preserveState: true, replace: true }),
        300,
    );
};
const open = (item: Item | null) => {
    editing.value = item;
    form.clearErrors();
    form.name = item?.name || '';
    form.ar_name = item?.ar_name || '';
    form.flag = item?.flag || '';
    form.is_arab = item?.is_arab || false;
    form.country_id = item?.country_id || '';
};
const save = () => {
    const name = `admin.${props.kind}.${editing.value ? 'update' : 'store'}`;
    const options = {
        preserveScroll: true,
        onSuccess: () => {
            editing.value = undefined;
            form.reset();
        },
    };
    if (editing.value) form.put(route(name, editing.value.id), options);
    else form.post(route(name), options);
};
const remove = () => {
    if (!deleting.value) return;
    busy.value = true;
    router.delete(route(`admin.${props.kind}.destroy`, deleting.value.id), {
        preserveScroll: true,
        onSuccess: () => (deleting.value = null),
        onFinish: () => (busy.value = false),
    });
};
</script>
<template>
    <main class="admin-page">
        <div class="admin-shell">
            <header class="admin-hero">
                <div>
                    <small>CONTROL CENTER / LOCATIONS</small>
                    <h1>{{ isCity ? 'Cities' : 'Countries' }}</h1>
                    <p>Keep the location choices accurate for member profiles and search.</p>
                </div>
                <button class="admin-hero-action" type="button" @click="open(null)">
                    <i class="ki-outline ki-plus"></i> Add {{ isCity ? 'city' : 'country' }}
                </button>
            </header>
            <div class="admin-panel">
                <div class="admin-panel-head">
                    <h2>{{ items.total }} {{ kind }}</h2>
                    <div class="admin-field">
                        <label for="location-search">Search</label
                        ><input id="location-search" v-model="query" placeholder="Search by name" @input="search" />
                    </div>
                </div>
                <div v-if="items.data.length" class="admin-table-wrap">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Arabic name</th>
                                <th>{{ isCity ? 'Country' : 'Flag' }}</th>
                                <th v-if="!isCity">Cities</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in items.data" :key="item.id">
                                <td>
                                    <strong>{{ item.name }}</strong
                                    ><small>#{{ item.id }}</small>
                                </td>
                                <td>{{ item.ar_name || '—' }}</td>
                                <td>{{ isCity ? item.country?.name : item.flag }}</td>
                                <td v-if="!isCity">{{ item.cities_count }}</td>
                                <td>
                                    <div class="admin-actions">
                                        <button class="admin-button" @click="open(item)"><i class="ki-outline ki-pencil"></i> Edit</button
                                        ><button class="admin-button danger" @click="deleting = item">
                                            <i class="ki-outline ki-trash"></i> Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-else class="admin-empty">No {{ kind }} found.</div>
                <div v-if="items.last_page > 1" class="admin-panel-body"><Pagination :data="items" /></div>
            </div>
        </div>
    </main>
    <div v-if="editing !== undefined" class="admin-dialog-backdrop" @click.self="editing = undefined">
        <form class="admin-dialog" @submit.prevent="save" role="dialog" aria-modal="true">
            <h2>{{ editing ? 'Edit' : 'Add' }} {{ isCity ? 'city' : 'country' }}</h2>
            <div class="admin-form-grid">
                <div class="admin-field">
                    <label for="location-name">English name</label><input id="location-name" v-model="form.name" required maxlength="255" /><small
                        v-if="form.errors.name"
                        class="text-danger"
                        >{{ form.errors.name }}</small
                    >
                </div>
                <div class="admin-field">
                    <label for="location-ar-name">Arabic name</label><input id="location-ar-name" v-model="form.ar_name" maxlength="255" /><small
                        v-if="form.errors.ar_name"
                        class="text-danger"
                        >{{ form.errors.ar_name }}</small
                    >
                </div>
                <template v-if="isCity"
                    ><div class="admin-field wide">
                        <label for="location-country">Country</label
                        ><select id="location-country" v-model="form.country_id" required>
                            <option value="">Select country</option>
                            <option v-for="country in countries" :key="country.id" :value="country.id">
                                {{ isRtl ? country.ar_name || country.name : country.name }}
                            </option></select
                        ><small v-if="form.errors.country_id" class="text-danger">{{ form.errors.country_id }}</small>
                    </div></template
                ><template v-else
                    ><div class="admin-field">
                        <label for="location-flag">Flag emoji</label><input id="location-flag" v-model="form.flag" required maxlength="10" /><small
                            v-if="form.errors.flag"
                            class="text-danger"
                            >{{ form.errors.flag }}</small
                        >
                    </div>
                    <label class="admin-field"><span>Arab country</span><input v-model="form.is_arab" type="checkbox" /></label
                ></template>
            </div>
            <div class="admin-dialog-actions">
                <button type="button" class="admin-button" @click="editing = undefined">Cancel</button
                ><button type="submit" class="admin-button primary" :disabled="form.processing">{{ form.processing ? 'Saving…' : 'Save' }}</button>
            </div>
        </form>
    </div>
    <div v-if="deleting" class="admin-dialog-backdrop" @click.self="deleting = null">
        <div class="admin-dialog" role="alertdialog" aria-modal="true">
            <h2>Delete {{ deleting.name }}?</h2>
            <p>This location will be removed. Locations used by members cannot be deleted.</p>
            <div class="admin-dialog-actions">
                <button class="admin-button" @click="deleting = null">Cancel</button
                ><button class="admin-button danger" :disabled="busy" @click="remove">Delete</button>
            </div>
        </div>
    </div>
</template>
