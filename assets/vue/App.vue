<template>
  <div id="app" :class="{ 'app--auth': estPageAuth }">
    <MenuBurger v-if="session.connecte" />
    <main class="content" :class="{ 'content--auth': estPageAuth }">
      <div v-if="!session.initialise" class="app__chargement">
        <span class="spinner" aria-hidden="true"></span>
        <span>Chargement…</span>
      </div>
      <router-view v-else v-slot="{ Component }">
        <transition name="page-slide" mode="out-in">
          <component :is="Component" />
        </transition>
      </router-view>
    </main>
    <NotificationPopup />
  </div>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import MenuBurger from './views/MenuView.vue'
import NotificationPopup from './components/NotificationPopup.vue'
import { useAuth } from './composables/auth'

const route = useRoute()
const { session, verifierSession } = useAuth()

const estPageAuth = computed(() => Boolean(route.meta.guest))

onMounted(verifierSession)
</script>

<style lang="scss">
@use '../styles/variables' as *;

.app--auth .content--auth {
  padding: 0;
  min-height: 100vh;
  min-height: 100dvh;
}

.app__chargement {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: $space-md;
  color: $color-muted;
  text-align: center;
  padding: $space-xl;
  animation: fade-in 0.4s ease;

  .spinner {
    margin: 0 auto;
  }

  span:not(.spinner) {
    animation: pulse-soft 1.5s ease infinite;
  }
}
</style>
