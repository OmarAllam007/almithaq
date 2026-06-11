<script setup lang="ts">
import { switchLanguage } from '@/actions/App/Http/Controllers/LanguageController';
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { route } from 'ziggy-js';
import { vueLang } from '@erag/lang-sync-inertia';

const { __ } = vueLang();
const page = usePage();

const currentLocale = computed(() => page.props.locale as string);
const isRtl = computed(() => currentLocale.value === 'ar');

function switchLang(lang: string): void {
    router.visit(switchLanguage.url(lang), { preserveScroll: true });
}
</script>

<template>
    <footer class="footer">
        <div class="shell footer__grid">
            <div class="footer__brand">
                <Link class="brand brand--light" :href="route('landing')">
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
                <p>{{ __('landing.footer-tagline') }}</p>
                <div class="footer__lang">
                    <button type="button" :class="{ active: !isRtl }" @click="switchLang('en')">English</button>
                    <span>/</span>
                    <button type="button" :class="{ active: isRtl }" @click="switchLang('ar')">العربية</button>
                </div>
            </div>

            <div class="footer__col">
                <h4>{{ __('landing.footer-explore') }}</h4>
                <Link :href="route('landing') + '#features'">{{ __('landing.nav-features') }}</Link>
                <Link :href="route('landing') + '#values'">{{ __('landing.nav-values') }}</Link>
                <Link :href="route('landing') + '#members'">{{ __('landing.nav-members') }}</Link>
                <Link :href="route('landing') + '#mobile-app'">{{ __('landing.nav-app') }}</Link>
            </div>

            <div class="footer__col">
                <h4>{{ __('landing.footer-company') }}</h4>
                <Link :href="route('about')">{{ __('landing.footer-about') }}</Link>
                <a href="#" @click.prevent>{{ __('landing.footer-stories') }}</a>
                <a href="#" @click.prevent>{{ __('landing.footer-pricing') }}</a>
            </div>

            <div class="footer__col">
                <h4>{{ __('landing.footer-support') }}</h4>
                <a href="#" @click.prevent>{{ __('landing.footer-help') }}</a>
                <a href="#" @click.prevent>{{ __('landing.footer-safety') }}</a>
                <Link :href="route('contact')">{{ __('landing.footer-contact') }}</Link>
            </div>

            <div class="footer__col footer__col--cta">
                <Link :href="route('signup')" class="btn btn--solid">{{ __('landing.nav-signup') }}</Link>
                <Link :href="route('login')" class="btn btn--ghost btn--ghost-light">{{ __('landing.nav-login') }}</Link>
            </div>
        </div>

        <div class="shell footer__bottom">
            <span>© {{ new Date().getFullYear() }} Khotobah · {{ __('landing.footer-rights') }}</span>
            <div class="footer__legal">
                <Link :href="route('privacy')">{{ __('landing.footer-privacy') }}</Link>
                <Link :href="route('terms')">{{ __('landing.footer-terms') }}</Link>
            </div>
        </div>
    </footer>
</template>
