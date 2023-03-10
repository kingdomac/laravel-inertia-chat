<script setup>
import { usePage } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { ref, onMounted, nextTick, reactive, computed, watch } from "vue";
import moment from "moment";
import PrivatChat from "@/Components/PrivatChat.vue";
import {
  broadcastRoomMessage,
  markAsReadMessages,
  countUsersNewMessages,
  countUserNewMessages,
} from "@/services/MessagesService";

const inputMessage = ref("");
const inputRef = ref(null);
const messagesBox = ref(null);
// const channel = Echo.join("presence.chatroom");
const authUser = computed(() => usePage().props.auth.user);

const messages = reactive([]);
const onlineUsers = ref([]);
const typingMessage = ref(null);
const selectedUser = ref(null);
const showModalChat = ref(false);
const isSendingMessage = ref(false);

onMounted(() => {
  inputRef.value.focus();
  connectToPresenceChannel();
});

const connectToPresenceChannel = () => {
  Echo.leave("presence.chatroom");
  Echo.join("presence.chatroom")
    .here(async (users) => {
      try {
        users = users.filter((user) => user.id !== authUser.value.id);
        const onlineUsersId = users.map((element) => element.id);

        const response = await countUsersNewMessages(onlineUsersId);
        users.map(
          (element) => (element.newMessages = response.data[element.id])
        );
        onlineUsers.value = [...users];
        connecToChannels();
      } catch (error) {}
    })
    .joining(async (user) => {
      try {
        const response = await countUserNewMessages(user.id);
        user.newMessages = response.data[user.id];
        onlineUsers.value.push(user);
        messages.push({
          user: user,
          message: "<span class='text-blue-500'>has joined the room</span>",
          date: moment(new Date()).format("ddd MM/YY, hh:mm:ss a"),
        });
      } catch (error) {}
    })
    .leaving((user) => {
      const newOnlineUsers = onlineUsers.value.filter(
        (olineUser) => olineUser.id !== user.id
      );
      onlineUsers.value = [...newOnlineUsers];

      messages.push({
        user: user,
        message: "<span class='text-red-500'>has left the room</span>",
        date: moment(new Date()).format("ddd MM/YY, hh:mm:ss a"),
      });
    })
    .error((error) => {
      console.error(error);
    })
    .listen(".chatroom", (event) => {
      const response = {
        user: event.user,
        message: event.message,
        date: event.date,
      };
      messages.push(response);
      inputMessage.value = null;
    });
};

const broadCastMessage = async () => {
  const userInput = inputMessage.value;
  if (!userInput) return;
  try {
    isSendingMessage.value = true;
    await broadcastRoomMessage(userInput);
    messagesBox.value.scrollTop = messagesBox.value.scrollHeight;
  } catch (error) {
  } finally {
    isSendingMessage.value = false;
  }
};

watch(messages, (current, old) => {
  nextTick(() => {
    messagesBox.value.scrollTop = messagesBox.value.scrollHeight;
  });
});

const connecToChannels = () => {
  // build channel name
  let channelName = "private.message.";

  onlineUsers.value.forEach((userElement) => {
    let subStr = `${parseInt(userElement.id)}.${authUser.value.id}`;
    if (authUser.value.id > userElement.id)
      subStr = `${authUser.value.id}.${parseInt(userElement.id)}`;
    Echo.join(channelName + subStr)

      .error((error) => {
        console.error(error);
      })
      .listen(".privateChat", (event) => {
        onlineUsers.value.map((element) => {
          if (
            element.id === event.user.id &&
            event.user.id !== selectedUser.value?.id
          )
            element.newMessages += 1;
        });

        inputMessage.value = null;
      });
  });
  //loop to connect
};

// const connecToChannel = (user) => {
//   // build channel name
//   let channelName = "private.message.";

//  let subStr = `${parseInt(user.id)}.${authUser.value.id}`;
//     if (authUser.value.id > user.id)
//       subStr = `${authUser.value.id}.${parseInt(user.id)}`;
//     Echo.join(channelName + subStr)

//       .error((error) => {
//         console.error(error);
//       })
//       .listen(".privateChat", (event) => {
//         onlineUsers.value.map((element) => {
//           if (
//             element.id === event.user.id &&
//             event.user.id !== selectedUser.value?.id
//           )
//             element.newMessages += 1;
//         });

//         inputMessage.value = null;
//       });
//   //loop to connect
// };

