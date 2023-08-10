<template>
    <div class="msg-item d-flex justify-content-end mb-4" :class="{'private': message.receiver }" v-if="message.sender.id === user.id">
        <div class="msg-actions d-flex" style="margin-right: 10px;">
            <div class="d-flex align-items-center text-white">
                <i class="far fa-smile" data-toggle="tooltip" data-placement="top" title="React" @click="showEmoji"></i>
            </div>
        </div>
        <div class="msg_container_send" :style="message.receiver ? `background-color: ${msgColor}` : ''" data-toggle="tooltip" data-placement="top" :title="message.created_at">
            {{ message.content }}
            <Reaction v-if="message.reactions.length" :reactions="message.reactions" />
        </div>
        <div class="img_cont_msg" data-toggle="tooltip" data-placement="top"
            :title="`${message.sender.name} (${message.sender.email})`">
            <img src="/images/current_user.jpg" class="rounded-circle user_img_msg">
        </div>
    </div>
    <div class="msg-item d-flex justify-content-start mb-4" v-else>

        <!-- <div class="img_cont_msg" data-toggle="tooltip" data-placement="top"
            :title="`${message.sender.name} (${message.sender.email})`">
            <img src="/images/other_user.jpg" class="rounded-circle user_img_msg">
        </div> -->
        <div class="img_cont_msg bg-white rounded-circle d-flex justify-content-center align-items-center"
            data-toggle="tooltip" data-placement="top" :title="`${message.sender.name} (${message.sender.email})`">
            <span class="rounded-circle d-flex justify-content-center align-items-center"
                :style="{'backgroundColor': message.sender.color, 'height':'100%', 'width': '100%'}">{{ message.sender.name[0].toUpperCase() }}</span>
        </div>
        <div class="msg_container" data-toggle="tooltip" data-placement="top" :title="message.created_at">
            {{ message.content }}

            <Reaction v-if="message.reactions.length" :reactions="message.reactions" />
        </div>

        <div class="msg-actions d-flex" style="margin-left: 10px;">
            <div class="d-flex align-items-center text-white">
                <i class="far fa-smile" data-toggle="tooltip" data-placement="top" title="React" @click="showEmoji"></i>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, defineProps, onMounted } from 'vue';
import sanitizeHtml from 'sanitize-html'
import Reaction from './Reaction.vue';
import $ from 'jquery';

const user = ref(window.__app__.user);
const props = defineProps({
    message: {
        required: true,
    },
    msgColor:{
        type: String
    }
});

onMounted(() => {
    // $(function () {
    //   $('[data-toggle="tooltip"]').tooltip();
    // })
});

const emit = defineEmits(['showEmoji'])

function showEmoji(event) {
    emit('showEmoji', props.message, event)
}

</script>

<style>
.bot-notification {
    max-width: 100% !important;
    width: 100%;
    border-radius: 4px;
    background-color: #043244;
}

.img_cont_msg{
    width: 40px;
    height: 40px;
}
.msg-item.private .msg-actions i:hover {
    color: #054760 !important;
}

.msg-item:hover .msg-actions {
    opacity: 1;
}

.msg-item .msg-actions {
    opacity: 0;
    transition: opacity 0.2s;
}

.msg-item .msg-actions i {
    color: lightgray;
    cursor: pointer;
    transition: color 0.2s;
}

.msg-item .msg-actions i:hover {
    color: white;
}

.highlightText {
    color: #f1765e;
    font-weight: 600;
    cursor: pointer;
}

.bg-gray {
    background-color: #f1f0f0;
    color: #444950;
}
</style>