<template>
  <header class="entete">
    <router-link :to="{ name: 'home' }" class="entete__logo">
      Kamille
    </router-link>
    <div class="entete__actions">
      <p v-if="session.utilisateur" class="entete__role">
        {{ roleUtilisateur }}
      </p>
      <MenuBurger />
    </div>
  </header>
</template>

<script setup>
import MenuBurger from './MenuBurger.vue'
import { useAuth } from '../composables/auth'

const { session, roleUtilisateur } = useAuth()
</script>

<style lang="scss" scoped>
@use '../../styles/variables' as *;

.entete {
  position: sticky;
  top: 0;
  z-index: 30;
  height: $header-height;
  background: $color-gold;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: $space-md;
  padding: 0 $space-side;
  box-shadow: 0 1px 0 rgba(29, 29, 29, 0.06);

  &__logo {
    font-weight: 400;
    letter-spacing: 0.02em;
    color: $color-text;
    font-size: var(--fs-base);
  }

  &__actions {
    display: flex;
    align-items: center;
    gap: $space-md;
    min-width: 0;
  }

  &__role {
    margin: 0;
    font-size: var(--fs-petit);
    letter-spacing: 0.04em;
    text-transform: uppercase;
    color: rgba(29, 29, 29, 0.72);
    white-space: nowrap;
  }
}

@media (min-width: $bp-tablet) {
  .entete {
    padding: 0 $space-side-tablet;
  }
}

@media (min-width: $bp-desktop) {
  .entete {
    padding: 0 $space-side-desktop;
  }
}

@media (min-width: $bp-wide) {
  .entete {
    padding: 0 $space-side-wide;
  }
}
</style>
