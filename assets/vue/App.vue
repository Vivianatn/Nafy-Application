<template>
  <div id="app" :class="classesApp">
    <AppHeader v-if="afficherNavigation" />
    <main class="content" :class="classesContenu">
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
    <MenuBottom v-if="afficherNavigation" />
    <NotificationPopup />
  </div>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import AppHeader from './components/AppHeader.vue'
import MenuBottom from './components/MenuBottom.vue'
import NotificationPopup from './components/NotificationPopup.vue'
import { useAuth } from './composables/auth'

const route = useRoute()
const { session, verifierSession } = useAuth()

const estPageAuth = computed(() => Boolean(route.meta.guest))
const afficherNavigation = computed(() => session.value.connecte && !estPageAuth.value)

const classesApp = computed(() => ({
  'app--auth': estPageAuth.value,
  'app--connecte': afficherNavigation.value,
}))

const classesContenu = computed(() => ({
  'content--auth': estPageAuth.value,
  'content--avec-tabbar': afficherNavigation.value,
}))

onMounted(verifierSession)
</script>

<style lang="scss">
@use '../styles/variables' as *;

.app--auth .content--auth {
  padding: 0;
  min-height: 100vh;
  min-height: 100dvh;
}

@media (max-width: #{$bp-desktop - 1px}) {
  .app--connecte .content--avec-tabbar {
    padding-bottom: calc(#{$space-xl} + #{$tabbar-height} + env(safe-area-inset-bottom, 0px));
  }
}

.app__chargement {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: $space-md;
  color: $color-muted;
  text-align: center;
  padding: $space-xl;
  animation: fade-in 0.55s ease-out;

  .spinner {
    margin: 0 auto;
  }

  span:not(.spinner) {
    animation: pulse-soft 1.5s ease infinite;
  }
}
</style>
