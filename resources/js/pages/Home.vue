<script setup lang="ts">
import ChatDrawer from '@/components/Chat/ChatDrawer.vue';
import GoogleAd from '@/components/GoogleAd.vue';
import { useLang } from '@/composables/useLang';
import AdvancedSearch from '@/pages/Home/Components/AdvancedSearch.vue';
import LatestNotifications from '@/pages/Home/Components/LatestNotifications.vue';
import RecentMessages from '@/pages/Home/Components/RecentMessages.vue';
import StatsCard from '@/pages/Home/Components/StatsCard.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { route } from 'ziggy-js';

interface Stats {
    profile_views: number;
    likes: number;
    conversations: number;
    days_since_joined: number;
}
interface Props {
    stats?: Stats;
    conversations?: Array<any>;
    notifications?: Array<any>;
    countries?: Array<{ id: number; name: string; ar_name: string }>;
    marriageStatuses?: Array<{ value: number; label: string }>;
}
const props = withDefaults(defineProps<Props>(), {
    stats: () => ({ profile_views: 0, likes: 0, conversations: 0, days_since_joined: 0 }),
    conversations: () => [],
    notifications: () => [],
    countries: () => [],
    marriageStatuses: () => [],
});
const page = usePage();
const { trans } = useLang();
const isRtl = computed(() => page.props.locale === 'ar');
const firstName = computed(
    () =>
        String((page.props.auth as any)?.user?.name || '')
            .trim()
            .split(/\s+/)[0],
);
const isChatOpen = ref(false);
const selectedConversationId = ref<number | undefined>();
const handleOpenChat = (conversationId: number) => {
    selectedConversationId.value = conversationId;
    isChatOpen.value = true;
};
const handleCloseChat = () => {
    isChatOpen.value = false;
    selectedConversationId.value = undefined;
};
</script>

<template>
    <div id="kt_app_content" class="app-content flex-column-fluid home-page">
        <div id="kt_app_content_container" class="app-container container-xxl home-shell">
            <section class="home-hero" aria-labelledby="home-heading">
                <div class="hero-copy">
                    <div class="hero-eyebrow">
                        <span class="eyebrow-dot"></span>{{ isRtl ? 'مساحتك للقاءات الجادة' : 'YOUR SPACE FOR MEANINGFUL CONNECTIONS' }}
                    </div>
                    <h1 id="home-heading">
                        {{ isRtl ? 'أهلاً بعودتك' : 'Welcome back' }}<span v-if="firstName" class="hero-name">، {{ firstName }}</span
                        ><span class="hero-period">.</span>
                    </h1>
                    <p>
                        {{
                            isRtl
                                ? 'كل قصة جميلة تبدأ بخطوة. اكتشف أشخاصاً يشاركونك ما يهمك.'
                                : 'Every meaningful story starts somewhere. Discover people who share what matters to you.'
                        }}
                    </p>
                    <div class="hero-actions">
                        <Link :href="route('new-members')" class="hero-primary"
                            >{{ isRtl ? 'اكتشف الأعضاء' : 'Explore members' }} <span aria-hidden="true">↗</span></Link
                        >
                        <Link :href="route('smart-search.index')" class="hero-secondary"
                            >{{ isRtl ? 'بحث مخصص' : 'Find your match' }} <span aria-hidden="true">⌕</span></Link
                        >
                    </div>
                </div>
                <div class="hero-art" aria-hidden="true">
                    <div class="art-orbit art-orbit-one"></div>
                    <div class="art-orbit art-orbit-two"></div>
                    <div class="art-card art-card-back"></div>
                    <div class="art-card art-card-front">
                        <div class="art-card-sun"></div>
                        <div class="art-card-line"></div>
                        <div class="art-card-line short"></div>
                        <div class="art-card-emblem">✳</div>
                    </div>
                    <div class="art-spark art-spark-one">✦</div>
                    <div class="art-spark art-spark-two">✳</div>
                </div>
            </section>

            <section class="home-stats home-reveal" aria-label="Your activity">
                <StatsCard
                    :profile-views="stats.profile_views"
                    :likes="stats.likes"
                    :conversations="stats.conversations"
                    :days-since-joined="stats.days_since_joined"
                />
            </section>

            <!-- <div class="home-ad home-reveal"><GoogleAd slot-id="HOME_TOP_BANNER" format="horizontal" /></div> -->

            <section class="home-search home-reveal" aria-label="Search members">
                <AdvancedSearch :countries="countries" :marriage-statuses="marriageStatuses" />
            </section>

            <section class="home-activity home-reveal" aria-labelledby="activity-heading">
                <div class="section-heading">
                    <div>
                        <span class="section-kicker">{{ isRtl ? 'ابقَ على تواصل' : 'STAY CONNECTED' }}</span>
                        <h2 id="activity-heading">{{ isRtl ? 'آخر المستجدات' : 'Your latest activity' }}</h2>
                    </div>
                    <Link :href="route('notifications.index')" class="section-link"
                        >{{ trans('home.view_all') }} <span aria-hidden="true">↗</span></Link
                    >
                </div>
                <div class="activity-grid">
                    <RecentMessages :conversations="conversations" @open-chat="handleOpenChat" />
                    <LatestNotifications :notifications="notifications" />
                </div>
            </section>

            <!-- <div class="home-ad home-ad-bottom"><GoogleAd slot-id="HOME_BOTTOM_BANNER" format="auto" /></div> -->
        </div>
        <ChatDrawer :is-open="isChatOpen" :conversation-id="selectedConversationId" @close="handleCloseChat" />
    </div>
