<template>
  <div class="inventaire anim-page">
    <h1 class="page-titre">Inventaire</h1>
    <p class="page-sous-titre">Voir les stocks par jour</p>

    <label class="filtre-date">
      <input type="date" v-model="dateSelectionnee" :min="dateMin" class="filtre-date__champ" />
    </label>

    <p v-if="chargement" class="inventaire__etat inventaire__etat--chargement">
      <span class="spinner" aria-hidden="true"></span>
      Chargement…
    </p>
    <p v-else-if="erreur" class="inventaire__etat inventaire__etat--erreur">{{ erreur }}</p>
    <p v-else-if="!dateSelectionnee" class="inventaire__etat">Choisissez une date pour voir les stocks.</p>

    <ul v-else-if="dateSelectionnee" class="grille anim-grid">
      <li v-for="kit in kits" :key="kit.id" class="carte">
        <div class="carte__image">
          <img
            v-if="imageKit(kit.nom)"
            :src="imageKit(kit.nom)"
            :alt="kit.nom"
            loading="lazy"
            @error="onErreurImageKit"
          />
        </div>
        <p class="carte__nom">{{ kit.nom }}</p>
        <p class="carte__stock">
          Stock : <strong>{{ kit.quantiteDisponible }}</strong>
        </p>
      </li>
    </ul>

    <a
      href="/documents/catalogue-produits.pdf"
      download="catalogue-produits.pdf"
      class="bouton bouton--bloc inventaire__catalogue"
    >
      Télécharger le catalogue des produits
    </a>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue'
import api from '../api'
import { imageKit, onErreurImageKit } from '../composables/kitImages'

function isoDate(date) {
  const mm = String(date.getMonth() + 1).padStart(2, '0')
  const jj = String(date.getDate()).padStart(2, '0')
  return `${date.getFullYear()}-${mm}-${jj}`
}

const aujourdHui = new Date()
aujourdHui.setHours(0, 0, 0, 0)

const dateMin = isoDate(aujourdHui)
const dateSelectionnee = ref(dateMin)
const kits = ref([])
const chargement = ref(false)
const erreur = ref('')

async function chargerStocks() {
  if (!dateSelectionnee.value) {
    kits.value = []
    return
  }

  chargement.value = true
  erreur.value = ''

  try {
    const { data } = await api.get('/inventaire', {
      params: { date: dateSelectionnee.value },
    })
    kits.value = data
  } catch {
    kits.value = []
    erreur.value = 'Impossible de charger les stocks pour cette date.'
  } finally {
    chargement.value = false
  }
}

watch(dateSelectionnee, chargerStocks)

onMounted(chargerStocks)
</script>

<style lang="scss" scoped>
@use '../../styles/variables' as *;

.inventaire {
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
  }
}

.filtre-date {
  display: block;
  margin-bottom: $space-lg;

  &__champ {
    width: 100%;
    font-family: inherit;
    font-size: var(--fs-base);
    color: $color-text;
    background: $color-bg;
    border: 1px solid $color-border;
    border-radius: $radius;
    padding: $space-sm;
    cursor: pointer;

    &:focus {
      outline: none;
      border-color: $color-gold;
    }
  }
}

.grille {
  list-style: none;
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: $space-lg;
  margin-bottom: $space-xl;
}

.carte {
  &__image {
    width: 100%;
    aspect-ratio: 153 / 150;
    background: $color-placeholder;
    border-radius: $radius;
    margin-bottom: $space-sm;
    overflow: hidden;
    transition: transform $transition, box-shadow $transition;

    img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
      transition: transform 0.45s cubic-bezier(0.22, 1, 0.36, 1);
    }

    &:hover img {
      transform: scale(1.06);
    }
  }

  &__nom {
    font-weight: 400;
    font-size: var(--fs-base);
  }

  &__stock {
    color: $color-text;
    font-size: var(--fs-base);
    margin-top: $space-xs;

    strong {
      font-weight: 400;
    }
  }
}

@media (min-width: $bp-tablet) {
  .grille {
    grid-template-columns: repeat(3, 1fr);
    gap: $space-xl;
  }
}

@media (min-width: $bp-desktop) {
  .grille {
    grid-template-columns: repeat(4, 1fr);
  }
}
</style>
