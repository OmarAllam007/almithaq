<script setup lang="ts">
import { ref, computed } from 'vue';
import axios from 'axios';
import { usePage } from '@inertiajs/vue3';
import { useLang } from '@/composables/useLang';

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
    registration_type?: number; // 1 = male, 2 = female
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

interface Props {
    user: User;
    countries?: Array<{ id: number; name: string; ar_name: string }>;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    message: [userId: number];
    viewProfile: [userId: number];
}>();

const isFavorited = ref(props.user.is_favorited ?? false);
const isTogglingFavorite = ref(false);

const isFemale = computed(() => props.user.registration_type === 2);
const genderLabel = computed(() => isFemale.value ? trans('home.female') : trans('home.male'));
const showMarriageTypeBadge = computed(() => isFemale.value && !!props.user.marriage_type_label);

const marriageStatusKeyMap: Record<number, string> = { 1: 'single', 2: 'married', 3: 'divorced', 4: 'widowed' };

const marriageStatusLabel = computed(() => {
    const status = Number(props.user.marriage_status);
    const key = marriageStatusKeyMap[status];
    const genderSuffix = isFemale.value ? 'female' : 'male';
    return key
        ? trans(`enums.marriage_status_${key}_${genderSuffix}`)
        : (props.user.marriage_status_label || props.user.marriage_status);
});

const getCountryLabel = (country: any) =>
    isArabic.value ? (country?.ar_name || country?.name) : country?.name;

const getNationalityName = computed(() => {
    const nat = props.user.nationality as any;
    if (nat && typeof nat === 'object') return getCountryLabel(nat);
    const country = props.countries?.find((c) => c.id === Number(nat));
    return country ? getCountryLabel(country) : nat;
});

const getResidenceName = computed(() => {
    const res = props.user.residence as any;
    if (res && typeof res === 'object') return getCountryLabel(res);
    const country = props.countries?.find((c) => c.id === Number(res));
    return country ? getCountryLabel(country) : res;
});

const getCityName = computed(() => {
    const city = props.user.city;
    return city ? getCountryLabel(city) : '';
});

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

const handleMessage = () => emit('message', props.user.id);
const handleCardClick = () => emit('viewProfile', props.user.id);
</script>

