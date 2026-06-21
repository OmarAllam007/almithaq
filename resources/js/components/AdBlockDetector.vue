<script setup lang="ts">
import { onMounted, onUnmounted } from 'vue';
import { router, usePage } from '@inertiajs/vue3';

const page = usePage();

let adBlockDetected = false;
let mutationObserver: MutationObserver | null = null;
let intervalId: ReturnType<typeof setInterval> | null = null;
let unsubscribeRouter: (() => void) | null = null;

const OVERLAY_ID = 'adblock-guard-overlay';

function isRtl(): boolean {
    return page.props.locale === 'ar';
}

function getOverlay(): HTMLElement | null {
    return document.getElementById(OVERLAY_ID);
}

function buildOverlay(): HTMLElement {
    const rtl = isRtl();

    const overlay = document.createElement('div');
    overlay.id = OVERLAY_ID;
    overlay.setAttribute('role', 'dialog');
    overlay.setAttribute('aria-modal', 'true');
    overlay.setAttribute('dir', rtl ? 'rtl' : 'ltr');
    overlay.style.cssText = [
        'position:fixed',
        'inset:0',
        'z-index:99999',
        'background:rgba(0,0,0,0.85)',
        'display:flex',
        'align-items:center',
        'justify-content:center',
        'padding:1rem',
        'backdrop-filter:blur(4px)',
    ].join(';');

    const card = document.createElement('div');
    card.style.cssText = [
        'background:#fff',
        'border-radius:1rem',
        'padding:2.5rem 2rem',
        'max-width:480px',
        'width:100%',
        'text-align:center',
        'box-shadow:0 20px 60px rgba(0,0,0,0.4)',
    ].join(';');

    const iconWrap = document.createElement('div');
    iconWrap.style.cssText = [
        'width:72px',
        'height:72px',
        'border-radius:50%',
        'background:#fff0f0',
        'display:flex',
        'align-items:center',
        'justify-content:center',
        'margin:0 auto 1.25rem',
    ].join(';');
    iconWrap.innerHTML = '<i class="ki-outline ki-shield-cross" style="font-size:2.25rem;color:#f1416c;"></i>';

    const title = document.createElement('h2');
    title.style.cssText = 'font-size:1.4rem;font-weight:700;margin-bottom:0.75rem;color:#181c32;';
    title.textContent = rtl ? 'مانع الإعلانات مفعّل' : 'Ad Blocker Detected';

    const body = document.createElement('p');
    body.style.cssText = 'font-size:0.925rem;color:#5e6278;line-height:1.6;margin-bottom:1.25rem;';
    body.textContent = rtl
        ? 'يعتمد هذا الموقع على الإعلانات لتمويل خدماته المجانية. يرجى تعطيل مانع الإعلانات والسماح للإعلانات من هذا الموقع ثم إعادة المحاولة.'
        : 'This website relies on ads to fund its free services. Please disable your ad blocker for this site, then click Retry to continue.';

    const steps = document.createElement('ol');
    steps.style.cssText = [
        'text-align:start',
        'font-size:0.875rem',
        'color:#5e6278',
        'margin:0 auto 1.75rem',
        'max-width:340px',
        'padding-inline-start:1.25rem',
        'line-height:1.8',
    ].join(';');

    const stepTexts = rtl
        ? [
              'افتح امتداد مانع الإعلانات في متصفحك',
              'اضغط على «توقف مؤقت» أو «تعطيل لهذا الموقع»',
              'اضغط على زر «إعادة المحاولة» أدناه',
          ]
        : [
              'Open your ad blocker extension in your browser',
              'Click Pause or Disable for this site',
              'Click the Retry button below',
          ];

    stepTexts.forEach((text) => {
        const li = document.createElement('li');
        li.textContent = text;
        steps.appendChild(li);
    });

    const btn = document.createElement('button');
    btn.id = 'adblock-retry-btn';
    btn.textContent = rtl ? 'إعادة المحاولة' : 'Retry';
    btn.style.cssText = [
        'display:inline-block',
        'padding:0.65rem 2rem',
        'background:#009ef7',
        'color:#fff',
        'border:none',
        'border-radius:0.5rem',
        'font-size:0.925rem',
        'font-weight:600',
        'cursor:pointer',
    ].join(';');
    btn.addEventListener('mouseenter', () => { btn.style.background = '#0095e8'; });
    btn.addEventListener('mouseleave', () => { btn.style.background = '#009ef7'; });
    btn.addEventListener('click', () => {
        removeOverlay();
        setTimeout(checkAdBlock, 150);
    });

    card.appendChild(iconWrap);
    card.appendChild(title);
    card.appendChild(body);
    card.appendChild(steps);
    card.appendChild(btn);
    overlay.appendChild(card);

    return overlay;
}

function mountOverlay(): void {
    if (getOverlay()) {
        return;
    }
    document.body.appendChild(buildOverlay());
}

function removeOverlay(): void {
    getOverlay()?.remove();
}

function checkAdBlock(): void {
    const bait = document.createElement('div');
    bait.setAttribute(
        'class',
        'adsbox pub_300x250 pub_300x250m pub_728x90 text-ad textAd text_ad text_ads ad-unit ad-zone ad-space',
    );
    bait.style.cssText = 'position:absolute;top:-9999px;left:-9999px;width:1px;height:1px;';
    document.body.appendChild(bait);

    setTimeout(() => {
        const style = window.getComputedStyle(bait);
        const blocked =
            bait.offsetHeight === 0 ||
            bait.offsetParent === null ||
            style.display === 'none' ||
            style.visibility === 'hidden' ||
            style.opacity === '0';

        document.body.removeChild(bait);
        adBlockDetected = blocked;

        if (blocked) {
            mountOverlay();
        } else {
            removeOverlay();
        }
    }, 200);
}

function setupMutationObserver(): void {
    mutationObserver = new MutationObserver((mutations) => {
        if (!adBlockDetected) {
            return;
        }

        for (const mutation of mutations) {
            for (const node of Array.from(mutation.removedNodes)) {
                if ((node as HTMLElement).id === OVERLAY_ID) {
                    // Re-add instantly when the overlay is removed from the DOM
                    mountOverlay();
                    return;
                }
            }
        }
    });

    mutationObserver.observe(document.body, { childList: true });
}

onMounted(() => {
    checkAdBlock();

    intervalId = setInterval(checkAdBlock, 4000);

    unsubscribeRouter = router.on('navigate', () => {
        checkAdBlock();
    });

    setupMutationObserver();
});

onUnmounted(() => {
    if (intervalId !== null) {
        clearInterval(intervalId);
    }
    unsubscribeRouter?.();
    mutationObserver?.disconnect();
    removeOverlay();
});
</script>

<template>
    <!-- Overlay is managed via raw DOM + MutationObserver, not Vue's vdom -->
</template>
