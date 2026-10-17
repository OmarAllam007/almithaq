<script setup lang="ts">
import { useLang } from '@/composables/useLang';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, ref, watch } from 'vue';

const { trans } = useLang();
const page = usePage();
const isArabic = computed(() => page.props.locale === 'ar');
interface User {
    id: number;
    name: string;
    username: string;
    age: number;
    nationality: string | number | { id: number; name: string; ar_name: string };
    residence: string | number | { id: number; name: string; ar_name: string };
    city?: { id: number; name: string; ar_name: string } | null;
    registration_type?: number;
    marriage_status: string;
    marriage_status_label?: string;
    marriage_type_label?: string;
    mainProfileImage?: string;
    is_online?: boolean;
    is_favorited?: boolean;
    is_ignored?: boolean;
    is_verified?: boolean;
    is_subscriber?: boolean;
    can_view_images?: boolean;
}
const props = defineProps<{ user: User; countries?: Array<{ id: number; name: string; ar_name: string }> }>();
const emit = defineEmits<{ message: [userId: number]; viewProfile: [userId: number] }>();
const isFavorited = ref(props.user.is_favorited ?? false);
const isTogglingFavorite = ref(false);
watch(
    () => props.user.is_favorited,
    (value) => {
        isFavorited.value = value ?? false;
    },
);
const isFemale = computed(() => props.user.registration_type === 2);
const genderLabel = computed(() => (isFemale.value ? trans('home.female') : trans('home.male')));
const marriageStatusKeyMap: Record<number, string> = { 1: 'single', 2: 'married', 3: 'divorced', 4: 'widowed' };
const marriageStatusLabel = computed(() => {
    const key = marriageStatusKeyMap[Number(props.user.marriage_status)];
    return key
        ? trans(`enums.marriage_status_${key}_${isFemale.value ? 'female' : 'male'}`)
        : props.user.marriage_status_label || props.user.marriage_status;
});
const getCountryLabel = (value: User['nationality']) => {
    if (value && typeof value === 'object') return isArabic.value ? value.ar_name || value.name : value.name;
    const country = props.countries?.find((c) => c.id === Number(value));
    return country ? (isArabic.value ? country.ar_name || country.name : country.name) : String(value || '');
};
const nationalityName = computed(() => getCountryLabel(props.user.nationality));
const residenceName = computed(() => getCountryLabel(props.user.residence));
const cityName = computed(() => (props.user.city ? (isArabic.value ? props.user.city.ar_name || props.user.city.name : props.user.city.name) : ''));
const fallbackImage = computed(() => (isFemale.value ? '/assets/media/auth/female_bgd.png' : '/assets/media/auth/male_bgd.png'));
const toggleFavorite = async () => {
    if (isTogglingFavorite.value) return;
    isTogglingFavorite.value = true;
    try {
        const response = await axios.post(`/users/${props.user.id}/favorite`);
        isFavorited.value = response.data.is_favorited;
    } catch (error) {
        console.error('Failed to toggle favorite:', error);
    } finally {
        isTogglingFavorite.value = false;
    }
};
</script>

