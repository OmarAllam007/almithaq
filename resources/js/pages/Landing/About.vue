<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { route } from 'ziggy-js';
import { vueLang } from '@erag/lang-sync-inertia';
import LandingNav from './_Shared/LandingNav.vue';
import LandingFooter from './_Shared/LandingFooter.vue';

const { __ } = vueLang();
const page = usePage();
const isRtl = computed(() => (page.props.locale as string) === 'ar');

let observer: IntersectionObserver | null = null;

onMounted(() => {
    observer = new IntersectionObserver(
        (entries) => {
            for (const entry of entries) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer?.unobserve(entry.target);
                }
            }
        },
        { threshold: 0.15 },
    );
    document.querySelectorAll('.reveal').forEach((el) => observer?.observe(el));
});

onBeforeUnmount(() => {
    observer?.disconnect();
});
</script>

<template>
    <Head>
        <link rel="preconnect" href="https://fonts.bunny.net" />
        <title>{{ __('pages.nav-about') }} — Khotobah</title>
    </Head>

    <div class="landing" :class="{ 'is-ar': isRtl }" :dir="isRtl ? 'rtl' : 'ltr'">
        <LandingNav />

        <!-- ── HERO ── -->
        <section class="about-hero">
            <div class="about-hero__glow about-hero__glow--1"></div>
            <div class="about-hero__glow about-hero__glow--2"></div>
            <div class="shell about-hero__inner">
                <div class="about-hero__copy">
                    <span class="eyebrow reveal">
                        <span class="eyebrow__diamond"></span>{{ __('pages.about-eyebrow') }}
                    </span>
                    <h1 class="page-title about-hero__title reveal">
                        <span class="line">{{ __('pages.about-hero-title-1') }}</span>
                        <span class="line line--accent">{{ __('pages.about-hero-title-2') }}</span>
                    </h1>
                    <p class="about-hero__desc reveal">{{ __('pages.about-hero-desc') }}</p>
                </div>
                <div class="about-hero__art reveal" aria-hidden="true">
                    <div class="geo-art">
                        <div class="geo-ring geo-ring--outer"></div>
                        <div class="geo-ring geo-ring--mid"></div>
                        <div class="geo-ring geo-ring--inner"></div>
                        <svg class="geo-star" viewBox="0 0 120 120" fill="none">
                            <path d="M60 8 L63.6 52.4 L108 60 L63.6 67.6 L60 112 L56.4 67.6 L12 60 L56.4 52.4Z" fill="currentColor" opacity="0.18" />
                            <path d="M60 22 L62.8 55.2 L96 60 L62.8 64.8 L60 98 L57.2 64.8 L24 60 L57.2 55.2Z" fill="currentColor" opacity="0.28" />
                        </svg>
                        <div class="geo-center">
                            <svg viewBox="0 0 40 28" fill="none">
                                <circle cx="14" cy="14" r="9" stroke="currentColor" stroke-width="2" />
                                <circle cx="26" cy="14" r="9" stroke="currentColor" stroke-width="2" opacity="0.5" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
            <div class="arabesque"></div>
        </section>

        <!-- ── MISSION ── -->
        <section class="section">
            <div class="shell mission-grid">
                <div class="mission__copy reveal">
                    <span class="eyebrow"><span class="eyebrow__diamond"></span>{{ __('pages.about-mission-eyebrow') }}</span>
                    <h2 class="section__title">{{ __('pages.about-mission-title') }}</h2>
                    <p class="mission__body">{{ __('pages.about-mission-body-1') }}</p>
                    <p class="mission__body">{{ __('pages.about-mission-body-2') }}</p>
                    <div class="mission__divider"></div>
                    <Link :href="route('signup')" class="btn btn--solid btn--lg">
                        {{ __('pages.about-cta-btn') }}
                        <svg class="btn__arrow" viewBox="0 0 24 24" fill="none">
                            <path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </Link>
                </div>
                <div class="mission__visual reveal">
                    <div class="mission__quote">
                        <span class="mission__quote-mark" aria-hidden="true">"</span>
                        <p>{{ isRtl ? 'وَمِنْ آيَاتِهِ أَنْ خَلَقَ لَكُم مِّنْ أَنفُسِكُمْ أَزْوَاجًا لِّتَسْكُنُوا إِلَيْهَا' : 'And among His signs is that He created for you from yourselves mates, that you may find tranquility in them.' }}</p>
                        <cite>{{ isRtl ? '— سورة الروم ٢١' : '— Quran 30:21' }}</cite>
                    </div>
                    <div class="mission__stats">
                        <div class="mission__stat">
                            <strong>1,200+</strong>
                            <span>{{ __('landing.stat-members') }}</span>
                        </div>
                        <div class="mission__stat">
                            <strong>32+</strong>
                            <span>{{ __('landing.stat-countries') }}</span>
                        </div>
                        <div class="mission__stat">
                            <strong>95%</strong>
                            <span>{{ __('landing.stat-verified') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ── VALUES ── -->
        <section class="section section--plum">
            <div class="shell">
                <div class="section__head reveal">
                    <span class="eyebrow eyebrow--light"><span class="eyebrow__diamond"></span>{{ __('pages.about-values-eyebrow') }}</span>
                    <h2 class="section__title section__title--light">{{ __('pages.about-values-title') }}</h2>
                </div>
                <div class="about-values">
                    <article
                        v-for="(v, i) in [
                            { num: '01', icon: 'star', key: 1 },
                            { num: '02', icon: 'lock', key: 2 },
                            { num: '03', icon: 'badge', key: 3 },
                            { num: '04', icon: 'moon', key: 4 },
                        ]"
                        :key="v.key"
                        class="about-value reveal"
                        :style="{ transitionDelay: i * 90 + 'ms' }"
                    >
                        <div class="about-value__head">
                            <span class="about-value__num">{{ v.num }}</span>
                            <span class="about-value__icon">
                                <svg v-if="v.icon === 'star'" viewBox="0 0 24 24" fill="none">
                                    <path d="M12 2l2.4 7.4H22l-6.2 4.5 2.4 7.4L12 17l-6.2 4.3 2.4-7.4L2 9.4h7.6L12 2Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round" />
                                </svg>
                                <svg v-else-if="v.icon === 'lock'" viewBox="0 0 24 24" fill="none">
                                    <rect x="5" y="11" width="14" height="10" rx="2" stroke="currentColor" stroke-width="1.6" />
                                    <path d="M8 11V7a4 4 0 0 1 8 0v4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" />
                                    <circle cx="12" cy="16" r="1.5" fill="currentColor" />
                                </svg>
                                <svg v-else-if="v.icon === 'badge'" viewBox="0 0 24 24" fill="none">
                                    <path d="m12 3 2.3 1.6 2.8-.2 1 2.6 2.3 1.6-.7 2.7.7 2.7-2.3 1.6-1 2.6-2.8-.2L12 21l-2.3-1.6-2.8.2-1-2.6L3.6 15.4 4.3 12.7 3.6 10l2.3-1.6 1-2.6 2.8.2L12 3Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round" />
                                    <path d="m9.5 12 1.8 1.8L15 10" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <svg v-else viewBox="0 0 24 24" fill="none">
                                    <path d="M16.5 15.5A7 7 0 1 1 9 4.2a5.6 5.6 0 0 0 7.5 11.3Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round" />
                                    <path d="m18 5 .7 1.8L20.5 7.5l-1.8.7L18 10l-.7-1.8L15.5 7.5l1.8-.7L18 5Z" fill="currentColor" />
                                </svg>
                            </span>
                        </div>
                        <h3 class="about-value__title">{{ __(`pages.about-val-${v.key}-title`) }}</h3>
                        <p class="about-value__desc">{{ __(`pages.about-val-${v.key}-desc`) }}</p>
                    </article>
                </div>
            </div>
        </section>

        <!-- ── TIMELINE ── -->
        <section class="section">
            <div class="shell">
                <div class="section__head reveal">
                    <span class="eyebrow"><span class="eyebrow__diamond"></span>{{ __('pages.about-story-eyebrow') }}</span>
                    <h2 class="section__title">{{ __('pages.about-story-title') }}</h2>
                </div>
                <div class="timeline">
                    <div
                        v-for="(item, i) in [
                            { label: __('pages.about-story-founded-label'), text: __('pages.about-story-founded-text'), icon: 'launch' },
                            { label: __('pages.about-story-growth-label'), text: __('pages.about-story-growth-text'), icon: 'people' },
                            { label: __('pages.about-story-matches-label'), text: __('pages.about-story-matches-text'), icon: 'rings' },
                            { label: __('pages.about-story-today-label'), text: __('pages.about-story-today-text'), icon: 'star' },
                        ]"
                        :key="i"
                        class="timeline__item reveal"
                        :style="{ transitionDelay: i * 100 + 'ms' }"
                    >
                        <div class="timeline__dot">
                            <svg v-if="item.icon === 'launch'" viewBox="0 0 24 24" fill="none">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2Zm-1 14H9V8h2v8Zm4 0h-2V8h2v8Z" fill="currentColor" opacity="0" />
                                <path d="M12 20V12M12 12l4 4M12 12l-4 4M5 12h2M17 12h2M12 5v2" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <svg v-else-if="item.icon === 'people'" viewBox="0 0 24 24" fill="none">
                                <circle cx="9" cy="7" r="3" stroke="currentColor" stroke-width="1.6" />
                                <path d="M3 20c.7-3 3-4.8 6-4.8s5.3 1.8 6 4.8" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" />
                                <circle cx="17" cy="8" r="2.5" stroke="currentColor" stroke-width="1.6" />
                                <path d="M20 20c-.4-2.2-2-3.6-3.8-3.8" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" />
                            </svg>
                            <svg v-else-if="item.icon === 'rings'" viewBox="0 0 24 24" fill="none">
                                <circle cx="9" cy="14" r="5" stroke="currentColor" stroke-width="1.7" />
                                <circle cx="15" cy="14" r="5" stroke="currentColor" stroke-width="1.7" />
                            </svg>
                            <svg v-else viewBox="0 0 24 24" fill="none">
                                <path d="M12 2l2.4 7.4H22l-6.2 4.5 2.4 7.4L12 17l-6.2 4.3 2.4-7.4L2 9.4h7.6L12 2Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round" />
                            </svg>
                        </div>
                        <div class="timeline__body">
                            <strong class="timeline__label">{{ item.label }}</strong>
                            <p class="timeline__text">{{ item.text }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ── CTA ── -->
        <section class="cta">
            <div class="shell cta__inner reveal">
                <div class="cta__glow"></div>
                <h2 class="cta__title">{{ __('pages.about-cta-title') }}</h2>
                <p class="cta__desc">{{ __('pages.about-cta-desc') }}</p>
                <Link :href="route('signup')" class="btn btn--gold btn--lg">{{ __('pages.about-cta-btn') }}</Link>
            </div>
        </section>

        <LandingFooter />
    </div>
</template>

<style scoped>
/* ── About hero ── */
.about-hero {
    position: relative;
    padding: 5rem 0 3rem;
    overflow: hidden;
}
.about-hero__glow {
    position: absolute; border-radius: 50%;
    filter: blur(80px); z-index: 0; pointer-events: none;
}
.about-hero__glow--1 { width: 480px; height: 480px; background: rgba(208, 46, 121, 0.13); top: -100px; inset-inline-end: -80px; }
.about-hero__glow--2 { width: 380px; height: 380px; background: rgba(199, 154, 63, 0.12); bottom: -80px; inset-inline-start: -60px; }
.about-hero__inner {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 3rem;
    align-items: center;
}
.about-hero__title {
    font-size: clamp(2.6rem, 5.5vw, 4rem);
    line-height: 1.06;
    font-weight: 600;
    letter-spacing: -0.01em;
    margin: 0 0 1.4rem;
}
.is-ar .about-hero__title { line-height: 1.3; }
.about-hero__title .line { display: block; }
.about-hero__title .line--accent {
    font-style: italic;
    background: linear-gradient(120deg, var(--rose), var(--gold));
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
}
.is-ar .about-hero__title .line--accent { font-style: normal; }
.about-hero__desc {
    font-size: 1.08rem; line-height: 1.75;
    color: rgba(44, 10, 24, 0.7);
    max-width: 30rem; margin: 0;
}

/* Geometric art */
.about-hero__art { display: flex; justify-content: center; }
.geo-art {
    position: relative;
    width: min(340px, 70vw);
    aspect-ratio: 1;
    display: grid;
    place-items: center;
}
.geo-ring {
    position: absolute;
    border-radius: 50%;
    border-style: solid;
}
.geo-ring--outer {
    inset: 0; border-width: 1px;
    border-color: rgba(199, 154, 63, 0.35);
    animation: spin 40s linear infinite;
}
.geo-ring--mid {
    inset: 12%; border-width: 1px;
    border-color: rgba(208, 46, 121, 0.3);
    animation: spin 28s linear infinite reverse;
}
.geo-ring--inner {
    inset: 26%; border-width: 1.5px;
    border-color: rgba(199, 154, 63, 0.5);
    animation: spin 18s linear infinite;
}
.geo-star {
    position: absolute; inset: 0;
    width: 100%; height: 100%;
    color: var(--rose);
}
.geo-center {
    position: relative; z-index: 2;
    width: 80px; height: 56px;
    color: var(--rose);
    filter: drop-shadow(0 6px 16px rgba(208,46,121,0.35));
}
.geo-center svg { width: 100%; height: 100%; }
@keyframes spin { to { transform: rotate(360deg); } }

/* ── Mission ── */
.mission-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 4rem;
    align-items: start;
}
.mission__body {
    font-size: 1.05rem; line-height: 1.78;
    color: rgba(44, 10, 24, 0.7);
    margin: 0 0 1.2rem;
}
.mission__divider {
    width: 56px; height: 3px;
    background: linear-gradient(90deg, var(--rose), var(--gold));
    margin: 2rem 0;
}
.mission__quote {
    position: relative;
    background: linear-gradient(135deg, var(--blush), rgba(252,232,243,0.4));
    border: 1px solid var(--line);
    border-radius: 1.2rem;
    padding: 2.2rem 1.8rem 1.8rem;
    margin-bottom: 1.5rem;
}
.mission__quote-mark {
    position: absolute;
    top: -0.2rem; inset-inline-start: 1.4rem;
    font-family: 'Cormorant Garamond', serif;
    font-size: 5rem; line-height: 1;
    color: var(--rose); opacity: 0.25;
    pointer-events: none;
    font-style: italic;
}
.mission__quote p {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.22rem; line-height: 1.65;
    color: var(--plum); font-style: italic;
    margin: 0 0 0.9rem;
}
.is-ar .mission__quote p { font-family: 'Cairo', serif; font-style: normal; font-size: 1.1rem; }
.mission__quote cite {
    font-size: 0.82rem; font-weight: 700;
    color: var(--rose); font-style: normal;
    letter-spacing: 0.06em;
}
.is-ar .mission__quote cite { letter-spacing: 0; }
.mission__stats {
    display: grid; grid-template-columns: repeat(3, 1fr);
    gap: 1rem; text-align: center;
}
.mission__stat {
    background: #fff; border: 1px solid var(--line);
    border-radius: 1rem; padding: 1.2rem 0.8rem;
}
.mission__stat strong {
    display: block;
    font-family: 'Cormorant Garamond', serif;
    font-size: 2rem; font-weight: 700;
    color: var(--plum); line-height: 1;
    margin-bottom: 0.3rem;
}
.mission__stat span { font-size: 0.78rem; font-weight: 600; color: rgba(44,10,24,0.6); }

