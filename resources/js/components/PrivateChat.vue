<template>
    <div class="private-message-container" :class="messages.isPrivateChatExpand ? 'expand' : ''"
        @click="emit('focusPrivateInput')">
        <div class="chat-header d-flex p-2 border-bottom" :class="messages.hasNewMessage ? 'blink-anim' : ''"
            @click="messages.isPrivateChatExpand = !messages.isPrivateChatExpand">
            <div class="img_cont">
                <img src="https://static.turbosquid.com/Preview/001292/481/WV/_D.jpg" class="rounded-circle user_img"
                    style="width: 40px; height: 40px;">
                <span class="online_icon" :class="messages.isOnline ? 'online' : 'offline'" style="bottom: -3px;"></span>
            </div>
            <div class="user_info">
                <span style="color: black;">{{ messages.selectedReceiver.name }}</span>
                <!-- <p style="color: black;" class="mb-0">{{ messages.selectedReceiver.name }} left 50 mins ago</p> -->
            </div>
            <div class="color-picker">
                <i data-toggle="tooltip" data-placement="top" title="Message Color" class="fas fa-circle"
                    @click.stop="toggleColorPicker" style="cursor: pointer;" :style="{ 'color': msgColor }"></i>
            </div>
            <button class="btn-close" @click="emit('closePrivateChat')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="private-chat-body p-2" v-if="messages.isPrivateChatExpand" id="private_room">
            <div class="loading mb-2 text-center" v-if="messages.message.isLoading">
                <svg version="1.1" id="loader-1" xmlns="http://www.w3.org/2000/svg"
                    xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="40px" height="40px"
                    viewBox="0 0 50 50" style="enable-background:new 0 0 50 50;" xml:space="preserve">
                    <path fill="#FF6700"
                        d="M43.935,25.145c0-10.318-8.364-18.683-18.683-18.683c-10.318,0-18.683,8.365-18.683,18.683h4.068c0-8.071,6.543-14.615,14.615-14.615c8.072,0,14.615,6.543,14.615,14.615H43.935z"
                        transform="rotate(18.3216 25 25)">
                        <animateTransform attributeType="xml" attributeName="transform" type="rotate" from="0 25 25"
                            to="360 25 25" dur="0.6s" repeatCount="indefinite"></animateTransform>
                    </path>
                </svg>
            </div>
            <MessageItem v-for="message in messages.message.list" :key="message.id" :message="message" :msgColor="msgColor"
                @showEmoji="showEmoji" />
            <div class="d-flex justify-content-end" v-if="messages.isSeen">
                <i class="font-12px">Seen {{ messages.seenAt || toLocalTime }}</i>
            </div>
            <div class="d-flex justify-content-start mb-4" v-if="messages.isSelectedReceiverTyping">
                <div class="img_cont_msg">
                    <img src="https://static.turbosquid.com/Preview/001292/481/WV/_D.jpg"
                        class="rounded-circle user_img_msg">
                </div>
                <div class="msg_container">
                    <div id="wave">
                        <span class="dot"></span>
                        <span class="dot"></span>
                        <span class="dot"></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="text-input" v-if="messages.isPrivateChatExpand">
            <input v-model="inputMessage" id="private_input" type="text" class="w-100" placeholder="Type a message..."
                @keyup.enter="saveMessage" @input="onInputPrivateChange">
        </div>
        <Emoji :emojiCoordinates="emojiCoordinates" :isShow="isShowEmoji" :selectedMessage="selectedMessage"
            @hideEmoji="hideEmoji" @selectEmoji="selectEmoji" />

        <transition name="fade">
            <ColorPickerModal v-if="isShowColorPicker" :isShow="isShowColorPicker" @hide="toggleColorPicker"
                @selectColor="selectColor" />
        </transition>
    </div>
</template>

<script setup>
import $ from 'jquery'
import Emoji from './Emoji.vue'
import MessageItem from './MessageItem.vue'
import ColorPickerModal from './ColorPickerModal.vue'
import { throttle } from 'lodash'
import { onBeforeUnmount, onMounted, ref, defineEmits } from 'vue'

const props = defineProps({
    messages: {
        type: Object,
        required: true,
    },

    selectedMessage: {
        type: Object
    },

    isShowEmoji: {
        type: Boolean
    },

    emojiCoordinates: {
        type: Object
    }
});

const user = ref(window.__app__.user);
const emit = defineEmits(['saveMessage', 'showEmoji', 'hideEmoji', 'selectEmoji', 'getMessages', 'focusPrivateInput', 'closePrivateChat'])
const inputMessage = ref('');
const isShowColorPicker = ref(false);
const msgColor = ref('#42e274');
msgColor.value = localStorage.getItem('msgColor')

onMounted(() => {
    // const emitM = defineEmits(['focusPrivateInput']);

    $('#private_room').on('scroll', async () => {
        const scroll = $('#private_room').scrollTop()
        if (scroll < 1 && props.messages.message.currentPage < props.messages.message.lastPage) {
            emit('getMessages', props.messages.roomId, props.messages.message.currentPage + 1, true)
        }
    })
})


function saveMessage() {
    emit('saveMessage', inputMessage.value, props.messages.selectedReceiver.id)
    inputMessage.value = ''
}

const onInputPrivateChange = throttle(function () {
    Echo.private(`room.${props.messages.roomId}`)
        .whisper('typing', {
            user: user.value,
            isTyping: inputMessage.value.length > 0
        })
}, 2000) // keep tell other that we're typing because other user may close the private chat window then re open during we're typing

function showEmoji(message, event) {
    emit('showEmoji', message, event)
}

function hideEmoji() {
    emit('hideEmoji')
}

function selectEmoji(emoji) {
    emit('selectEmoji', emoji)
}

function toggleColorPicker() {
    isShowColorPicker.value = !isShowColorPicker.value
}

function selectColor(value) {
    localStorage.setItem('msgColor', value)
    msgColor.value = value;
    toggleColorPicker()
}

onBeforeUnmount(() => {
    Echo.leave(`room.${props.messages.roomId}`)
    $('#private_room').off('scroll')
})
</script>

<style>
.private-message-container {
    z-index: 1;
}

.color-picker {
    position: absolute;
    right: 45px;
    top: 17px;
}

.color-picker i {
    font-size: 22px;
}

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s;
}

.fade-enter,
.fade-leave-to {
    /* .fade-leave-active below version 2.1.8 */
    opacity: 0;
}
</style>