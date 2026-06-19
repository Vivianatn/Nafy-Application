<template>
  <div class="plus anim-page">
    <h1 class="page-titre">Plus</h1>
    <p class="page-sous-titre">Autres services et paramètres du compte</p>

    <section class="plus__bloc">
      <h2 class="plus__titre-section">Navigation</h2>
      <ul class="plus__liste">
        <li>
          <router-link :to="{ name: 'evenements' }" class="plus__lien">
            <span>Événements</span>
            <span class="plus__fleche" aria-hidden="true">›</span>
          </router-link>
        </li>
      </ul>
    </section>

    <section v-if="peutGererSecretaires" class="plus__bloc">
      <h2 class="plus__titre-section">Gestion de l'équipe</h2>
      <p class="plus__info">
        Seuls les <strong>responsables</strong> peuvent consulter l'équipe et ajouter des secrétaires.
      </p>
      <ul class="plus__liste">
        <li>
          <router-link :to="{ name: 'secretaires' }" class="plus__lien">
            <span>Secrétaires</span>
            <span class="plus__fleche" aria-hidden="true">›</span>
          </router-link>
        </li>
        <li>
          <router-link :to="{ name: 'ajouter-secretaire' }" class="plus__lien">
            <span>Ajouter une secrétaire</span>
            <span class="plus__fleche" aria-hidden="true">›</span>
          </router-link>
        </li>
        <li>
          <router-link :to="{ name: 'ajouter-responsable' }" class="plus__lien">
            <span>Ajouter un responsable</span>
            <span class="plus__fleche" aria-hidden="true">›</span>
          </router-link>
        </li>
      </ul>
    </section>

    <section v-else class="plus__bloc">
      <h2 class="plus__titre-section">Gestion de l'équipe</h2>
      <p class="plus__info plus__info--restreint">
        En tant que <strong>secrétaire</strong>, vous ne pouvez pas ajouter de nouvelles secrétaires.
        Contactez un responsable si besoin.
      </p>
    </section>

    <section class="plus__bloc">
      <h2 class="plus__titre-section">Compte</h2>
      <div v-if="session.utilisateur" class="plus__profil">
        <p class="plus__profil-nom">
          {{ session.utilisateur.prenom }} {{ session.utilisateur.nom }}
        </p>
        <p class="plus__profil-role">{{ roleUtilisateur }}</p>
      </div>
      <button type="button" class="bouton bouton--secondaire bouton--bloc plus__deconnexion" @click="seDeconnecter">
        Déconnexion
      </button>
    </section>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router'
import { useAuth } from '../composables/auth'

const router = useRouter()
const { session, peutGererSecretaires, roleUtilisateur, deconnecter } = useAuth()

async function seDeconnecter() {
  await deconnecter()
  router.push({ name: 'connexion' })
}
</script>

<style lang="scss" scoped>
@use '../../styles/variables' as *;

.plus {
  &__bloc {
    margin-bottom: $space-xl;
  }

  &__titre-section {
    font-size: var(--fs-petit);
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: $color-muted;
    margin-bottom: $space-sm;
  }

  &__info {
    margin-bottom: $space-md;
    font-size: var(--fs-base);
    line-height: 1.45;
    color: $color-text;
    padding: $space-sm $space-md;
    border-radius: $radius;
    background: rgba(204, 167, 97, 0.08);
    border: 1px solid rgba(204, 167, 97, 0.22);

    &--restreint {
      background: rgba(107, 107, 107, 0.06);
      border-color: rgba(107, 107, 107, 0.18);
    }
  }

  &__liste {
    list-style: none;
    border: 1px solid rgba(204, 167, 97, 0.28);
    border-radius: calc($radius + 2px);
    overflow: hidden;
    background: $color-bg;
  }

  &__liste li + li {
    border-top: 1px solid rgba(204, 167, 97, 0.18);
  }

  &__lien {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: $space-md;
    padding: $space-md;
    color: $color-text;
    transition: background-color $transition;

    &:hover,
    &.router-link-active {
      background: rgba(204, 167, 97, 0.08);
    }
  }

  &__fleche {
    color: $color-gold-dark;
    font-size: calc(var(--fs-base) + 4px);
    line-height: 1;
  }

  &__profil {
    margin-bottom: $space-md;
    padding: $space-md;
    border: 1px solid rgba(204, 167, 97, 0.28);
    border-radius: calc($radius + 2px);
    background: linear-gradient(145deg, $color-bg 0%, rgba(204, 167, 97, 0.05) 100%);
  }

  &__profil-nom {
    margin: 0;
    font-size: var(--fs-base);
  }

  &__profil-role {
    margin: $space-xs 0 0;
    font-size: var(--fs-petit);
    letter-spacing: 0.04em;
    text-transform: uppercase;
    color: $color-muted;
  }

  &__deconnexion {
    width: 100%;
  }
}
</style>
