<template>
  <div class="anim-page">
    <h1 class="page-titre">Ajouter une secrétaire</h1>

    <form class="form anim-form" @submit.prevent="envoyer">
      <p v-if="messageErreur" class="message message--erreur">{{ messageErreur }}</p>

      <div class="form-grille">
        <label class="champ">Nom
          <input type="text" name="nom" v-model.trim="form.nom" />
          <span v-if="erreurs.nom" class="champ__erreur">{{ erreurs.nom }}</span>
        </label>
        <label class="champ">Prénom
          <input type="text" name="prenom" v-model.trim="form.prenom" />
          <span v-if="erreurs.prenom" class="champ__erreur">{{ erreurs.prenom }}</span>
        </label>
      </div>

      <label class="champ">Adresse email
        <input type="email" name="email" v-model.trim="form.email" />
        <span v-if="erreurs.email" class="champ__erreur">{{ erreurs.email }}</span>
      </label>
      <label class="champ">Numéro de téléphone
        <input type="tel" name="telephone" v-model.trim="form.telephone" />
        <span v-if="erreurs.telephone" class="champ__erreur">{{ erreurs.telephone }}</span>
      </label>
      <label class="champ">Mot de passe
        <input type="password" name="motDePasse" v-model="form.motDePasse" />
        <span v-if="erreurs.motDePasse" class="champ__erreur">{{ erreurs.motDePasse }}</span>
      </label>
      <label class="champ">Confirmation du mot de passe
        <input type="password" name="confirmationMotDePasse" v-model="form.confirmationMotDePasse" />
        <span v-if="erreurs.confirmationMotDePasse" class="champ__erreur">{{ erreurs.confirmationMotDePasse }}</span>
      </label>

      <button type="submit" class="bouton bouton--bloc" :disabled="envoiEnCours">
        {{ envoiEnCours ? 'Envoi…' : 'Ajouter une secrétaire' }}
      </button>
    </form>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '../api'
import { useNotification } from '../composables/notification'

const router = useRouter()
const { notifier } = useNotification()

const form = reactive({
  nom: '',
  prenom: '',
  email: '',
  telephone: '',
  motDePasse: '',
  confirmationMotDePasse: '',
})

const erreurs = ref({})
const messageErreur = ref('')
const envoiEnCours = ref(false)

function reinitialiser() {
  form.nom = ''
  form.prenom = ''
  form.email = ''
  form.telephone = ''
  form.motDePasse = ''
  form.confirmationMotDePasse = ''
}

async function envoyer() {
  erreurs.value = {}
  messageErreur.value = ''
  envoiEnCours.value = true

  try {
    const reponse = await api.post('/secretaires', form)
    const secretaire = reponse.data
    reinitialiser()
    notifier(`La secrétaire « ${secretaire.prenom} ${secretaire.nom} » a bien été ajoutée.`, 'succes')
    router.push({ name: 'secretaires' })
  } catch (error) {
    if (error.response?.status === 422) {
      erreurs.value = error.response.data.erreurs ?? {}
      messageErreur.value = 'Veuillez corriger les champs en erreur.'
      return
    }

    messageErreur.value = error.response?.data?.message ?? 'Une erreur est survenue.'
  } finally {
    envoiEnCours.value = false
  }
}
</script>
