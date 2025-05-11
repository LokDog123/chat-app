<template>
  <div class="chat-container">
    <div class="chat-header">
      <h2>Чат</h2>
    </div>
    <form @submit.prevent="handleRegister">
      <div>
        <label for="username">Login</label>
        <input id="username" v-model="username" required />
      </div>
      <div>
        <label for="password">Password</label>
        <input id="password" type="password" v-model="password" required />
      </div>
      <button type="submit">Register</button>
      <p>
        Already have an account?
        <router-link to="/">Login</router-link>
      </p>
    </form>
    <p v-if="error" class="error">{{ error }}</p>
  </div>
  <link rel="stylesheet" href="login.css" />
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'

const username = ref('')
const password = ref('')
const error = ref('')
const router = useRouter()

async function handleRegister() {
  try { 
    const response = await fetch('http://localhost/sites/2/chat-app/Server/index.php?action=register', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        username: username.value,
        password: password.value
      })
    })

    const text = await response.text();
    console.log("Raw server response:", text);

    try {
      const data = JSON.parse(text); 
      if (data.success) {
          router.push('/')
      } else {
        error.value = data.message || 'Ошибка регистрации';
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

</style>