<template>
    <article
        class="profile-card"
        :class="{ 'profile-card--ignored': user.is_ignored }"
        tabindex="0"
        :aria-label="`${user.name}, ${user.age} ${trans('home.years_old')}`"
        @click="emit('viewProfile', user.id)"
        @keydown.enter.self="emit('viewProfile', user.id)"
    >
        <div class="profile-cover">
            <img
                :src="user.mainProfileImage || fallbackImage"
                :alt="user.name"
                :class="{ 'profile-photo--blurred': user.can_view_images === false && !!user.mainProfileImage }"
                @error="($event.target as HTMLImageElement).src = fallbackImage"
            />
            <div class="profile-cover-shade"></div>
            <div class="cover-top">
                <span class="cover-labels"
                    ><span v-if="user.is_subscriber" class="premium-label"><i class="ki-outline ki-crown"></i>{{ trans('home.premium') }}</span
                    ><span v-if="user.is_online" class="online-label"><span></span>{{ trans('chat.online') }}</span></span
                >
                <button
                    type="button"
                    class="favorite-button"
                    :class="{ selected: isFavorited }"
                    :disabled="isTogglingFavorite"
                    :aria-label="isFavorited ? trans('home.remove_from_favorites') : trans('home.add_to_favorites')"
                    :aria-pressed="isFavorited"
                    @click.stop="toggleFavorite"
                >
                    <i :class="isFavorited ? 'ki-solid ki-heart' : 'ki-outline ki-heart'"></i>
                </button>
            </div>
            <div class="cover-bottom">
                <span class="member-eyebrow">{{ isArabic ? 'تعرّف على' : 'MEET' }}</span>
                <div class="member-name-row">
                    <h3>{{ user.name }}</h3>
                    <i v-if="user.is_verified" class="ki-solid ki-shield-tick verified-mark" :title="isArabic ? 'حساب موثق' : 'Verified profile'"></i>
                </div>
                <div class="member-handle">@{{ user.username }} <span class="handle-dot">·</span> {{ user.age }} {{ trans('home.years_old') }}</div>
            </div>
        </div>
        <div class="profile-details">
            <div class="detail-location">
                <i class="ki-outline ki-geolocation"></i
                ><span
                    >{{ residenceName }}<template v-if="cityName"> · {{ cityName }}</template></span
                >
            </div>
            <div class="detail-nationality">
                <i class="ki-outline ki-flag"></i><span>{{ nationalityName }}</span>
            </div>
            <div class="detail-tags">
                <span class="detail-tag">{{ genderLabel }}</span
                ><span v-if="marriageStatusLabel" class="detail-tag">{{ marriageStatusLabel }}</span
                ><span v-if="isFemale && user.marriage_type_label" class="detail-tag">{{ user.marriage_type_label }}</span>
            </div>
        </div>
        <div class="profile-actions">
            <button type="button" class="message-button" @click.stop="emit('message', user.id)">
                <i class="ki-outline ki-message-text"></i>{{ trans('home.message') }}<span aria-hidden="true">↗</span>
            </button>
        </div>
        <div v-if="user.is_ignored" class="ignored-ribbon"><i class="ki-outline ki-eye-slash"></i>{{ trans('user_interactions.ignored') }}</div>
    </article>
</template>

