<script setup lang="ts">
import { Head, useForm, usePage, router } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { route } from 'ziggy-js';
import { vueLang } from '@erag/lang-sync-inertia';
import LandingNav from './_Shared/LandingNav.vue';
import LandingFooter from './_Shared/LandingFooter.vue';

const { __ } = vueLang();
const page = usePage();
const isRtl = computed(() => (page.props.locale as string) === 'ar');
const success = computed(() => page.props.flash?.success as boolean | undefined);

const openFaq = ref<number | null>(null);

const form = useForm({
    name: '',
    email: '',
    subject: '',
    message: '',
});

function submit(): void {
    form.post(route('contact.send'), {
        onSuccess: () => form.reset(),
    });
}

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
        <title>{{ __('pages.nav-contact') }} — Khotobah</title>
    </Head>

    <div class="landing" :class="{ 'is-ar': isRtl }" :dir="isRtl ? 'rtl' : 'ltr'">
        <LandingNav />

        <!-- ── HERO ── -->
        <section class="contact-hero">
            <div class="contact-hero__glow"></div>
            <div class="shell contact-hero__inner">
                <span class="eyebrow reveal">
                    <span class="eyebrow__diamond"></span>{{ __('pages.contact-eyebrow') }}
                </span>
                <h1 class="page-title contact-hero__title reveal">
                    <span class="line">{{ __('pages.contact-hero-title-1') }}</span>
                    <span class="line line--accent">{{ __('pages.contact-hero-title-2') }}</span>
                </h1>
                <p class="contact-hero__desc reveal">{{ __('pages.contact-hero-desc') }}</p>
            </div>
            <div class="arabesque"></div>
        </section>

        <!-- ── BODY ── -->
        <section class="section contact-section">
            <div class="shell contact-grid">

                <!-- Form / success -->
                <div class="contact-form-wrap reveal">
                    <h2 class="contact-block__title">{{ __('pages.contact-form-title') }}</h2>

                    <!-- success state -->
                    <div v-if="success" class="contact-success">
                        <span class="contact-success__icon">
                            <svg viewBox="0 0 24 24" fill="none">
                                <circle cx="12" cy="12" r="10" fill="currentColor" opacity="0.12" />
                                <path d="M7 12.5l3.5 3.5 7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </span>
                        <h3>{{ __('pages.contact-success-title') }}</h3>
                        <p>{{ __('pages.contact-success-desc') }}</p>
                        <button type="button" class="btn btn--solid" @click="router.reload()">
                            {{ __('pages.contact-success-btn') }}
                        </button>
                    </div>

                    <form v-else class="contact-form" @submit.prevent="submit">
                        <div class="contact-form__row">
                            <div class="contact-field">
                                <label class="contact-field__label">{{ __('pages.contact-form-name') }}</label>
                                <input
                                    v-model="form.name"
                                    type="text"
                                    class="contact-field__input"
                                    :class="{ 'is-error': form.errors.name }"
                                    :placeholder="__('pages.contact-form-name')"
                                />
                                <span v-if="form.errors.name" class="contact-field__error">{{ form.errors.name }}</span>
                            </div>
                            <div class="contact-field">
                                <label class="contact-field__label">{{ __('pages.contact-form-email') }}</label>
                                <input
                                    v-model="form.email"
                                    type="email"
                                    class="contact-field__input"
                                    :class="{ 'is-error': form.errors.email }"
                                    :placeholder="__('pages.contact-form-email')"
                                />
                                <span v-if="form.errors.email" class="contact-field__error">{{ form.errors.email }}</span>
                            </div>
                        </div>

                        <div class="contact-field">
                            <label class="contact-field__label">{{ __('pages.contact-form-subject') }}</label>
                            <select v-model="form.subject" class="contact-field__input" :class="{ 'is-error': form.errors.subject }">
                                <option value="" disabled>—</option>
                                <option value="general">{{ __('pages.contact-subject-general') }}</option>
                                <option value="account">{{ __('pages.contact-subject-account') }}</option>
                                <option value="safety">{{ __('pages.contact-subject-safety') }}</option>
                                <option value="feedback">{{ __('pages.contact-subject-feedback') }}</option>
                                <option value="press">{{ __('pages.contact-subject-press') }}</option>
                            </select>
                            <span v-if="form.errors.subject" class="contact-field__error">{{ form.errors.subject }}</span>
                        </div>

                        <div class="contact-field">
                            <label class="contact-field__label">{{ __('pages.contact-form-message') }}</label>
                            <textarea
                                v-model="form.message"
                                rows="6"
                                class="contact-field__input contact-field__textarea"
                                :class="{ 'is-error': form.errors.message }"
                                :placeholder="__('pages.contact-form-message')"
                            ></textarea>
                            <span v-if="form.errors.message" class="contact-field__error">{{ form.errors.message }}</span>
                        </div>

                        <button type="submit" class="btn btn--solid btn--lg contact-form__submit" :disabled="form.processing">
                            <svg v-if="!form.processing" viewBox="0 0 24 24" fill="none" style="width:18px;height:18px">
                                <path d="M22 2 11 13M22 2 15 22l-4-9-9-4 20-7Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            {{ form.processing ? __('pages.contact-form-sending') : __('pages.contact-form-submit') }}
                        </button>
                    </form>
                </div>

                <!-- Info sidebar -->
                <aside class="contact-info reveal">
                    <h2 class="contact-block__title">{{ __('pages.contact-info-title') }}</h2>

                    <div class="contact-info__card">
                        <div class="contact-info__row">
                            <span class="contact-info__icon">
                                <svg viewBox="0 0 24 24" fill="none">
                                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2Z" stroke="currentColor" stroke-width="1.7" />
                                    <path d="M22 6 12 13 2 6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" />
                                </svg>
                            </span>
                            <div>
                                <span class="contact-info__label">{{ __('pages.contact-email-label') }}</span>
                                <a href="mailto:contact@khotobah.com" class="contact-info__value">contact@khotobah.com</a>
                            </div>
                        </div>
                        <div class="contact-info__row">
                            <span class="contact-info__icon">
                                <svg viewBox="0 0 24 24" fill="none">
                                    <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.7" />
                                    <path d="M12 7v5l3 3" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </span>
                            <div>
                                <span class="contact-info__label">{{ __('pages.contact-hours-label') }}</span>
                                <span class="contact-info__value">{{ __('pages.contact-hours-value') }}</span>
                            </div>
                        </div>
                        <div class="contact-info__row">
                            <span class="contact-info__icon">
                                <svg viewBox="0 0 24 24" fill="none">
                                    <circle cx="12" cy="11" r="4" stroke="currentColor" stroke-width="1.7" />
                                    <path d="M3 20c1.7-4 4.8-6 9-6s7.3 2 9 6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" />
                                </svg>
                            </span>
                            <div>
                                <span class="contact-info__label">{{ __('pages.contact-location-label') }}</span>
                                <span class="contact-info__value">{{ __('pages.contact-location-value') }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- FAQ -->
                    <h3 class="contact-faq__title">{{ __('pages.contact-faq-title') }}</h3>
                    <div class="contact-faq">
                        <div
                            v-for="(item, i) in [
                                { q: __('pages.contact-faq-1-q'), a: __('pages.contact-faq-1-a') },
                                { q: __('pages.contact-faq-2-q'), a: __('pages.contact-faq-2-a') },
                                { q: __('pages.contact-faq-3-q'), a: __('pages.contact-faq-3-a') },
                            ]"
                            :key="i"
                            class="contact-faq__item"
                            :class="{ 'is-open': openFaq === i }"
                        >
                            <button type="button" class="contact-faq__q" @click="openFaq = openFaq === i ? null : i">
                                <span>{{ item.q }}</span>
                                <svg viewBox="0 0 24 24" fill="none">
                                    <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </button>
                            <div class="contact-faq__a">{{ item.a }}</div>
                        </div>
                    </div>
                </aside>
            </div>
        </section>

        <LandingFooter />
    </div>