const openUserChat = (user) => {
  selectedUser.value = user;
  showModalChat.value = true;
  onlineUsers.value.map(async (userOnline) => {
    if (userOnline.id === user.id) {
      try {
        await markAsReadMessages(userOnline.id);
        userOnline.newMessages = 0;
      } catch (error) {}
    }
  });
};

const closeUserChat = (user) => {
  selectedUser.value = null;
  onlineUsers.value.map(async (userOnline) => {
    if (userOnline.id === user.id) {
      try {
        await markAsReadMessages(userOnline.id);
        userOnline.newMessages = 0;
      } catch (error) {}
    }
  });
};
</script>

<template>
  <AuthenticatedLayout>
    <PrivatChat
      @close-user-chat="closeUserChat"
      v-if="selectedUser"
      :user="selectedUser"
    ></PrivatChat>

    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Chat Room
      </h2>

      <div class="flex">
        <div class="p-5">
          <div class="text-green-500">Online users</div>
          <div class="bg-gray-300 p-5 h-[80vh] overflow-y-scroll scroll-smooth">
            <div
              @click="openUserChat(onlineUser)"
              v-for="onlineUser in onlineUsers"
              :key="onlineUser.id"
              class="relative bg-green-500 p-3 rounded-md rounded-r-full mb-3 text-sm cursor-pointer"
            >
              <div
                v-if="onlineUser.newMessages > 0"
                :class="onlineUser.newMessages > 0 ? 'animate-bounce' : ''"
                class="absolute p-2 w-6 h-6 flex items-center justify-center -top-2 -right-1 text-xs bg-red-600 text-white rounded-full"
              >
                {{ onlineUser.newMessages }}
              </div>
              {{ onlineUser.name }}
            </div>
          </div>
        </div>
        <div class="flex-1 p:2 sm:p-6 justify-between flex flex-col h-screen">
          <div
            class="flex sm:items-center justify-between py-3 border-b-2 border-gray-200"
          >
            <div class="relative flex items-center space-x-4">
              <div class="relative">
                <span class="absolute text-green-500 right-0 bottom-0">
                  <svg width="20" height="20">
                    <circle cx="8" cy="8" r="8" fill="currentColor"></circle>
                  </svg>
                </span>
                <img
                  src="https://images.unsplash.com/photo-1549078642-b2ba4bda0cdb?ixlib=rb-1.2.1&amp;ixid=eyJhcHBfaWQiOjEyMDd9&amp;auto=format&amp;fit=facearea&amp;facepad=3&amp;w=144&amp;h=144"
                  alt=""
                  class="w-10 sm:w-16 h-10 sm:h-16 rounded-full"
                />
              </div>
              <div class="flex flex-col leading-tight">
                <div class="text-2xl mt-1 flex items-center">
                  <span class="text-gray-700 mr-3">{{ authUser.name }}</span>
                </div>
                <span class="text-lg text-gray-600">{{ authUser.email }}</span>
              </div>
            </div>

            <!-- <div class="flex items-center space-x-2">
            <button
              type="button"
              class="inline-flex items-center justify-center rounded-lg border h-10 w-10 transition duration-500 ease-in-out text-gray-500 hover:bg-gray-300 focus:outline-none"
            >
              <svg
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                class="h-6 w-6"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                ></path>
              </svg>
            </button>
            <button
              type="button"
              class="inline-flex items-center justify-center rounded-lg border h-10 w-10 transition duration-500 ease-in-out text-gray-500 hover:bg-gray-300 focus:outline-none"
            >
              <svg
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                class="h-6 w-6"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"
                ></path>
              </svg>
            </button>
            <button
              type="button"
              class="inline-flex items-center justify-center rounded-lg border h-10 w-10 transition duration-500 ease-in-out text-gray-500 hover:bg-gray-300 focus:outline-none"
            >
              <svg
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                class="h-6 w-6"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
                ></path>
              </svg>
            </button>
          </div> -->
          </div>
          <div
            ref="messagesBox"
            class="flex flex-col space-y-4 p-3 overflow-y-auto scrollbar-thumb-blue scrollbar-thumb-rounded scrollbar-track-blue-lighter scrollbar-w-2 scrolling-touch"
          >
            <div v-if="messages.length">
              <div
                v-for="(message, key) in messages"
                :key="`message-${key}`"
                class="chat-message mb-2"
              >
                <div
                  :class="authUser.id === message.user.id ? ' justify-end' : ''"
                  class="flex items-start"
                >
                  <div
                    class="flex flex-col space-y-2 text-xs max-w-xs mx-2 order-2 items-start"
                  >
                    <div>
                      <div
                        v-if="authUser.id != message.user.id"
                        class="flex-none"
                      >
                        {{ message.user.name }}
                      </div>
                      <div class="text-gray-400 text-xs mb-1">
                        {{ message.date }}
                      </div>
                      <span
                        :class="
                          authUser.id === message.user.id
                            ? 'bg-blue-600 text-white'
                            : 'bg-gray-300 text-gray-600'
                        "
                        class="px-4 py-2 rounded-lg inline-block rounded-bl-none"
                        v-html="message.message"
                      ></span>
                    </div>
                  </div>
                  <img
                    src="https://images.unsplash.com/photo-1549078642-b2ba4bda0cdb?ixlib=rb-1.2.1&amp;ixid=eyJhcHBfaWQiOjEyMDd9&amp;auto=format&amp;fit=facearea&amp;facepad=3&amp;w=144&amp;h=144"
                    alt="My profile"
                    class="w-6 h-6 rounded-full order-1"
                  />
                </div>
              </div>
            </div>
          </div>
          <div class="border-t-2 border-gray-200 px-4 pt-4 mb-2 sm:mb-0">
            <div class="relative flex">
              <!-- <span class="absolute inset-y-0 flex items-center">
              <button
                type="button"
                class="inline-flex items-center justify-center rounded-full h-12 w-12 transition duration-500 ease-in-out text-gray-500 hover:bg-gray-300 focus:outline-none"
              >
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                  class="h-6 w-6 text-gray-600"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"
                  ></path>
                </svg>
              </button>
            </span> -->

              <form @submit.prevent="broadCastMessage" class="w-full">
                <input
                  :disabled="isSendingMessage"
                  v-model.trim="inputMessage"
                  ref="inputRef"
                  type="text"
                  placeholder="Write your message!"
                  class="w-full focus:outline-none focus:placeholder-gray-400 text-gray-600 placeholder-gray-600 pl-12 bg-gray-200 rounded-md py-3"
                />
                <div
                  class="absolute z-10 right-0 items-center inset-y-0 hidden sm:flex"
                >
                  <!-- <button
                    type="button"
                    class="inline-flex items-center justify-center rounded-full h-10 w-10 transition duration-500 ease-in-out text-gray-500 hover:bg-gray-300 focus:outline-none"
                  >
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke="currentColor"
                      class="h-6 w-6 text-gray-600"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"
                      ></path>
                    </svg>
                  </button>
                  <button
                    type="button"
                    class="inline-flex items-center justify-center rounded-full h-10 w-10 transition duration-500 ease-in-out text-gray-500 hover:bg-gray-300 focus:outline-none"
                  >
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke="currentColor"
                      class="h-6 w-6 text-gray-600"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"
                      ></path>
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"
                      ></path>
                    </svg>
                  </button> -->
                  <!-- <button
                    type="button"
                    class="inline-flex items-center justify-center rounded-full h-10 w-10 transition duration-500 ease-in-out text-gray-500 hover:bg-gray-300 focus:outline-none"
                  >
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke="currentColor"
                      class="h-6 w-6 text-gray-600"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                      ></path>
                    </svg>
                  </button> -->
                  <button
                    type="submit"
                    class="inline-flex items-center justify-center rounded-lg px-4 py-3 transition duration-500 ease-in-out text-white bg-blue-500 hover:bg-blue-400 focus:outline-none"
                  >
                    <span class="font-bold">Send</span>
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      viewBox="0 0 20 20"
                      fill="currentColor"
                      class="h-6 w-6 ml-2 transform rotate-90"
                    >
                      <path
                        d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z"
                      ></path>
                    </svg>
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </template>
  </AuthenticatedLayout>
</template>
<style>
.scrollbar-w-2::-webkit-scrollbar {
  width: 0.25rem;
  height: 0.25rem;
}

.scrollbar-track-blue-lighter::-webkit-scrollbar-track {
  --bg-opacity: 1;
  background-color: #f7fafc;
  background-color: rgba(247, 250, 252, var(--bg-opacity));
}

.scrollbar-thumb-blue::-webkit-scrollbar-thumb {
  --bg-opacity: 1;
  background-color: #edf2f7;
  background-color: rgba(237, 242, 247, var(--bg-opacity));
}

.scrollbar-thumb-rounded::-webkit-scrollbar-thumb {
  border-radius: 0.25rem;
}
</style>
