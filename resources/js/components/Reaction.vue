<template>
    <div class="reaction-container d-flex justify-content-center align-items-center">
        <div class="reaction-item d-flex" v-for="e in reactionFormat" :key="e.id">
            <img :src="e.src" :alt="e.alt">
        </div>
        <div class="reaction-item d-flex">
            <span class="total ml-1">
                {{ reactions.length }}
            </span>
        </div>
    </div>
</template>
  
<script setup>
import { defineProps, computed, ref } from 'vue'

const props = defineProps({
    reactions: {
        type: Array
    }
})
const emojis = ref(window.__app__.emojis)
const reactionFormat = computed(() => {
    const listReactionEmojis = []

    for (const e of emojis.value) {
        const index = props.reactions.findIndex(r => r.emoji_id === e.id)

        if (index > -1) {
            const indexInList = listReactionEmojis.findIndex(item => item.id === e.id)

            if (indexInList === -1) {
                listReactionEmojis.push(e)
            }
        }
    }

    return listReactionEmojis
});

</script>
  
<style lang="scss">
.reaction-container {
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
    background: #fff;
    border-radius: 15px;
    position: absolute;
    right: 0;
    padding: 0 4px;
}

.reaction-container .reaction-item img {
    width: 14px;
    height: 14px;
}

.reaction-container .reaction-item .total {
    color: #0084ff;
    font-weight: 600;
    font-size: 13px;
}

.reaction-container .reaction-item:not(:first-child):not(:last-child) {
    margin-left: 4px;
}
</style>
  