<style scoped>
.profile-card {
    --card-ink: #3c2533;
    position: relative;
    display: flex;
    flex-direction: column;
    min-width: 0;
    height: 100%;
    overflow: hidden;
    border: 1px solid #eedfe4;
    border-radius: 19px;
    background: #fff;
    box-shadow: 0 8px 25px #63304412;
    cursor: pointer;
    transition:
        transform 0.25s ease,
        box-shadow 0.25s ease,
        border-color 0.25s ease;
}
.profile-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 18px 35px #63304425;
    border-color: #dcbec9;
}
.profile-card:focus-visible {
    outline: 3px solid #a34268;
    outline-offset: 3px;
}
.profile-card--ignored .profile-cover img {
    filter: grayscale(0.65);
}
.profile-card--ignored .profile-cover-shade {
    background: linear-gradient(180deg, #35232b4d, #24191fe8);
}
.ignored-ribbon {
    position: absolute;
    inset-inline-start: 13px;
    top: 13px;
    display: flex;
    align-items: center;
    gap: 5px;
    padding: 6px 9px;
    border-radius: 7px;
    background: #362733df;
    color: #f4ced6;
    font-size: 10px;
    font-weight: 800;
    z-index: 3;
}
.profile-cover {
    height: clamp(245px, 20vw, 330px);
    position: relative;
    overflow: hidden;
    background: #eadfdf;
    flex-shrink: 0;
}
.profile-cover > img {
    display: block;
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.55s ease;
}
.profile-card:hover .profile-cover > img {
    transform: scale(1.055);
}
.profile-cover > img.profile-photo--blurred {
    filter: blur(13px) brightness(0.88);
    transform: scale(1.1);
}
.profile-card:hover .profile-cover > img.profile-photo--blurred {
    transform: scale(1.16);
}
.profile-cover-shade {
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, #28182338 0%, transparent 35%, #27182414 55%, #20131edb 100%);
}
.cover-top {
    position: absolute;
    top: 14px;
    inset-inline: 14px;
    display: flex;
    justify-content: space-between;
    align-items: start;
    gap: 8px;
}
.cover-labels {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
}
.premium-label,
.online-label {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    border-radius: 8px;
    padding: 6px 9px;
    color: #fff;
    background: #3b2633a8;
    backdrop-filter: blur(10px);
    font-size: 10px;
    font-weight: 800;
}
.premium-label {
    color: #ffe7c5;
}
.online-label span {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #66dd9b;
    box-shadow: 0 0 0 3px #66dd9b34;
}
.favorite-button {
    width: 33px;
    height: 33px;
    border: 0;
    border-radius: 10px;
    background: #fffdf9ed;
    display: grid;
    place-items: center;
    color: #89596e;
    font-size: 16px;
    box-shadow: 0 4px 12px #20131e27;
    transition:
        transform 0.2s,
        color 0.2s,
        background 0.2s;
}
.favorite-button:hover {
    transform: scale(1.08);
    color: #9d4266;
}
.favorite-button.selected {
    color: #ad3f6b;
    background: #f9e8ed;
}
.favorite-button:disabled {
    opacity: 0.65;
}
.cover-bottom {
    position: absolute;
    inset-inline: 19px;
    bottom: 18px;
    color: #fff;
}
.member-eyebrow {
    display: block;
    margin-bottom: 3px;
    font-size: 9px;
    font-weight: 800;
    letter-spacing: 0.14em;
    color: #f3c5d1;
}
.member-name-row {
    display: flex;
    align-items: center;
    gap: 6px;
    min-width: 0;
}
.member-name-row h3 {
    color: #fff;
    margin: 0;
    font-size: clamp(18px, 1.5vw, 23px);
    font-weight: 800;
    line-height: 1.3;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    text-shadow: 0 2px 10px #0007;
}
.verified-mark {
    color: #a9d9f3;
    font-size: 16px;
    flex: 0 0 auto;
}
.member-handle {
    color: #f5e8eb;
    font-size: 11px;
    margin-top: 2px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.handle-dot {
    margin-inline: 3px;
    color: #e6a8bd;
}
.profile-details {
    padding: 17px 18px 9px;
    display: grid;
    gap: 8px;
    flex: 1;
}
.detail-location,
.detail-nationality {
    display: flex;
    align-items: center;
    gap: 8px;
    min-width: 0;
    color: #6d5361;
    font-size: 11px;
    font-weight: 700;
    line-height: 1.35;
}
.detail-location i,
.detail-nationality i {
    color: #ae6a84;
    font-size: 15px;
    flex: 0 0 auto;
}
.detail-location span,
.detail-nationality span {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.detail-tags {
    display: flex;
    flex-wrap: wrap;
    align-items: start;
    gap: 5px;
    margin-top: 3px;
}
.detail-tag {
    color: #86516a;
    background: #f8edf1;
    padding: 4px 8px;
    border-radius: 7px;
    font-size: 10px;
    font-weight: 800;
    line-height: 1.3;
}
.detail-tag:nth-child(2) {
    color: #866248;
    background: #fbf1e7;
}
.detail-tag:nth-child(3) {
    color: #68597a;
    background: #f1ebf5;
}
.profile-actions {
    padding: 10px 18px 18px;
}
.message-button {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
    min-height: 39px;
    padding: 8px 12px;
    border: 0;
    border-radius: 9px;
    background: #913d60;
    color: #fff;
    font-size: 11px;
    font-weight: 800;
    transition:
        background 0.2s,
        transform 0.2s;
}
.message-button:hover {
    background: #742a4b;
    transform: translateY(-1px);
}
.message-button span {
    margin-inline-start: auto;
    font-size: 16px;
    line-height: 1;
    font-weight: 400;
}
.message-button i {
    font-size: 15px;
}
@media (max-width: 767px) {
    .profile-cover {
        height: 315px;
    }
    .member-name-row h3 {
        font-size: 22px;
    }
}
@media (prefers-reduced-motion: reduce) {
    .profile-card,
    .profile-cover > img,
    .favorite-button,
    .message-button {
        transition: none;
    }
}
</style>
