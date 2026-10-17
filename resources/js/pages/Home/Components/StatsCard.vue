<script setup lang="ts">
import { useLang } from '@/composables/useLang';
const { trans } = useLang();
defineProps<{ profileViews: number; likes: number; conversations: number; daysSinceJoined: number }>();
</script>

<template>
    <div class="stats-grid">
        <div class="stat-tile">
            <span class="stat-symbol rose"><i class="ki-outline ki-eye"></i></span>
            <div>
                <strong>{{ profileViews }}</strong
                ><span>{{ trans('home.profile_views') }}</span>
            </div>
            <span class="stat-index">01</span>
        </div>
        <div class="stat-tile">
            <span class="stat-symbol coral"><i class="ki-solid ki-heart-circle"></i></span>
            <div>
                <strong>{{ likes }}</strong
                ><span>{{ trans('home.liked_you') }}</span>
            </div>
            <span class="stat-index">02</span>
        </div>
        <div class="stat-tile">
            <span class="stat-symbol plum"><i class="ki-outline ki-message-text-2"></i></span>
            <div>
                <strong>{{ conversations }}</strong
                ><span>{{ trans('home.conversations') }}</span>
            </div>
            <span class="stat-index">03</span>
        </div>
        <div class="stat-tile">
            <span class="stat-symbol gold"><i class="ki-outline ki-calendar"></i></span>
            <div>
                <strong>{{ daysSinceJoined }}</strong
                ><span>{{ trans('home.days_since_joined') }}</span>
            </div>
            <span class="stat-index">04</span>
        </div>
    </div>
</template>

<style scoped>
.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 14px;
}
.stat-tile {
    min-width: 0;
    position: relative;
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 21px 20px;
    background: #fff;
    border: 1px solid #f1e8e8;
    border-radius: 17px;
    box-shadow: 0 8px 28px #6130480a;
    transition:
        transform 0.2s,
        box-shadow 0.2s;
}
.stat-tile:hover {
    transform: translateY(-3px);
    box-shadow: 0 13px 28px #61304819;
}
.stat-symbol {
    width: 46px;
    height: 46px;
    flex: 0 0 46px;
    border-radius: 13px;
    display: grid;
    place-items: center;
    font-size: 22px;
}
.rose {
    background: #fce9ee;
    color: #b94b72;
}
.coral {
    background: #fff0e9;
    color: #d46d5f;
}
.plum {
    background: #f0e9f2;
    color: #86507c;
}
.gold {
    background: #fbf2e1;
    color: #b98b44;
}
.stat-tile div {
    min-width: 0;
}
.stat-tile strong {
    display: block;
    font-size: 24px;
    line-height: 1.2;
    color: #3e2735;
    font-weight: 800;
}
.stat-tile div span {
    display: block;
    margin-top: 3px;
    color: #8d7a84;
    font-size: 11px;
    line-height: 1.5;
    font-weight: 700;
}
.stat-index {
    position: absolute;
    top: 13px;
    inset-inline-end: 15px;
    font-size: 10px;
    font-weight: 700;
    color: #d5c8cc;
}
@media (max-width: 1199px) {
    .stats-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}
@media (max-width: 500px) {
    .stats-grid {
        gap: 9px;
    }
    .stat-tile {
        padding: 16px 12px;
        gap: 9px;
    }
    .stat-symbol {
        width: 36px;
        height: 36px;
        flex-basis: 36px;
        font-size: 17px;
    }
    .stat-tile strong {
        font-size: 19px;
    }
    .stat-tile div span {
        font-size: 10px;
    }
    .stat-index {
        display: none;
    }
}
</style>
