<script setup lang="ts">
import { switchLanguage } from '@/actions/App/Http/Controllers/LanguageController';
import { vueLang } from '@erag/lang-sync-inertia';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { route } from 'ziggy-js';
import LandingFooter from './_Shared/LandingFooter.vue';

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

defineProps<{ recentMembers: Member[]; stats: Stats }>();
const { __ } = vueLang();
const page = usePage();
const isRtl = computed(() => page.props.locale === 'ar');
const showLanguageMenu = ref(false);
const mobileNavigationOpen = ref(false);
const scrolled = ref(false);

const principles = [
    { key: 1, icon: 'lock' },
    { key: 2, icon: 'verified' },
    { key: 3, icon: 'intention' },
    { key: 4, icon: 'values' },
] as const;
const steps = [1, 2, 3, 4] as const;

function switchLang(locale: string): void {
    showLanguageMenu.value = false;
    router.visit(switchLanguage.url(locale), { preserveScroll: true });
}

function navigateTo(sectionId: string): void {
    mobileNavigationOpen.value = false;
    document.getElementById(sectionId)?.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function avatarStyle(member: Member): Record<string, string> {
    let hash = 0;
    for (const character of member.username) hash = (hash * 31 + character.charCodeAt(0)) % 5;
    const palettes = [
        ['#7d234c', '#d2829f'],
        ['#825b2c', '#d5ae6d'],
        ['#3f5d54', '#8aafa1'],
        ['#523b67', '#9b82ac'],
        ['#954b45', '#d99783'],
    ];
    return { background: `linear-gradient(145deg, ${palettes[hash][0]}, ${palettes[hash][1]})` };
}

function memberInitial(member: Member): string {
    return (member.username.trim()[0] ?? '•').toUpperCase();
}

let revealObserver: IntersectionObserver | null = null;
const handleScroll = (): void => {
    scrolled.value = window.scrollY > 16;
};
const handleKeydown = (event: KeyboardEvent): void => {
    if (event.key === 'Escape') {
        mobileNavigationOpen.value = false;
        showLanguageMenu.value = false;
    }
};

watch(mobileNavigationOpen, (isOpen) => {
    document.body.style.overflow = isOpen ? 'hidden' : '';
});

onMounted(() => {
    window.addEventListener('scroll', handleScroll, { passive: true });
    window.addEventListener('keydown', handleKeydown);
    handleScroll();
    const elements = document.querySelectorAll('.home-reveal');
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        elements.forEach((element) => element.classList.add('is-visible'));
        return;
    }
    revealObserver = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    revealObserver?.unobserve(entry.target);
                }
            });
        },
        { threshold: 0.12, rootMargin: '0px 0px -40px' },
    );
    elements.forEach((element) => revealObserver?.observe(element));
});

onBeforeUnmount(() => {
    window.removeEventListener('scroll', handleScroll);
    window.removeEventListener('keydown', handleKeydown);
    document.body.style.overflow = '';
    revealObserver?.disconnect();
});
</script>

