<template>
  <div class="champ-mdp">
    <input
      :id="id"
      :type="afficher ? 'text' : 'password'"
      :name="name"
      :value="modelValue"
      :autocomplete="autocomplete"
      :placeholder="placeholder"
      :disabled="disabled"
      @input="$emit('update:modelValue', $event.target.value)"
    />
    <button
      type="button"
      class="champ-mdp__oeil"
      :aria-label="afficher ? 'Masquer le mot de passe' : 'Afficher le mot de passe'"
      :aria-pressed="afficher"
      :disabled="disabled"
      @click="afficher = !afficher"
    >
      <svg v-if="afficher" viewBox="0 0 24 24" fill="none" aria-hidden="true">
        <path
          d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"
          stroke="currentColor"
          stroke-width="1.6"
        />
        <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.6" />
      </svg>
      <svg v-else viewBox="0 0 24 24" fill="none" aria-hidden="true">
        <path
          d="M3 3l18 18M10.5 10.7a2.5 2.5 0 0 0 3.5 3.5M7.2 7.4C5.2 8.8 3.6 11 2 12c0 0 3.5 7 10 7 1.8 0 3.4-.5 4.8-1.3M9.9 5.1A10.8 10.8 0 0 1 12 5c6.5 0 10 7 10 7a18.2 18.2 0 0 1-4.1 5.2"
          stroke="currentColor"
          stroke-width="1.6"
          stroke-linecap="round"
        />
      </svg>
    </button>
  </div>
</template>

<script setup>
import { ref } from 'vue'

defineProps({
  modelValue: {
    type: String,
    default: '',
  },
  name: {
    type: String,
    default: '',
  },
  autocomplete: {
    type: String,
    default: 'current-password',
  },
  placeholder: {
    type: String,
    default: '',
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  id: {
    type: String,
    default: '',
  },
})

defineEmits(['update:modelValue'])

const afficher = ref(false)
</script>

<style lang="scss" scoped>
@use '../../styles/variables' as *;

.champ-mdp {
  position: relative;
  display: flex;
  width: 100%;

  :deep(input) {
    padding-right: calc(#{$space-sm} + 36px);
  }

  &__oeil {
    position: absolute;
    top: 50%;
    right: 4px;
    transform: translateY(-50%);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    padding: 0;
    border: 0;
    border-radius: $radius;
    background: transparent;
    color: $color-muted;
    cursor: pointer;
    transition:
      color $transition,
      background-color $transition;

    svg {
      width: 20px;
      height: 20px;
    }

    &:hover:not(:disabled) {
      color: $color-gold-dark;
      background: $color-gold-ghost;
    }

    &:disabled {
      opacity: 0.5;
      cursor: not-allowed;
    }
  }
}
</style>
