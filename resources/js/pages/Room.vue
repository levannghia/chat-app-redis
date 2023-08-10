<template>
  <div class="row justify-content-center h-100">
    <div class="col-md-8 chat">
      <SharedRoom :messages="roomChat" :currentRoom="currentRoom" :selectedMessage="selectedMessage"
        :isShowEmoji="isShowEmoji" :emojiCoordinates="emojiCoordinates" @saveMessage="saveMessage" @showEmoji="showEmoji"
        @hideEmoji="hideEmoji" @selectEmoji="selectEmoji" @getMessages="getMessage" />
    </div>
    <div class="col-md-4 chat">
      <ListUser :usersOnline="usersOnline" @selectReceiver="selectReceiver" />
    </div>

    <PrivateChat v-if="privateChat.selectedReceiver" :messages="privateChat" :selectedMessage="selectedMessage"
      :isShowEmoji="isShowEmoji" :emojiCoordinates="emojiCoordinates" @closePrivateChat="closePrivateChat"
      @saveMessage="saveMessage" @focusPrivateInput="focusPrivateInput" @showEmoji="showEmoji" @hideEmoji="hideEmoji"
      @selectEmoji="selectEmoji" @getMessages="getMessage" />
  </div>
</template>

<script setup>
import SharedRoom from '../components/SharedRoom.vue';
import ListUser from '../components/ListUser.vue'
import axios from 'axios';
import PrivateChat from '../components/PrivateChat.vue';
import { reactive, ref, nextTick, onBeforeUnmount, computed, watch } from 'vue';
import { useRoute } from 'vue-router';
import sanitizeHtml from 'sanitize-html';
import $ from 'jquery';

const user = ref(window.__app__.user);
const route = useRoute();
const rooms = window.__app__.rooms;
const currentRoom = ref({});
const roomChat = ref({
  message: {
    isLoading: false,
    list: [],
    currentPage: 0,
    perPage: 0,
    total: 0,
    lastPage: 0,
    newMessageArrived: 0 // number of new messages we just got (use for saving scroll position)
  }
});

const privateChat = ref({
  selectedReceiver: null,
  isPrivateChatExpand: false,
  isSelectedReceiverTyping: false,
  hasNewMessage: false,
  isSeen: null, // null: no new message, false: a message is waiting to be seen, true: user seen message (should display "Seen at..")
  seenAt: '',
  roomId: '',
  isOnline: true,
  message: {
    isLoading: false,
    list: [],
    currentPage: 0,
    perPage: 0,
    total: 0,
    lastPage: 0,
    newMessageArrived: 0 // number of new messages we just got (use for saving scroll position)
  }
})

const usersOnline = ref([]);
const emojiCoordinates = ref({
  x: 0,
  y: 0
});
const isShowEmoji = ref(false);
const selectedMessage = ref(null);

const index = rooms.findIndex(item => item.id === parseInt(route.params.roomId));
if (index > -1) {
  currentRoom.value = rooms[index];
  Echo.join(`room.${currentRoom.value.id}`)
    .here((users) => { // gọi ngay thời điểm ta join vào phòng, trả về tổng số user hiện tại có trong phòng (cả ta)
      usersOnline.value = users;
    })
    .joining((user) => { // gọi khi có user mới join vào phòng
      usersOnline.value.push(user);
      if (privateChat.value.selectedReceiver && privateChat.value.selectedReceiver.id === user.id) {
        privateChat.value.isOnline = true
      }
    })
    .leaving((user) => { // gọi khi có user mới join vào phòng
      const index = usersOnline.value.findIndex(item => item.id === user.id);
      if (index > -1) {
        usersOnline.value.splice(index, 1);
      }
      if (privateChat.value.selectedReceiver && privateChat.value.selectedReceiver.id === user.id) {
        privateChat.value.isOnline = false
      }
    })
    .listen('MessagePosted', (e) => {
      roomChat.value.message.list.push(e.message);
      scrollToBottom(document.getElementById('shared_room'), true);
    })
    .listen('MessageReacted', (e) => {
      onOtherUserReaction(e.reaction, "share");
    });


  Echo.private(`room.${user.value.id}`) // listen to user's own room (in order to receive all private messages from other users)
    .listen('MessagePosted', e => {
      if (privateChat.value.selectedReceiver && e.message.sender.id === privateChat.value.selectedReceiver.id) {
        privateChat.value.message.list.push(e.message)
        privateChat.value.isSeen = null // when receive new private message, considered user have seen -> reset isSeen to inital state
        privateChat.value.hasNewMessage = true // notify user there's new message
        scrollToBottom(document.getElementById('private_room'), true)
      } else { // if private chat window doens't open, then we set the badge in ListUser
        const index = usersOnline.value.findIndex(item => item.id === e.message.sender.id)
        if (index > -1) {
          usersOnline.value[index].new_messages++
        }
      }
    });
}

