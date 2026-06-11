<script setup lang="ts">
import { switchLanguage } from '@/actions/App/Http/Controllers/LanguageController';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { route } from 'ziggy-js';
import { vueLang } from '@erag/lang-sync-inertia';

interface Member {
    id: number;
    username: string;
    age: number | null;
    is_verified: boolean;
    is_online: boolean;
}

interface Stats {
    members: number;
    matches: number;
    countries: number;
    verified: number;
}

const props = defineProps<{
    recentMembers: Member[];
    stats: Stats;
}>();

const { __ } = vueLang();
const page = usePage();
const currentLocale = computed(() => page.props.locale as string);
const isRtl = computed(() => currentLocale.value === 'ar');

const showLangMenu = ref(false);
const scrolled = ref(false);
const mobileNav = ref(false);

function switchLang(lang: string): void {
    showLangMenu.value = false;
    router.visit(switchLanguage.url(lang), { preserveScroll: true });
}

function navTo(id: string): void {
    mobileNav.value = false;
    const el = document.getElementById(id);
    if (el) {
        el.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

// Deterministic warm gradient + initial for each member avatar.
function avatarStyle(member: Member): Record<string, string> {
    let hash = 0;
    for (const ch of member.username) {
        hash = (hash * 31 + ch.charCodeAt(0)) % 360;
    }
    const h1 = hash;
    const h2 = (hash + 38) % 360;
    return {
        background: `linear-gradient(135deg, hsl(${h1} 62% 64%), hsl(${h2} 70% 48%))`,
    };
}

function initial(member: Member): string {
    return (member.username?.trim()?.[0] ?? '•').toUpperCase();
}

// --- Scroll reveal + animated stat counters ---
const statEls = ref<Record<string, number>>({
    members: 0,
    matches: 0,
    countries: 0,
    verified: 0,
});

let observer: IntersectionObserver | null = null;
let onScroll: (() => void) | null = null;

function animateCount(key: keyof Stats, target: number): void {
    const duration = 1600;
    const start = performance.now();
    const step = (now: number): void => {
        const progress = Math.min((now - start) / duration, 1);
        const eased = 1 - Math.pow(1 - progress, 3);
        statEls.value[key] = Math.round(target * eased);
        if (progress < 1) {
            requestAnimationFrame(step);
        }
    };
    requestAnimationFrame(step);
}

onMounted(() => {
    onScroll = () => {
        scrolled.value = window.scrollY > 24;
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();

    observer = new IntersectionObserver(
        (entries) => {
            for (const entry of entries) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    if (entry.target === statsSection.value) {
                        animateCount('members', props.stats.members);
                        animateCount('matches', props.stats.matches);
                        animateCount('countries', props.stats.countries);
                        animateCount('verified', props.stats.verified);
                    }
                    observer?.unobserve(entry.target);
                }
            }
        },
        { threshold: 0.18 },
    );

    document.querySelectorAll('.reveal').forEach((el) => observer?.observe(el));
});

onBeforeUnmount(() => {
    if (onScroll) {
        window.removeEventListener('scroll', onScroll);
    }
    observer?.disconnect();
});

const statsSection = ref<HTMLElement | null>(null);

const features = computed(() => [
    { key: 1, icon: 'profile' },
    { key: 2, icon: 'search' },
    { key: 3, icon: 'chat' },
    { key: 4, icon: 'rings' },
]);

const values = computed(() => [
    { key: 1, icon: 'shield' },
    { key: 2, icon: 'badge' },
    { key: 3, icon: 'heart' },
    { key: 4, icon: 'moon' },
]);
</script>

<template>
    <Head>
        <link rel="preconnect" href="https://fonts.bunny.net" />
        <link
            href="https://fonts.bunny.net/css?family=cormorant-garamond:400,500,600,600i,700"
            rel="stylesheet"
        />
        <title>{{ __('landing.hero-title-1') }} — Khotobah</title>
    </Head>

    <div class="landing" :dir="isRtl ? 'rtl' : 'ltr'" :class="{ 'is-ar': isRtl }">

        <!-- ============ NAV ============ -->
        <header class="nav" :class="{ scrolled }">
            <div class="shell nav__inner">
                <a class="brand" href="#top" @click.prevent="navTo('top')">
                    <span class="brand__mark" aria-hidden="true">
                        <svg viewBox="0 0 40 28" fill="none">
                            <circle cx="14" cy="14" r="9" stroke="currentColor" stroke-width="2.2" />
                            <circle cx="26" cy="14" r="9" stroke="currentColor" stroke-width="2.2" opacity="0.55" />
                        </svg>
                    </span>
                    <span class="brand__text">
                        <span class="brand__name">{{ isRtl ? 'خطوبة' : 'Khotobah' }}</span>
                        <span class="brand__sub">{{ isRtl ? 'Khotobah' : 'خطوبة' }}</span>
                    </span>
                </a>

                <nav class="nav__links">
                    <a href="#features" @click.prevent="navTo('features')">{{ __('landing.nav-features') }}</a>
                    <a href="#values" @click.prevent="navTo('values')">{{ __('landing.nav-values') }}</a>
                    <a href="#members" @click.prevent="navTo('members')">{{ __('landing.nav-members') }}</a>
                    <a href="#mobile-app" @click.prevent="navTo('mobile-app')">{{ __('landing.nav-app') }}</a>
                </nav>

                <div class="nav__actions">
                    <div class="lang">
                        <button type="button" class="lang__toggle" @click="showLangMenu = !showLangMenu">
                            <img
                                :src="isRtl ? '/assets/media/flags/saudi-arabia.svg' : '/assets/media/flags/united-states.svg'"
                                alt=""
                            />
                            <span>{{ isRtl ? 'العربية' : 'EN' }}</span>
                        </button>
                        <transition name="fade">
                            <div v-if="showLangMenu" class="lang__menu">
                                <button type="button" :class="{ active: !isRtl }" @click="switchLang('en')">
                                    <img src="/assets/media/flags/united-states.svg" alt="" /> English
                                </button>
                                <button type="button" :class="{ active: isRtl }" @click="switchLang('ar')">
                                    <img src="/assets/media/flags/saudi-arabia.svg" alt="" /> العربية
                                </button>
                            </div>
                        </transition>
                    </div>

                    <Link :href="route('login')" class="btn btn--ghost">{{ __('landing.nav-login') }}</Link>
                    <Link :href="route('signup')" class="btn btn--solid">{{ __('landing.nav-signup') }}</Link>

                    <button class="burger" type="button" @click="mobileNav = !mobileNav" aria-label="Menu">
                        <span></span><span></span><span></span>
                    </button>
                </div>
            </div>

            <transition name="slide">
                <div v-if="mobileNav" class="nav__mobile">
                    <a href="#features" @click.prevent="navTo('features')">{{ __('landing.nav-features') }}</a>
                    <a href="#values" @click.prevent="navTo('values')">{{ __('landing.nav-values') }}</a>
                    <a href="#members" @click.prevent="navTo('members')">{{ __('landing.nav-members') }}</a>
                    <a href="#mobile-app" @click.prevent="navTo('mobile-app')">{{ __('landing.nav-app') }}</a>
                    <div class="nav__mobile-actions">
                        <Link :href="route('login')" class="btn btn--ghost">{{ __('landing.nav-login') }}</Link>
                        <Link :href="route('signup')" class="btn btn--solid">{{ __('landing.nav-signup') }}</Link>
                    </div>
                </div>
            </transition>
        </header>

        <!-- ============ HERO ============ -->
        <section id="top" class="hero">
            <div class="hero__glow hero__glow--1"></div>
            <div class="hero__glow hero__glow--2"></div>

            <div class="shell hero__grid">
                <div class="hero__copy">
                    <span class="eyebrow reveal">
                        <span class="eyebrow__diamond"></span>{{ __('landing.hero-eyebrow') }}
                    </span>
                    <h1 class="hero__title reveal">
                        <span class="line">{{ __('landing.hero-title-1') }}</span>
                        <span class="line line--accent">{{ __('landing.hero-title-2') }}</span>
                    </h1>
                    <p class="hero__desc reveal">{{ __('landing.hero-desc') }}</p>
                    <div class="hero__cta reveal">
                        <Link :href="route('signup')" class="btn btn--solid btn--lg">
                            {{ __('landing.hero-cta-primary') }}
                            <svg class="btn__arrow" viewBox="0 0 24 24" fill="none">
                                <path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </Link>
                        <Link :href="route('login')" class="btn btn--outline btn--lg">{{ __('landing.hero-cta-secondary') }}</Link>
                    </div>
                    <div class="hero__trust reveal">
                        <div class="hero__avatars">
                            <span v-for="n in 4" :key="n" class="hero__avatar" :style="{ background: `linear-gradient(135deg, hsl(${320 + n * 14} 60% 62%), hsl(${280 + n * 18} 55% 50%))` }"></span>
                        </div>
                        <span>{{ __('landing.hero-trust') }}</span>
                    </div>
                </div>

                <div class="hero__art reveal">
                    <div class="ring-art">
                        <div class="ring ring--a"></div>
                        <div class="ring ring--b"></div>
                        <div class="ring__heart">
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 21s-7.5-4.7-10-9.3C.4 8.4 1.8 4.7 5.2 4.2 7.4 3.9 9 5.2 12 8c3-2.8 4.6-4.1 6.8-3.8 3.4.5 4.8 4.2 3.2 7.5C19.5 16.3 12 21 12 21z" />
                            </svg>
                        </div>
                        <div class="float-card float-card--1">
                            <span class="dot"></span>
                            <div>
                                <strong>1,200+</strong>
                                <small>{{ __('landing.members-online-now') }}</small>
                            </div>
                        </div>
                        <div class="float-card float-card--2">
                            <span class="float-card__ico">
                                <svg viewBox="0 0 24 24" fill="none"><path d="M20 6 9 17l-5-5" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </span>
                            <div>
                                <strong>{{ stats.verified }}%</strong>
                                <small>{{ __('landing.stat-verified') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="arabesque" aria-hidden="true"></div>
        </section>

        <!-- ============ STATS ============ -->
        <section ref="statsSection" class="stats reveal">
            <div class="shell stats__grid">
                <div class="stat">
                    <span class="stat__num">{{ statEls.members.toLocaleString() }}<i>+</i></span>
                    <span class="stat__label">{{ __('landing.stat-members') }}</span>
                </div>
                <div class="stat">
                    <span class="stat__num">{{ statEls.matches.toLocaleString() }}<i>+</i></span>
                    <span class="stat__label">{{ __('landing.stat-matches') }}</span>
                </div>
                <div class="stat">
                    <span class="stat__num">{{ statEls.countries }}<i>+</i></span>
                    <span class="stat__label">{{ __('landing.stat-countries') }}</span>
                </div>
                <div class="stat">
                    <span class="stat__num">{{ statEls.verified }}<i>%</i></span>
                    <span class="stat__label">{{ __('landing.stat-verified') }}</span>
                </div>
            </div>
        </section>

        <!-- ============ FEATURES ============ -->
        <section id="features" class="section">
            <div class="shell">
                <div class="section__head reveal">
                    <span class="eyebrow"><span class="eyebrow__diamond"></span>{{ __('landing.features-eyebrow') }}</span>
                    <h2 class="section__title">{{ __('landing.features-title') }}</h2>
                    <p class="section__desc">{{ __('landing.features-desc') }}</p>
                </div>

                <div class="features">
                    <article
                        v-for="(f, i) in features"
                        :key="f.key"
                        class="feature reveal"
                        :style="{ transitionDelay: i * 90 + 'ms' }"
                    >
                        <span class="feature__step">{{ f.key }}</span>
                        <span class="feature__icon">
                            <!-- profile -->
                            <svg v-if="f.icon === 'profile'" viewBox="0 0 24 24" fill="none">
                                <circle cx="12" cy="8" r="3.6" stroke="currentColor" stroke-width="1.7" />
                                <path d="M5 20c.8-3.6 3.6-5.6 7-5.6s6.2 2 7 5.6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" />
                            </svg>
                            <!-- search -->
                            <svg v-else-if="f.icon === 'search'" viewBox="0 0 24 24" fill="none">
                                <circle cx="11" cy="11" r="6.5" stroke="currentColor" stroke-width="1.7" />
                                <path d="m16 16 4.5 4.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" />
                                <path d="M11 8.5v5M8.5 11h5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" />
                            </svg>
                            <!-- chat -->
                            <svg v-else-if="f.icon === 'chat'" viewBox="0 0 24 24" fill="none">
                                <path d="M4 6.5A2.5 2.5 0 0 1 6.5 4h11A2.5 2.5 0 0 1 20 6.5v7A2.5 2.5 0 0 1 17.5 16H9l-4 4v-3.5" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round" />
                                <path d="M8.5 10h7M8.5 12.6h4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                            </svg>
                            <!-- rings -->
                            <svg v-else viewBox="0 0 24 24" fill="none">
                                <circle cx="9" cy="14" r="5.4" stroke="currentColor" stroke-width="1.7" />
                                <circle cx="15" cy="14" r="5.4" stroke="currentColor" stroke-width="1.7" />
                                <path d="m9 4 1.6 2.4L9 8.6 7.4 6.4 9 4Z" fill="currentColor" />
                            </svg>
                        </span>
                        <h3 class="feature__title">{{ __(`landing.feature-${f.key}-title`) }}</h3>
                        <p class="feature__desc">{{ __(`landing.feature-${f.key}-desc`) }}</p>
                    </article>
                </div>
            </div>
        </section>

        <!-- ============ VALUES ============ -->
        <section id="values" class="section section--plum">
            <div class="shell values__grid">
                <div class="values__intro reveal">
                    <span class="eyebrow eyebrow--light"><span class="eyebrow__diamond"></span>{{ __('landing.values-eyebrow') }}</span>
                    <h2 class="section__title section__title--light">{{ __('landing.values-title') }}</h2>
                    <div class="values__divider"></div>
                </div>

                <div class="values__list">
                    <article
                        v-for="(v, i) in values"
                        :key="v.key"
                        class="value reveal"
                        :style="{ transitionDelay: i * 80 + 'ms' }"
                    >
                        <span class="value__icon">
                            <svg v-if="v.icon === 'shield'" viewBox="0 0 24 24" fill="none">
                                <path d="M12 3 5 6v5c0 4.4 3 7.7 7 9 4-1.3 7-4.6 7-9V6l-7-3Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round" />
                                <path d="m9 12 2 2 4-4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <svg v-else-if="v.icon === 'badge'" viewBox="0 0 24 24" fill="none">
                                <path d="m12 3 2.3 1.6 2.8-.2 1 2.6 2.3 1.6-.7 2.7.7 2.7-2.3 1.6-1 2.6-2.8-.2L12 21l-2.3-1.6-2.8.2-1-2.6L3.6 15.4 4.3 12.7 3.6 10l2.3-1.6 1-2.6 2.8.2L12 3Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round" />
                                <path d="m9.5 12 1.8 1.8L15 10" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <svg v-else-if="v.icon === 'heart'" viewBox="0 0 24 24" fill="none">
                                <path d="M12 20s-6.5-4-8.5-8C2.2 9.3 3.4 6.4 6.2 6c1.9-.3 3.2.7 5.8 3 2.6-2.3 3.9-3.3 5.8-3 2.8.4 4 3.3 2.7 6-2 4-8.5 8-8.5 8Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round" />
                            </svg>
                            <svg v-else viewBox="0 0 24 24" fill="none">
                                <path d="M16.5 15.5A7 7 0 1 1 9 4.2a5.6 5.6 0 0 0 7.5 11.3Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round" />
                                <path d="m18 5 .7 1.8L20.5 7.5l-1.8.7L18 10l-.7-1.8L15.5 7.5l1.8-.7L18 5Z" fill="currentColor" />
                            </svg>
                        </span>
                        <div>
                            <h3 class="value__title">{{ __(`landing.value-${v.key}-title`) }}</h3>
                            <p class="value__desc">{{ __(`landing.value-${v.key}-desc`) }}</p>
                        </div>
                    </article>
                </div>
            </div>
        </section>

        <!-- ============ MEMBERS ONLINE ============ -->
        <section id="members" class="section">
            <div class="shell">
                <div class="section__head reveal">
                    <span class="eyebrow"><span class="eyebrow__diamond"></span>{{ __('landing.members-eyebrow') }}</span>
                    <h2 class="section__title">{{ __('landing.members-title') }}</h2>
                    <p class="section__desc">{{ __('landing.members-desc') }}</p>
                </div>

                <div v-if="recentMembers.length" class="members">
                    <article
                        v-for="(m, i) in recentMembers"
                        :key="m.id"
                        class="member reveal"
                        :style="{ transitionDelay: i * 60 + 'ms' }"
                    >
                        <div class="member__avatar" :style="avatarStyle(m)">
                            <span>{{ initial(m) }}</span>
                            <span v-if="m.is_online" class="member__online" :title="__('landing.members-online-now')"></span>
                        </div>
                        <div class="member__name">
                            <span>{{ m.username }}</span>
                            <svg v-if="m.is_verified" class="member__verified" viewBox="0 0 24 24" fill="none">
                                <circle cx="12" cy="12" r="10" fill="currentColor" />
                                <path d="m8 12.5 2.8 2.8 5.2-5.6" stroke="#fff" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </div>
                        <span v-if="m.age" class="member__age">{{ __('landing.members-years', { age: m.age }) }}</span>
                        <Link :href="route('signup')" class="member__join">{{ __('landing.members-join') }}</Link>
                    </article>
                </div>

                <p v-else class="members__empty reveal">{{ __('landing.members-empty') }}</p>

                <div class="members__cta reveal">
                    <Link :href="route('signup')" class="btn btn--solid btn--lg">{{ __('landing.members-cta') }}</Link>
                </div>
            </div>
        </section>

        <!-- ============ MOBILE APP ============ -->
        <section id="mobile-app" class="section app">
            <div class="shell app__grid">
                <div class="app__copy reveal">
                    <span class="eyebrow"><span class="eyebrow__diamond"></span>{{ __('landing.app-eyebrow') }}</span>
                    <h2 class="section__title">{{ __('landing.app-title') }}</h2>
                    <p class="section__desc">{{ __('landing.app-desc') }}</p>
                    <ul class="app__list">
                        <li v-for="n in 3" :key="n">
                            <span class="app__check">
                                <svg viewBox="0 0 24 24" fill="none"><path d="M20 6 9 17l-5-5" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </span>
                            {{ __(`landing.app-feature-${n}`) }}
                        </li>
                    </ul>
                    <div class="app__stores">
                        <a class="store" href="#" @click.prevent>
                            <svg class="store__ico" viewBox="0 0 24 24" fill="currentColor"><path d="M16.5 1.6c.1 1-.3 2-.9 2.8-.7.8-1.7 1.4-2.7 1.3-.1-1 .4-2 .9-2.7.7-.8 1.8-1.3 2.7-1.4Zm3 16.4c-.5 1.2-.8 1.7-1.5 2.7-.9 1.4-2.2 3.1-3.9 3.1-1.4 0-1.8-.9-3.7-.9s-2.3.9-3.7.9c-1.6 0-2.8-1.5-3.8-2.9-2.6-3.8-2.9-8.3-1.3-10.7 1.1-1.7 2.9-2.7 4.6-2.7 1.7 0 2.8.9 4.2.9 1.4 0 2.2-.9 4.2-.9 1.5 0 3 .8 4.1 2.2-3.6 2-3 7.1.5 8.3Z"/></svg>
                            <span><small>{{ __('landing.app-store') }}</small><strong>{{ __('landing.app-store-name') }}</strong></span>
                        </a>
                        <a class="store" href="#" @click.prevent>
                            <svg class="store__ico" viewBox="0 0 24 24" fill="currentColor"><path d="M4 2.5 14.5 12 4 21.5c-.3-.2-.5-.6-.5-1V3.5c0-.4.2-.8.5-1Zm11.7 10.6 2.8 1.6c1 .6 1 1.6 0 2.2l-2.8 1.6-2.4-2.5 2.4-2.9Zm-2.4-2.3L5.9 4l9.3 5.3-1.9 1.5Zm0 5.4 1.9 1.5L5.9 20l7.4-3.8Z"/></svg>
                            <span><small>{{ __('landing.app-google') }}</small><strong>{{ __('landing.app-google-name') }}</strong></span>
                        </a>
                    </div>
                </div>

                <div class="app__art reveal">
                    <div class="phone">
                        <div class="phone__notch"></div>
                        <div class="phone__screen">
                            <div class="phone__top">
                                <span class="phone__brand">Khotobah</span>
                                <span class="phone__live"><i></i>{{ __('landing.members-online-now') }}</span>
                            </div>
                            <div class="phone__bubble phone__bubble--in">{{ __('landing.feature-3-title') }}</div>
                            <div class="phone__bubble phone__bubble--out">{{ __('landing.hero-cta-primary') }}</div>
                            <div class="phone__bubble phone__bubble--in phone__bubble--typing"><i></i><i></i><i></i></div>
                            <div class="phone__match">
                                <div class="phone__rings">
                                    <svg viewBox="0 0 24 24" fill="none"><circle cx="9" cy="13" r="5" stroke="currentColor" stroke-width="1.8"/><circle cx="15" cy="13" r="5" stroke="currentColor" stroke-width="1.8"/></svg>
                                </div>
                                <strong>{{ __('landing.feature-4-title') }}</strong>
                            </div>
                        </div>
                    </div>
                    <div class="phone__halo"></div>
                </div>
            </div>
        </section>

        <!-- ============ FINAL CTA ============ -->
        <section class="cta">
            <div class="shell cta__inner reveal">
                <div class="cta__glow"></div>
                <h2 class="cta__title">{{ __('landing.cta-title') }}</h2>
                <p class="cta__desc">{{ __('landing.cta-desc') }}</p>
                <Link :href="route('signup')" class="btn btn--gold btn--lg">{{ __('landing.cta-button') }}</Link>
            </div>
        </section>

        <!-- ============ FOOTER ============ -->
        <footer class="footer">
            <div class="shell footer__grid">
                <div class="footer__brand">
                    <a class="brand brand--light" href="#top" @click.prevent="navTo('top')">
                        <span class="brand__mark" aria-hidden="true">
                            <svg viewBox="0 0 40 28" fill="none">
                                <circle cx="14" cy="14" r="9" stroke="currentColor" stroke-width="2.2" />
                                <circle cx="26" cy="14" r="9" stroke="currentColor" stroke-width="2.2" opacity="0.55" />
                            </svg>
                        </span>
                        <span class="brand__text">
                            <span class="brand__name">{{ isRtl ? 'خطوبة' : 'Khotobah' }}</span>
                            <span class="brand__sub">{{ isRtl ? 'Khotobah' : 'خطوبة' }}</span>
                        </span>
                    </a>
                    <p>{{ __('landing.footer-tagline') }}</p>
                    <div class="footer__lang">
                        <button type="button" :class="{ active: !isRtl }" @click="switchLang('en')">English</button>
                        <span>/</span>
                        <button type="button" :class="{ active: isRtl }" @click="switchLang('ar')">العربية</button>
                    </div>
                </div>

                <div class="footer__col">
                    <h4>{{ __('landing.footer-explore') }}</h4>
                    <a href="#features" @click.prevent="navTo('features')">{{ __('landing.nav-features') }}</a>
                    <a href="#values" @click.prevent="navTo('values')">{{ __('landing.nav-values') }}</a>
                    <a href="#members" @click.prevent="navTo('members')">{{ __('landing.nav-members') }}</a>
                    <a href="#mobile-app" @click.prevent="navTo('mobile-app')">{{ __('landing.nav-app') }}</a>
                </div>

                <div class="footer__col">
                    <h4>{{ __('landing.footer-company') }}</h4>
                    <a href="#" @click.prevent>{{ __('landing.footer-about') }}</a>
                    <a href="#" @click.prevent>{{ __('landing.footer-stories') }}</a>
                    <a href="#" @click.prevent>{{ __('landing.footer-pricing') }}</a>
                </div>

                <div class="footer__col">
                    <h4>{{ __('landing.footer-support') }}</h4>
                    <a href="#" @click.prevent>{{ __('landing.footer-help') }}</a>
                    <a href="#" @click.prevent>{{ __('landing.footer-safety') }}</a>
                    <a href="#" @click.prevent>{{ __('landing.footer-contact') }}</a>
                </div>

                <div class="footer__col footer__col--cta">
                    <Link :href="route('signup')" class="btn btn--solid">{{ __('landing.nav-signup') }}</Link>
                    <Link :href="route('login')" class="btn btn--ghost btn--ghost-light">{{ __('landing.nav-login') }}</Link>
                </div>
            </div>

            <div class="shell footer__bottom">
                <span>© {{ new Date().getFullYear() }} Khotobah · {{ __('landing.footer-rights') }}</span>
                <div class="footer__legal">
                    <a href="#" @click.prevent>{{ __('landing.footer-privacy') }}</a>
                    <a href="#" @click.prevent>{{ __('landing.footer-terms') }}</a>
                </div>
            </div>
        </footer>
    </div>
</template>

<style scoped>
.landing {
    --ink: #2c0a18;
    --plum: #45112a;
    --plum-deep: #2a0a1a;
    --rose: #d02e79;
    --rose-soft: #e85fa0;
    --gold: #c79a3f;
    --gold-soft: #e6c878;
    --cream: #fbf5ef;
    --cream-2: #f5ebe1;
    --blush: #fce8f3;
    --line: rgba(69, 17, 42, 0.12);

    position: relative;
    background: var(--cream);
    color: var(--ink);
    font-family: 'Cairo', sans-serif;
    overflow-x: hidden;
    min-height: 100vh;
}

/* Subtle geometric texture */
.landing::before {
    content: '';
    position: fixed;
    inset: 0;
    background-image:
        radial-gradient(circle at 1px 1px, rgba(69, 17, 42, 0.05) 1px, transparent 0);
    background-size: 26px 26px;
    pointer-events: none;
    z-index: 0;
    opacity: 0.7;
}

.shell {
    width: 100%;
    max-width: 1180px;
    margin-inline: auto;
    padding-inline: 1.5rem;
    position: relative;
    z-index: 1;
}

.display,
.hero__title,
.section__title,
.cta__title,
.stat__num,
.brand__name {
    font-family: 'Cormorant Garamond', 'Cairo', serif;
}

.is-ar .hero__title,
.is-ar .section__title,
.is-ar .cta__title,
.is-ar .brand__name {
    font-family: 'Cairo', serif;
    font-weight: 700;
}

/* ===== Reveal ===== */
.reveal {
    opacity: 0;
    transform: translateY(26px);
    transition: opacity 0.7s ease, transform 0.7s cubic-bezier(0.22, 1, 0.36, 1);
}
.reveal.is-visible {
    opacity: 1;
    transform: none;
}
@media (prefers-reduced-motion: reduce) {
    .reveal { opacity: 1; transform: none; transition: none; }
}

/* ===== Eyebrow ===== */
.eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 0.55rem;
    font-size: 0.78rem;
    font-weight: 700;
    letter-spacing: 0.16em;
    text-transform: uppercase;
    color: var(--rose);
    margin-bottom: 1.1rem;
}
.is-ar .eyebrow { letter-spacing: 0; }
.eyebrow--light { color: var(--gold-soft); }
.eyebrow__diamond {
    width: 7px;
    height: 7px;
    rotate: 45deg;
    background: currentColor;
    flex-shrink: 0;
}

/* ===== Buttons ===== */
.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    font-family: 'Cairo', sans-serif;
    font-weight: 600;
    font-size: 0.95rem;
    padding: 0.7rem 1.4rem;
    border-radius: 999px;
    border: 1.5px solid transparent;
    cursor: pointer;
    text-decoration: none;
    transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease, color 0.2s ease;
    white-space: nowrap;
}
.btn--lg { padding: 0.95rem 1.9rem; font-size: 1rem; }
.btn--solid {
    background: linear-gradient(135deg, var(--rose), var(--rose-soft));
    color: #fff;
    box-shadow: 0 10px 26px -10px rgba(208, 46, 121, 0.8);
}
.btn--solid:hover { transform: translateY(-2px); box-shadow: 0 16px 32px -10px rgba(208, 46, 121, 0.9); }
.btn--outline {
    background: transparent;
    border-color: var(--line);
    color: var(--plum);
}
.btn--outline:hover { border-color: var(--rose); color: var(--rose); transform: translateY(-2px); }
.btn--ghost { background: transparent; color: var(--plum); border-color: transparent; }
.btn--ghost:hover { color: var(--rose); }
.btn--ghost-light { color: #fff; border: 1.5px solid rgba(255,255,255,0.25); }
.btn--ghost-light:hover { background: rgba(255,255,255,0.08); }
.btn--gold {
    background: linear-gradient(135deg, var(--gold), var(--gold-soft));
    color: var(--plum-deep);
    box-shadow: 0 12px 30px -10px rgba(199, 154, 63, 0.7);
}
.btn--gold:hover { transform: translateY(-2px); box-shadow: 0 18px 38px -10px rgba(199, 154, 63, 0.85); }
.btn__arrow { width: 18px; height: 18px; }
.is-ar .btn__arrow { rotate: 180deg; }

/* ===== HERO ===== */
.hero { position: relative; padding: 5rem 0 5rem; overflow: hidden; }
.hero__glow {
    position: absolute;
    border-radius: 50%;
    filter: blur(80px);
    z-index: 0;
    pointer-events: none;
}
.hero__glow--1 { width: 420px; height: 420px; background: rgba(208, 46, 121, 0.16); top: -80px; inset-inline-end: -60px; }
.hero__glow--2 { width: 360px; height: 360px; background: rgba(199, 154, 63, 0.14); bottom: -100px; inset-inline-start: -80px; }

.hero__grid {
    display: grid;
    grid-template-columns: 1.05fr 0.95fr;
    gap: 3rem;
    align-items: center;
}
.hero__title {
    font-size: clamp(2.6rem, 6vw, 4.4rem);
    line-height: 1.04;
    font-weight: 600;
    letter-spacing: -0.01em;
    margin: 0 0 1.4rem;
}
.is-ar .hero__title { line-height: 1.3; }
.hero__title .line { display: block; }
.hero__title .line--accent {
    font-style: italic;
    background: linear-gradient(120deg, var(--rose), var(--gold));
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
}
.is-ar .hero__title .line--accent { font-style: normal; }
.hero__desc {
    font-size: 1.08rem;
    line-height: 1.75;
    color: rgba(44, 10, 24, 0.72);
    max-width: 30rem;
    margin: 0 0 2rem;
}
.hero__cta { display: flex; flex-wrap: wrap; gap: 0.8rem; margin-bottom: 2.2rem; }
.hero__trust { display: flex; align-items: center; gap: 0.8rem; font-size: 0.88rem; color: rgba(44, 10, 24, 0.6); font-weight: 600; }
.hero__avatars { display: flex; }
.hero__avatar {
    width: 34px; height: 34px;
    border-radius: 50%;
    border: 2.5px solid var(--cream);
    margin-inline-start: -10px;
}
.hero__avatar:first-child { margin-inline-start: 0; }

/* Hero ring art */
.hero__art { display: flex; justify-content: center; }
.ring-art {
    position: relative;
    width: min(420px, 80vw);
    aspect-ratio: 1;
}
.ring {
    position: absolute;
    border-radius: 50%;
    border: 2px solid;
}
.ring--a {
    width: 65%;
    height: 65%;
    top: 5%;
    left: 5%;
    border-color: rgba(208, 46, 121, 0.55);
    box-shadow: inset 0 0 40px rgba(208, 46, 121, 0.12);
    animation: floaty 6s ease-in-out infinite;
}
.ring--b {
    width: 65%;
    height: 65%;
    bottom: 5%;
    right: 5%;
    border-color: rgba(199, 154, 63, 0.6);
    box-shadow: inset 0 0 40px rgba(199, 154, 63, 0.14);
    animation: floaty 6s ease-in-out infinite reverse;
}
.ring__heart {
    position: absolute;
    inset: 0;
    display: grid;
    place-items: center;
    color: var(--rose);
    z-index: 2;
}
.ring__heart svg {
    width: 64px; height: 64px;
    filter: drop-shadow(0 8px 18px rgba(208, 46, 121, 0.4));
    animation: beat 2.6s ease-in-out infinite;
}
@keyframes floaty { 50% { transform: translateY(-12px); } }
@keyframes beat { 0%, 100% { transform: scale(1); } 12% { transform: scale(1.12); } 24% { transform: scale(1); } }

.float-card {
    position: absolute;
    display: flex; align-items: center; gap: 0.6rem;
    background: rgba(255, 255, 255, 0.92);
    backdrop-filter: blur(8px);
    border: 1px solid var(--line);
    border-radius: 0.9rem;
    padding: 0.6rem 0.85rem;
    box-shadow: 0 16px 40px -18px rgba(69, 17, 42, 0.45);
    z-index: 3;
}
.float-card strong { display: block; font-size: 1.05rem; color: var(--plum); line-height: 1; }
.float-card small { font-size: 0.72rem; color: rgba(44,10,24,0.6); }
.float-card--1 { top: 6%; inset-inline-start: -6%; animation: floaty 5s ease-in-out infinite; }
.float-card--2 { bottom: 8%; inset-inline-end: -4%; animation: floaty 5.5s ease-in-out infinite reverse; }
.float-card .dot {
    width: 10px; height: 10px; border-radius: 50%;
    background: #2ecc71;
    box-shadow: 0 0 0 0 rgba(46, 204, 113, 0.5);
    animation: pulse 2s infinite;
}
.float-card__ico {
    width: 30px; height: 30px; border-radius: 50%;
    display: grid; place-items: center;
    background: var(--blush); color: var(--rose);
}
.float-card__ico svg { width: 16px; height: 16px; }
@keyframes pulse { 70% { box-shadow: 0 0 0 8px rgba(46, 204, 113, 0); } 100% { box-shadow: 0 0 0 0 rgba(46, 204, 113, 0); } }

/* Arabesque divider */
.arabesque {
    height: 26px;
    margin-top: 2rem;
    background-image: repeating-linear-gradient(
        90deg,
        transparent 0,
        transparent 14px,
        rgba(199, 154, 63, 0.5) 14px,
        rgba(199, 154, 63, 0.5) 15px
    );
    -webkit-mask-image: radial-gradient(circle at 50% 0, #000 9px, transparent 10px);
    mask-image: radial-gradient(circle at 50% 0, #000 9px, transparent 10px);
    -webkit-mask-size: 30px 26px;
    mask-size: 30px 26px;
    -webkit-mask-repeat: repeat-x;
    mask-repeat: repeat-x;
    opacity: 0.5;
}

/* ===== STATS ===== */
.stats { padding: 2.5rem 0; border-block: 1px solid var(--line); background: rgba(252, 232, 243, 0.35); }
.stats__grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1.5rem;
    text-align: center;
}
.stat { display: flex; flex-direction: column; gap: 0.3rem; position: relative; }
.stat:not(:last-child)::after {
    content: '';
    position: absolute;
    inset-inline-end: 0; top: 15%;
    height: 70%; width: 1px;
    background: var(--line);
}
.stat__num {
    font-size: clamp(2rem, 4vw, 2.9rem);
    font-weight: 700;
    color: var(--plum);
    line-height: 1;
}
.stat__num i { color: var(--rose); font-style: normal; }
.stat__label { font-size: 0.84rem; font-weight: 600; color: rgba(44, 10, 24, 0.6); }

/* ===== SECTIONS ===== */
.section { padding: 5.5rem 0; position: relative; }
.section__head { max-width: 40rem; margin: 0 auto 3.5rem; text-align: center; }
.section__title {
    font-size: clamp(2rem, 4.2vw, 3.1rem);
    font-weight: 600;
    line-height: 1.12;
    margin: 0 0 1rem;
    color: var(--plum);
}
.section__title--light { color: #fff; }
.section__desc { font-size: 1.05rem; line-height: 1.7; color: rgba(44, 10, 24, 0.68); margin: 0; }

/* ===== FEATURES ===== */
.features {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1.2rem;
}
.feature {
    position: relative;
    background: #fff;
    border: 1px solid var(--line);
    border-radius: 1.1rem;
    padding: 2rem 1.4rem 1.7rem;
    overflow: hidden;
    transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease;
}
.feature::before {
    content: '';
    position: absolute;
    top: 0; inset-inline-start: 0;
    width: 100%; height: 3px;
    background: linear-gradient(90deg, var(--rose), var(--gold));
    transform: scaleX(0);
    transform-origin: inline-start;
    transition: transform 0.35s ease;
}
.feature:hover { transform: translateY(-6px); box-shadow: 0 24px 48px -24px rgba(69, 17, 42, 0.4); border-color: transparent; }
.feature:hover::before { transform: scaleX(1); }
.feature__step {
    position: absolute;
    top: 1.1rem; inset-inline-end: 1.2rem;
    font-family: 'Cormorant Garamond', serif;
    font-size: 2.6rem;
    font-weight: 600;
    color: rgba(208, 46, 121, 0.12);
    line-height: 1;
}
.feature__icon {
    display: grid; place-items: center;
    width: 56px; height: 56px;
    border-radius: 16px;
    background: var(--blush);
    color: var(--rose);
    margin-bottom: 1.3rem;
}
.feature__icon svg { width: 28px; height: 28px; }
.feature__title { font-size: 1.18rem; font-weight: 700; margin: 0 0 0.5rem; color: var(--plum); }
.feature__desc { font-size: 0.92rem; line-height: 1.6; color: rgba(44, 10, 24, 0.66); margin: 0; }

/* ===== VALUES (plum) ===== */
.section--plum {
    background:
        radial-gradient(circle at 85% 10%, rgba(208, 46, 121, 0.22), transparent 45%),
        radial-gradient(circle at 10% 90%, rgba(199, 154, 63, 0.16), transparent 45%),
        linear-gradient(160deg, var(--plum), var(--plum-deep));
    color: #fff;
}
.values__grid { display: grid; grid-template-columns: 0.85fr 1.15fr; gap: 3rem; align-items: start; }
.values__intro { position: sticky; top: 100px; }
.values__divider {
    width: 56px; height: 3px;
    background: linear-gradient(90deg, var(--gold-soft), transparent);
    margin-top: 1.6rem;
}
.values__list { display: grid; gap: 1.2rem; }
.value {
    display: flex;
    gap: 1.1rem;
    background: rgba(255, 255, 255, 0.045);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 1rem;
    padding: 1.4rem 1.5rem;
    transition: transform 0.3s ease, background 0.3s ease, border-color 0.3s ease;
}
.value:hover { transform: translateX(0); background: rgba(255, 255, 255, 0.08); border-color: rgba(230, 200, 120, 0.4); }
.value__icon {
    flex-shrink: 0;
    width: 50px; height: 50px;
    display: grid; place-items: center;
    border-radius: 14px;
    background: rgba(230, 200, 120, 0.14);
    color: var(--gold-soft);
}
.value__icon svg { width: 26px; height: 26px; }
.value__title { font-size: 1.12rem; font-weight: 700; margin: 0 0 0.35rem; color: #fff; }
.value__desc { font-size: 0.93rem; line-height: 1.6; color: rgba(255, 255, 255, 0.72); margin: 0; }

/* ===== MEMBERS ===== */
.members {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1.2rem;
}
.member {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    background: #fff;
    border: 1px solid var(--line);
    border-radius: 1.1rem;
    padding: 1.6rem 1rem 1.3rem;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.member:hover { transform: translateY(-5px); box-shadow: 0 22px 44px -22px rgba(69, 17, 42, 0.4); }
.member__avatar {
    position: relative;
    width: 74px; height: 74px;
    border-radius: 50%;
    display: grid; place-items: center;
    color: #fff;
    font-size: 1.7rem;
    font-weight: 700;
    font-family: 'Cormorant Garamond', serif;
    margin-bottom: 0.9rem;
    box-shadow: 0 10px 22px -10px rgba(69, 17, 42, 0.5);
}
.member__online {
    position: absolute;
    bottom: 3px; inset-inline-end: 3px;
    width: 16px; height: 16px;
    border-radius: 50%;
    background: #2ecc71;
    border: 3px solid #fff;
    box-shadow: 0 0 0 0 rgba(46, 204, 113, 0.5);
    animation: pulse 2s infinite;
}
.member__name {
    display: inline-flex; align-items: center; gap: 0.3rem;
    font-weight: 700; color: var(--plum); font-size: 1rem;
}
.member__verified { width: 18px; height: 18px; color: var(--rose); }
.member__age { font-size: 0.82rem; color: rgba(44, 10, 24, 0.55); margin-top: 0.15rem; }
.member__join {
    margin-top: 0.9rem;
    font-size: 0.8rem; font-weight: 700;
    color: var(--rose);
    text-decoration: none;
    padding: 0.35rem 0.95rem;
    border: 1.5px solid var(--blush);
    border-radius: 999px;
    background: var(--blush);
    transition: background 0.2s ease, color 0.2s ease;
}
.member__join:hover { background: var(--rose); color: #fff; }
.members__empty {
    text-align: center;
    font-size: 1.05rem;
    color: rgba(44, 10, 24, 0.6);
    padding: 2rem 0;
}
.members__cta { text-align: center; margin-top: 3rem; }

/* ===== APP ===== */
.app { background: linear-gradient(180deg, var(--cream), var(--cream-2)); }
.app__grid { display: grid; grid-template-columns: 1fr 1fr; gap: 3rem; align-items: center; }
.app__list { list-style: none; padding: 0; margin: 1.8rem 0 2.2rem; display: grid; gap: 0.9rem; }
.app__list li {
    display: flex; align-items: center; gap: 0.7rem;
    font-weight: 600; color: var(--plum); font-size: 1rem;
}
.app__check {
    flex-shrink: 0;
    width: 26px; height: 26px;
    border-radius: 50%;
    display: grid; place-items: center;
    background: linear-gradient(135deg, var(--rose), var(--rose-soft));
    color: #fff;
}
.app__check svg { width: 14px; height: 14px; }
.app__stores { display: flex; flex-wrap: wrap; gap: 0.8rem; }
.store {
    display: inline-flex; align-items: center; gap: 0.7rem;
    background: var(--plum-deep);
    color: #fff;
    padding: 0.65rem 1.2rem;
    border-radius: 0.85rem;
    text-decoration: none;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.store:hover { transform: translateY(-2px); box-shadow: 0 14px 30px -14px rgba(42, 10, 26, 0.7); }
.store__ico { width: 26px; height: 26px; }
.store span { display: flex; flex-direction: column; line-height: 1.1; }
.store small { font-size: 0.66rem; opacity: 0.75; }
.store strong { font-size: 1rem; }

/* Phone mockup */
.app__art { display: flex; justify-content: center; position: relative; }
.phone {
    position: relative;
    width: 280px;
    aspect-ratio: 280 / 560;
    background: #1a0712;
    border-radius: 40px;
    padding: 12px;
    box-shadow: 0 40px 80px -30px rgba(42, 10, 26, 0.7), inset 0 0 0 2px rgba(255,255,255,0.06);
    z-index: 2;
    animation: floaty 7s ease-in-out infinite;
}
.phone__notch {
    position: absolute;
    top: 14px; left: 50%; transform: translateX(-50%);
    width: 110px; height: 22px;
    background: #1a0712;
    border-radius: 0 0 14px 14px;
    z-index: 3;
}
.phone__screen {
    height: 100%;
    border-radius: 30px;
    background: linear-gradient(170deg, #fff, var(--blush));
    padding: 2.4rem 1rem 1rem;
    display: flex;
    flex-direction: column;
    gap: 0.7rem;
    overflow: hidden;
}
.phone__top { display: flex; align-items: center; justify-content: space-between; padding: 0 0.3rem 0.4rem; border-bottom: 1px solid var(--line); }
.phone__brand { font-family: 'Cormorant Garamond', serif; font-weight: 700; font-size: 1.1rem; color: var(--plum); }
.phone__live { display: inline-flex; align-items: center; gap: 0.35rem; font-size: 0.66rem; font-weight: 700; color: #2ecc71; }
.phone__live i { width: 7px; height: 7px; border-radius: 50%; background: #2ecc71; }
.phone__bubble {
    max-width: 80%;
    padding: 0.55rem 0.85rem;
    border-radius: 14px;
    font-size: 0.8rem;
    line-height: 1.35;
    animation: rise 0.6s ease both;
}
.phone__bubble--in { align-self: flex-start; background: #fff; color: var(--plum); border: 1px solid var(--line); border-bottom-inline-start-radius: 4px; }
.phone__bubble--out { align-self: flex-end; background: linear-gradient(135deg, var(--rose), var(--rose-soft)); color: #fff; border-bottom-inline-end-radius: 4px; animation-delay: 0.2s; }
.phone__bubble--typing { display: inline-flex; gap: 4px; align-items: center; animation-delay: 0.4s; }
.phone__bubble--typing i { width: 6px; height: 6px; border-radius: 50%; background: var(--rose); opacity: 0.5; animation: blink 1.2s infinite; }
.phone__bubble--typing i:nth-child(2) { animation-delay: 0.2s; }
.phone__bubble--typing i:nth-child(3) { animation-delay: 0.4s; }
.phone__match {
    margin-top: auto;
    display: flex; flex-direction: column; align-items: center; gap: 0.4rem;
    padding: 0.9rem;
    border-radius: 16px;
    background: linear-gradient(135deg, rgba(208,46,121,0.1), rgba(199,154,63,0.12));
    border: 1px dashed rgba(208, 46, 121, 0.4);
}
.phone__rings { color: var(--rose); }
.phone__rings svg { width: 38px; height: 24px; }
.phone__match strong { font-size: 0.84rem; color: var(--plum); }
.phone__halo {
    position: absolute;
    width: 320px; height: 320px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(208, 46, 121, 0.22), transparent 65%);
    filter: blur(20px);
    z-index: 1;
}
@keyframes rise { from { opacity: 0; transform: translateY(10px); } }
@keyframes blink { 0%, 60%, 100% { opacity: 0.35; } 30% { opacity: 1; } }

/* ===== FINAL CTA ===== */
.cta { padding: 2rem 0 5rem; }
.cta__inner {
    position: relative;
    text-align: center;
    background: linear-gradient(150deg, var(--plum), var(--plum-deep));
    border-radius: 2rem;
    padding: 4.5rem 2rem;
    overflow: hidden;
    max-width: 1180px;
}
.cta__glow {
    position: absolute;
    inset: -40% 30% auto;
    height: 320px;
    background: radial-gradient(circle, rgba(208, 46, 121, 0.4), transparent 60%);
    filter: blur(40px);
}
.cta__title { position: relative; font-size: clamp(2rem, 4.5vw, 3.2rem); font-weight: 600; color: #fff; margin: 0 0 1rem; }
.cta__desc { position: relative; font-size: 1.1rem; color: rgba(255, 255, 255, 0.78); max-width: 32rem; margin: 0 auto 2rem; line-height: 1.6; }

/* ===== FOOTER ===== */
.footer { background: var(--plum-deep); color: rgba(255, 255, 255, 0.7); padding: 4rem 0 2rem; }
.footer__grid {
    display: grid;
    grid-template-columns: 1.6fr 1fr 1fr 1fr 1.2fr;
    gap: 2.5rem;
    padding-bottom: 3rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}
.footer__brand p { font-size: 0.92rem; line-height: 1.6; margin: 1rem 0 1.2rem; max-width: 17rem; }
.footer__lang { display: flex; align-items: center; gap: 0.6rem; font-size: 0.88rem; }
.footer__lang button { background: none; border: none; cursor: pointer; color: rgba(255,255,255,0.6); font-weight: 600; padding: 0; }
.footer__lang button.active { color: var(--gold-soft); }
.footer__col { display: flex; flex-direction: column; gap: 0.7rem; }
.footer__col h4 { font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.12em; color: var(--gold-soft); margin: 0 0 0.4rem; }
.is-ar .footer__col h4 { letter-spacing: 0; }
.footer__col a { color: rgba(255, 255, 255, 0.7); text-decoration: none; font-size: 0.92rem; transition: color 0.2s ease; }
.footer__col a:hover { color: #fff; }
.footer__col--cta { gap: 0.7rem; }
.footer__col--cta .btn { width: 100%; }
.footer__bottom {
    display: flex; align-items: center; justify-content: space-between;
    padding-top: 1.5rem;
    font-size: 0.84rem;
    flex-wrap: wrap; gap: 1rem;
}
.footer__legal { display: flex; gap: 1.5rem; }
.footer__legal a { color: rgba(255, 255, 255, 0.6); text-decoration: none; }
.footer__legal a:hover { color: #fff; }

/* ===== Transitions ===== */
.fade-enter-active, .fade-leave-active { transition: opacity 0.18s ease, transform 0.18s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; transform: translateY(-6px); }

/* ===== NAV ===== */
.nav {
    position: sticky;
    top: 0;
    z-index: 50;
    transition: background 0.3s ease, box-shadow 0.3s ease, backdrop-filter 0.3s ease;
}
.nav.scrolled {
    background: rgba(251, 245, 239, 0.85);
    backdrop-filter: blur(14px);
    box-shadow: 0 1px 0 var(--line);
}
.nav__inner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    height: 74px;
}
.nav__links { display: flex; gap: 1.8rem; }
.nav__links a {
    font-size: 0.92rem;
    font-weight: 600;
    color: var(--plum);
    text-decoration: none;
    position: relative;
    opacity: 0.85;
    transition: opacity 0.2s ease;
}
.nav__links a::after {
    content: '';
    position: absolute;
    left: 0; bottom: -6px;
    width: 0; height: 2px;
    background: var(--rose);
    transition: width 0.25s ease;
}
.nav__links a:hover { opacity: 1; }
.nav__links a:hover::after { width: 100%; }
.nav__actions { display: flex; align-items: center; gap: 0.7rem; }

.lang { position: relative; }
.lang__toggle {
    display: inline-flex; align-items: center; gap: 0.4rem;
    background: rgba(255, 255, 255, 0.6);
    border: 1px solid var(--line);
    border-radius: 999px;
    padding: 0.4rem 0.75rem;
    font-size: 0.82rem; font-weight: 700; color: var(--plum);
    cursor: pointer;
}
.lang__toggle img { width: 16px; height: 16px; border-radius: 3px; }
.lang__menu {
    position: absolute;
    top: calc(100% + 8px);
    inset-inline-end: 0;
    background: #fff;
    border: 1px solid var(--line);
    border-radius: 0.75rem;
    box-shadow: 0 18px 40px -18px rgba(69, 17, 42, 0.4);
    padding: 0.3rem;
    min-width: 160px;
    z-index: 60;
}
.lang__menu button {
    display: flex; align-items: center; gap: 0.55rem;
    width: 100%;
    padding: 0.55rem 0.7rem;
    border: none; background: none; cursor: pointer;
    border-radius: 0.5rem;
    font-size: 0.88rem; font-weight: 600; color: var(--plum);
    text-align: start;
}
.lang__menu button img { width: 18px; height: 18px; border-radius: 3px; }
.lang__menu button:hover { background: var(--cream-2); }
.lang__menu button.active { color: var(--rose); background: var(--blush); }

.burger {
    display: none;
    flex-direction: column;
    gap: 4px;
    background: none; border: none; cursor: pointer;
    padding: 6px;
}
.burger span { width: 22px; height: 2px; background: var(--plum); border-radius: 2px; }

.nav__mobile {
    display: none;
    flex-direction: column;
    gap: 0.2rem;
    padding: 0.8rem 1.5rem 1.4rem;
    background: rgba(251, 245, 239, 0.97);
    backdrop-filter: blur(14px);
    border-bottom: 1px solid var(--line);
}
.nav__mobile a {
    padding: 0.7rem 0.4rem;
    font-weight: 600; color: var(--plum); text-decoration: none;
    border-bottom: 1px solid var(--line);
}
.nav__mobile-actions { display: flex; gap: 0.6rem; margin-top: 0.8rem; }
.nav__mobile-actions .btn { flex: 1; }

/* ===== Brand ===== */
.brand { display: inline-flex; align-items: center; gap: 0.6rem; text-decoration: none; color: var(--plum); }
.brand__mark { color: var(--rose); display: inline-flex; }
.brand__mark svg { width: 40px; height: 28px; }
.brand__text { display: flex; flex-direction: column; line-height: 1; }
.brand__name { font-size: 1.4rem; font-weight: 700; letter-spacing: 0.01em; color: var(--plum); }
.brand__sub { font-size: 0.72rem; color: var(--rose); font-weight: 600; margin-top: 2px; }
.brand--light .brand__name { color: #fff; }
.brand--light .brand__sub { color: var(--gold-soft); }
.brand--light .brand__mark { color: var(--gold-soft); }

/* ===== Responsive ===== */
@media (max-width: 980px) {
    .nav__links { display: none; }
    .burger { display: flex; }
    .nav__mobile { display: flex; }
    .nav__actions .btn { display: none; }
    .hero__grid { grid-template-columns: 1fr; gap: 2.5rem; }
    .hero__art { order: -1; }
    .ring-art { width: min(340px, 70vw); }
    .values__grid { grid-template-columns: 1fr; gap: 2rem; }
    .values__intro { position: static; }
    .app__grid { grid-template-columns: 1fr; gap: 2.5rem; }
    .app__copy { order: 2; }
    .features { grid-template-columns: repeat(2, 1fr); }
    .members { grid-template-columns: repeat(2, 1fr); }
    .footer__grid { grid-template-columns: 1fr 1fr; gap: 2rem; }
    .footer__col--cta { grid-column: span 2; flex-direction: row; }
}
@media (max-width: 560px) {
    .stats__grid { grid-template-columns: repeat(2, 1fr); gap: 1.8rem 1rem; }
    .stat:nth-child(2)::after { display: none; }
    .features { grid-template-columns: 1fr; }
    .members { grid-template-columns: repeat(2, 1fr); }
    .footer__grid { grid-template-columns: 1fr; }
    .footer__col--cta { grid-column: auto; flex-direction: column; }
    .float-card--1 { inset-inline-start: 0; }
    .float-card--2 { inset-inline-end: 0; }
    .cta__inner { padding: 3rem 1.4rem; }
}
</style>
