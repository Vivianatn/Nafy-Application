<template>
  <div class="vaisselle-detail anim-page">
    <button type="button" class="vaisselle-detail__retour bouton bouton--secondaire" @click="retour">
      ← Retour à l'inventaire
    </button>

    <h1 class="page-titre">{{ modeCreation ? 'Ajouter une vaisselle' : 'Modifier la vaisselle' }}</h1>

    <p v-if="chargement" class="vaisselle-detail__etat">Chargement…</p>
    <p v-else-if="erreurChargement" class="vaisselle-detail__etat vaisselle-detail__etat--erreur">{{ erreurChargement }}</p>

    <form v-else class="form form--mobile anim-form" @submit.prevent="enregistrer">
      <p v-if="messageErreur" class="message message--erreur">{{ messageErreur }}</p>

      <div v-if="!modeCreation" class="vaisselle-detail__media">
        <img
          v-if="imageKit(nom)"
          :src="imageKit(nom)"
          :alt="nom"
          @error="onErreurImageKit"
        />
      </div>

      <label class="champ">Nom<span class="obligatoire">*</span>
        <input type="text" v-model.trim="nom" maxlength="100" />
        <span v-if="erreurs.nom" class="champ__erreur">{{ erreurs.nom }}</span>
      </label>

      <label class="champ">Quantité maximale<span class="obligatoire">*</span>
        <input type="number" min="1" v-model.number="quantiteMax" />
        <span v-if="erreurs.quantiteMax" class="champ__erreur">{{ erreurs.quantiteMax }}</span>
      </label>

      <div class="form-submit-sticky">
        <button type="submit" class="bouton bouton--bloc" :disabled="envoiEnCours">
          {{ envoiEnCours ? 'Enregistrement…' : modeCreation ? 'Créer la vaisselle' : 'Enregistrer les modifications' }}
        </button>
      </div>

      <button
        v-if="!modeCreation"
        type="button"
        class="bouton bouton--secondaire bouton--bloc vaisselle-detail__supprimer"
        :disabled="suppressionEnCours"
        @click="supprimer"
      >
        {{ suppressionEnCours ? 'Suppression…' : 'Supprimer cette vaisselle' }}
      </button>
    </form>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '../api'
import { imageKit, onErreurImageKit } from '../composables/kitImages'
import { useNotification } from '../composables/notification'

const props = defineProps({
  mode: {
    type: String,
    default: 'edit',
  },
})

const route = useRoute()
const router = useRouter()
const { notifier } = useNotification()

const modeCreation = computed(() => props.mode === 'create' || route.name === 'vaisselle-ajouter')

const nom = ref('')
const quantiteMax = ref(300)
const chargement = ref(!modeCreation.value)
const erreurChargement = ref('')
const erreurs = ref({})
const messageErreur = ref('')
const envoiEnCours = ref(false)
const suppressionEnCours = ref(false)

function retour() {
  router.push({ name: 'inventaire' })
}

async function chargerKit() {
  if (modeCreation.value) {
    return
  }

  chargement.value = true
  erreurChargement.value = ''

  try {
    const { data } = await api.get(`/kits/${route.params.id}`)
    nom.value = data.nom ?? ''
    quantiteMax.value = data.quantiteMax ?? 300
  } catch {
    erreurChargement.value = 'Impossible de charger cette vaisselle.'
  } finally {
    chargement.value = false
  }
}

async function enregistrer() {
  erreurs.value = {}
  messageErreur.value = ''
  envoiEnCours.value = true

  const payload = {
    nom: nom.value,
    quantiteMax: quantiteMax.value,
  }

  try {
    if (modeCreation.value) {
      await api.post('/kits', payload)
      notifier('Vaisselle créée.', 'succes')
    } else {
      await api.put(`/kits/${route.params.id}`, payload)
      notifier('Vaisselle mise à jour.', 'succes')
    }
    router.push({ name: 'inventaire' })
  } catch (error) {
    if (error.response?.status === 422) {
      erreurs.value = error.response.data.erreurs ?? {}
      messageErreur.value = 'Veuillez corriger les champs en erreur.'
    } else {
      messageErreur.value = error.response?.data?.message ?? 'Enregistrement impossible.'
    }
  } finally {
    envoiEnCours.value = false
  }
}

async function supprimer() {
  if (!window.confirm(`Supprimer la vaisselle « ${nom.value} » ?`)) {
    return
  }

  suppressionEnCours.value = true

  try {
    await api.delete(`/kits/${route.params.id}`)
    notifier('Vaisselle supprimée.', 'succes')
    router.push({ name: 'inventaire' })
  } catch (error) {
    notifier(error.response?.data?.message ?? 'Suppression impossible.', 'erreur')
  } finally {
    suppressionEnCours.value = false
  }
}

onMounted(chargerKit)
</script>

<style lang="scss" scoped>
@use '../../styles/variables' as *;

.vaisselle-detail {
  &__retour {
    margin-bottom: $space-md;
  }

  &__etat {
    color: $color-muted;

    &--erreur {
      color: $color-error;
    }
  }

  &__media {
    margin-bottom: $space-lg;
    border-radius: calc($radius + 2px);
    overflow: hidden;
    border: 1px solid rgba(204, 167, 97, 0.35);
    aspect-ratio: 16 / 10;
    background: $color-placeholder;

    img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
    }
  }

  &__supprimer {
    margin-top: $space-md;
    color: $color-error;
    border-color: rgba(192, 57, 43, 0.35);
  }
}
</style>