getMessage(currentRoom.value.id);

async function getMessage(room, page = 1, loadMore = false) {
  const isPrivate = room.toString().includes('__');
  const chat = isPrivate ? privateChat.value : roomChat.value
  try {
    chat.message.isLoading = true
    const response = await axios.get(`/message?room=${room}&page=${page}`)

    chat.message.list = [...response.data.data.reverse(), ...chat.message.list]
    chat.message.currentPage = response.data.current_page
    chat.message.perPage = response.data.per_page
    chat.message.lastPage = response.data.last_page
    chat.message.total = response.data.total
    chat.message.newMessageArrived = response.data.data.length
    if (loadMore) {
      nextTick(() => {
        const el = $(isPrivate ? '#private_room' : '#shared_room')
        const lastFirstMessage = el.children().eq(chat.message.newMessageArrived - 1)
        el.scrollTop(lastFirstMessage.position().top - 10)
      })
    } else {
      scrollToBottom(document.getElementById(isPrivate ? 'private_room' : 'shared_room'), false)
    }
  } catch (error) {
    console.log(error)
  } finally {
    chat.message.isLoading = false
  }

}

async function saveMessage(content, receiver = null) {
  try {
    if ((!receiver && !content.trim().length)) {
      return
    }

    // clean data before save to DB
    content = sanitizeHtml(content).trim()

    if (!content.length) {
      return
    }

    const response = await axios.post('/message', {
      receiver,
      room: receiver ? null : currentRoom.value.id,
      content: content
    })

    if (receiver) {
      privateChat.value.message.list.push(response.data.message)
      privateChat.value.isSeen = false // waiting for other to seen this message
      Echo.private(`room.${privateChat.value.roomId}`)
        .whisper('typing', {
          user: user.value.id,
          isTyping: false
        });
    } else {
      roomChat.value.message.list.push(response.data.message)
    }
    scrollToBottom(document.getElementById(`${receiver ? 'private' : 'shared'}_room`), true)
  } catch (error) {
    console.log(error)
  }
}

function focusPrivateInput() {
  const input = document.getElementById('private_input')
  if (input) { // incase we toggle private chat then this input will be removed
    input.focus()
    Echo.private(`room.${privateChat.value.roomId}`)
      .whisper('seen', {
        user: user.value,
        seen: true,
        time: new Date()
      })
    privateChat.value.hasNewMessage = false // set this to false as now user is focusing the chat
    const index = usersOnline.value.findIndex(item => item.id === privateChat.value.selectedReceiver.id)
    if (index > -1) {
      usersOnline.value[index].new_messages = 0
    }
  }
}

async function selectReceiver(receiver) {
  if (user.value.id == receiver.id) {
    return
  }

  resetPrivateChat()
  const roomId = user.value.id > receiver.id ? `${receiver.id}__${user.value.id}` : `${user.value.id}__${receiver.id}`
  privateChat.value.selectedReceiver = receiver
  privateChat.value.isPrivateChatExpand = true
  privateChat.value.roomId = roomId
  Echo.private(`room.${roomId}`)
    .listenForWhisper('typing', (e) => {
      privateChat.value.isSelectedReceiverTyping = e.isTyping
      scrollToBottom(document.getElementById('private_room'), true)
    })
    .listenForWhisper('seen', (e) => {
      if (privateChat.value.isSeen === false) { // check if user waiting for his message to be seen
        privateChat.value.isSeen = true
        privateChat.value.seenAt = e.time
        scrollToBottom(document.getElementById('private_room'), true)
      }
    })
    .listen('MessageReacted', e => {
      onOtherUserReaction(e.reaction, 'private')
    })
  await getMessage(roomId) // need to await until messages are loaded first then we are able to focus the input below
}