<template>
    <div
        class="uc-card"
        :class="{ 'uc-card--premium': user.is_subscriber, 'uc-card--ignored': user.is_ignored }"
        @click="handleCardClick"
    >
        <!-- Content layer (filtered when ignored) -->
        <div class="uc-content">
        <!-- Photo Section -->
        <div class="uc-photo">
            
            <img
                :src="user.mainProfileImage || (isFemale ? '/assets/media/auth/female_bgd.png' : '/assets/media/auth/male_bgd.png')"
                :alt="user.username"
                class="uc-photo__img"
                :class="{ 'uc-photo__img--blurred': user.can_view_images === false && !!user.mainProfileImage }"
                @error="($event.target as HTMLImageElement).src = isFemale ? '/assets/media/auth/female_bgd.png' : '/assets/media/auth/male_bgd.png'"
            />

            <div class="uc-photo__gradient"></div>

            <!-- Premium badge -->
            <div v-if="user.is_subscriber" class="uc-premium-badge">
                <i class="ki-outline ki-crown"></i>
                {{ trans('home.premium') }}
            </div>

            <!-- Online pulse indicator -->
            <div
                class="uc-online"
                :class="user.is_online ? 'uc-online--on' : 'uc-online--off'"
                :title="user.is_online ? trans('chat.online') : trans('chat.offline')"
            >
                <span v-if="user.is_online" class="uc-online__pulse"></span>
            </div>

            <!-- Favorite button -->
            <button
                class="uc-fav"
                :class="{ 'uc-fav--active': isFavorited }"
                :disabled="isTogglingFavorite"
                @click.stop="toggleFavorite"
                :title="isFavorited ? trans('home.remove_from_favorites') : trans('home.add_to_favorites')"
            >
                <i :class="isFavorited ? 'ki-solid ki-heart' : 'ki-outline ki-heart'"></i>
            </button>

            <!-- Name overlay -->
            <div class="uc-photo__footer">
                <div class="uc-username">
                    {{ user.name }}
                    <i v-if="user.is_subscriber" class="ki-outline ki-verify uc-verify"></i>
                    <i :class="user.is_verified ? 'ki-solid ki-shield-tick uc-verified-icon' : 'ki-outline ki-shield-tick uc-unverified-icon'"></i>
                </div>
                <div class="uc-age-line">{{ user.age }} {{ trans('home.years_old') }}</div>
            </div>
        </div>

        <!-- Info Section -->
        <div class="uc-body">
            <div class="uc-meta">
                <span class="uc-meta__item">
                    <i class="ki-outline ki-flag"></i>
                    {{ getNationalityName }}
                </span>
                <span class="uc-meta__sep">·</span>
                <span class="uc-meta__item">
                    <i class="ki-outline ki-geolocation"></i>
                    {{ getResidenceName }}
                </span>
                <template v-if="getCityName">
                    <span class="uc-meta__sep">·</span>
                    <span class="uc-meta__item">
                        <i class="ki-outline ki-home-2"></i>
                        {{ getCityName }}
                    </span>
                </template>
            </div>

            <div class="uc-badges">
                <span class="uc-badge" :class="isFemale ? 'uc-badge--female' : 'uc-badge--male'">
                    <span class="uc-badge__gender">{{ isFemale ? '♀' : '♂' }}</span>
                    {{ genderLabel }}
                </span>
                <span v-if="marriageStatusLabel" class="uc-badge uc-badge--status">
                    {{ marriageStatusLabel }}
                </span>
                <span v-if="showMarriageTypeBadge" class="uc-badge uc-badge--type">
                    {{ user.marriage_type_label }}
                </span>
            </div>
        </div>

        <!-- Action -->
        <div class="uc-actions" @click.stop>
            <button
                class="uc-msg-btn"
                :class="{ 'uc-msg-btn--premium': user.is_subscriber }"
                @click.stop="handleMessage"
            >
                <i class="ki-outline ki-message-text"></i>
                {{ trans('home.message') }}
            </button>
        </div>
        </div><!-- end uc-content -->

        <!-- Ignored overlays — siblings of uc-content, not affected by filter -->
        <template v-if="user.is_ignored">
            <div class="uc-ignored-overlay" aria-hidden="true"></div>
            <div class="uc-ignored-badge">
                <span class="uc-ignored-badge__dot"></span>
                <i class="ki-outline ki-eye-slash uc-ignored-badge__icon"></i>
                <span class="uc-ignored-badge__text">{{ trans('user_interactions.ignored') }}</span>
            </div>
        </template>
    </div>
</template>

<style scoped>
/* ─── Base Card ──────────────────────────────────────────── */
.uc-card {
    border-radius: 18px;
    overflow: hidden;
    background: #fff;
    box-shadow: 0 2px 14px rgba(0, 0, 0, 0.07);
    cursor: pointer;
    display: flex;
    flex-direction: column;
    height: 100%;
    position: relative;
    transition: transform 0.28s cubic-bezier(0.34, 1.56, 0.64, 1),
        box-shadow 0.28s ease;
    will-change: transform;
}

/* ─── Content wrapper (filtered when ignored) ────────────── */
.uc-content {
    display: flex;
    flex-direction: column;
    height: 100%;
}

/* ─── Ignored card ───────────────────────────────────────── */
.uc-card--ignored .uc-content {
    filter: grayscale(0.78) brightness(0.86) opacity(0.82);
    transition: filter 0.42s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}

.uc-card--ignored:hover .uc-content {
    filter: grayscale(0.1) brightness(1) opacity(1);
}

.uc-ignored-overlay {
    position: absolute;
    inset: 0;
    border-radius: 18px;
    background: repeating-linear-gradient(
        -45deg,
        transparent 0px,
        transparent 16px,
        rgba(100, 10, 20, 0.055) 16px,
        rgba(100, 10, 20, 0.055) 17px
    );
    pointer-events: none;
    z-index: 10;
    transition: opacity 0.42s ease;
}

.uc-card--ignored:hover .uc-ignored-overlay {
    opacity: 0;
}

.uc-ignored-badge {
    position: absolute;
    top: 10px;
    inset-inline-start: 10px;
    z-index: 11;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    background: rgba(10, 3, 6, 0.76);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    color: #ff6060;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.65px;
    text-transform: uppercase;
    padding: 4px 9px 4px 6px;
    border-radius: 20px;
    border: 1px solid rgba(255, 80, 80, 0.32);
    box-shadow:
        0 2px 12px rgba(0, 0, 0, 0.45),
        inset 0 1px 0 rgba(255, 255, 255, 0.06);
    pointer-events: none;
    animation: uc-badge-in 0.36s cubic-bezier(0.34, 1.56, 0.64, 1) both;
}

