<template>
  <div class="recherche-ville" ref="conteneur">
    <input
      type="text"
      class="recherche-ville__input"
      :value="recherche"
      placeholder="Rechercher une ville…"
      autocomplete="off"
      role="combobox"
      aria-autocomplete="list"
      :aria-expanded="listeOuverte"
      @input="onSaisie"
      @focus="ouvrirListe"
      @blur="fermerListeAvecDelai"
      @keydown.escape="fermerListe"
    />
    <ul
      v-if="listeOuverte && villesFiltrees.length > 0"
      class="recherche-ville__liste"
      role="listbox"
    >
      <li
        v-for="ville in villesFiltrees"
        :key="ville.id"
        role="option"
        class="recherche-ville__option"
        @mousedown.prevent="choisir(ville)"
      >
        <span class="recherche-ville__nom">{{ ville.nom }}</span>
        <span class="recherche-ville__meta">({{ ville.departement }}) — {{ ville.kilometres }} km</span>
      </li>
    </ul>
    <p v-else-if="listeOuverte && recherche.trim()" class="recherche-ville__vide">
      Aucune ville trouvée.
    </p>
    <p v-if="kilometresAffiches !== null" class="recherche-ville__km">
      Nombre de km : <strong>{{ kilometresAffiches }}</strong>
    </p>
  </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue'

const props = defineProps({
  modelValue: { type: [String, Number], default: '' },
  villes: { type: Array, default: () => [] },
})

const emit = defineEmits(['update:modelValue'])

const conteneur = ref(null)
const recherche = ref('')
const listeOuverte = ref(false)

function libelleVille(ville) {
  return `${ville.nom} (${ville.departement})`
}

const villesFiltrees = computed(() => {
  const terme = recherche.value.trim().toLowerCase()

  if (!terme) {
    return props.villes
  }

  return props.villes.filter((ville) => {
    const nom = ville.nom?.toLowerCase() ?? ''
    const departement = String(ville.departement ?? '')

    return nom.includes(terme) || departement.includes(terme)
  })
})

const kilometresAffiches = computed(() => {
  if (!props.modelValue) {
    return null
  }

  const ville = props.villes.find((v) => v.id === Number(props.modelValue))

  return ville ? ville.kilometres : null
})

function synchroniserDepuisSelection() {
  if (!props.modelValue) {
    recherche.value = ''
    return
  }

  const ville = props.villes.find((v) => v.id === Number(props.modelValue))

  if (ville) {
    recherche.value = libelleVille(ville)
  }
}

function ouvrirListe() {
  listeOuverte.value = true
}

function fermerListe() {
  listeOuverte.value = false
}

function fermerListeAvecDelai() {
  window.setTimeout(() => {
    listeOuverte.value = false
  }, 150)
}

function onSaisie(event) {
  recherche.value = event.target.value
  ouvrirListe()

  if (!props.modelValue) {
    return
  }

  const ville = props.villes.find((v) => v.id === Number(props.modelValue))

  if (!ville || recherche.value !== libelleVille(ville)) {
    emit('update:modelValue', '')
  }
}

function choisir(ville) {
  recherche.value = libelleVille(ville)
  emit('update:modelValue', String(ville.id))
  fermerListe()
}

watch(() => props.modelValue, synchroniserDepuisSelection)
watch(() => props.villes, synchroniserDepuisSelection, { deep: true })
</script>

<style lang="scss" scoped>
@use '../../styles/variables' as *;

.recherche-ville {
  position: relative;
  width: 100%;
}

.recherche-ville__input {
  width: 100%;
  font-family: inherit;
  font-size: var(--fs-base);
  padding: $space-sm;
  border: 1px solid $color-border;
  border-radius: $radius;
  background: $color-bg;
  color: $color-text;
  transition:
    border-color $transition,
    box-shadow $transition,
    transform $transition;

  &:focus {
    outline: none;
    border-color: $color-gold;
    box-shadow: 0 0 0 3px $color-gold-ghost;
    transform: translateY(-1px);
  }
}

.recherche-ville__liste {
  position: absolute;
  z-index: 20;
  top: calc(100% + 4px);
  left: 0;
  right: 0;
  max-height: 220px;
  overflow-y: auto;
  margin: 0;
  padding: $space-xs 0;
  list-style: none;
  background: $color-bg;
  border: 1px solid rgba(204, 167, 97, 0.45);
  border-radius: $radius;
  box-shadow: $shadow-soft;
}

.recherche-ville__option {
  display: flex;
  flex-wrap: wrap;
  gap: 0.35rem 0.5rem;
  padding: $space-sm $space-md;
  cursor: pointer;
  transition: background $transition;

  &:hover {
    background: rgba(204, 167, 97, 0.12);
  }
}

.recherche-ville__nom {
  font-weight: 500;
}

.recherche-ville__meta {
  color: $color-muted;
  font-size: var(--fs-petit);
}

.recherche-ville__vide {
  margin: $space-xs 0 0;
  padding: $space-sm;
  color: $color-muted;
  font-size: var(--fs-petit);
}

.recherche-ville__km {
  margin: $space-sm 0 0;
  font-size: var(--fs-base);
  color: $color-text;

  strong {
    font-weight: 600;
    color: $color-gold-dark;
  }
}
</style>