function closePrivateChat() {
  resetPrivateChat()
}

function resetPrivateChat() { // reset private chat when change to another user
  privateChat.value.message.list = []
  privateChat.value.selectedReceiver = null
  privateChat.value.isPrivateChatExpand = false
  privateChat.value.isSelectedReceiverTyping = false
  privateChat.value.hasNewMessage = false
  privateChat.value.isSeen = null // null: no new message, false: a message is waiting to be seen, true: user seen message (should display "Seen at..")
  privateChat.value.seenAt = ''
  privateChat.value.roomId = ''
  privateChat.value.isOnline = true
}

function showEmoji(message, event) {
  emojiCoordinates.value.x = event.clientX;
  emojiCoordinates.value.y = event.clientY;
  isShowEmoji.value = true;
  selectedMessage.value = message;
}

function hideEmoji() {
  isShowEmoji.value = false
  selectedMessage.value = null
}

async function selectEmoji(emoji) {
  try {
    const response = await axios.post('/reactions', {
      msg_id: selectedMessage.value.id,
      user_id: user.value.id,
      emoji_id: emoji.id
    })
    const index = selectedMessage.value.reactions.findIndex(item => item.user_id === user.value.id)
    if (index > -1) {
      const reaction = selectedMessage.value.reactions[index]
      if (emoji.id === reaction.emoji_id) { // deactive
        selectedMessage.value.reactions.splice(index, 1)
      } else {
        reaction.emoji_id = emoji.id
      }
    } else { // user first react
      const { reaction } = response.data
      selectedMessage.value.reactions.push({ ...reaction, user: user })
    }
    hideEmoji()
  } catch (error) {
    console.log(error)
  }
}

function onOtherUserReaction(reaction, room) {
  let listMessage = []
  if (room === 'share') {
    listMessage = roomChat.value.message.list
  } else {
    listMessage = privateChat.value.message.list
  }
  const messageIndex = listMessage.findIndex(m => m.id === reaction.msg_id)

  if (messageIndex > -1) {
    const message = listMessage[messageIndex]
    const index = message.reactions.findIndex(item => item.user.id === reaction.user.id)

    if (index > -1) {
      const r = message.reactions[index]
      if (reaction.emoji_id === r.emoji_id) { // deactive
        message.reactions.splice(index, 1)
      } else {
        r.emoji_id = reaction.emoji_id
      }
    } else {
      message.reactions.push({ ...reaction, user: reaction.user })
    }
  }
}

async function scrollToBottom(element, animate = true) {
  if (!element) {
    return
  }
  await nextTick(() => { // run after Vue finishes updating the DOM
    if (animate) {
      $(element).animate(
        { scrollTop: element.scrollHeight },
        { duration: 'medium', easing: 'swing' }
      )
    } else {
      $(element).scrollTop(element.scrollHeight);
    }
  })
}

// const totalUnreadPrivateMessages = computed(() => {
//   let count = 0
//   usersOnline.value.forEach(item => {
//     count += item.new_messages
//   })
//   return count
// })

// console.log(totalUnreadPrivateMessages);
// watch(totalUnreadPrivateMessages, (value) => {
//   value = totalUnreadPrivateMessages.value;
//   // console.log(value);
//   if (value > 0) {
//     document.title = `${value > 0 ? '(' + value + ')' : ''} - Tin nhắn mới`
//   } else {
//     document.title = "Laravel app chat"
//   }
// })

onBeforeUnmount(() => {
  if (privateChat.value.selectedReceiver) { // leave private chat if current has
    Echo.leave(`room.${privateChat.value.roomId}`);
  }
  Echo.leave(`room.${currentRoom.value.id}`);
});

</script>