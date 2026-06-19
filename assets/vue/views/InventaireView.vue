<template>
  <div class="inventaire anim-page">
    <h1 class="page-titre">Inventaire</h1>
    <p class="page-sous-titre">Voir les stocks par jour</p>

    <label class="filtre-date">
      <span class="filtre-date__libelle">Date</span>
      <input type="date" v-model="dateSelectionnee" :min="dateMin" class="filtre-date__champ" />
    </label>

    <div
      class="inventaire__contenu"
      :class="{ 'inventaire__contenu--actualisation': actualisation }"
      :aria-busy="actualisation"
    >
      <p v-if="chargementInitial" class="inventaire__etat inventaire__etat--chargement">
        <span class="spinner" aria-hidden="true"></span>
        Chargement…
      </p>
      <p v-else-if="erreur" class="inventaire__etat inventaire__etat--erreur">{{ erreur }}</p>
      <p v-else-if="!dateSelectionnee" class="inventaire__etat">Choisissez une date pour voir les stocks.</p>
      <p v-else-if="kits.length === 0" class="inventaire__etat">Aucun kit disponible pour cette date.</p>

      <ul v-else class="inventaire__grille">
        <li
          v-for="(kit, index) in kits"
          :key="kit.id"
          class="carte"
          :style="{ '--carte-i': index }"
        >
          <div class="carte__media">
            <img
              v-if="imageKit(kit.nom)"
              :src="imageKit(kit.nom)"
              :alt="kit.nom"
              loading="lazy"
              @error="onErreurImageKit"
            />
          </div>
          <div class="carte__corps">
            <p class="carte__nom">{{ kit.nom }}</p>
            <p class="carte__stock" :class="classeStock(kit)">
              <span class="carte__stock-valeur">{{ kit.quantiteDisponible }}</span>
              <span class="carte__stock-libelle">disponible{{ kit.quantiteDisponible > 1 ? 's' : '' }}</span>
            </p>
          </div>
        </li>
      </ul>

      <p v-if="actualisation" class="inventaire__actualisation" aria-live="polite">
        <span class="spinner" aria-hidden="true"></span>
        Mise à jour des stocks…
      </p>
    </div>

    <button
      type="button"
      class="bouton bouton--bloc inventaire__catalogue"
      :disabled="catalogueEnCours"
      @click="telechargerCatalogue"
    >
      {{ catalogueEnCours ? 'Téléchargement…' : 'Télécharger le catalogue des produits' }}
    </button>
  </div>
</template>

<script setup>
import { ref, watch, onBeforeUnmount } from 'vue'
import api from '../api'
import { telechargerPdfCatalogue, messageTelechargement } from '../composables/telechargement'
import { useNotification } from '../composables/notification'
import { imageKit, onErreurImageKit } from '../composables/kitImages'

function isoDate(date) {
  const mm = String(date.getMonth() + 1).padStart(2, '0')
  const jj = String(date.getDate()).padStart(2, '0')
  return `${date.getFullYear()}-${mm}-${jj}`
}

function classeStock(kit) {
  if (kit.quantiteDisponible <= 0) {
    return 'carte__stock--epuise'
  }

  if (kit.quantiteDisponible <= Math.max(1, Math.floor(kit.quantiteMax * 0.2))) {
    return 'carte__stock--faible'
  }

  return 'carte__stock--ok'
}

const aujourdHui = new Date()
aujourdHui.setHours(0, 0, 0, 0)

const dateMin = isoDate(aujourdHui)
const dateSelectionnee = ref(dateMin)
const kits = ref([])
const chargementInitial = ref(true)
const actualisation = ref(false)
const erreur = ref('')
const catalogueEnCours = ref(false)
const { notifier } = useNotification()

let controleurRequete = null
let sequenceRequete = 0

function requeteAnnulee(error) {
  return error?.code === 'ERR_CANCELED' || error?.name === 'CanceledError'
}

async function chargerStocks() {
  if (!dateSelectionnee.value) {
    kits.value = []
    chargementInitial.value = false
    actualisation.value = false
    return
  }

  const dateDemandee = dateSelectionnee.value
  const sequence = ++sequenceRequete
  const premiereChargement = kits.value.length === 0

  if (controleurRequete) {
    controleurRequete.abort()
  }
  controleurRequete = new AbortController()

  if (premiereChargement) {
    chargementInitial.value = true
    actualisation.value = false
  } else {
    actualisation.value = true
  }
  erreur.value = ''

  try {
    const { data } = await api.get('/inventaire', {
      params: { date: dateDemandee },
      signal: controleurRequete.signal,
    })

    if (sequence !== sequenceRequete || dateDemandee !== dateSelectionnee.value) {
      return
    }

    kits.value = Array.isArray(data) ? data : []
  } catch (error) {
    if (requeteAnnulee(error) || sequence !== sequenceRequete) {
      return
    }

    kits.value = []
    erreur.value = 'Impossible de charger les stocks pour cette date.'
  } finally {
    if (sequence === sequenceRequete) {
      chargementInitial.value = false
      actualisation.value = false
      controleurRequete = null
    }
  }
}

