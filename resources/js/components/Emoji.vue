<template>
    <transition name="slide-fade">
        <div class="emoji-container d-flex align-items-center"
            :style="{ left: `${emojiCoordinates.x}px`, top: `${emojiCoordinates.y - 60}px` }" v-if="isShow">
            <div class="emoji-item" v-for="e in emojis" :key="e.src" @click="selectEmoji(e)">
                <div class="emoji-text">
                    {{ e.name }}
                </div>
                <img :src="e.src" :alt="e.alt">
                <div class="dot" v-if="userReaction.emojiId === e.id"></div>
            </div>
            <div class="overlay"></div>
        </div>
    </transition>
</template>

<script setup>
import $ from 'jquery';
import { defineEmits, defineProps, ref, onMounted, onBeforeUnmount, computed } from 'vue';

const props = defineProps({
    emojiCoordinates: {
        type: Object,
    },

    isShow: {
        type: Boolean,
    },

    selectedMessage: {
        type: Object
    }

});

const emojis = ref(window.__app__.emojis);
const user = ref(window.__app__.user);
const emit = defineEmits(['hideEmoji', 'selectEmoji']);

onMounted(() => {
    $(document).on('click', (e) => {
        const container = $('.emoji-container')

        if ($(e.target).attr('class') === 'far fa-smile') {
            return
        }
        // check if the clicked area is dropDown or not
        if (props.isShow && container.has(e.target).length === 0) {
            emit('hideEmoji');
        }
    })

    $(window).scroll(() => {
        if (props.isShow) {
            emit('hideEmoji');
        }
    })

    $(window).resize(() => {
        if (props.isShow) {
            emit('hideEmoji');
        }
    })

    $('#shared_room').scroll(() => {
        if (props.isShow) {
            emit('hideEmoji');
        }
    })
})

const userReaction = computed(() => {
    let emojiId = -1
    let reactionId = -1
    if (props.selectedMessage) {
        for (const r of props.selectedMessage.reactions) {
            if (r.user.id === user.value.id) {
                emojiId = r.emoji_id
                reactionId = r.id
                break
            }
        }
    }

    return { emojiId, reactionId }

})


function selectEmoji(e) {
    emit('selectEmoji', e);
}

onBeforeUnmount(() => {
    $(document).off('click');
    $(window).off('scroll');
    $(window).off('resize');
    $('#shared_room').off('scroll');
})

</script>

<style>
.emoji-container {
    border-radius: 20px;
    box-shadow: 0 2px 4px 1px rgba(0, 0, 0, 0.1);
    background: white;
    padding: 7px 5px;
    position: fixed;
    transform: translateX(-50%);
}

.emoji-container .emoji-item {
    cursor: pointer;
    transition: all 0.2s ease-in-out;
    padding: 0 4px;
    position: relative;
}

.emoji-container .emoji-item:hover {
    transform: scale(1.3);
    transform-origin: bottom;
}

.emoji-container .emoji-item:hover .emoji-text {
    opacity: 1;
}

.emoji-container .emoji-item img {
    height: 32px;
    width: 32px;
}

.emoji-container .emoji-item .emoji-text {
    transition: opacity 0.2s;
    opacity: 0;
    position: absolute;
    top: -20px;
    background: rgba(0, 0, 0, 0.75);
    border-radius: 10px;
    color: #fff;
    font-size: 11px;
    padding: 0 6px;
}

.emoji-container .emoji-item .dot {
    width: 4px;
    height: 4px;
    background-color: #0084ff;
    border-radius: 50%;
    left: 50%;
    bottom: -5px;
    position: absolute;
}

.slide-fade-enter-active,
.slide-fade-leave-active {
    transition: opacity 0.25s, transform 0.25s;
}

.slide-fade-enter {
    opacity: 0;
    transform: translate(-50%, 20px);
}

.slide-fade-leave-to {
    opacity: 0;
    transform: translate(-50%, 20px);
}
</style>