.uc-ignored-badge__dot {
    width: 5px;
    height: 5px;
    border-radius: 50%;
    background: #ff4040;
    flex-shrink: 0;
    box-shadow: 0 0 6px rgba(255, 64, 64, 0.8);
    animation: uc-dot-pulse 2s ease-in-out infinite;
}

.uc-ignored-badge__icon {
    font-size: 11px;
    flex-shrink: 0;
    opacity: 0.9;
}

.uc-ignored-badge__text {
    line-height: 1;
}

@keyframes uc-badge-in {
    from { transform: scale(0.65) translateY(-5px); opacity: 0; }
    to   { transform: scale(1) translateY(0);       opacity: 1; }
}

@keyframes uc-dot-pulse {
    0%, 100% { opacity: 1;    box-shadow: 0 0 6px rgba(255, 64, 64, 0.8); }
    50%       { opacity: 0.55; box-shadow: 0 0 2px rgba(255, 64, 64, 0.3); }
}

.uc-card:hover {
    transform: translateY(-7px);
    box-shadow: 0 16px 40px rgba(0, 0, 0, 0.13);
}

/* ─── Photo ──────────────────────────────────────────────── */
.uc-photo {
    position: relative;
    height: 230px;
    flex-shrink: 0;
    background: #e5e7eb;
    overflow: hidden;
}

.uc-photo__img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: transform 0.5s ease;
}

.uc-photo__img--blurred {
    filter: blur(12px) brightness(0.88);
    transform: scale(1.1);
}

.uc-card:hover .uc-photo__img {
    transform: scale(1.04);
}

.uc-photo__gradient {
    position: absolute;
    inset: 0;
    background: linear-gradient(
        to bottom,
        rgba(0, 0, 0, 0) 35%,
        rgba(0, 0, 0, 0.72) 100%
    );
    pointer-events: none;
}

/* ─── Premium Badge ──────────────────────────────────────── */
.uc-premium-badge {
    position: absolute;
    top: 11px;
    left: 50%;
    transform: translateX(-50%);
    display: inline-flex;
    align-items: center;
    gap: 4px;
    background: linear-gradient(135deg, #f6d365, #fda085);
    color: #fff;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.6px;
    text-transform: uppercase;
    padding: 3px 12px;
    border-radius: 20px;
    box-shadow: 0 2px 10px rgba(246, 211, 101, 0.55);
    white-space: nowrap;
}

/* ─── Online Indicator ───────────────────────────────────── */
.uc-online {
    position: absolute;
    bottom: 56px;
    /* physical right so it stays at visual right even in RTL */
    right: 12px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid rgba(255, 255, 255, 0.9);
    z-index: 2;
}

.uc-online--on {
    background: #22c55e;
}

.uc-online--off {
    background: #9ca3af;
}

.uc-online__pulse {
    position: absolute;
    inset: -4px;
    border-radius: 50%;
    border: 2px solid #22c55e;
    animation: uc-pulse 1.8s ease-out infinite;
    opacity: 0;
}

@keyframes uc-pulse {
    0% { transform: scale(1); opacity: 0.75; }
    100% { transform: scale(2.4); opacity: 0; }
}

/* ─── Favorite Button ────────────────────────────────────── */
.uc-fav {
    position: absolute;
    top: 10px;
    right: 10px;
    width: 34px;
    height: 34px;
    border-radius: 50%;
    border: none;
    background: rgba(255, 255, 255, 0.92);
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    padding: 0;
    color: #9ca3af;
    font-size: 15px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.14);
    transition: transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1),
        background 0.18s, color 0.18s, box-shadow 0.18s;
    z-index: 2;
}

.uc-fav:hover {
    transform: scale(1.12);
    color: rgb(208, 46, 121);
    background: rgba(255, 255, 255, 0.98);
}

