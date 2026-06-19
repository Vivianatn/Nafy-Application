<template>
  <div class="secretaires anim-page">
    <header class="secretaires__entete">
      <div>
        <h1 class="page-titre">Secrétaires</h1>
        <p class="page-sous-titre">Informations générales de l'équipe</p>
      </div>
      <router-link :to="{ name: 'ajouter-secretaire' }" class="bouton secretaires__ajouter">
        + Ajouter
      </router-link>
    </header>

    <p v-if="chargement" class="secretaires__etat secretaires__etat--chargement">
      <span class="spinner" aria-hidden="true"></span>
      Chargement…
    </p>
    <p v-else-if="erreur" class="secretaires__etat secretaires__etat--erreur">{{ erreur }}</p>
    <p v-else-if="secretaires.length === 0" class="secretaires__etat">
      Aucune secrétaire enregistrée pour le moment.
    </p>

    <ul v-else class="secretaires__liste anim-list">
      <li
        v-for="(secretaire, index) in secretaires"
        :key="secretaire.id"
        class="carte"
        :style="{ '--carte-i': index }"
      >
        <div class="carte__entete">
          <span class="carte__initiales" aria-hidden="true">{{ initiales(secretaire) }}</span>
          <div>
            <h2 class="carte__nom">{{ secretaire.prenom }} {{ secretaire.nom }}</h2>
            <p class="carte__role">Secrétaire</p>
          </div>
        </div>

        <dl class="carte__infos">
          <div class="carte__ligne">
            <dt>Email</dt>
            <dd>
              <a :href="'mailto:' + secretaire.email" class="carte__lien">{{ secretaire.email }}</a>
            </dd>
          </div>
          <div class="carte__ligne">
            <dt>Téléphone</dt>
            <dd>
              <a
                v-if="secretaire.telephone"
                :href="'tel:' + secretaire.telephone"
                class="carte__lien"
              >
                {{ secretaire.telephone }}
              </a>
              <span v-else class="carte__vide">Non renseigné</span>
            </dd>
          </div>
        </dl>
      </li>
    </ul>

    <p v-if="!chargement && !erreur && secretaires.length > 0" class="secretaires__compteur">
      {{ secretaires.length }} secrétaire{{ secretaires.length > 1 ? 's' : '' }} au total
    </p>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import api from '../api'

const secretaires = ref([])
const chargement = ref(true)
const erreur = ref('')

function initiales(secretaire) {
  const p = (secretaire.prenom || '').charAt(0)
  const n = (secretaire.nom || '').charAt(0)

  return (p + n).toUpperCase() || '?'
}

async function chargerSecretaires() {
  chargement.value = true
  erreur.value = ''

  try {
    const { data } = await api.get('/secretaires')
    secretaires.value = data
  } catch {
    secretaires.value = []
    erreur.value = 'Impossible de charger la liste des secrétaires.'
  } finally {
    chargement.value = false
  }
}

onMounted(chargerSecretaires)
</script>

<style lang="scss" scoped>
@use '../../styles/variables' as *;

.secretaires {
  &__entete {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: $space-md;
    margin-bottom: $space-lg;
  }

  &__ajouter {
    flex-shrink: 0;
    text-decoration: none;
    white-space: nowrap;
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

  &__liste {
    list-style: none;
    display: flex;
    flex-direction: column;
    gap: $space-md;
  }

  &__compteur {
    margin-top: $space-lg;
    color: $color-muted;
    font-size: var(--fs-petit);
    text-align: center;
  }
}

.carte {
  border: 1px solid rgba(204, 167, 97, 0.35);
  border-radius: calc($radius + 4px);
  background: linear-gradient(145deg, $color-bg 0%, rgba(204, 167, 97, 0.05) 100%);
  box-shadow: $shadow-soft;
  padding: $space-md;
  transition:
    transform $transition,
    box-shadow $transition,
    border-color $transition;

  &:hover {
    transform: translateY(-2px);
    border-color: rgba(204, 167, 97, 0.65);
    box-shadow: $shadow-lift;
  }

  &__entete {
    display: flex;
    align-items: center;
    gap: $space-md;
    margin-bottom: $space-md;
    padding-bottom: $space-md;
    border-bottom: 1px solid rgba(204, 167, 97, 0.2);
  }

  &__initiales {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: $color-gold;
    color: $color-text;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: var(--fs-base);
    flex-shrink: 0;
  }

  &__nom {
    font-size: var(--fs-base);
    font-weight: 400;
    margin: 0;
  }

  &__role {
    margin: 2px 0 0;
    font-size: var(--fs-petit);
    color: $color-muted;
    text-transform: uppercase;
    letter-spacing: 0.06em;
  }

  &__infos {
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: $space-sm;
  }

  &__ligne {
    display: grid;
    grid-template-columns: 100px 1fr;
    gap: $space-sm;
    align-items: baseline;

    dt {
      margin: 0;
      font-size: var(--fs-petit);
      color: $color-muted;
    }

    dd {
      margin: 0;
      font-size: var(--fs-base);
      word-break: break-word;
    }
  }

  &__lien {
    color: $color-text;
    text-decoration: underline;
    text-decoration-color: rgba(204, 167, 97, 0.5);
    transition: color $transition;

    &:hover {
      color: $color-gold-dark;
    }
  }

  &__vide {
    color: $color-muted;
    font-style: italic;
  }
}

@media (max-width: #{$bp-tablet - 1px}) {
  .secretaires__entete {
    flex-direction: column;
    align-items: stretch;
  }

  .secretaires__ajouter {
    text-align: center;
  }

  .carte__ligne {
    grid-template-columns: 1fr;
    gap: 2px;
  }
}
</style>
