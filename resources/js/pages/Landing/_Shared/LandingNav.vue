<script setup lang="ts">
import { switchLanguage } from '@/actions/App/Http/Controllers/LanguageController';
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { route } from 'ziggy-js';
import { vueLang } from '@erag/lang-sync-inertia';

const { __ } = vueLang();
const page = usePage();

const currentLocale = computed(() => page.props.locale as string);
const isRtl = computed(() => currentLocale.value === 'ar');
const isLandingPage = computed(() => page.component === 'Landing/Index');

const showLangMenu = ref(false);
const scrolled = ref(false);
const mobileNav = ref(false);

function switchLang(lang: string): void {
    showLangMenu.value = false;
    router.visit(switchLanguage.url(lang), { preserveScroll: true });
}

function navTo(id: string): void {
    mobileNav.value = false;
    if (isLandingPage.value) {
        const el = document.getElementById(id);
        if (el) {
            el.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    } else {
        window.location.href = route('landing') + '#' + id;
    }
}

let onScroll: (() => void) | null = null;

onMounted(() => {
    onScroll = () => {
        scrolled.value = window.scrollY > 24;
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
});

onBeforeUnmount(() => {
    if (onScroll) {
        window.removeEventListener('scroll', onScroll);
    }
});
</script>

<template>
    <!-- ============ NAV ============ -->
    <header class="nav" :class="{ scrolled }">
        <div class="shell nav__inner">
            <Link class="brand" :href="route('landing')">
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
            </Link>

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

                <button
                    class="burger"
                    :class="{ 'is-open': mobileNav }"
                    type="button"
                    aria-label="Menu"
                    @click="mobileNav = !mobileNav"
                >
                    <span></span><span></span><span></span>
                </button>
            </div>
        </div>
    </header>

    <!-- ============ OFF-CANVAS NAV ============ -->
    <div
        class="nav__backdrop"
        :class="{ 'is-open': mobileNav }"
        aria-hidden="true"
        @click="mobileNav = false"
    ></div>

    <aside
        class="nav__offcanvas"
        :class="{ 'is-open': mobileNav }"
        role="dialog"
        aria-modal="true"
        :aria-hidden="!mobileNav"
    >
        <div class="nav__offcanvas-stripe" aria-hidden="true"></div>

        <div class="nav__offcanvas-header">
            <Link class="brand" :href="route('landing')">
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
            </Link>
            <button
                class="nav__offcanvas-close"
                type="button"
                aria-label="Close navigation"
                @click="mobileNav = false"
            >
                <svg viewBox="0 0 24 24" fill="none">
                    <path d="M18 6 6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                </svg>
            </button>
        </div>

        <nav class="nav__offcanvas-links">
            <a href="#features" @click.prevent="navTo('features')">
                <span class="nav__offcanvas-num">01</span>
                <span>{{ __('landing.nav-features') }}</span>
                <svg class="nav__offcanvas-arrow" viewBox="0 0 24 24" fill="none">
                    <path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </a>
            <a href="#values" @click.prevent="navTo('values')">
                <span class="nav__offcanvas-num">02</span>
                <span>{{ __('landing.nav-values') }}</span>
                <svg class="nav__offcanvas-arrow" viewBox="0 0 24 24" fill="none">
                    <path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </a>
            <a href="#members" @click.prevent="navTo('members')">
                <span class="nav__offcanvas-num">03</span>
                <span>{{ __('landing.nav-members') }}</span>
                <svg class="nav__offcanvas-arrow" viewBox="0 0 24 24" fill="none">
                    <path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </a>
            <a href="#mobile-app" @click.prevent="navTo('mobile-app')">
                <span class="nav__offcanvas-num">04</span>
                <span>{{ __('landing.nav-app') }}</span>
                <svg class="nav__offcanvas-arrow" viewBox="0 0 24 24" fill="none">
                    <path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </a>
        </nav>

        <div class="nav__offcanvas-footer">
            <div class="nav__offcanvas-lang">
                <button type="button" :class="{ active: !isRtl }" @click="switchLang('en')">
                    <img src="/assets/media/flags/united-states.svg" alt="" /> English
                </button>
                <span>/</span>
                <button type="button" :class="{ active: isRtl }" @click="switchLang('ar')">
                    <img src="/assets/media/flags/saudi-arabia.svg" alt="" /> العربية
                </button>
            </div>
            <div class="nav__offcanvas-actions">
                <Link :href="route('login')" class="btn btn--outline">{{ __('landing.nav-login') }}</Link>
                <Link :href="route('signup')" class="btn btn--solid">{{ __('landing.nav-signup') }}</Link>
            </div>
        </div>

        <div class="nav__offcanvas-glow" aria-hidden="true"></div>
    </aside>
</template>
