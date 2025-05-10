<template>
  <div class="chat-container">
    <h2>Вход</h2>
    <form @submit.prevent="handleLogin">
      <div>
        <label for="username">Имя пользователя</label>
        <input id="username" v-model="username" required />
      </div>
      <div>
        <label for="password">Пароль</label>
        <input id="password" type="password" v-model="password" required />
      </div>
      <button type="submit">Войти</button>
      <p>
        Нет аккаунта?
        <router-link to="/register">Зарегистрироваться</router-link>
      </p>
    </form>
    <p v-if="error" class="error">{{ error }}</p>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'

const username = ref('')
const password = ref('')
const error = ref('')
const router = useRouter()

async function handleLogin() {
  try {
    const response = await fetch('http://localhost/sites/2/chat-app/Server/index.php?action=login', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        username: username.value,
        password: password.value
      })
    });

    //
    const text = await response.text();
    console.log("Raw server response:", text);


    try {
      const data = JSON.parse(text); // Пробуем распарсить вручную
      if (data.success) {
        localStorage.setItem('currentUser', JSON.stringify({
          id: data.user.id,
          username: data.user.username}))
          router.push('/chat')
      } else {
        error.value = data.message || 'Ошибка входа';
      }
    } catch (e) {
      throw new Error(`Invalid JSON: ${text}`);
    }
  } catch (err) {
    console.error('Login error:', err);
    error.value = 'Ошибка соединения с сервером'
  }
}
</script>

<style scoped>
form {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

input {
  padding: 10px;
  border-radius: 8px;
  border: none;
  font-size: 16px;
}

button {
  padding: 10px;
  background-color: #4a90e2;
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
}

button:hover {
  background-color: #357ab8;
}
.error {
  color: red;
  margin-top: 10px;
}
</style>
