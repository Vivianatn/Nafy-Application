<template>
  <div class="menu-burger">
    <button
      type="button"
      class="burger"
      :class="{ 'burger--open': ouvert }"
      :aria-expanded="ouvert"
      aria-label="Menu"
      @click="ouvert = !ouvert"
    >
      <span class="burger__ligne"></span>
      <span class="burger__ligne"></span>
      <span class="burger__ligne"></span>
    </button>

    <Transition name="overlay-fade">
      <div v-if="ouvert" class="overlay" @click="fermer"></div>
    </Transition>

    <nav class="drawer" :class="{ 'drawer--open': ouvert }" aria-label="Menu principal">
      <ul>
        <li><router-link :to="{ name: 'home' }" @click="fermer">Accueil</router-link></li>
        <li><router-link :to="{ name: 'inventaire' }" @click="fermer">Inventaire</router-link></li>
        <li><router-link :to="{ name: 'evenements' }" @click="fermer">Événements</router-link></li>
        <li><router-link :to="{ name: 'devis' }" @click="fermer">Devis</router-link></li>
        <li><router-link :to="{ name: 'facture' }" @click="fermer">Facturation</router-link></li>
        <li v-if="peutGererSecretaires"><router-link :to="{ name: 'secretaires' }" @click="fermer">Secrétaires</router-link></li>
        <li v-if="peutGererSecretaires"><router-link :to="{ name: 'ajouter-secretaire' }" @click="fermer">+ Secrétaire</router-link></li>
        <li v-if="peutGererSecretaires" class="drawer__extra"><router-link :to="{ name: 'ajouter-responsable' }" @click="fermer">+ Responsable</router-link></li>
        <li class="drawer__extra">
          <button type="button" class="drawer__deconnexion" @click="seDeconnecter">
            Déconnexion
          </button>
        </li>
      </ul>
    </nav>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuth } from '../composables/auth'

const route = useRoute()
const router = useRouter()
const { peutGererSecretaires, deconnecter } = useAuth()
const ouvert = ref(false)

function fermer() {
  ouvert.value = false
}

async function seDeconnecter() {
  fermer()
  await deconnecter()
  router.push({ name: 'connexion' })
}

watch(() => route.fullPath, fermer)
</script>

<style lang="scss" scoped>
@use '../../styles/variables' as *;

.menu-burger {
  display: inline-flex;
  align-items: center;
  flex-shrink: 0;
}

.overlay {
  position: fixed;
  inset: $header-height 0 0 0;
  background: rgba(0, 0, 0, 0.2);
  backdrop-filter: blur(2px);
  z-index: 20;
}

.drawer {
  position: fixed;
  top: $header-height;
  right: 0;
  bottom: 0;
  width: $drawer-width;
  background: $color-gold-soft;
  transform: translateX(100%);
  transition: transform 0.42s cubic-bezier(0.33, 1, 0.68, 1);
  z-index: 25;
  box-shadow: -8px 0 24px rgba(0, 0, 0, 0.08);

  &--open {
    transform: translateX(0);

    li {
      animation: slide-in-right 0.45s cubic-bezier(0.33, 1, 0.68, 1) backwards;

      @for $i from 1 through 9 {
        &:nth-child(#{$i}) {
          animation-delay: #{0.03 + $i * 0.03}s;
        }
      }
    }
  }

  ul {
    list-style: none;
    padding: $space-lg $space-md;
    display: flex;
    flex-direction: column;
    gap: $space-md;
  }

  a,
  &__deconnexion {
    display: block;
    color: $color-text;
    font-size: var(--fs-base);
    font-weight: 400;
    line-height: 1.4;
    padding: $space-xs $space-sm;
    margin: 0 (-$space-sm);
    border-radius: $radius;
    transition:
      background-color $transition,
      padding-left $transition,
      transform $transition;
  }

  a:hover,
  &__deconnexion:hover {
    background: rgba(255, 255, 255, 0.35);
    padding-left: $space-md;
    transform: translateX(2px);
  }

  &__extra {
    margin-top: $space-sm;
    padding-top: $space-md;
    border-top: 1px solid rgba(29, 29, 29, 0.12);
  }

  &__deconnexion {
    width: calc(100% + #{$space-sm * 2});
    border: 0;
    background: none;
    font-family: inherit;
    text-align: left;
    cursor: pointer;
  }
}

@media (min-width: $bp-desktop) {
  .drawer {
    width: 240px;
  }
}
</style>