</template>

<style scoped>
/* ── Contact hero ── */
.contact-hero {
    position: relative;
    padding: 5rem 0 3rem;
    overflow: hidden;
    text-align: center;
}
.contact-hero__glow {
    position: absolute;
    width: 500px; height: 300px; border-radius: 50%;
    background: radial-gradient(ellipse, rgba(208,46,121,0.14), transparent 65%);
    filter: blur(40px);
    top: 0; left: 50%; transform: translateX(-50%);
    pointer-events: none;
}
.contact-hero__inner { max-width: 660px; margin: 0 auto; }
.contact-hero__title {
    font-size: clamp(2.6rem, 5.5vw, 4rem);
    line-height: 1.06; font-weight: 600;
    letter-spacing: -0.01em; margin: 0 0 1.2rem;
}
.is-ar .contact-hero__title { line-height: 1.3; }
.contact-hero__title .line { display: block; }
.contact-hero__title .line--accent {
    font-style: italic;
    background: linear-gradient(120deg, var(--rose), var(--gold));
    -webkit-background-clip: text; background-clip: text; color: transparent;
}
.is-ar .contact-hero__title .line--accent { font-style: normal; }
.contact-hero__desc {
    font-size: 1.08rem; line-height: 1.75;
    color: rgba(44,10,24,0.7); margin: 0;
}

/* ── Contact body ── */
.contact-section { padding-top: 3.5rem; }
.contact-grid {
    display: grid;
    grid-template-columns: 1.2fr 0.85fr;
    gap: 3.5rem;
    align-items: start;
}

.contact-block__title {
    font-family: 'Cormorant Garamond', 'Cairo', serif;
    font-size: 1.75rem; font-weight: 600;
    color: var(--plum); margin: 0 0 1.8rem;
    line-height: 1.2;
}
.is-ar .contact-block__title { font-family: 'Cairo', serif; }

