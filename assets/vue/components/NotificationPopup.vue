<template>
  <Transition name="popup-fade">
    <div v-if="etat.visible" class="popup-overlay" @click.self="fermer">
      <div class="popup" :class="`popup--${etat.type}`" role="dialog" aria-modal="true">
        <button type="button" class="popup__fermer" aria-label="Fermer" @click="fermer">&times;</button>
        <p class="popup__message">{{ etat.message }}</p>
        <button type="button" class="bouton" @click="fermer">OK</button>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { useNotification } from '../composables/notification'

const { etat, fermer } = useNotification()
</script>

<style lang="scss" scoped>
@use '../../styles/variables' as *;

.popup-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.45);
  backdrop-filter: blur(3px);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: $space-lg;
  z-index: 1000;
}

.popup {
  position: relative;
  background: $color-bg;
  border-radius: calc($radius + 2px);
  box-shadow: $shadow-lift;
  padding: $space-xl;
  max-width: 360px;
  width: 100%;
  text-align: center;
  display: flex;
  flex-direction: column;
  gap: $space-lg;
  border-top: 4px solid $color-gold;

  &--succes {
    border-top-color: $color-success;
  }

  &--erreur {
    border-top-color: $color-error;
  }

  &__message {
    font-size: var(--fs-base);
  }

  &__fermer {
    position: absolute;
    top: $space-sm;
    right: $space-sm;
    width: 32px;
    height: 32px;
    border: 0;
    background: transparent;
    color: $color-muted;
    font-size: 24px;
    line-height: 1;
    border-radius: $radius;
    transition: color $transition, background-color $transition;

    &:hover {
      color: $color-text;
      background: $color-gold-ghost;
    }
  }

  .bouton {
    align-self: center;
  }
}
</style>
