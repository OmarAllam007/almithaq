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

const activeSection = ref('s1');

const sections = computed(() => [
    { id: 's1', title: __('pages.privacy-s1-title'), body: __('pages.privacy-s1-body') },
    { id: 's2', title: __('pages.privacy-s2-title'), body: __('pages.privacy-s2-body') },
    { id: 's3', title: __('pages.privacy-s3-title'), body: __('pages.privacy-s3-body') },
    { id: 's4', title: __('pages.privacy-s4-title'), body: __('pages.privacy-s4-body') },
    { id: 's5', title: __('pages.privacy-s5-title'), body: __('pages.privacy-s5-body') },
    { id: 's6', title: __('pages.privacy-s6-title'), body: __('pages.privacy-s6-body') },
    { id: 's7', title: __('pages.privacy-s7-title'), body: __('pages.privacy-s7-body') },
    { id: 's8', title: __('pages.privacy-s8-title'), body: __('pages.privacy-s8-body') },
    { id: 's9', title: __('pages.privacy-s9-title'), body: __('pages.privacy-s9-body') },
    { id: 's10', title: __('pages.privacy-s10-title'), body: __('pages.privacy-s10-body') },
]);

let sectionObserver: IntersectionObserver | null = null;
let revealObserver: IntersectionObserver | null = null;

function scrollToSection(id: string): void {
    const el = document.getElementById(id);
    if (el) {
        el.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

onMounted(() => {
    revealObserver = new IntersectionObserver(
        (entries) => {
            for (const entry of entries) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    revealObserver?.unobserve(entry.target);
                }
            }
        },
        { threshold: 0.1 },
    );
    document.querySelectorAll('.reveal').forEach((el) => revealObserver?.observe(el));

    sectionObserver = new IntersectionObserver(
        (entries) => {
            for (const entry of entries) {
                if (entry.isIntersecting) {
                    activeSection.value = entry.target.id;
                }
            }
        },
        { rootMargin: '-20% 0px -70% 0px' },
    );
    sections.value.forEach(({ id }) => {
        const el = document.getElementById(id);
        if (el) {
            sectionObserver?.observe(el);
        }
    });
});

onBeforeUnmount(() => {
    revealObserver?.disconnect();
    sectionObserver?.disconnect();
});
</script>

<template>
    <Head>
        <title>{{ __('pages.nav-privacy') }} — Khotobah</title>
    </Head>

    <div class="landing" :class="{ 'is-ar': isRtl }" :dir="isRtl ? 'rtl' : 'ltr'">
        <LandingNav />

        <!-- ── HERO ── -->
        <section class="legal-hero">
            <div class="legal-hero__glow"></div>
            <div class="shell legal-hero__inner">
                <span class="eyebrow reveal">
                    <span class="eyebrow__diamond"></span>{{ __('pages.privacy-eyebrow') }}
                </span>
                <h1 class="page-title legal-hero__title reveal">
                    <span class="line">{{ __('pages.privacy-hero-title-1') }}</span>
                    <span class="line line--accent">{{ __('pages.privacy-hero-title-2') }}</span>
                </h1>
                <p class="legal-hero__date reveal">{{ __('pages.privacy-hero-desc') }}</p>
            </div>
            <div class="arabesque"></div>
        </section>

        <!-- ── BODY ── -->
        <section class="section legal-section">
            <div class="shell legal-body">

                <!-- Sticky TOC sidebar -->
                <aside class="legal-toc">
                    <h3 class="legal-toc__title">{{ __('pages.privacy-toc-title') }}</h3>
                    <nav>
                        <a
                            v-for="sec in sections"
                            :key="sec.id"
                            :class="{ 'is-active': activeSection === sec.id }"
                            href="#"
                            @click.prevent="scrollToSection(sec.id)"
                        >{{ sec.title }}</a>
                    </nav>
                </aside>

                <!-- Content -->
                <div class="legal-content">
                    <div class="legal-intro reveal">
                        <p>{{ __('pages.privacy-intro') }}</p>
                    </div>

                    <div
                        v-for="(sec, i) in sections"
                        :id="sec.id"
                        :key="sec.id"
                        class="legal-section-block reveal"
                        :style="{ transitionDelay: i * 40 + 'ms' }"
                    >
                        <h2 class="legal-section-block__title">{{ sec.title }}</h2>
                        <p class="legal-section-block__body">{{ sec.body }}</p>
                    </div>

                    <div class="legal-contact-cta reveal">
                        <p>
                            {{ isRtl ? 'للاستفسار عن هذه السياسة: ' : 'Questions? Contact us at ' }}
                            <a href="mailto:contact@khotobah.com">contact@khotobah.com</a>
                        </p>
                        <Link :href="route('privacy')" class="btn btn--outline">
                            {{ isRtl ? 'نسخة للطباعة' : 'Print version' }}
                        </Link>
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
/* ── Legal hero ── */
.legal-hero {
    position: relative;
    padding: 5rem 0 3rem;
    overflow: hidden;
    text-align: center;
}
.legal-hero__glow {
    position: absolute;
    width: 500px; height: 260px; border-radius: 50%;
    background: radial-gradient(ellipse, rgba(199,154,63,0.14), transparent 65%);
    filter: blur(50px);
    top: 0; left: 50%; transform: translateX(-50%);
    pointer-events: none;
}
.legal-hero__inner { max-width: 600px; margin: 0 auto; }
.legal-hero__title {
    font-size: clamp(2.6rem, 5.5vw, 4rem);
    line-height: 1.06; font-weight: 600;
    letter-spacing: -0.01em; margin: 0 0 1rem;
}
.is-ar .legal-hero__title { line-height: 1.3; }
.legal-hero__title .line { display: block; }
.legal-hero__title .line--accent {
    font-style: italic;
    background: linear-gradient(120deg, var(--rose), var(--gold));
    -webkit-background-clip: text; background-clip: text; color: transparent;
}
.is-ar .legal-hero__title .line--accent { font-style: normal; }
.legal-hero__date {
    font-size: 0.88rem; font-weight: 700;
    color: rgba(44,10,24,0.5); letter-spacing: 0.06em;
    text-transform: uppercase; margin: 0;
}
.is-ar .legal-hero__date { letter-spacing: 0; text-transform: none; }

