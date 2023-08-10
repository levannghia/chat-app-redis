<template>
    <div class="card">
        <div class="card-header msg_head">
            <div class="bd-highlight">
                <div class="user_info">
                    <span>{{ currentRoom.name }}</span>
                </div>
                <div class="text-white ml-3">
                    {{ currentRoom.description }}
                </div>
            </div>
        </div>
        <div class="card-body msg_card_body" id="shared_room">
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
            <MessageItem v-for="message in messages.message.list" :key="message.id" :message="message"
                @showEmoji="showEmoji" />
        </div>
        <div class="card-footer">
            <div class="input-group">
                <textarea v-model="inputMessage" class="form-control type_msg" placeholder="Type your message..."
                    @keyup.enter="saveMessage" />
                <div class="input-group-append">
                    <span class="input-group-text send_btn" @click="saveMessage"><i
                            class="fas fa-location-arrow"></i></span>
                </div>
            </div>
        </div>

        <Emoji :emojiCoordinates="emojiCoordinates" :isShow="isShowEmoji" :selectedMessage="selectedMessage"
            @hideEmoji="hideEmoji" @selectEmoji="selectEmoji" />
    </div>
</template>
  
<script setup>
import MessageItem from './MessageItem.vue';
import Emoji from './Emoji.vue';
import { ref, defineEmits, onMounted, onBeforeUnmount } from 'vue';
import $ from 'jquery';

const props = defineProps({
    messages: {
        type: Object,
        required: true,
    },

    currentRoom: {
        type: Object,
        required: true
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

const inputMessage = ref('');
const emit = defineEmits(['saveMessage', 'showEmoji', 'hideEmoji', 'selectEmoji','getMessages'])

function saveMessage() {
    emit('saveMessage', inputMessage.value);
    inputMessage.value = '';
}

function showEmoji(message, event) {
    emit('showEmoji', message, event)
}
function hideEmoji() {
    emit('hideEmoji')
}
function selectEmoji(emoji) {
    emit('selectEmoji', emoji)
}

onMounted(() => {
    $('#shared_room').on('scroll', async () => {
      const scroll = $('#shared_room').scrollTop()
      if (scroll < 1 && props.messages.message.currentPage < props.messages.message.lastPage) {
        emit('getMessages', props.currentRoom.id, props.messages.message.currentPage + 1, true)
      }
    })
})

onBeforeUnmount(() => {
    $('#shared_room').off('scroll');
})
</script> 
  
<style></style>