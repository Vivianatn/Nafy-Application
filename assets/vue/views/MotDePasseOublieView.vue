<template>
  <div class="connexion">
    <Transition name="connexion-carte" appear>
      <div class="connexion__carte">
        <header class="connexion__entete">
          <p class="connexion__marque">Kamille Events</p>
          <h1 class="connexion__titre">Réinitialiser le mot de passe</h1>
          <p class="connexion__sous-titre">
            Indiquez votre identifiant et choisissez un nouveau mot de passe (8 caractères minimum).
          </p>
        </header>

        <form class="connexion__form" @submit.prevent="envoyer">
          <p v-if="messageSucces" class="connexion__message connexion__message--succes">{{ messageSucces }}</p>
          <p v-if="messageErreur" class="connexion__message connexion__message--erreur">{{ messageErreur }}</p>

          <label class="champ">
            Email ou numéro de téléphone
            <input
              type="text"
              v-model.trim="identifiant"
              autocomplete="username"
              placeholder="ex. marie@exemple.fr ou 0612345678"
            />
            <span v-if="erreurs.identifiant" class="champ__erreur">{{ erreurs.identifiant }}</span>
          </label>

          <label class="champ">
            Nouveau mot de passe
            <ChampMotDePasse v-model="motDePasse" autocomplete="new-password" />
            <span v-if="erreurs.motDePasse" class="champ__erreur">{{ erreurs.motDePasse }}</span>
          </label>

          <label class="champ">
            Confirmation
            <ChampMotDePasse v-model="confirmationMotDePasse" autocomplete="new-password" />
            <span v-if="erreurs.confirmationMotDePasse" class="champ__erreur">{{ erreurs.confirmationMotDePasse }}</span>
          </label>

          <button type="submit" class="bouton bouton--bloc" :disabled="envoiEnCours">
            {{ envoiEnCours ? 'Enregistrement…' : 'Enregistrer le mot de passe' }}
          </button>

          <router-link :to="{ name: 'connexion' }" class="connexion__lien">
            {{ messageSucces ? 'Se connecter' : 'Retour à la connexion' }}
          </router-link>
        </form>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import api from '../api'
import ChampMotDePasse from '../components/ChampMotDePasse.vue'

const identifiant = ref('')
const motDePasse = ref('')
const confirmationMotDePasse = ref('')
const erreurs = ref({})
const messageErreur = ref('')
const messageSucces = ref('')
const envoiEnCours = ref(false)

async function envoyer() {
  erreurs.value = {}
  messageErreur.value = ''
  messageSucces.value = ''
  envoiEnCours.value = true

  try {
    const { data } = await api.post('/auth/reinitialiser-mot-de-passe', {
      identifiant: identifiant.value,
      motDePasse: motDePasse.value,
      confirmationMotDePasse: confirmationMotDePasse.value,
    })
    messageSucces.value = data.message
    motDePasse.value = ''
    confirmationMotDePasse.value = ''
  } catch (error) {
    if (error.response?.status === 422) {
      erreurs.value = error.response.data.erreurs ?? {}
      messageErreur.value = 'Veuillez corriger les champs en erreur.'
    } else {
      messageErreur.value = 'Impossible de mettre à jour le mot de passe.'
    }
  } finally {
    envoiEnCours.value = false
  }
}
</script>

<style lang="scss" scoped>
@use '../../styles/variables' as *;

.connexion {
  min-height: 100vh;
  min-height: 100dvh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: $space-lg $space-side;

  &__carte {
    width: 100%;
    max-width: 420px;
    border: 1px solid rgba(204, 167, 97, 0.35);
    border-radius: calc($radius + 4px);
    overflow: hidden;
    background: $color-bg;
    box-shadow: $shadow-soft;
  }

  &__entete {
    padding: $space-lg $space-lg $space-md;
    background: linear-gradient(180deg, rgba(204, 167, 97, 0.28) 0%, rgba(204, 167, 97, 0.08) 100%);
    border-bottom: 1px solid rgba(204, 167, 97, 0.25);
    text-align: center;
  }

  &__marque {
    font-size: var(--fs-petit);
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: $color-muted;
    margin-bottom: $space-xs;
  }

  &__titre {
    font-size: var(--fs-grand);
    margin-bottom: $space-xs;
  }

  &__sous-titre {
    color: $color-muted;
    font-size: var(--fs-petit);
  }

  &__form {
    display: flex;
    flex-direction: column;
    gap: $space-md;
    padding: $space-lg;
  }

  &__lien {
    text-align: center;
    color: $color-muted;
    font-size: var(--fs-petit);
    transition: color $transition;

    &:hover {
      color: $color-gold-dark;
    }
  }

  &__message {
    padding: $space-sm $space-md;
    border-radius: $radius;
    font-size: var(--fs-petit);

    &--erreur {
      background: rgba(192, 57, 43, 0.1);
      color: $color-error;
      border: 1px solid rgba(192, 57, 43, 0.25);
    }

    &--succes {
      background: rgba(46, 125, 50, 0.1);
      color: $color-success;
      border: 1px solid rgba(46, 125, 50, 0.25);
    }
  }
}

.connexion-carte-enter-active {
  transition: opacity 0.5s ease-out, transform 0.5s cubic-bezier(0.33, 1, 0.68, 1);
}

.connexion-carte-enter-from {
  opacity: 0;
  transform: translateY(8px);
}

@media (prefers-reduced-motion: reduce) {
  .connexion-carte-enter-active {
    transition: opacity 0.2s ease;
  }

  .connexion-carte-enter-from {
    transform: none;
  }
}
</style>
