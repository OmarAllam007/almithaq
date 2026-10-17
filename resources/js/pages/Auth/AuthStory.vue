<script setup lang="ts">
defineProps<{ mode: 'login' | 'signup' | 'verify'; isRtl: boolean }>();
</script>

<template>
    <aside class="auth-story" :class="`auth-story--${mode}`" aria-hidden="true">
        <div class="auth-story__top">
            <span class="auth-story__overline"
                >KHOTOBAH <span>✦</span> {{ mode === 'login' ? 'WELCOME BACK' : mode === 'verify' ? 'ALMOST THERE' : 'A NEW BEGINNING' }}</span
            >
            <span class="auth-story__number">{{ mode === 'login' ? '01' : mode === 'verify' ? '03' : '02' }} / 03</span>
        </div>

        <div class="auth-story__art">
            <div class="auth-story__orbit auth-story__orbit--outer"></div>
            <div class="auth-story__orbit auth-story__orbit--inner"></div>
            <span class="auth-story__spark auth-story__spark--one">✦</span>
            <span class="auth-story__spark auth-story__spark--two">✳</span>
            <div class="auth-story__card auth-story__card--back"></div>
            <div class="auth-story__card auth-story__card--front">
                <span class="auth-story__seal">✳</span>
                <span class="auth-story__line"></span>
                <span class="auth-story__line auth-story__line--short"></span>
                <span class="auth-story__card-caption">KHOTOBAH · 2026</span>
            </div>
            <div class="auth-story__float auth-story__float--top">
                ✦ <span>{{ isRtl ? 'بداية أجمل' : 'A thoughtful beginning' }}</span>
            </div>
            <div class="auth-story__float auth-story__float--bottom">
                <span class="auth-story__float-mark">♡</span>{{ isRtl ? 'قصة تستحق أن تبدأ' : 'A story worth beginning' }}
            </div>
        </div>

        <div class="auth-story__copy">
            <span class="auth-story__eyebrow">{{ isRtl ? 'مساحة لبدايات جميلة' : 'MADE FOR MEANINGFUL CONNECTIONS' }}</span>
            <h2 v-if="mode === 'login'">{{ isRtl ? 'أهلاً بعودتك إلى قصتك.' : 'Your story is waiting for you.' }}</h2>
            <h2 v-else-if="mode === 'verify'">{{ isRtl ? 'بقيت خطوة، وتبدأ الحكاية.' : 'One more step, then your story begins.' }}</h2>
            <h2 v-else>{{ isRtl ? 'كل قصة جميلة تبدأ بخطوة.' : 'Every great story begins somewhere.' }}</h2>
            <p v-if="mode === 'login'">
                {{
                    isRtl
                        ? 'عُد إلى مكان يقدّر ما يهمك، واكتشف من يشاركك الطريق.'
                        : 'Come back to a place shaped around what matters to you and the people who share it.'
                }}
            </p>
            <p v-else-if="mode === 'verify'">
                {{
                    isRtl
                        ? 'رسالة صغيرة في بريدك تفتح لك مساحة لاكتشاف أشخاص يشاركونك ما يهمك.'
                        : 'A small message in your inbox opens the door to meaningful connections.'
                }}
            </p>
            <p v-else>
                {{
                    isRtl
                        ? 'أنشئ حسابك، عبّر عن نفسك، واترك للمسافات الجميلة فرصة أن تقترب.'
                        : 'Create your account, show who you are, and make room for a meaningful connection.'
                }}
            </p>
            <div class="auth-story__footer">
                <span>✦</span><span>{{ isRtl ? 'خطوبة، حيث تبدأ الحكاية' : 'Khotobah · Where stories begin' }}</span>
            </div>
        </div>
    </aside>
</template>
