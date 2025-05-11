<!-- ispravlenie oshibok gita -->
<template>
  <div class="chat-container">
    <div class="chat-header">
      <h2>Чат</h2>
      <button @click="logout" class = "logout">Выйти</button>
    </div>

    <div class="chat-messages">
      <template v-for="(groupedMessages, date) in groupByDate(messages)">
      <div v-if="groupedMessages.length > 0" class="message-day" key="date">
        {{ date }}
      </div>
      <div 
        class="message" 
        v-for="msg in groupedMessages" 
        :key="msg.id"
        :class="{ 'own-message': msg.user_id === currentUser.id }"
        @contextmenu.prevent="showContextMenu($event, msg)"
      >
        <strong v-if="msg.user_id !== currentUser.id">{{ msg.username }}</strong>
        <p>{{ msg.text }}</p>
        <span class="message-date">{{ formatTime(msg.created_at) }}</span>
      </div>
    </template>
    </div>

    <form @submit.prevent="sendMessage" class="chat-input">
      <input v-model="newMessage" placeholder="Start typing..." />
      <button type="submit"><img src="img/send.png" alt="Send" class="send-icon" /></button>
    </form>
    <p v-if="error" class="error">{{ error }}</p>
  </div>
  <link rel="stylesheet" href="chat.css" />
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const messages = ref([])
const newMessage = ref('')
const currentUser = JSON.parse(localStorage.getItem('currentUser'))
const error = ref('')

if (!currentUser) {
  router.push('/')
}
function groupByDate(messages) {
  const grouped = {};
  
  messages.forEach(msg => {
    const date = formatDate(msg.created_at); 
    if (!grouped[date]) {
      grouped[date] = []; 
    }
    grouped[date].push(msg); 
  });
  
  return grouped; 
}
function formatDate(dateString) {
  const date = new Date(dateString)
  const options = {
    hour: 'numeric',
    minute: 'numeric',
    hour12: true, 
  };
  return date.toLocaleTimeString([], options);
}
function formatTime(dateString) {
  const date = new Date(dateString)
  const options = {
    hour: 'numeric',
    minute: 'numeric',
    hour12: true, 
  };
  return date.toLocaleTimeString([], options);
}

async function loadMessages() {
  try {
    const response = await fetch('http://localhost/sites/2/chat-app/Server/index.php?action=messages')
    if (!response.ok) throw new Error('Network response was not ok')
    messages.value = await response.json()
  } catch (err) {
    console.error('Failed to load messages:', err)
    error.value = 'Ошибка загрузки сообщений'
  }
}

async function sendMessage() {
  if (!newMessage.value.trim()) return;
  
  try {

    console.log('id:', currentUser?.id);
    console.log('name:', currentUser?.username);
    if (!currentUser?.id || !currentUser?.username) {
      throw new Error('Данные пользователя неполные');
    }

    const messageData = {
      userId: currentUser.id,
      username: currentUser.username,
      text: newMessage.value.trim()
    };

    console.log('Отправляемые данные:', messageData);

    const response = await fetch('http://localhost/sites/2/chat-app/Server/index.php?action=messages', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(messageData),
    });

    const responseData = await response.json();
    console.log('Ответ сервера:', responseData);
    
    if (!responseData.success) {
      throw new Error(responseData.message || 'Ошибка сервера');
    }

    newMessage.value = '';
    await loadMessages(); 
    
  } catch (err) {
    console.error('Ошибка при отправке:', err);
    error.value = err.message;
  }
}

function logout() {
  
  localStorage.removeItem('currentUser')
  router.push('/')
}

onMounted(() => {
  loadMessages()
  setInterval(loadMessages, 5000)
})
</script>

<style scoped>

</style>