.uc-fav--active {
    background: rgba(208, 46, 121, 0.12);
    color: rgb(208, 46, 121);
    box-shadow: 0 2px 10px rgba(208, 46, 121, 0.3);
    animation: uc-heartbeat 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.uc-fav--active i {
    color: rgb(148, 28, 82);
}

.uc-fav--active:hover {
    transform: scale(1.12);
    background: rgba(208, 46, 121, 0.18);
    color: rgb(208, 46, 121);
}

@keyframes uc-heartbeat {
    0%   { transform: scale(1); }
    50%  { transform: scale(1.28); }
    100% { transform: scale(1); }
}

/* ─── Name / Age overlay ─────────────────────────────────── */
.uc-photo__footer {
    position: absolute;
    bottom: 12px;
    left: 14px;
    right: 30px;
    color: #fff;
    pointer-events: none;
}

.uc-username {
    font-size: 1.1rem;
    font-weight: 700;
    line-height: 1.25;
    text-shadow: 0 1px 6px rgba(0, 0, 0, 0.55);
    letter-spacing: 0.01em;
}

.uc-age-line {
    font-size: 0.78rem;
    opacity: 0.88;
    margin-top: 2px;
    text-shadow: 0 1px 4px rgba(0, 0, 0, 0.4);
}

.uc-verify {
    color: #fcd34d;
    font-size: 0.95rem;
    margin-inline-start: 3px;
}

.uc-verified-icon {
    color: #38bdf8;
    font-size: 0.95rem;
    margin-inline-start: 3px;
    filter: drop-shadow(0 0 3px rgba(56, 189, 248, 0.6));
}

.uc-unverified-icon {
    color: rgba(255, 255, 255, 0.5);
    font-size: 0.95rem;
    margin-inline-start: 3px;
}

/* ─── Body (meta + status) ───────────────────────────────── */
.uc-body {
    padding: 12px 14px 8px;
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.uc-meta {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 4px;
    font-size: 0.78rem;
    color: #6b7280;
    line-height: 1.4;
}

.uc-meta__item {
    display: inline-flex;
    align-items: center;
    gap: 3px;
}

.uc-meta__item i {
    font-size: 13px;
    color: rgb(208, 46, 121);
    flex-shrink: 0;
}

.uc-meta__sep {
    color: #d1d5db;
    font-size: 0.9rem;
    line-height: 1;
}

.uc-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
    margin-top: auto;
}

.uc-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 0.68rem;
    font-weight: 600;
    padding: 3px 9px;
    border-radius: 20px;
    line-height: 1.3;
    white-space: nowrap;
}

.uc-badge__gender {
    font-size: 0.75rem;
    line-height: 1;
}

.uc-badge--male {
    background: #eff6ff;
    color: #2563eb;
    border: 1px solid rgba(37, 99, 235, 0.22);
}

.uc-badge--female {
    background: #fdf0f6;
    color: rgb(208, 46, 121);
    border: 1px solid rgba(208, 46, 121, 0.25);
}

.uc-badge--status {
    background: #f0fdf4;
    color: #16a34a;
    border: 1px solid rgba(22, 163, 74, 0.22);
}

.uc-badge--type {
    background: #fff7ed;
    color: #c2410c;
    border: 1px solid rgba(194, 65, 12, 0.22);
}

/* ─── Actions ────────────────────────────────────────────── */
.uc-actions {
    padding: 0 14px 14px;
}

.uc-msg-btn {
    width: 100%;
    padding: 9px 0;
    border: none;
    border-radius: 11px;
    font-size: 0.85rem;
    font-weight: 600;
    letter-spacing: 0.02em;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    cursor: pointer;
    transition: all 0.22s ease;
    background: rgb(208, 46, 121);
    color: #fff;
}

.uc-msg-btn:hover {
    background: rgb(185, 38, 106);
    box-shadow: 0 5px 14px rgba(208, 46, 121, 0.38);
}

.uc-msg-btn i {
    color: rgba(255, 255, 255, 0.7);
}

.uc-msg-btn--premium {
    background: linear-gradient(135deg, #f6d365, #f0a04b);
    color: #fff;
}

.uc-msg-btn--premium:hover {
    background: linear-gradient(135deg, #f0c844, #e89530);
    box-shadow: 0 5px 14px rgba(246, 211, 101, 0.42);
}

/* ─── Premium Card Border ────────────────────────────────── */
.uc-card--premium {
    border: 2px solid transparent;
    background-image: linear-gradient(#fff, #fff),
        linear-gradient(135deg, #f6d365 0%, #fda085 50%, #f6d365 100%);
    background-origin: border-box;
    background-clip: padding-box, border-box;
    position: relative;
    overflow: hidden;
}

.uc-card--premium::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(
        90deg,
        transparent,
        rgba(255, 215, 0, 0.06),
        transparent
    );
    animation: uc-shimmer 3.5s infinite;
    pointer-events: none;
    z-index: 0;
}

.uc-card--premium:hover {
    box-shadow: 0 16px 40px rgba(253, 160, 133, 0.3) !important;
}

@keyframes uc-shimmer {
    0% { left: -100%; }
    100% { left: 100%; }
}
</style>
