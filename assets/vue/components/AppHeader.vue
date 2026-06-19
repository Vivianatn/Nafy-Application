<template>
  <header class="entete">
    <router-link :to="{ name: 'home' }" class="entete__logo">
      Kamille
    </router-link>

    <div v-if="session.utilisateur" class="entete__droite">
      <span class="entete__role">{{ roleUtilisateur }}</span>
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
  box-sizing: border-box;
  min-height: $header-height;
  height: $header-height;
  background: $color-gold;
  display: grid;
  grid-template-columns: minmax(0, 1fr) auto;
  align-items: center;
  column-gap: $space-md;
  padding: env(safe-area-inset-top, 0) $space-side 0;
  padding-left: max($space-side, env(safe-area-inset-left, 0));
  padding-right: max($space-side, env(safe-area-inset-right, 0));
  box-shadow: 0 1px 0 rgba(29, 29, 29, 0.06);

  &__logo {
    font-weight: 400;
    letter-spacing: 0.02em;
    color: $color-text;
    font-size: var(--fs-base);
    line-height: 1;
  }

  &__droite {
    display: inline-flex;
    flex-direction: row;
    flex-wrap: nowrap;
    align-items: center;
    justify-content: flex-end;
    gap: 12px;
    flex-shrink: 0;
  }

  &__role {
    font-size: var(--fs-petit);
    letter-spacing: 0.04em;
    text-transform: uppercase;
    color: rgba(29, 29, 29, 0.72);
    white-space: nowrap;
    line-height: 1;
  }

  :deep(.menu-burger) {
    display: none;
  }
}

@media (min-width: $bp-desktop) {
  .entete :deep(.menu-burger) {
    display: inline-flex;
    align-items: center;
    flex-shrink: 0;
  }

  .entete :deep(.burger) {
    display: inline-flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 5px;
    width: 44px;
    height: 44px;
    margin: 0;
    padding: 0;
    border: 0;
    background: none;
    cursor: pointer;
    flex-shrink: 0;
  }

  .entete :deep(.burger__ligne) {
    display: block;
    width: 24px;
    height: 2px;
    background: $color-text;
    border-radius: 1px;
    transition: transform 0.32s ease, opacity 0.32s ease;
  }

  .entete :deep(.burger--open .burger__ligne:nth-child(1)) {
    transform: translateY(7px) rotate(45deg);
  }

  .entete :deep(.burger--open .burger__ligne:nth-child(2)) {
    opacity: 0;
    transform: scaleX(0);
  }

  .entete :deep(.burger--open .burger__ligne:nth-child(3)) {
    transform: translateY(-7px) rotate(-45deg);
  }
}

@media (min-width: $bp-tablet) {
  .entete {
    padding-left: max($space-side-tablet, env(safe-area-inset-left, 0));
    padding-right: max($space-side-tablet, env(safe-area-inset-right, 0));
  }
}

@media (min-width: $bp-desktop) {
  .entete {
    padding-left: max($space-side-desktop, env(safe-area-inset-left, 0));
    padding-right: max($space-side-desktop, env(safe-area-inset-right, 0));
  }
}

@media (min-width: $bp-wide) {
  .entete {
    padding-left: max($space-side-wide, env(safe-area-inset-left, 0));
    padding-right: max($space-side-wide, env(safe-area-inset-right, 0));
  }
}
</style>