async function telechargerCatalogue() {
  catalogueEnCours.value = true

  try {
    const resultat = await telechargerPdfCatalogue()
    notifier(messageTelechargement(resultat), 'succes')
  } catch (erreur) {
    if (erreur?.name === 'AbortError') {
      return
    }
    notifier('Le téléchargement du catalogue a échoué.', 'erreur')
  } finally {
    catalogueEnCours.value = false
  }
}

watch(dateSelectionnee, chargerStocks, { immediate: true })

onBeforeUnmount(() => {
  controleurRequete?.abort()
})
</script>

<style lang="scss" scoped>
@use '../../styles/variables' as *;

.inventaire {
  &__contenu {
    position: relative;

    &--actualisation .inventaire__grille {
      opacity: 0.55;
      pointer-events: none;
      transition: opacity 0.25s ease;
    }
  }

  &__actualisation {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: $space-sm;
    margin: $space-md 0 $space-lg;
    color: $color-muted;
    font-size: var(--fs-petit);

    .spinner {
      width: 18px;
      height: 18px;
    }
  }

  &__etat {
    color: $color-muted;
    font-size: var(--fs-base);
    margin-bottom: $space-lg;

    &--chargement {
      display: flex;
      align-items: center;
      gap: $space-sm;
    }

    &--erreur {
      color: $color-error;
    }
  }

  &__catalogue {
    text-decoration: none;
    width: 100%;
    box-sizing: border-box;
  }

  &__grille {
    list-style: none;
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: $space-md;
    margin-bottom: $space-xl;

    > * {
      animation: inventaire-fade-up 0.45s cubic-bezier(0.33, 1, 0.68, 1) backwards;
      animation-delay: calc(0.03s + var(--carte-i, 0) * 0.03s);
    }
  }
}

@keyframes inventaire-fade-up {
  from {
    opacity: 0;
    transform: translateY(5px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.filtre-date {
  display: flex;
  flex-direction: column;
  gap: $space-xs;
  margin-bottom: $space-lg;

  &__libelle {
    font-size: var(--fs-petit);
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: $color-muted;
  }

  &__champ {
    width: 100%;
    max-width: 280px;
    font-family: inherit;
    font-size: var(--fs-base);
    color: $color-text;
    background: $color-bg;
    border: 1px solid rgba(204, 167, 97, 0.35);
    border-radius: $radius;
    padding: $space-sm $space-md;
    cursor: pointer;
    transition:
      border-color $transition,
      box-shadow $transition;

    &:hover {
      border-color: rgba(204, 167, 97, 0.65);
    }

    &:focus {
      outline: none;
      border-color: $color-gold;
      box-shadow: 0 0 0 3px $color-gold-ghost;
    }
  }
}

.carte {
  display: flex;
  flex-direction: column;
  min-width: 0;
  border: 1px solid rgba(204, 167, 97, 0.22);
  border-radius: calc($radius + 2px);
  background: linear-gradient(180deg, rgba(204, 167, 97, 0.04) 0%, $color-bg 38%);
  overflow: hidden;
  transition:
    border-color $transition,
    box-shadow $transition,
    transform $transition;

  @media (hover: hover) {
    &:hover {
      border-color: rgba(204, 167, 97, 0.55);
      box-shadow: $shadow-soft;
      transform: translateY(-1px);

      .carte__media img {
        transform: scale(1.02);
      }
    }
  }

  &__media {
    position: relative;
    width: 100%;
    aspect-ratio: 153 / 150;
    background: linear-gradient(145deg, rgba(217, 217, 217, 0.45) 0%, $color-placeholder 100%);
    overflow: hidden;

    img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
      transform: scale(1);
      transition: transform 0.5s cubic-bezier(0.22, 1, 0.36, 1);
    }
  }

  &__corps {
    display: flex;
    flex-direction: column;
    gap: $space-xs;
    padding: $space-sm $space-md $space-md;
  }

  &__nom {
    font-size: var(--fs-base);
    line-height: 1.25;
    color: $color-text;
  }

  &__stock {
    display: inline-flex;
    align-items: baseline;
    gap: 6px;
    align-self: flex-start;
    margin: 0;
    padding: 4px 10px;
    border-radius: 999px;
    font-size: var(--fs-petit);
    letter-spacing: 0.02em;
    background: rgba(204, 167, 97, 0.1);
    color: $color-gold-dark;
    transition:
      background-color $transition,
      color $transition;

    &--ok {
      background: rgba(46, 125, 50, 0.1);
      color: $color-success;
    }

    &--faible {
      background: rgba(204, 167, 97, 0.16);
      color: $color-gold-dark;
    }

    &--epuise {
      background: rgba(192, 57, 43, 0.1);
      color: $color-error;
    }
  }

  &__stock-valeur {
    font-size: calc(var(--fs-petit) + 2px);
    font-weight: 400;
    line-height: 1;
  }

  &__stock-libelle {
    opacity: 0.9;
  }
}

@media (min-width: $bp-tablet) {
  .inventaire__grille {
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: $space-lg;
  }
}

@media (min-width: $bp-desktop) {
  .inventaire__grille {
    grid-template-columns: repeat(4, minmax(0, 1fr));
  }
}

@media (prefers-reduced-motion: reduce) {
  .inventaire__grille > * {
    animation: none;
  }

  .carte,
  .carte__media img {
    transition-duration: 0.01ms !important;
  }
}
</style>