/* ── About values (plum section) ── */
.about-values {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1.2rem;
}
.about-value {
    background: rgba(255,255,255,0.06);
    border: 1px solid rgba(255,255,255,0.12);
    border-radius: 1.1rem;
    padding: 1.8rem 1.4rem;
    transition: transform 0.3s ease, background 0.3s ease;
}
.about-value:hover { transform: translateY(-4px); background: rgba(255,255,255,0.1); }
.about-value__head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.1rem; }
.about-value__num {
    font-family: 'Cormorant Garamond', serif;
    font-size: 2rem; font-weight: 700;
    color: rgba(230,200,120,0.25); line-height: 1;
}
.about-value__icon {
    width: 44px; height: 44px;
    display: grid; place-items: center;
    border-radius: 12px;
    background: rgba(230,200,120,0.14);
    color: var(--gold-soft);
}
.about-value__icon svg { width: 22px; height: 22px; }
.about-value__title { font-size: 1.08rem; font-weight: 700; margin: 0 0 0.45rem; color: #fff; }
.about-value__desc { font-size: 0.9rem; line-height: 1.62; color: rgba(255,255,255,0.7); margin: 0; }

/* ── Timeline ── */
.timeline {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 0;
    position: relative;
}
.timeline::before {
    content: '';
    position: absolute;
    top: 28px;
    inset-inline-start: calc(12.5%);
    width: 75%;
    height: 2px;
    background: linear-gradient(90deg, var(--rose), var(--gold));
    opacity: 0.3;
}
.timeline__item {
    display: flex; flex-direction: column; align-items: center;
    text-align: center; padding: 0 1rem;
    position: relative;
}
.timeline__dot {
    width: 56px; height: 56px; border-radius: 50%;
    background: linear-gradient(135deg, var(--blush), rgba(252,232,243,0.6));
    border: 2px solid var(--line);
    display: grid; place-items: center;
    color: var(--rose);
    margin-bottom: 1.3rem;
    position: relative; z-index: 1;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    flex-shrink: 0;
}
.timeline__item:hover .timeline__dot {
    transform: scale(1.1);
    box-shadow: 0 12px 28px -10px rgba(208,46,121,0.4);
}
.timeline__dot svg { width: 22px; height: 22px; }
.timeline__label { font-weight: 700; font-size: 0.95rem; color: var(--plum); display: block; margin-bottom: 0.4rem; }
.timeline__text { font-size: 0.88rem; line-height: 1.6; color: rgba(44,10,24,0.65); margin: 0; }

/* ── Responsive ── */
@media (max-width: 980px) {
    .about-hero__inner { grid-template-columns: 1fr; gap: 2.5rem; }
    .about-hero__art { order: -1; }
    .mission-grid { grid-template-columns: 1fr; gap: 2.5rem; }
    .about-values { grid-template-columns: repeat(2, 1fr); }
    .timeline { grid-template-columns: 1fr; gap: 2rem; }
    .timeline::before { display: none; }
    .timeline__item { flex-direction: row; align-items: flex-start; text-align: start; gap: 1.2rem; }
    .timeline__dot { flex-shrink: 0; margin-bottom: 0; }
}
@media (max-width: 560px) {
    .about-values { grid-template-columns: 1fr; }
    .mission__stats { grid-template-columns: repeat(3, 1fr); }
}
</style>
