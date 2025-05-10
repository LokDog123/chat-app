<template>
  <div class="chat-container">
    <div class="chat-header">
      <h2>Чат</h2>
      <button @click="logout">Выйти</button>
    </div>

    <div class="chat-messages">
      <div 
        class="message" 
        v-for="msg in messages" 
        :key="msg.id"
        :class="{ 'own-message': msg.user_id === currentUser.id }"
      >
        <strong>{{ msg.username }}:</strong> {{ msg.text }}
        <span class="message-date">{{ formatDate(msg.created_at) }}</span>
      </div>
    </div>

    <form @submit.prevent="sendMessage" class="chat-input">
      <input v-model="newMessage" placeholder="Введите сообщение..." />
      <button type="submit">Отправить</button>
    </form>
    <p v-if="error" class="error">{{ error }}</p>
  </div>
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

function formatDate(dateString) {
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
.chat-container {
  display: flex;
  flex-direction: column;
  height: 100vh;
  padding: 20px;
  max-width: 800px;
  margin: 0 auto;
}

.chat-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.chat-messages {
  flex: 1;
  overflow-y: auto;
  padding: 15px;
  background: #1e1e2f;
  border-radius: 8px;
  margin-bottom: 15px;
}

.message {
  width: 35%;
  margin-bottom: 12px;
  padding: 8px 12px;
  border-radius: 6px;
  background: #2a2a3a;
}

.own-message {
  margin-left: 50%;
  width: 35%;
  background: #4a90e2;
  align-self: flex-end;
}

.message-date {
  display: block;
  margin-left: 50px;
  font-size: 0.8em;
  color: #ddd;
  margin-top: 4px;
  width: auto;
}

.chat-input {
  display: flex;
  gap: 10px;
}

.chat-input input {
  flex: 1;
  padding: 10px;
  border-radius: 6px;
  border: 1px solid #444;
}

.chat-input button {
  padding: 10px 20px;
  background: #4a90e2;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
}

.error {
  color: #ff6b6b;
  margin-top: 10px;
  text-align: center;
}
</style>
