<script setup lang="ts">
import { onClickOutside } from '@vueuse/core';
import { ref, computed } from 'vue';

const emit = defineEmits<{ select: [emoji: string]; close: [] }>();
const pickerRef = ref<HTMLElement | null>(null);

const categories = [
    {
        label: '😀',
        emojis: [
            '😀','😃','😄','😁','😆','😅','🤣','😂','🙂','🙃',
            '😉','😊','😇','🥰','😍','🤩','😘','😗','😚','😙',
            '🥲','😋','😛','😜','🤪','😝','🤑','🤗','🤭','🫢',
            '🤫','🤔','🫠','🤐','🥴','😐','😑','😶','😏','😒',
            '🙄','😬','🤥','😌','😔','😪','🤤','😴','😷','🤒',
            '🤕','🥵','🥶','😶‍🌫️','😱','😨','😰','😥','😓','🤯',
            '😳','🥺','😢','😭','😤','😠','😡','🤬','😈','💀',
            '☠️','💩','🤡','👹','👺','👻','👽','👾','🤖',
        ],
    },
    {
        label: '👋',
        emojis: [
            '👋','🤚','🖐️','✋','🖖','🫱','🫲','🫳','🫴','🤙',
            '💪','🦾','🖕','✌️','🤞','🫰','🤟','🤘','🤙','👌',
            '🤌','🤏','👈','👉','👆','🖕','👇','☝️','🫵','👍',
            '👎','✊','👊','🤛','🤜','👏','🫶','🙌','👐','🤲',
            '🤝','🙏','✍️','💅','🤳','💃','🕺','🤸','🙆','🙅',
        ],
    },
    {
        label: '❤️',
        emojis: [
            '❤️','🧡','💛','💚','💙','💜','🖤','🤍','🤎','❤️‍🔥',
            '❤️‍🩹','💔','💕','💞','💓','💗','💖','💘','💝','💟',
            '☮️','✝️','☪️','🕉️','☸️','🪯','✡️','🔯','🕎','☯️',
            '♾️','💯','🎊','🎉','🥳','🎈','🎀','🎁','🪅','🎆',
        ],
    },
    {
        label: '🐶',
        emojis: [
            '🐶','🐱','🐭','🐹','🐰','🦊','🐻','🐼','🐨','🐯',
            '🦁','🐮','🐷','🐸','🐵','🙈','🙉','🙊','🐔','🐧',
            '🐦','🦆','🦅','🦉','🦇','🐺','🐗','🐴','🦄','🐝',
            '🪱','🐛','🦋','🐌','🐞','🐜','🦟','🦗','🪲','🐢',
            '🦎','🐊','🐸','🦈','🐙','🦑','🦐','🦞','🦀','🐡',
            '🌸','🌺','🌻','🌹','🥀','🌷','🌱','🌿','☘️','🍀',
            '🍁','🍂','🍃','🌾','🌴','🌵','🎋','🎍','🌊','🌈',
            '⚡','🔥','❄️','💧','🌙','⭐','🌟','💫','✨','☀️',
        ],
    },
    {
        label: '🍕',
        emojis: [
            '🍕','🍔','🌮','🌯','🥙','🧆','🍟','🌭','🥪','🥗',
            '🍿','🧂','🍱','🍘','🍣','🍤','🍙','🍚','🍛','🍜',
            '🍝','🍠','🥚','🍳','🧇','🥞','🧈','🍞','🥐','🥖',
            '🥨','🧀','🥓','🥩','🍗','🍖','🌽','🥕','🫛','🧄',
            '🧅','🥔','🍆','🥑','🥦','🥬','🥒','🌶️','🫑','🍅',
            '🍓','🫐','🍒','🍑','🥭','🍍','🥥','🍌','🍋','🍊',
            '🍎','🍏','🍇','🍉','🍈','🍐','🍑','🍒','🍓','🫒',
            '☕','🍵','🧃','🥤','🧋','🍺','🍻','🥂','🍷','🥃',
        ],
    },
    {
        label: '⚽',
        emojis: [
            '⚽','🏀','🏈','⚾','🥎','🏐','🏉','🎾','🥏','🎱',
            '🏓','🏸','🥊','⛳','🎿','🛷','🏹','🎣','🤿','🎽',
            '🎮','🕹️','🎲','♟️','🎯','🎳','🎰','🚀','🚗','🚕',
            '🚌','🚎','🚐','🚑','🚒','🚓','🚔','🚖','🚘','🚙',
            '🏠','🏡','🏢','🏣','🏤','🏥','🏦','🏨','🏩','🏪',
            '📱','💻','⌨️','🖥️','🖨️','🖱️','💾','💿','📷','📹',
            '📞','☎️','📟','📠','📺','📻','🎷','🎸','🎹','🎺',
            '🎻','🪕','🥁','🪘','🎤','🎧','🎼','🎵','🎶','🎙️',
        ],
    },
];

