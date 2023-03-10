<template>
  <div class="fixed bottom-0 right-0">
    <div
      class="fixed z-50 w-full h-full top-0 left-0 flex items-center justify-center"
    >
      <div class="absolute w-full h-full bg-gray-900 opacity-50"></div>

      <div class="absolute z-40 max-h-full w-[50vw] h-[50vh]">
        <div class="container relative bg-white overflow-hidden md:rounded">
          <div class="absolute right-2 top-1 text-xl text-white font-bold">
            <div class="flex items-center">
              <!-- <div class="cursor-pointer mr-3">-</div> -->
              <div class="cursor-pointer" @click="$emit('closeUserChat', user)">
                x
              </div>
            </div>
          </div>
          <div
            class="pb-4 leading-none justify-between items-center font-medium text-sm bg-gray-100 border-b select-none"
          >
            <h3 class="mb-5 text-lg font-bold px-4 p-2 bg-blue-500 text-white">
              {{ user.name }}
            </h3>
            <div class="text-2xl hover:text-gray-600 cursor-pointer"></div>
            <div
              class="flex flex-col space-y-2 text-xs w-full mx-2 px-2 order-2 items-start"
            >
              <div
                ref="messagesBoxPrivate"
                class="flex flex-col w-full h-48 space-y-4 p-3 overflow-y-auto overscroll-none scrollbar-thumb-blue scrollbar-thumb-rounded scrollbar-track-blue-lighter scrollbar-w-2 scrolling-touch"
              >
                <div v-if="messages.length">
                  <div
                    v-for="(message, key) in messages"
                    :key="`message-${key}`"
                    class="mb-2"
                  >
                    <div
                      :class="
                        authUser.id === message.from_u ? ' justify-end' : ''
                      "
                      class="flex items-start"
                    >
                      <div
                        class="flex flex-col space-y-2 text-xs max-w-xs mx-2 order-2 items-start"
                      >
                        <div>
                          <div class="text-gray-400 text-xs mb-1">
                            {{ message.created_at }}
                          </div>
                          <span
                            :class="
                              authUser.id === message.from_u
                                ? 'bg-blue-600 text-white'
                                : 'bg-gray-300 text-gray-600'
                            "
                            class="px-4 py-2 rounded-lg inline-block rounded-bl-none"
                            v-html="message.body"
                          ></span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="text-xs text-blue-300 mb-1 ml-2 h-4">
                {{ typingMessage }}
              </div>
              <div class="relative flex w-full px-2">
                <form class="flex-1" @submit.prevent="sendMessage">
                  <input
                    :disabled="isSendingMessage"
                    @input="typing"
                    v-model.trim="inputMessage"
                    ref="inputRef"
                    type="text"
                    placeholder="Write your message!"
                    class="w-full focus:outline-none focus:placeholder-gray-400 text-gray-600 placeholder-gray-500 pl-5 bg-gray-200 rounded-md py-1"
                  />
                  <div
                    class="absolute z-10 right-0 items-center inset-y-0 hidden sm:flex"
                  >
                    <button
                      type="submit"
                      class="inline-flex items-center justify-center rounded-lg mr-1 px-2 py-1.5 transition duration-500 ease-in-out text-white bg-blue-500 hover:bg-blue-400 focus:outline-none"
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
      </div>
    </div>
  </div>
</template>
<script setup>
import { usePage } from "@inertiajs/vue3";
import { ref, onMounted, nextTick, computed, watch } from "vue";
import { getChatMessages, sendChatMessage } from "@/services/MessagesService";
import moment from "moment";

const props = defineProps({
  user: Object,
});

defineEmits(["closeUserChat"]);

const authUser = computed(() => usePage().props.auth.user);

const inputMessage = ref("");
const inputRef = ref(null);
const messagesBoxPrivate = ref(null);
const messages = ref([]);
const isSendingMessage = ref(false);

const buildChannelString = () => {
  if (authUser.value.id > parseInt(props.user.id))
    return `private.message.${authUser.value.id}.${parseInt(props.user.id)}`;
  return `private.message.${parseInt(props.user.id)}.${authUser.value.id}`;
};
const channel = Echo.join(
  buildChannelString()
  //   `private.message.2.3`
);
const typingMessage = ref(null);

const getMessages = async () => {
  try {
    const response = await getChatMessages(props.user.id);
    const result = response.data;

    messages.value = result;
    nextTick(() => {
      messagesBoxPrivate.value.scrollTop =
        messagesBoxPrivate.value?.scrollHeight;
    });
  } catch (error) {}
};

onMounted(() => {
  inputRef.value.focus();

  getMessages();
  channel
    .joining((user) => {
      messages.value.push({
        from_u: user.id,
        body: "<span class='text-blue-500'>has joined the room</span>",
        created_at: moment(new Date()).format("ddd MM/YY, hh:mm:ss a"),
        isRead: true,
      });
    })
    .leaving((user) => {
      messages.value.push({
        from_u: user.id,
        body: "<span class='text-red-500'>has left the room</span>",
        created_at: moment(new Date()).format("ddd MM/YY, hh:mm:ss a"),
        isRead: true,
      });
    })
    .error((error) => {
      console.error(error);
    })
    .listen(".privateChat", (event) => {
      //   console.log(event);
      const response = {
        from_u: event.user.id,
        body: event.message,
        created_at: event.date,
        isRead: event.isRead,
      };
      messages.value.push(response);
      nextTick(() => {
        messagesBoxPrivate.value.scrollTop =
          messagesBoxPrivate.value?.scrollHeight;
      });
      //inputMessage.value = null;
    })
    .listenForWhisper("start-typing", (event) => {
      typingMessage.value = `${event.user} is typing...`;
    })
    .listenForWhisper("stop-typing", (event) => {
      typingMessage.value = null;
    });
});

const sendMessage = async () => {
  const userInput = inputMessage.value;
  if (!userInput) return;
  try {
    isSendingMessage.value = true;
    await sendChatMessage(props.user.id, userInput);
    nextTick(() => {
      messagesBoxPrivate.value.scrollTop =
        messagesBoxPrivate.value?.scrollHeight;
    });
    inputMessage.value = null;
    channel.whisper("stop-typing");
  } catch (error) {
    //console.log(error.body);
  } finally {
    isSendingMessage.value = false;
  }
};

watch(messages, (current, old) => {
  nextTick(() => {
    messagesBoxPrivate.value.scrollTop = messagesBoxPrivate.value.scrollHeight;
  });
});

const typing = () => {
  if (!inputMessage.value.length) {
    channel.whisper("stop-typing");
  } else {
    channel.whisper("start-typing", {
      user: authUser.value.name,
    });
  }
};
</script>
<style scoped>
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
</style>>
