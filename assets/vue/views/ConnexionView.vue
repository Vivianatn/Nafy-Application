<template>
  <div class="connexion">
    <div class="connexion__decor connexion__decor--1" aria-hidden="true"></div>
    <div class="connexion__decor connexion__decor--2" aria-hidden="true"></div>

    <Transition name="connexion-carte" appear>
      <div class="connexion__carte">
        <header class="connexion__entete">
          <p class="connexion__marque">Kamille Events</p>
          <h1 class="connexion__titre">Connexion</h1>
          <p class="connexion__sous-titre">Accès réservé aux secrétaires et responsables</p>
        </header>

        <form class="connexion__form" @submit.prevent="envoyer">
          <p v-if="messageErreur" class="connexion__message connexion__message--erreur">{{ messageErreur }}</p>

          <label class="champ">
            Email ou numéro de téléphone
            <input
              type="text"
              name="identifiant"
              v-model.trim="identifiant"
              autocomplete="username"
              placeholder="ex. marie@exemple.fr ou 0612345678"
            />
            <span v-if="erreurs.identifiant" class="champ__erreur">{{ erreurs.identifiant }}</span>
          </label>

          <label class="champ">
            Mot de passe
            <input
              type="password"
              name="motDePasse"
              v-model="motDePasse"
              autocomplete="current-password"
              placeholder="Votre mot de passe"
            />
            <span v-if="erreurs.motDePasse" class="champ__erreur">{{ erreurs.motDePasse }}</span>
          </label>

          <label class="connexion__rester champ-inline">
            <input type="checkbox" v-model="resterConnecte" />
            Rester connecté
          </label>

          <button type="submit" class="bouton bouton--bloc connexion__submit" :disabled="envoiEnCours">
            {{ envoiEnCours ? 'Connexion…' : 'Se connecter' }}
          </button>

          <router-link :to="{ name: 'mot-de-passe-oublie' }" class="connexion__lien">
            Mot de passe oublié ?
          </router-link>
        </form>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuth } from '../composables/auth'

const route = useRoute()
const router = useRouter()
const { connecter } = useAuth()

const identifiant = ref('')
const motDePasse = ref('')
const resterConnecte = ref(localStorage.getItem('kamille_rester_connecte') === '1')
const erreurs = ref({})
const messageErreur = ref('')
const envoiEnCours = ref(false)

async function envoyer() {
  erreurs.value = {}
  messageErreur.value = ''

  if (!identifiant.value) {
    erreurs.value.identifiant = 'Indiquez votre email ou numéro de téléphone.'
  }
  if (!motDePasse.value) {
    erreurs.value.motDePasse = 'Indiquez votre mot de passe.'
  }
  if (Object.keys(erreurs.value).length > 0) {
    return
  }

  envoiEnCours.value = true

  try {
    await connecter(identifiant.value, motDePasse.value, resterConnecte.value)
    localStorage.setItem('kamille_rester_connecte', resterConnecte.value ? '1' : '0')
    const redirect = typeof route.query.redirect === 'string' ? route.query.redirect : '/'
    router.replace(redirect)
  } catch (error) {
    const message = error.response?.data?.message

    if (!error.response) {
      messageErreur.value = 'Impossible de joindre le serveur. Vérifiez que Symfony est bien démarré.'
    } else if (error.response?.status === 401) {
      messageErreur.value = message ?? 'Identifiant ou mot de passe incorrect.'
    } else if (message) {
      messageErreur.value = message
    } else {
      messageErreur.value = 'Impossible de se connecter. Réessayez plus tard.'
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
  position: relative;
  overflow: hidden;

  &__decor {
    position: absolute;
    border-radius: 50%;
    pointer-events: none;
    opacity: 0.45;

    &--1 {
      width: 280px;
      height: 280px;
      top: -80px;
      right: -60px;
      background: radial-gradient(circle, rgba(204, 167, 97, 0.35) 0%, transparent 70%);
      animation: connexion-flotte 8s ease-in-out infinite;
    }

    &--2 {
      width: 220px;
      height: 220px;
      bottom: -40px;
      left: -50px;
      background: radial-gradient(circle, rgba(204, 167, 97, 0.25) 0%, transparent 70%);
      animation: connexion-flotte 10s ease-in-out infinite reverse;
    }
  }

  &__carte {
    width: 100%;
    max-width: 420px;
    border: 1px solid rgba(204, 167, 97, 0.35);
    border-radius: calc($radius + 4px);
    overflow: hidden;
    background: $color-bg;
    box-shadow: $shadow-soft;
    position: relative;
    z-index: 1;
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

  &__submit {
    margin-top: $space-xs;

    &:disabled {
      &::after {
        display: none;
      }
    }
  }

  &__rester {
    color: $color-text;
    font-size: var(--fs-base);
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
    animation: scale-in 0.45s cubic-bezier(0.33, 1, 0.68, 1);

    &--erreur {
      background: rgba(192, 57, 43, 0.1);
      color: $color-error;
      border: 1px solid rgba(192, 57, 43, 0.25);
    }
  }
}

.connexion-carte-enter-active {
  transition:
    opacity 0.5s ease-out,
    transform 0.5s cubic-bezier(0.33, 1, 0.68, 1);
}

.connexion-carte-enter-from {
  opacity: 0;
  transform: translateY(8px);
}

@keyframes connexion-flotte {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(6px); }
}

@media (prefers-reduced-motion: reduce) {
  .connexion__decor {
    animation: none;
  }

  .connexion-carte-enter-active {
    transition: opacity 0.2s ease;
  }

  .connexion-carte-enter-from {
    transform: none;
  }
}
</style>