/* ── Form ── */
.contact-form-wrap {
    background: #fff;
    border: 1px solid var(--line);
    border-radius: 1.4rem;
    padding: 2.4rem 2rem;
}
.contact-form__row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.contact-field { display: flex; flex-direction: column; gap: 0.4rem; margin-bottom: 1.2rem; }
.contact-field__label {
    font-size: 0.84rem; font-weight: 700;
    color: var(--plum); letter-spacing: 0.04em;
    text-transform: uppercase;
}
.is-ar .contact-field__label { letter-spacing: 0; text-transform: none; }
.contact-field__input {
    font-family: 'Cairo', sans-serif;
    font-size: 0.95rem; color: var(--ink);
    background: var(--cream);
    border: 1.5px solid var(--line);
    border-radius: 0.65rem;
    padding: 0.7rem 0.95rem;
    outline: none;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
    width: 100%;
    appearance: none;
}
.contact-field__input:focus {
    border-color: var(--rose);
    box-shadow: 0 0 0 3px rgba(208,46,121,0.1);
}
.contact-field__input.is-error { border-color: #e53935; }
.contact-field__textarea { resize: vertical; min-height: 140px; }
.contact-field__error { font-size: 0.8rem; color: #e53935; font-weight: 600; }
.contact-form__submit { width: 100%; justify-content: center; gap: 0.6rem; }

/* ── Success ── */
.contact-success {
    display: flex; flex-direction: column; align-items: center;
    text-align: center; padding: 2.5rem 1rem;
    gap: 0.8rem;
}
.contact-success__icon {
    width: 72px; height: 72px; border-radius: 50%;
    background: var(--blush); display: grid; place-items: center;
    color: var(--rose); margin-bottom: 0.4rem;
}
.contact-success__icon svg { width: 36px; height: 36px; }
.contact-success h3 {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.8rem; font-weight: 600; color: var(--plum); margin: 0;
}
.is-ar .contact-success h3 { font-family: 'Cairo', serif; }
.contact-success p {
    font-size: 1rem; line-height: 1.6;
    color: rgba(44,10,24,0.7); max-width: 26rem; margin: 0;
}

/* ── Info sidebar ── */
.contact-info { position: sticky; top: 90px; }
.contact-info__card {
    background: #fff; border: 1px solid var(--line);
    border-radius: 1.2rem; padding: 1.6rem 1.4rem;
    display: flex; flex-direction: column; gap: 1.2rem;
    margin-bottom: 2rem;
}
.contact-info__row { display: flex; align-items: flex-start; gap: 1rem; }
.contact-info__icon {
    flex-shrink: 0; width: 40px; height: 40px;
    border-radius: 10px; background: var(--blush);
    display: grid; place-items: center; color: var(--rose);
}
.contact-info__icon svg { width: 20px; height: 20px; }
.contact-info__label {
    display: block; font-size: 0.75rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: 0.1em;
    color: rgba(44,10,24,0.5); margin-bottom: 0.2rem;
}
.is-ar .contact-info__label { letter-spacing: 0; text-transform: none; }
.contact-info__value {
    display: block; font-size: 0.92rem; font-weight: 600;
    color: var(--plum); text-decoration: none;
}
a.contact-info__value:hover { color: var(--rose); }

/* ── FAQ ── */
.contact-faq__title {
    font-size: 1rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: 0.1em; color: var(--plum); margin: 0 0 1rem;
    opacity: 0.7;
}
.is-ar .contact-faq__title { letter-spacing: 0; text-transform: none; }
.contact-faq { display: flex; flex-direction: column; gap: 0.5rem; }
.contact-faq__item {
    border: 1px solid var(--line);
    border-radius: 0.85rem; overflow: hidden;
    background: #fff;
    transition: border-color 0.2s ease;
}
.contact-faq__item.is-open { border-color: rgba(208,46,121,0.25); }
.contact-faq__q {
    width: 100%; background: none; border: none; cursor: pointer;
    display: flex; align-items: center; justify-content: space-between; gap: 1rem;
    padding: 1rem 1.1rem;
    font-family: 'Cairo', sans-serif;
    font-size: 0.92rem; font-weight: 700; color: var(--plum);
    text-align: start;
    transition: color 0.2s ease;
}
.contact-faq__item.is-open .contact-faq__q { color: var(--rose); }
.contact-faq__q svg {
    width: 18px; height: 18px; flex-shrink: 0; color: var(--rose);
    transition: transform 0.28s cubic-bezier(0.22, 1, 0.36, 1);
}
.contact-faq__item.is-open .contact-faq__q svg { transform: rotate(180deg); }
.contact-faq__a {
    max-height: 0; overflow: hidden;
    font-size: 0.88rem; line-height: 1.65; color: rgba(44,10,24,0.68);
    padding: 0 1.1rem;
    transition: max-height 0.35s cubic-bezier(0.22, 1, 0.36, 1), padding 0.2s ease;
}
.contact-faq__item.is-open .contact-faq__a { max-height: 200px; padding: 0 1.1rem 1rem; }

/* ── Responsive ── */
@media (max-width: 980px) {
    .contact-grid { grid-template-columns: 1fr; }
    .contact-info { position: static; }
}
@media (max-width: 560px) {
    .contact-form__row { grid-template-columns: 1fr; }
    .contact-form-wrap { padding: 1.6rem 1.2rem; }
}
</style>