</template>

<style scoped>
.home-page {
    --ink: #351c2a;
    --berry: #9e315e;
    --rose: #d96988;
    --cream: #fbf8f5;
    color: var(--ink);
    background: #fbf8f5;
    min-height: 100vh;
}
.home-shell {
    max-width: 1450px;
    padding-block: 28px 72px;
}
.home-hero {
    min-height: 354px;
    position: relative;
    display: flex;
    overflow: hidden;
    align-items: center;
    border-radius: 28px;
    background: linear-gradient(112deg, #441f35 0%, #642b47 57%, #a44a69 100%);
    box-shadow: 0 20px 50px rgba(78, 29, 53, 0.15);
}
.home-hero::before {
    content: '';
    position: absolute;
    inset: 0;
    opacity: 0.27;
    background-image: radial-gradient(#fff 0.7px, transparent 0.7px);
    background-size: 22px 22px;
    mask-image: linear-gradient(to right, transparent 25%, #000);
}
.hero-copy {
    z-index: 1;
    position: relative;
    width: 62%;
    padding: 50px clamp(28px, 5vw, 74px);
}
.hero-eyebrow {
    display: flex;
    align-items: center;
    gap: 10px;
    color: #f9c3d2;
    font-size: 11px;
    letter-spacing: 0.16em;
    font-weight: 800;
    margin-bottom: 18px;
}
.eyebrow-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: #f3b9a0;
    box-shadow: 0 0 0 5px #ffffff1f;
}
.home-hero h1 {
    color: white;
    font-size: clamp(33px, 3.1vw, 52px);
    line-height: 1.25;
    font-weight: 800;
    letter-spacing: -0.035em;
    margin: 0 0 13px;
}
.hero-name {
    color: #ffcfcb;
}
.hero-period {
    color: #f7ab90;
}
.home-hero p {
    color: #f4dce4;
    font-size: 15px;
    line-height: 1.9;
    max-width: 530px;
    margin: 0 0 25px;
}
.hero-actions {
    display: flex;
    gap: 11px;
    flex-wrap: wrap;
}
.hero-actions a {
    text-decoration: none;
    display: inline-flex;
    justify-content: center;
    align-items: center;
    gap: 20px;
    min-height: 45px;
    border-radius: 10px;
    padding: 10px 18px;
    font-weight: 800;
    font-size: 13px;
    transition:
        transform 0.2s,
        background 0.2s;
}
.hero-actions a:hover {
    transform: translateY(-2px);
}
.hero-primary {
    background: #fff;
    color: #6f2d4b;
}
.hero-primary:hover {
    color: #6f2d4b;
    background: #fff3f1;
}
.hero-secondary {
    border: 1px solid #ffffff75;
    color: #fff;
}
.hero-secondary:hover {
    color: #fff;
    background: #ffffff1c;
}
.hero-actions span {
    font-size: 18px;
    font-weight: 400;
}
.hero-art {
    position: absolute;
    inset-inline-end: 0;
    width: 42%;
    height: 100%;
}
.art-orbit {
    position: absolute;
    border: 1px solid #ffffff3c;
    border-radius: 50%;
    width: 390px;
    aspect-ratio: 1;
    top: -70px;
    inset-inline-end: -40px;
}
.art-orbit-two {
    width: 285px;
    top: -15px;
    inset-inline-end: 15px;
}
.art-card {
    position: absolute;
    width: 200px;
    height: 260px;
    border-radius: 110px 110px 22px 22px;
    transform: rotate(-13deg);
    top: 58px;
    inset-inline-end: 120px;
    box-shadow: 0 25px 35px #32122357;
}
.art-card-back {
    background: #d88d9c;
    transform: rotate(15deg);
    inset-inline-end: 80px;
    top: 68px;
}
.art-card-front {
    background: linear-gradient(150deg, #f9e7dd, #e4bbad);
    display: flex;
    align-items: center;
    flex-direction: column;
    overflow: hidden;
}
.art-card-sun {
    width: 96px;
    height: 96px;
    border-radius: 50%;
    background: #c97477;
    margin-top: 37px;
    opacity: 0.75;
}
.art-card-line {
    width: 118px;
    height: 1px;
    background: #a1616c;
    margin-top: 21px;
}
.art-card-line.short {
    width: 78px;
    margin-top: 10px;
}
.art-card-emblem {
    position: absolute;
    bottom: 11px;
    color: #9d5367;
    font-size: 30px;
}
.art-spark {
    position: absolute;
    color: #ffd2c4;
    font-size: 34px;
    top: 43px;
    inset-inline-end: 59px;
}
.art-spark-two {
    top: 252px;
    inset-inline-end: 323px;
    font-size: 20px;
}
.home-stats {
    margin-top: 20px;
}
.home-ad {
    margin: 18px 0 0;
}
.home-search {
    margin-top: 18px;
}
.home-activity {
    margin-top: 38px;
}
.home-ad-bottom {
    margin-top: 28px;
}
.section-heading {
    display: flex;
    justify-content: space-between;
    align-items: end;
    gap: 16px;
    margin-bottom: 17px;
}
.section-kicker {
    display: block;
    color: #ae5273;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 0.16em;
    margin-bottom: 7px;
}
.section-heading h2 {
    margin: 0;
    color: #351c2a;
    font-size: 25px;
    font-weight: 800;
}
.section-link {
    text-decoration: none;
    color: #913c60;
    font-size: 12px;
    font-weight: 800;
    white-space: nowrap;
}
.section-link span {
    margin-inline-start: 6px;
}
.activity-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 18px;
    align-items: stretch;
}
.activity-grid :deep(> div) {
    min-width: 0;
    margin: 0;
    width: 100%;
}
.activity-grid :deep(.card) {
    height: 100%;
    border: 1px solid #f0e7e7;
    border-radius: 19px;
    box-shadow: 0 8px 28px #6130480b;
    overflow: hidden;
}
.activity-grid :deep(.card-header) {
    padding: 19px 22px 10px;
    min-height: 78px;
}
.activity-grid :deep(.card-body) {
    padding: 4px 15px 18px !important;
}
.activity-grid :deep(.conversation-item),
.activity-grid :deep(.notification-item) {
    border-radius: 11px !important;
}
.activity-grid :deep(.card-label) {
    color: #3d2734 !important;
    font-size: 16px;
}
.home-reveal {
    animation: rise 0.65s both;
}
.home-stats {
    animation-delay: 0.08s;
}
.home-search {
    animation-delay: 0.17s;
}
.home-activity {
    animation-delay: 0.24s;
}
@keyframes rise {
    from {
        opacity: 0;
        transform: translateY(16px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
[dir='rtl'] .home-hero::before {
    transform: scaleX(-1);
}
@media (max-width: 1050px) {
    .hero-art {
        opacity: 0.65;
        inset-inline-end: -70px;
    }
    .hero-copy {
        width: 73%;
    }
    .art-card {
        inset-inline-end: 80px;
    }
    .art-card-back {
        inset-inline-end: 40px;
    }
}
@media (max-width: 767px) {
    .home-shell {
        padding: 14px 14px 65px;
    }
    .home-hero {
        min-height: 355px;
        border-radius: 20px;
        align-items: flex-start;
    }
    .hero-copy {
        width: 100%;
        padding: 30px 25px 115px;
    }
    .hero-eyebrow {
        font-size: 9px;
    }
    .home-hero h1 {
        font-size: 32px;
    }
    .home-hero p {
        font-size: 13px;
        max-width: 380px;
    }
    .hero-art {
        transform: scale(0.6);
        transform-origin: bottom right;
        opacity: 0.38;
        inset-inline-end: -90px;
        bottom: -45px;
        top: auto;
        height: 330px;
        width: 300px;
    }
    .activity-grid {
        grid-template-columns: 1fr;
    }
    .section-heading h2 {
        font-size: 21px;
    }
    .home-activity {
        margin-top: 30px;
    }
}
@media (prefers-reduced-motion: reduce) {
    .home-reveal {
        animation: none;
    }
    .hero-actions a {
        transition: none;
    }
}
</style>