/* ── Legal layout ── */
.legal-section { padding-top: 3.5rem; }
.legal-body {
    display: grid;
    grid-template-columns: 240px 1fr;
    gap: 4rem;
    align-items: start;
}

/* ── TOC ── */
.legal-toc {
    position: sticky;
    top: 90px;
    padding: 1.4rem;
    background: #fff;
    border: 1px solid var(--line);
    border-radius: 1.2rem;
}
.legal-toc__title {
    font-size: 0.75rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: 0.12em; color: var(--rose); margin: 0 0 0.9rem;
}
.is-ar .legal-toc__title { letter-spacing: 0; text-transform: none; }
.legal-toc nav { display: flex; flex-direction: column; gap: 0.1rem; }
.legal-toc nav a {
    font-size: 0.84rem; font-weight: 600; color: rgba(44,10,24,0.6);
    text-decoration: none; padding: 0.4rem 0.6rem;
    border-radius: 0.5rem; border-inline-start: 2px solid transparent;
    transition: color 0.2s ease, border-color 0.2s ease, background 0.2s ease;
    line-height: 1.4;
}
.legal-toc nav a:hover { color: var(--plum); background: var(--cream-2); }
.legal-toc nav a.is-active {
    color: var(--rose);
    border-inline-start-color: var(--rose);
    background: var(--blush);
}

/* ── Content ── */
.legal-intro {
    background: var(--blush);
    border: 1px solid rgba(208,46,121,0.18);
    border-radius: 1rem;
    padding: 1.4rem 1.6rem;
    margin-bottom: 2.5rem;
}
.legal-intro p {
    font-size: 1rem; line-height: 1.75;
    color: var(--plum); margin: 0;
    font-style: italic;
}
.is-ar .legal-intro p { font-style: normal; }
.legal-section-block {
    padding: 2rem 0;
    border-bottom: 1px solid var(--line);
    scroll-margin-top: 90px;
}
.legal-section-block:last-of-type { border-bottom: none; }
.legal-section-block__title {
    font-family: 'Cormorant Garamond', 'Cairo', serif;
    font-size: 1.5rem; font-weight: 600;
    color: var(--plum); margin: 0 0 0.9rem;
    line-height: 1.2;
}
.is-ar .legal-section-block__title { font-family: 'Cairo', serif; }
.legal-section-block__body {
    font-size: 0.98rem; line-height: 1.8;
    color: rgba(44,10,24,0.72); margin: 0;
}

.legal-contact-cta {
    display: flex; align-items: center; justify-content: space-between;
    gap: 1rem; flex-wrap: wrap;
    padding: 1.4rem 1.6rem;
    background: var(--cream-2); border-radius: 1rem;
    margin-top: 2rem;
}
.legal-contact-cta p { margin: 0; font-size: 0.92rem; color: rgba(44,10,24,0.7); }
.legal-contact-cta a { color: var(--rose); font-weight: 700; }

/* ── Responsive ── */
@media (max-width: 980px) {
    .legal-body { grid-template-columns: 1fr; }
    .legal-toc { position: static; }
    .legal-toc nav { display: grid; grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 560px) {
    .legal-toc nav { grid-template-columns: 1fr; }
    .legal-contact-cta { flex-direction: column; align-items: flex-start; }
}
</style>
