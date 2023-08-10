<template>
    <div class="row justify-content-center h-100">
        <div class="col-md-8 chat">
            <div class="card mb-sm-3 mb-md-0 contacts_card">
                <div class="card-header">
                    <div class="d-flex justify-content-between mb-2">
                        <h3 class="d-flex text-white">
                            Chatroom<span class="badge badge-success ml-2">{{
                                rooms.length
                            }}</span>
                        </h3>

                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#exampleModal">
                            Create Room
                        </button>

                    </div>
                    <div class="input-group">
                        <input v-model="searchQuery" type="text" placeholder="Search..." name=""
                            class="form-control search" />
                        <div class="input-group-prepend">
                            <span class="input-group-text search_btn"><i class="fas fa-search"></i></span>
                        </div>
                    </div>
                </div>
                <div class="card-body contacts_body">
                    <div class="contacts">
                        <li v-for="room in filteredRooms" :key="room.id">
                            <router-link :to="`/rooms/${room.id}`">
                                <div class="d-flex bd-highlight">
                                    <div class="user_info">
                                        <span>{{ room.name }}</span>
                                        <p v-if="room.description">{{ room.description }}</p>
                                    </div>
                                </div>
                            </router-link>
                        </li>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <room-modal title="Create Room Of User" @saveRoom="saveRoom">
        <div class="row g-3">
            <div class="col-12">
                <label for="inputEmail4" class="form-label">Room name</label>
                <input type="text" v-model="form.name" class="form-control" id="inputEmail4" required>
            </div>
            <div class="col-12">
                <label for="inputAddress" class="form-label">Description</label>
                <input type="text" v-model="form.description" class="form-control" id="inputAddress" placeholder="">
            </div>
            <div class="col-12">
                <label for="inputState" class="form-label">users</label>
                <select id="inputState" v-model="form.users" class="form-select" multiple>
                    <option selected>Choose...</option>
                    <option>...</option>
                </select>
            </div>
        </div>
    </room-modal>
</template>

<script setup>
import axios from "axios";
import { computed, ref, reactive, onMounted, nextTick, onBeforeUnmount } from "vue";
import RoomModal from "../components/RoomModal.vue";
import $ from 'jquery';

const rooms = ref(window.__app__.rooms);
const searchQuery = ref("");
const form = reactive({
    name: null,
    description: null,
    users: [],
})

Echo.channel('listRoom')
    .listen('RoomPosted', function (data) {
        let room = data.room;
        rooms.value.push(room);
    })

const filteredRooms = computed(() => {
    return rooms.value.filter((item) =>
        item.name.toLowerCase().includes(searchQuery.value.toLowerCase())
    );
});

async function saveRoom() {
    const response = await axios.post(`/room`, form);
    // closeModal()
    rooms.value.push(response.data.rooms);
}

async function closeModal() {
    await nextTick(() => { // run after Vue finishes updating the DOM
        // var myModal = new Modal(document.getElementById('exampleModal'), {
        //     keyboard: false
        // })
        
        // myModal.hide();
    })
}

onBeforeUnmount(() => {
    Echo.leave('listRoom');
})
</script>