<template>
    <Head>
        <title>{{ __('landing.meta-title') }}</title>
        <meta name="description" :content="__('landing.meta-description')" />
    </Head>

    <div class="landing home-landing" :class="{ 'is-ar': isRtl }" :dir="isRtl ? 'rtl' : 'ltr'">
        <a class="skip-link" href="#main-content">{{ __('landing.skip-content') }}</a>

        <header class="home-nav" :class="{ 'is-scrolled': scrolled }">
            <div class="home-shell home-nav__inner">
                <a class="home-brand" href="#top" :aria-label="__('landing.home-label')" @click.prevent="navigateTo('top')">
                    <span class="home-brand__mark" aria-hidden="true">
                        <img src="/favicon.svg" alt="" />
                    </span>
                    <span class="home-brand__wordmark">
                        <strong>{{ isRtl ? 'خطوبة' : 'Khotobah' }}</strong>
                        <small>{{ isRtl ? 'Khotobah' : 'خطوبة' }}</small>
                    </span>
                </a>
                <nav class="home-nav__links" :aria-label="__('landing.primary-navigation')">
                    <a href="#values" @click.prevent="navigateTo('values')">{{ __('landing.nav-values') }}</a>
                    <a href="#features" @click.prevent="navigateTo('features')">{{ __('landing.nav-features') }}</a>
                    <a href="#members" @click.prevent="navigateTo('members')">{{ __('landing.nav-members') }}</a>
                </nav>
                <div class="home-nav__actions">
                    <div class="language-picker">
                        <button
                            class="language-picker__button"
                            type="button"
                            :aria-expanded="showLanguageMenu"
                            aria-haspopup="menu"
                            @click="showLanguageMenu = !showLanguageMenu"
                        >
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.6" />
                                <path
                                    d="M3.5 12h17M12 3c2.1 2.4 3.2 5.4 3.2 9S14.1 18.6 12 21c-2.1-2.4-3.2-5.4-3.2-9S9.9 5.4 12 3Z"
                                    stroke="currentColor"
                                    stroke-width="1.6"
                                />
                            </svg>
                            <span>{{ isRtl ? 'ع' : 'EN' }}</span>
                            <svg class="language-picker__chevron" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                                <path d="m4 6 4 4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>
                        <transition name="home-fade">
                            <div v-if="showLanguageMenu" class="language-picker__menu" role="menu">
                                <button type="button" :class="{ active: !isRtl }" role="menuitem" @click="switchLang('en')">English</button>
                                <button type="button" :class="{ active: isRtl }" role="menuitem" @click="switchLang('ar')">العربية</button>
                            </div>
                        </transition>
                    </div>
                    <Link :href="route('login')" class="home-sign-in">{{ __('landing.nav-login') }}</Link>
                    <Link :href="route('signup')" class="home-action home-action--small">{{ __('landing.nav-signup') }}</Link>
                    <button
                        class="menu-button"
                        type="button"
                        :aria-expanded="mobileNavigationOpen"
                        :aria-label="__('landing.menu-label')"
                        @click="mobileNavigationOpen = true"
                    >
                        <span></span><span></span>
                    </button>
                </div>
            </div>
        </header>

        <transition name="home-fade"
            ><div v-if="mobileNavigationOpen" class="mobile-menu__backdrop" @click="mobileNavigationOpen = false"></div
        ></transition>
        <aside class="mobile-menu" :class="{ 'is-open': mobileNavigationOpen }" :aria-hidden="!mobileNavigationOpen">
            <div class="mobile-menu__top">
                <span>{{ __('landing.menu-label') }}</span>
                <button type="button" :aria-label="__('landing.close-menu')" @click="mobileNavigationOpen = false">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="m6 6 12 12M18 6 6 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                    </svg>
                </button>
            </div>
            <nav>
                <a href="#values" @click.prevent="navigateTo('values')"><small>01</small>{{ __('landing.nav-values') }}</a>
                <a href="#features" @click.prevent="navigateTo('features')"><small>02</small>{{ __('landing.nav-features') }}</a>
                <a href="#members" @click.prevent="navigateTo('members')"><small>03</small>{{ __('landing.nav-members') }}</a>
            </nav>
            <div class="mobile-menu__actions">
                <Link :href="route('login')" class="home-action home-action--outline">{{ __('landing.nav-login') }}</Link>
                <Link :href="route('signup')" class="home-action">{{ __('landing.nav-signup') }}</Link>
            </div>
        </aside>

        <main id="main-content">
            <section id="top" class="home-hero">
                <div class="home-hero__orb home-hero__orb--rose" aria-hidden="true"></div>
                <div class="home-hero__orb home-hero__orb--gold" aria-hidden="true"></div>
                <div class="home-shell home-hero__grid">
                    <div class="home-hero__copy">
                        <div class="home-kicker home-reveal"><span aria-hidden="true"></span>{{ __('landing.hero-eyebrow') }}</div>
                        <h1 class="home-hero__title home-reveal">
                            {{ __('landing.hero-title-1') }}<em>{{ __('landing.hero-title-2') }}</em>
                        </h1>
                        <p class="home-hero__description home-reveal">{{ __('landing.hero-desc-short') }}</p>
                        <div class="home-hero__actions home-reveal">
                            <Link :href="route('signup')" class="home-action home-action--large"
                                >{{ __('landing.hero-cta-primary')
                                }}<svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path
                                        d="M5 12h14M14 7l5 5-5 5"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    /></svg
                            ></Link>
                            <button type="button" class="home-text-link" @click="navigateTo('features')">
                                {{ __('landing.hero-how-it-works') }}<span aria-hidden="true">↓</span>
                            </button>
                        </div>
                        <div class="home-hero__assurances home-reveal" :aria-label="__('landing.hero-assurances')">
                            <span><i aria-hidden="true">✓</i>{{ __('landing.hero-assurance-1') }}</span>
                            <span><i aria-hidden="true">✓</i>{{ __('landing.hero-assurance-2') }}</span>
                            <span><i aria-hidden="true">✓</i>{{ __('landing.hero-assurance-3') }}</span>
                        </div>
                    </div>
                    <div class="home-hero__visual home-reveal" aria-hidden="true">
                        <div class="covenant-card">
                            <div class="covenant-card__topline">
                                <span>{{ __('landing.hero-card-eyebrow') }}</span
                                ><i></i>
                            </div>
                            <div class="covenant-card__seal">
                                <img src="/favicon.svg" alt="" />
                            </div>
                            <blockquote>“{{ __('landing.hero-card-quote') }}”</blockquote>
                            <div class="covenant-card__rule"><span></span><i></i><span></span></div>
                            <p>{{ __('landing.hero-card-note') }}</p>
                        </div>
                        <div class="trust-note trust-note--verified">
                            <span
                                ><svg viewBox="0 0 24 24" fill="none">
                                    <path
                                        d="m12 3 2.3 1.6 2.8-.2 1 2.6 2.3 1.6-.7 2.7.7 2.7-2.3 1.6-1 2.6-2.8-.2L12 21l-2.3-1.6-2.8.2-1-2.6L3.6 15.4l.7-2.7L3.6 10l2.3-1.6 1-2.6 2.8.2L12 3Z"
                                        stroke="currentColor"
                                        stroke-width="1.4"
                                    />
                                    <path
                                        d="m9 12 2 2 4-4"
                                        stroke="currentColor"
                                        stroke-width="1.6"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    /></svg></span
                            ><strong>{{ stats.verified }}%</strong><small>{{ __('landing.stat-verified') }}</small>
                        </div>
                        <div class="trust-note trust-note--privacy">
                            <span
                                ><svg viewBox="0 0 24 24" fill="none">
                                    <rect x="5" y="10" width="14" height="10" rx="2" stroke="currentColor" stroke-width="1.6" />
                                    <path d="M8 10V7a4 4 0 0 1 8 0v3" stroke="currentColor" stroke-width="1.6" /></svg></span
                            ><small>{{ __('landing.hero-private-note') }}</small>
                        </div>
                    </div>
                </div>
                <div class="home-shell home-hero__proof home-reveal">
                    <p>{{ __('landing.proof-intro') }}</p>
                    <dl>
                        <div>
                            <dt>{{ stats.members.toLocaleString() }}+</dt>
                            <dd>{{ __('landing.stat-members') }}</dd>
                        </div>
                        <div>
                            <dt>{{ stats.countries }}+</dt>
                            <dd>{{ __('landing.stat-countries') }}</dd>
                        </div>
                        <div>
                            <dt>{{ stats.verified }}%</dt>
                            <dd>{{ __('landing.stat-verified') }}</dd>
                        </div>
                    </dl>
                </div>
            </section>

            <section id="values" class="home-values">
                <div class="home-shell">
                    <div class="home-section-heading home-section-heading--split home-reveal">
                        <div>
                            <span class="home-kicker"><span aria-hidden="true"></span>{{ __('landing.values-eyebrow') }}</span>
                            <h2>{{ __('landing.values-title-new') }}</h2>
                        </div>
                        <p>{{ __('landing.values-desc') }}</p>
                    </div>
                    <div class="principles">
                        <article
                            v-for="(principle, index) in principles"
                            :key="principle.key"
                            class="principle home-reveal"
                            :style="{ transitionDelay: `${index * 70}ms` }"
                        >
                            <span class="principle__number">0{{ principle.key }}</span>
                            <div class="principle__icon">
                                <svg v-if="principle.icon === 'lock'" viewBox="0 0 24 24" fill="none">
                                    <rect x="4" y="9" width="16" height="11" rx="2" stroke="currentColor" stroke-width="1.6" />
                                    <path d="M8 9V7a4 4 0 0 1 8 0v2M12 13v3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" />
                                </svg>
                                <svg v-else-if="principle.icon === 'verified'" viewBox="0 0 24 24" fill="none">
                                    <path
                                        d="m12 3 2.3 1.6 2.8-.2 1 2.6 2.3 1.6-.7 2.7.7 2.7-2.3 1.6-1 2.6-2.8-.2L12 21l-2.3-1.6-2.8.2-1-2.6L3.6 15.4l.7-2.7L3.6 10l2.3-1.6 1-2.6 2.8.2L12 3Z"
                                        stroke="currentColor"
                                        stroke-width="1.5"
                                    />
                                    <path d="m9 12 2 2 4-4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <svg v-else-if="principle.icon === 'intention'" viewBox="0 0 24 24" fill="none">
                                    <path
                                        d="M12 20s-6.5-4-8.5-8C2.2 9.3 3.4 6.4 6.2 6c1.9-.3 3.2.7 5.8 3 2.6-2.3 3.9-3.3 5.8-3 2.8.4 4 3.3 2.7 6-2 4-8.5 8-8.5 8Z"
                                        stroke="currentColor"
                                        stroke-width="1.6"
                                        stroke-linejoin="round"
                                    />
                                </svg>
                                <svg v-else viewBox="0 0 24 24" fill="none">
                                    <path
                                        d="M12 3v18M5 7h14M7 7c0 3-1.3 5.5-4 7h8c-2.7-1.5-4-4-4-7ZM17 7c0 3-1.3 5.5-4 7h8c-2.7-1.5-4-4-4-7Z"
                                        stroke="currentColor"
                                        stroke-width="1.5"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                </svg>
                            </div>
                            <h3>{{ __(`landing.value-${principle.key}-title`) }}</h3>
                            <p>{{ __(`landing.value-${principle.key}-desc`) }}</p>
                        </article>
                    </div>
                </div>
            </section>

            <section id="features" class="home-process">
                <div class="home-shell home-process__grid">
                    <div class="home-process__intro home-reveal">
                        <span class="home-kicker home-kicker--light"><span aria-hidden="true"></span>{{ __('landing.features-eyebrow') }}</span>
                        <h2>{{ __('landing.features-title') }}</h2>
                        <p>{{ __('landing.features-desc') }}</p>
                        <Link :href="route('signup')" class="home-action home-action--gold">{{ __('landing.cta-button') }}</Link>
                    </div>
                    <ol class="process-list">
                        <li
                            v-for="(step, index) in steps"
                            :key="step"
                            class="process-step home-reveal"
                            :style="{ transitionDelay: `${index * 70}ms` }"
                        >
                            <span class="process-step__index">0{{ step }}</span>
                            <div>
                                <h3>{{ __(`landing.feature-${step}-title`) }}</h3>
                                <p>{{ __(`landing.feature-${step}-desc`) }}</p>
                            </div>
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="m8 5 7 7-7 7" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </li>
                    </ol>
                </div>
            </section>

            <section id="members" class="home-members">
                <div class="home-shell">
                    <div class="home-section-heading home-section-heading--center home-reveal">
                        <span class="home-kicker"><span aria-hidden="true"></span>{{ __('landing.members-eyebrow') }}</span>
                        <h2>{{ __('landing.members-title') }}</h2>
                        <p>{{ __('landing.members-desc') }}</p>
                    </div>
                    <div v-if="recentMembers.length" class="member-list home-reveal">
                        <article v-for="member in recentMembers.slice(0, 6)" :key="member.id" class="member-card">
                            <div class="member-card__avatar" :style="avatarStyle(member)">
                                {{ memberInitial(member) }}<span v-if="member.is_online" :title="__('landing.members-online-now')"></span>
                            </div>
                            <div class="member-card__identity">
                                <strong
                                    >{{ member.username
                                    }}<svg v-if="member.is_verified" viewBox="0 0 24 24" fill="none" :aria-label="__('landing.member-verified')">
                                        <circle cx="12" cy="12" r="10" fill="currentColor" />
                                        <path
                                            d="m8 12.5 2.6 2.6 5.4-5.5"
                                            stroke="#fff"
                                            stroke-width="1.8"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        /></svg></strong
                                ><small>{{ member.age ? __('landing.members-years', { age: member.age }) : __('landing.member-private-age') }}</small>
                            </div>
                            <Link :href="route('signup')" :aria-label="`${__('landing.members-join')} ${member.username}`"
                                ><svg viewBox="0 0 24 24" fill="none">
                                    <path
                                        d="M5 12h14M14 7l5 5-5 5"
                                        stroke="currentColor"
                                        stroke-width="1.7"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    /></svg
                            ></Link>
                        </article>
                    </div>
                    <p v-else class="member-list__empty home-reveal">{{ __('landing.members-empty') }}</p>
                    <div class="home-members__action home-reveal">
                        <Link :href="route('signup')" class="home-action home-action--outline">{{ __('landing.members-cta') }}</Link>
                    </div>
                </div>
            </section>

            <section class="home-cta">
                <div class="home-shell">
                    <div class="home-cta__inner home-reveal">
                        <div class="home-cta__seal" aria-hidden="true">
                            <svg viewBox="0 0 80 80" fill="none">
                                <circle cx="30" cy="40" r="19" stroke="currentColor" stroke-width="1.7" />
                                <circle cx="50" cy="40" r="19" stroke="currentColor" stroke-width="1.7" />
                            </svg>
                        </div>
                        <span class="home-kicker home-kicker--light"><span aria-hidden="true"></span>{{ __('landing.cta-eyebrow') }}</span>
                        <h2>{{ __('landing.cta-title') }}</h2>
                        <p>{{ __('landing.cta-desc') }}</p>
                        <div class="home-cta__actions">
                            <Link :href="route('signup')" class="home-action home-action--gold home-action--large">{{
                                __('landing.cta-button')
                            }}</Link
                            ><Link :href="route('login')" class="home-cta__login">{{ __('landing.hero-cta-secondary') }}</Link>
                        </div>
                    </div>
                </div>
            </section>
        </main>
        <LandingFooter />
    </div>