const activeCategory = ref(0);
const search = ref('');

const visibleEmojis = computed(() => {
    const q = search.value.trim();
    if (q) {
        return categories.flatMap((c) => c.emojis).filter(() => true);
    }
    return categories[activeCategory.value].emojis;
});

onClickOutside(pickerRef, () => emit('close'));

const pick = (emoji: string) => emit('select', emoji);
</script>

<template>
    <div ref="pickerRef" class="ep-picker">
        <div class="ep-search">
            <input
                v-model="search"
                type="text"
                class="ep-search__input"
                placeholder="🔍"
                autocomplete="off"
            />
        </div>

        <div v-if="!search" class="ep-cats">
            <button
                v-for="(cat, i) in categories"
                :key="i"
                type="button"
                class="ep-cats__btn"
                :class="{ 'ep-cats__btn--active': activeCategory === i }"
                @click="activeCategory = i"
            >
                {{ cat.label }}
            </button>
        </div>

        <div class="ep-grid">
            <button
                v-for="emoji in visibleEmojis"
                :key="emoji"
                type="button"
                class="ep-grid__btn"
                @click="pick(emoji)"
            >
                {{ emoji }}
            </button>
        </div>
    </div>
</template>

<style scoped>
.ep-picker {
    position: absolute;
    bottom: calc(100% + 10px);
    inset-inline-start: 0;
    width: min(300px, calc(450px - 32px));
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.14);
    z-index: 100;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.ep-search {
    padding: 10px 10px 6px;
    border-bottom: 1px solid #f3f4f6;
}

.ep-search__input {
    width: 100%;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 5px 10px;
    font-size: 0.82rem;
    outline: none;
    background: #f9fafb;
    color: #374151;
    transition: border-color 0.18s;
}

.ep-search__input:focus {
    border-color: #9ca3af;
    background: #fff;
}

.ep-cats {
    display: flex;
    gap: 2px;
    padding: 6px 8px;
    border-bottom: 1px solid #f3f4f6;
    overflow-x: auto;
    scrollbar-width: none;
}

.ep-cats::-webkit-scrollbar {
    display: none;
}

.ep-cats__btn {
    flex-shrink: 0;
    width: 34px;
    height: 30px;
    border: none;
    border-radius: 8px;
    background: transparent;
    cursor: pointer;
    font-size: 16px;
    line-height: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.15s;
}

.ep-cats__btn:hover {
    background: #f3f4f6;
}

.ep-cats__btn--active {
    background: #ede9fe;
}

.ep-grid {
    display: grid;
    grid-template-columns: repeat(8, 1fr);
    gap: 1px;
    padding: 6px 8px;
    max-height: 200px;
    overflow-y: auto;
    scrollbar-width: thin;
    scrollbar-color: #d1d5db transparent;
}

.ep-grid__btn {
    border: none;
    background: transparent;
    cursor: pointer;
    font-size: 18px;
    line-height: 1;
    padding: 4px 2px;
    border-radius: 6px;
    transition: background 0.12s, transform 0.1s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.ep-grid__btn:hover {
    background: #f3f4f6;
    transform: scale(1.25);
}
</style>