</template>

<style scoped>
.home-landing {
    --h-ink: #2b111c;
    --h-plum: #461329;
    --h-deep: #240a15;
    --h-rose: #c82d6f;
    --h-rose-dark: #a51f58;
    --h-gold: #c8a45b;
    --h-gold-light: #ead69c;
    --h-cream: #fcf8f2;
    --h-line: rgba(70, 19, 41, 0.14);
    background: var(--h-cream);
    color: var(--h-ink);
    min-height: 100vh;
    overflow: clip;
}
.home-landing::before {
    background-image: radial-gradient(rgba(70, 19, 41, 0.055) 0.65px, transparent 0.65px);
    background-size: 7px 7px;
    opacity: 0.42;
}
.home-shell {
    width: min(100% - 40px, 1180px);
    margin-inline: auto;
    position: relative;
    z-index: 1;
}
.skip-link {
    position: fixed;
    inset-block-start: 10px;
    inset-inline-start: 10px;
    z-index: 100;
    transform: translateY(-160%);
    background: #fff;
    color: var(--h-plum);
    padding: 0.75rem 1rem;
}
.skip-link:focus {
    transform: none;
}
.home-reveal {
    opacity: 0;
    transform: translateY(24px);
    transition:
        opacity 0.75s ease,
        transform 0.75s cubic-bezier(0.2, 0.8, 0.2, 1);
}
.home-reveal.is-visible {
    opacity: 1;
    transform: none;
}
.home-nav {
    position: fixed;
    inset: 0 0 auto;
    z-index: 40;
    transition: 0.25s;
}
.home-nav.is-scrolled {
    background: rgba(252, 248, 242, 0.9);
    box-shadow: 0 1px 0 var(--h-line);
    backdrop-filter: blur(16px);
}
.home-nav__inner {
    height: 82px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 2rem;
}
.home-brand {
    display: inline-flex;
    align-items: center;
    gap: 0.7rem;
    color: var(--h-plum);
    text-decoration: none;
}
.home-brand__mark {
    width: 45px;
    color: var(--h-rose);
    line-height: 0;
}
.home-brand__mark img {
    width: 100%;
    display: block;
}
.home-brand__wordmark {
    display: grid;
    line-height: 1;
}
.home-brand__wordmark strong {
    font-family: 'Cormorant Garamond', 'Cairo', serif;
    font-size: 1.45rem;
}
.home-brand__wordmark small {
    margin-top: 3px;
    color: var(--h-rose);
    font-size: 0.65rem;
    font-weight: 700;
    letter-spacing: 0.08em;
}
.is-ar .home-brand__wordmark strong {
    font-family: 'Cairo', sans-serif;
}
.home-nav__links {
    display: flex;
    gap: 2rem;
}
.home-nav__links a,
.home-sign-in {
    color: var(--h-plum);
    text-decoration: none;
    font-size: 0.88rem;
    font-weight: 700;
}
.home-nav__links a {
    position: relative;
}
.home-nav__links a::after {
    content: '';
    position: absolute;
    inset-inline: 0;
    bottom: -8px;
    height: 1.5px;
    background: var(--h-rose);
    transform: scaleX(0);
    transition: 0.2s;
}
.home-nav__links a:hover::after {
    transform: scaleX(1);
}
.home-nav__actions {
    display: flex;
    align-items: center;
    gap: 1rem;
}
.language-picker {
    position: relative;
}
.language-picker__button {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    border: 0;
    background: transparent;
    color: var(--h-plum);
    font: inherit;
    font-size: 0.76rem;
    font-weight: 800;
    cursor: pointer;
    padding: 0.45rem;
}
.language-picker__button svg {
    width: 17px;
}
.language-picker__button .language-picker__chevron {
    width: 12px;
}
.language-picker__menu {
    position: absolute;
    top: calc(100% + 10px);
    inset-inline-end: 0;
    width: 142px;
    padding: 0.35rem;
    background: #fff;
    border: 1px solid var(--h-line);
    box-shadow: 0 18px 45px rgba(56, 19, 34, 0.12);
}
.language-picker__menu button {
    width: 100%;
    border: 0;
    background: transparent;
    padding: 0.6rem 0.7rem;
    text-align: start;
    color: var(--h-ink);
    font: inherit;
    font-size: 0.82rem;
    cursor: pointer;
}
.language-picker__menu button:hover,
.language-picker__menu button.active {
    background: #faedf3;
    color: var(--h-rose);
}
.home-action {
    min-height: 46px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.7rem;
    padding: 0.72rem 1.35rem;
    background: var(--h-rose);
    border: 1px solid var(--h-rose);
    border-radius: 4px;
    box-shadow: 0 12px 26px -16px rgba(165, 31, 88, 0.85);
    color: #fff;
    font-size: 0.9rem;
    font-weight: 800;
    text-decoration: none;
    transition: 0.2s;
}
.home-action:hover {
    background: var(--h-rose-dark);
    transform: translateY(-2px);
}
.home-action--small {
    min-height: 42px;
    padding: 0.58rem 1.15rem;
    font-size: 0.82rem;
}
.home-action--large {
    min-height: 54px;
    padding-inline: 1.7rem;
}
.home-action--large svg {
    width: 19px;
}
.is-ar .home-action--large svg {
    transform: rotate(180deg);
}
.home-action--outline {
    background: transparent;
    border-color: var(--h-line);
    color: var(--h-plum);
    box-shadow: none;
}
.home-action--outline:hover {
    background: #fff;
    color: var(--h-rose);
    border-color: var(--h-rose);
}
.home-action--gold {
    background: var(--h-gold-light);
    border-color: var(--h-gold-light);
    color: var(--h-deep);
    box-shadow: none;
}
.home-action--gold:hover {
    background: #f3e3b4;
    color: var(--h-deep);
}
.menu-button {
    display: none;
    width: 42px;
    height: 42px;
    place-content: center;
    gap: 6px;
    border: 1px solid var(--h-line);
    background: transparent;
}
.menu-button span {
    display: block;
    width: 18px;
    height: 1.5px;
    background: var(--h-plum);
}
.mobile-menu__backdrop {
    position: fixed;
    inset: 0;
    z-index: 49;
    background: rgba(24, 7, 14, 0.48);
    backdrop-filter: blur(4px);
}
.mobile-menu {
    position: fixed;
    inset-block: 0;
    inset-inline-end: 0;
    z-index: 50;
    width: min(88vw, 380px);
    background: var(--h-cream);
    padding: 1.5rem;
    transform: translateX(105%);
    transition: transform 0.4s cubic-bezier(0.2, 0.8, 0.2, 1);
    display: flex;
    flex-direction: column;
}
.is-ar .mobile-menu {
    transform: translateX(-105%);
}
.mobile-menu.is-open {
    transform: none;
}
.mobile-menu__top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid var(--h-line);
    font-weight: 800;
}
.mobile-menu__top button {
    width: 40px;
    height: 40px;
    display: grid;
    place-items: center;
    border: 1px solid var(--h-line);
    background: transparent;
}
.mobile-menu__top svg {
    width: 19px;
}
.mobile-menu nav {
    margin-top: 1rem;
}
.mobile-menu nav a {
    display: flex;
    gap: 1rem;
    padding: 1.2rem 0.25rem;
    border-bottom: 1px solid var(--h-line);
    color: var(--h-plum);
    font-size: 1.15rem;
    font-weight: 800;
    text-decoration: none;
}
.mobile-menu nav small {
    color: var(--h-rose);
    font-family: 'Cormorant Garamond', serif;
    font-size: 0.75rem;
}
.mobile-menu__actions {
    margin-top: auto;
    display: grid;
    gap: 0.65rem;
}
.home-hero {
    min-height: 100vh;
    position: relative;
    display: flex;
    flex-direction: column;
    justify-content: center;
    padding: 140px 0 38px;
    isolation: isolate;
}
.home-hero::after {
    content: '';
    position: absolute;
    inset: 0;
    z-index: -2;
    background: linear-gradient(90deg, transparent 49.95%, rgba(70, 19, 41, 0.05) 50%, transparent 50.05%);
}
.home-hero__orb {
    position: absolute;
    z-index: -1;
    border-radius: 50%;
}
.home-hero__orb--rose {
    width: 460px;
    height: 460px;
    inset-block-start: 7%;
    inset-inline-end: -150px;
    background: radial-gradient(circle, rgba(200, 45, 111, 0.14), transparent 68%);
}
.home-hero__orb--gold {
    width: 380px;
    height: 380px;
    inset-block-end: 0;
    inset-inline-start: -160px;
    background: radial-gradient(circle, rgba(200, 164, 91, 0.15), transparent 68%);
}
.home-hero__grid {
    display: grid;
    grid-template-columns: minmax(0, 1.08fr) minmax(380px, 0.92fr);
    align-items: center;
    gap: clamp(3rem, 8vw, 7rem);
}
.home-kicker {
    display: inline-flex;
    align-items: center;
    gap: 0.65rem;
    color: var(--h-rose);
    font-size: 0.72rem;
    font-weight: 800;
    letter-spacing: 0.16em;
    text-transform: uppercase;
}
.is-ar .home-kicker {
    letter-spacing: 0;
}
.home-kicker > span {
    width: 26px;
    height: 1px;
    background: currentColor;
}
.home-kicker--light {
    color: var(--h-gold-light);
}
.home-hero__title {
    max-width: 720px;
    margin: 1.25rem 0 1.35rem;
    color: var(--h-plum);
    font-family: 'Cormorant Garamond', 'Cairo', serif;
    font-size: clamp(3.2rem, 6.8vw, 6rem);
    font-weight: 600;
    letter-spacing: -0.035em;
    line-height: 0.9;
}
.home-hero__title em {
    display: block;
    color: var(--h-rose);
    font-weight: 500;
}
.is-ar .home-hero__title {
    font-family: 'Cairo';
    font-size: clamp(2.65rem, 5.8vw, 5rem);
    line-height: 1.2;
}
.home-hero__title.home-reveal {
    transition-delay: 0.08s;
}
.home-hero__description {
    max-width: 570px;
    margin: 0 0 1.9rem;
    color: rgba(43, 17, 28, 0.7);
    font-size: 1.02rem;
    line-height: 1.9;
}
.home-hero__actions {
    display: flex;
    align-items: center;
    gap: 1.4rem;
}
.home-text-link {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    border: 0;
    background: transparent;
    color: var(--h-plum);
    font: inherit;
    font-size: 0.86rem;
    font-weight: 800;
    cursor: pointer;
}
.home-text-link span {
    color: var(--h-rose);
}
.home-hero__assurances {
    display: flex;
    flex-wrap: wrap;
    gap: 0.65rem 1.1rem;
    margin-top: 1.7rem;
}
.home-hero__assurances span {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    color: rgba(43, 17, 28, 0.63);
    font-size: 0.74rem;
    font-weight: 700;
}
.home-hero__assurances i {
    width: 18px;
    height: 18px;
    display: grid;
    place-items: center;
    border: 1px solid rgba(200, 45, 111, 0.25);
    border-radius: 50%;
    color: var(--h-rose);
    font-style: normal;
    font-size: 0.65rem;
}
.home-hero__visual {
    position: relative;
    min-height: 510px;
    display: grid;
    place-items: center;
}
.covenant-card {
    width: min(100%, 385px);
    min-height: 480px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 3.1rem 2.6rem;
    background: linear-gradient(145deg, rgba(255, 255, 255, 0.94), rgba(247, 237, 224, 0.9));
    border: 1px solid rgba(70, 19, 41, 0.13);
    box-shadow: 0 32px 80px -44px rgba(44, 10, 24, 0.55);
    position: relative;
    text-align: center;
}
.covenant-card::before,
.covenant-card::after {
    content: '';
    position: absolute;
    width: 54px;
    height: 54px;
    border-color: rgba(200, 164, 91, 0.55);
}
.covenant-card::before {
    inset: 15px auto auto 15px;
    border-top: 1px solid;
    border-left: 1px solid;
}
.covenant-card::after {
    inset: auto 15px 15px auto;
    border-right: 1px solid;
    border-bottom: 1px solid;
}
.covenant-card__topline {
    position: absolute;
    inset: 1.5rem 1.7rem auto;
    display: flex;
    justify-content: space-between;
    color: var(--h-gold);
    font-size: 0.65rem;
    font-weight: 800;
    letter-spacing: 0.14em;
    text-transform: uppercase;
}
.covenant-card__topline i {
    width: 6px;
    height: 6px;
    background: currentColor;
    transform: rotate(45deg);
}
.covenant-card__seal {
    width: 148px;
}
.covenant-card__seal img {
    display: block;
    width: 100%;
    height: auto;
}
.covenant-card blockquote {
    margin: 1rem 0 0.9rem;
    color: var(--h-plum);
    font-family: 'Cormorant Garamond', 'Cairo', serif;
    font-size: 1.65rem;
    font-weight: 600;
    line-height: 1.2;
}
.is-ar .covenant-card blockquote {
    font-family: 'Cairo';
    font-size: 1.3rem;
    line-height: 1.7;
}
.covenant-card__rule {
    width: 120px;
    display: flex;
    align-items: center;
    gap: 0.55rem;
    margin: 0.35rem 0 1rem;
}
.covenant-card__rule span {
    height: 1px;
    flex: 1;
    background: rgba(200, 164, 91, 0.65);
}
.covenant-card__rule i {
    width: 5px;
    height: 5px;
    background: var(--h-gold);
    transform: rotate(45deg);
}
.covenant-card p {
    margin: 0;
    color: rgba(43, 17, 28, 0.58);
    font-size: 0.74rem;
    font-weight: 700;
    line-height: 1.7;
}
.trust-note {
    position: absolute;
    display: grid;
    grid-template-columns: auto auto;
    align-items: center;
    gap: 0.05rem 0.65rem;
    padding: 0.8rem 0.9rem;
    background: rgba(255, 255, 255, 0.95);
    border: 1px solid var(--h-line);
    box-shadow: 0 18px 45px -28px rgba(36, 10, 21, 0.55);
}
.trust-note > span {
    grid-row: span 2;
    width: 35px;
    height: 35px;
    display: grid;
    place-items: center;
    background: #f9e7ef;
    color: var(--h-rose);
    border-radius: 50%;
}
.trust-note svg {
    width: 19px;
}
.trust-note strong {
    color: var(--h-plum);
    font-family: 'Cormorant Garamond';
    font-size: 1.15rem;
}
.trust-note small {
    color: rgba(43, 17, 28, 0.58);
    font-size: 0.65rem;
    font-weight: 700;
}
.trust-note--verified {
    inset-block-start: 13%;
    inset-inline-start: -11%;
}
.trust-note--privacy {
    inset-block-end: 11%;
    inset-inline-end: -8%;
    grid-template-columns: auto 1fr;
    max-width: 170px;
}
.home-hero__proof {
    margin-top: 4rem;
    padding-top: 1.4rem;
    border-top: 1px solid var(--h-line);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 2rem;
}
.home-hero__proof > p {
    max-width: 260px;
    margin: 0;
    color: rgba(43, 17, 28, 0.52);
    font-size: 0.78rem;
    font-weight: 700;
    line-height: 1.6;
}
.home-hero__proof dl {
    margin: 0;
    display: flex;
    gap: clamp(1.8rem, 5vw, 4.5rem);
}
.home-hero__proof dl div {
    display: grid;
    gap: 0.2rem;
}
.home-hero__proof dt {
    color: var(--h-plum);
    font-family: 'Cormorant Garamond', 'Cairo';
    font-size: 1.8rem;
    font-weight: 700;
    line-height: 1;
}
.home-hero__proof dd {
    margin: 0;
    color: rgba(43, 17, 28, 0.55);
    font-size: 0.68rem;
    font-weight: 700;
}
.home-values,
.home-members {
    padding: clamp(5rem, 9vw, 8rem) 0;
}
.home-section-heading--split {
    display: grid;
    grid-template-columns: 1fr minmax(280px, 0.62fr);
    gap: 4rem;
    align-items: end;
    margin-bottom: 3.5rem;
}
.home-section-heading h2,
.home-process__intro h2,
.home-cta h2 {
    margin: 0.8rem 0 0;
    color: var(--h-plum);
    font-family: 'Cormorant Garamond', 'Cairo', serif;
    font-size: clamp(2.5rem, 5vw, 4rem);
    font-weight: 600;
    letter-spacing: -0.025em;
    line-height: 1;
}
.is-ar .home-section-heading h2,
.is-ar .home-process__intro h2,
.is-ar .home-cta h2 {
    font-family: 'Cairo';
    line-height: 1.35;
}
.home-section-heading--split > p {
    margin: 0;
    color: rgba(43, 17, 28, 0.64);
    font-size: 0.96rem;
    line-height: 1.8;
}
.principles {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    border-block: 1px solid var(--h-line);
}
.principle {
    min-height: 315px;
    position: relative;
    padding: 2rem 1.5rem;
    border-inline-end: 1px solid var(--h-line);
}
.principle:last-child {
    border-inline-end: 0;
}
.principle:hover {
    background: rgba(255, 255, 255, 0.65);
}
.principle__number {
    position: absolute;
    inset-block-start: 1.3rem;
    inset-inline-end: 1.3rem;
    color: rgba(70, 19, 41, 0.3);
    font-family: 'Cormorant Garamond';
    font-size: 0.8rem;
}
.principle__icon {
    width: 50px;
    height: 50px;
    display: grid;
    place-items: center;
    margin-bottom: 4rem;
    background: #f8e5ed;
    color: var(--h-rose);
    border-radius: 50%;
}
.principle__icon svg {
    width: 23px;
}
.principle h3 {
    margin: 0 0 0.65rem;
    color: var(--h-plum);
    font-size: 1.05rem;
}
.principle p {
    margin: 0;
    color: rgba(43, 17, 28, 0.62);
    font-size: 0.83rem;
    line-height: 1.75;
}
.home-process {
    position: relative;
    padding: clamp(5rem, 9vw, 8rem) 0;
    background: var(--h-deep);
    color: #fff;
}
.home-process::before {
    content: '';
    position: absolute;
    inset: 0;
    background:
        radial-gradient(circle at 15% 25%, rgba(200, 45, 111, 0.22), transparent 30%),
        radial-gradient(circle at 88% 80%, rgba(200, 164, 91, 0.12), transparent 30%);
}
.home-process__grid {
    display: grid;
    grid-template-columns: 0.75fr 1.25fr;
    gap: clamp(3rem, 8vw, 7rem);
    align-items: start;
}
.home-process__intro {
    position: sticky;
    top: 120px;
}
.home-process__intro h2 {
    color: #fff;
}
.home-process__intro > p {
    margin: 1.2rem 0 2rem;
    max-width: 390px;
    color: rgba(255, 255, 255, 0.65);
    line-height: 1.8;
}
.process-list {
    list-style: none;
    margin: 0;
    padding: 0;
    border-top: 1px solid rgba(255, 255, 255, 0.14);
}
.process-step {
    min-height: 145px;
    display: grid;
    grid-template-columns: 56px 1fr auto;
    gap: 1.2rem;
    align-items: center;
    border-bottom: 1px solid rgba(255, 255, 255, 0.14);
}
.process-step:hover {
    background: rgba(255, 255, 255, 0.035);
    padding-inline: 1rem;
}
.process-step__index {
    color: var(--h-gold-light);
    font-family: 'Cormorant Garamond';
    font-size: 1.1rem;
}
.process-step h3 {
    margin: 0 0 0.4rem;
    color: #fff;
    font-size: 1.08rem;
}
.process-step p {
    margin: 0;
    color: rgba(255, 255, 255, 0.58);
    font-size: 0.84rem;
    line-height: 1.65;
}
.process-step > svg {
    width: 19px;
    color: rgba(255, 255, 255, 0.28);
}
.is-ar .process-step > svg {
    transform: rotate(180deg);
}
.home-section-heading--center {
    max-width: 670px;
    margin: 0 auto 3.4rem;
    text-align: center;
}
.home-section-heading--center .home-kicker {
    justify-content: center;
}
.home-section-heading--center p {
    margin: 1rem auto 0;
    color: rgba(43, 17, 28, 0.62);
    line-height: 1.8;
}
.member-list {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    border: 1px solid var(--h-line);
}
.member-card {
    min-height: 112px;
    display: grid;
    grid-template-columns: auto 1fr auto;
    align-items: center;
    gap: 1rem;
    padding: 1.2rem 1.3rem;
    background: rgba(255, 255, 255, 0.62);
    border-inline-end: 1px solid var(--h-line);
    border-bottom: 1px solid var(--h-line);
}
.member-card:nth-child(3n) {
    border-inline-end: 0;
}
.member-card:nth-last-child(-n + 3) {
    border-bottom: 0;
}
.member-card:hover {
    background: #fff;
}
.member-card__avatar {
    position: relative;
    width: 56px;
    height: 56px;
    display: grid;
    place-items: center;
    border-radius: 50%;
    color: #fff;
    font-family: 'Cormorant Garamond';
    font-size: 1.45rem;
    font-weight: 700;
}
.member-card__avatar > span {
    position: absolute;
    inset-block-end: 1px;
    inset-inline-end: 1px;
    width: 12px;
    height: 12px;
    background: #39a96b;
    border: 2px solid #fff;
    border-radius: 50%;
}
.member-card__identity {
    min-width: 0;
    display: grid;
    gap: 0.25rem;
}
.member-card__identity strong {
    display: flex;
    align-items: center;
    gap: 0.35rem;
    color: var(--h-plum);
    font-size: 0.9rem;
    overflow: hidden;
}
.member-card__identity strong svg {
    flex: none;
    width: 16px;
    color: var(--h-rose);
}
.member-card__identity small {
    color: rgba(43, 17, 28, 0.52);
    font-size: 0.72rem;
}
.member-card > a {
    width: 36px;
    height: 36px;
    display: grid;
    place-items: center;
    border: 1px solid var(--h-line);
    color: var(--h-rose);
}
.member-card > a:hover {
    background: var(--h-rose);
    color: #fff;
}
.member-card > a svg {
    width: 17px;
}
.is-ar .member-card > a svg {
    transform: rotate(180deg);
}
.member-list__empty {
    text-align: center;
    color: rgba(43, 17, 28, 0.62);
}
.home-members__action {
    margin-top: 2rem;
    text-align: center;
}
.home-cta {
    padding: 0 0 clamp(5rem, 9vw, 8rem);
}
.home-cta__inner {
    position: relative;
    overflow: hidden;
    padding: clamp(3.5rem, 7vw, 6rem) 2rem;
    background: var(--h-plum);
    text-align: center;
}
.home-cta__inner::before {
    content: '';
    position: absolute;
    width: 500px;
    height: 500px;
    inset: -300px auto auto 50%;
    transform: translateX(-50%);
    border: 1px solid rgba(234, 214, 156, 0.15);
    border-radius: 50%;
    box-shadow:
        0 0 0 70px rgba(234, 214, 156, 0.03),
        0 0 0 140px rgba(234, 214, 156, 0.02);
}
.home-cta__seal {
    width: 100px;
    margin: 0 auto 0.7rem;
    color: var(--h-gold-light);
}
.home-cta h2 {
    position: relative;
    max-width: 680px;
    margin: 0.9rem auto 0;
    color: #fff;
}
.home-cta p {
    position: relative;
    max-width: 520px;
    margin: 1.1rem auto 1.8rem;
    color: rgba(255, 255, 255, 0.7);
    line-height: 1.8;
}
.home-cta__actions {
    position: relative;
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 1.4rem;
}
.home-cta__login {
    color: #fff;
    font-size: 0.86rem;
    font-weight: 700;
    text-underline-offset: 4px;
}
.home-fade-enter-active,
.home-fade-leave-active {
    transition: opacity 0.2s;
}
.home-fade-enter-from,
.home-fade-leave-to {
    opacity: 0;
}
@media (max-width: 960px) {
    .home-nav__links,
    .home-sign-in,
    .home-nav__actions > .home-action {
        display: none;
    }
    .menu-button {
        display: grid;
    }
    .home-hero__grid {
        grid-template-columns: 1fr;
    }
    .home-hero__copy {
        max-width: 720px;
    }
    .home-hero__visual {
        min-height: 470px;
    }
    .trust-note--verified {
        inset-inline-start: calc(50% - 260px);
    }
    .trust-note--privacy {
        inset-inline-end: calc(50% - 260px);
    }
    .principles {
        grid-template-columns: repeat(2, 1fr);
    }
    .principle:nth-child(2) {
        border-inline-end: 0;
    }
    .principle:nth-child(-n + 2) {
        border-bottom: 1px solid var(--h-line);
    }
    .home-process__grid {
        grid-template-columns: 1fr;
    }
    .home-process__intro {
        position: static;
    }
    .member-list {
        grid-template-columns: repeat(2, 1fr);
    }
    .member-card:nth-child(3n) {
        border-inline-end: 1px solid var(--h-line);
    }
    .member-card:nth-child(2n) {
        border-inline-end: 0;
    }
    .member-card:nth-last-child(-n + 3) {
        border-bottom: 1px solid var(--h-line);
    }
    .member-card:nth-last-child(-n + 2) {
        border-bottom: 0;
    }
}
@media (max-width: 640px) {
    .home-shell {
        width: min(100% - 28px, 1180px);
    }
    .home-nav__inner {
        height: 70px;
    }
    .home-brand__mark {
        width: 39px;
    }
    .language-picker {
        display: none;
    }
    .home-hero {
        min-height: auto;
        padding: 108px 0 28px;
    }
    .home-hero__title {
        font-size: clamp(2.8rem, 14vw, 4.25rem);
    }
    .is-ar .home-hero__title {
        font-size: clamp(2.35rem, 11vw, 3.5rem);
    }
    .home-hero__description {
        font-size: 0.92rem;
    }
    .home-hero__actions {
        align-items: stretch;
        flex-direction: column;
        gap: 0.9rem;
    }
    .home-text-link {
        justify-content: center;
    }
    .home-hero__assurances {
        display: grid;
    }
    .home-hero__visual {
        min-height: 400px;
    }
    .covenant-card {
        width: calc(100% - 28px);
        min-height: 380px;
        padding: 2.8rem 2rem;
    }
    .covenant-card__seal {
        width: 112px;
    }
    .trust-note--verified {
        inset-block-start: 4%;
        inset-inline-start: 0;
    }
    .trust-note--privacy {
        inset-block-end: 3%;
        inset-inline-end: 0;
    }
    .home-hero__proof {
        margin-top: 2.5rem;
        align-items: flex-start;
        flex-direction: column;
    }
    .home-hero__proof dl {
        width: 100%;
        justify-content: space-between;
        gap: 1rem;
    }
    .home-hero__proof dt {
        font-size: 1.45rem;
    }
    .home-section-heading--split {
        grid-template-columns: 1fr;
        gap: 1.2rem;
        margin-bottom: 2.2rem;
    }
    .principles {
        grid-template-columns: 1fr;
    }
    .principle,
    .principle:nth-child(2) {
        min-height: auto;
        padding: 1.6rem 1rem;
        border-inline-end: 0;
        border-bottom: 1px solid var(--h-line);
    }
    .principle:last-child {
        border-bottom: 0;
    }
    .principle__icon {
        margin-bottom: 2rem;
    }
    .process-step {
        grid-template-columns: 42px 1fr;
        padding: 1.2rem 0;
    }
    .process-step > svg {
        display: none;
    }
    .member-list {
        grid-template-columns: 1fr;
    }
    .member-card,
    .member-card:nth-child(3n),
    .member-card:nth-child(2n),
    .member-card:nth-last-child(-n + 3),
    .member-card:nth-last-child(-n + 2) {
        border-inline-end: 0;
        border-bottom: 1px solid var(--h-line);
    }
    .member-card:last-child {
        border-bottom: 0;
    }
    .home-cta__actions {
        flex-direction: column;
    }
}
@media (prefers-reduced-motion: reduce) {
    .home-reveal {
        opacity: 1;
        transform: none;
        transition: none;
    }
    *,
    *::before,
    *::after {
        scroll-behavior: auto !important;
        transition-duration: 0.01ms !important;
        animation-duration: 0.01ms !important;
    }
}
</style>
