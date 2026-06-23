<template>
  <div class="anim-page">
    <h1 class="page-titre">{{ titre }}</h1>

    <form class="form form--mobile anim-form" @submit.prevent="envoyer">
      <p v-if="messageErreur" class="message message--erreur">{{ messageErreur }}</p>

      <section class="form-section">
        <h2 class="form-section__titre">Client(s)<span class="obligatoire">*</span></h2>
        <div class="form-grille">
          <label class="champ">Nom<span class="obligatoire">*</span>
            <input type="text" v-model.trim="clients[0].nom" />
            <span v-if="erreurs.client1Nom" class="champ__erreur">{{ erreurs.client1Nom }}</span>
          </label>
          <label class="champ">Prénom<span class="obligatoire">*</span>
            <input type="text" v-model.trim="clients[0].prenom" />
            <span v-if="erreurs.client1Prenom" class="champ__erreur">{{ erreurs.client1Prenom }}</span>
          </label>
        </div>
        <label class="champ">Adresse
          <input type="text" v-model.trim="clients[0].adresse" />
        </label>
        <label class="champ">Numéro de téléphone (référence)<span class="obligatoire">*</span>
          <input type="tel" inputmode="tel" autocomplete="tel" v-model.trim="clients[0].telephone" />
          <span v-if="erreurs.client1Telephone" class="champ__erreur">{{ erreurs.client1Telephone }}</span>
        </label>

        <template v-if="deuxiemeClientActif">
          <p class="form-section__sous-titre">Client 2<span class="obligatoire">*</span></p>
          <div class="form-grille">
            <label class="champ">Nom<span class="obligatoire">*</span>
              <input type="text" v-model.trim="clients[1].nom" />
              <span v-if="erreurs.client2Nom" class="champ__erreur">{{ erreurs.client2Nom }}</span>
            </label>
            <label class="champ">Prénom<span class="obligatoire">*</span>
              <input type="text" v-model.trim="clients[1].prenom" />
              <span v-if="erreurs.client2Prenom" class="champ__erreur">{{ erreurs.client2Prenom }}</span>
            </label>
          </div>
          <label class="champ">Adresse
            <input type="text" v-model.trim="clients[1].adresse" />
          </label>
          <label class="champ">Numéro de téléphone
            <input type="tel" inputmode="tel" autocomplete="tel" v-model.trim="clients[1].telephone" />
          </label>
          <button type="button" class="bouton bouton--secondaire" @click="retirerSecondClient">
            Retirer le second client
          </button>
        </template>

        <button
          v-else
          type="button"
          class="bouton bouton--secondaire"
          @click="ajouterSecondClient"
        >
          Ajouter un client
        </button>
      </section>

      <section class="form-section">
        <h2 class="form-section__titre">Événement</h2>
        <label class="champ">Rattacher à un événement existant
          <select v-model="evenementSelectionneId">
            <option value="">— Aucun —</option>
            <option v-for="evt in evenementsDisponibles" :key="evt.id" :value="String(evt.id)">
              {{ libelleEvenement(evt) }}
            </option>
          </select>
        </label>
        <label class="champ">Adresse de l'événement<span class="obligatoire">*</span>
          <input type="text" v-model.trim="adresseEvenement" />
          <span v-if="erreurs.adresseEvenement" class="champ__erreur">{{ erreurs.adresseEvenement }}</span>
        </label>
        <label class="champ">Date de réservation<span class="obligatoire">*</span>
          <input type="date" v-model="dateReservation" />
          <span v-if="erreurs.dateReservation" class="champ__erreur">{{ erreurs.dateReservation }}</span>
        </label>
      </section>

      <section class="form-section">
        <h2 class="form-section__titre">Kits de vaisselle<span class="obligatoire">*</span></h2>
        <p v-if="erreurs.kits" class="champ__erreur">{{ erreurs.kits }}</p>
        <ul class="kits">
          <li v-for="kit in kits" :key="kit.id" class="kit">
            <span class="kit__nom">{{ kit.nom }}</span>
            <span class="kit__photo-label">Photo</span>
            <button
              type="button"
              class="kit__photo"
              :class="{ 'kit__photo--actif': kitsActifs[kit.id] }"
              :aria-pressed="!!kitsActifs[kit.id]"
              :aria-label="'Sélectionner le kit ' + kit.nom"
              @click="toggleKit(kit.id)"
            >
              <img
                v-if="imageKit(kit.nom)"
                :src="imageKit(kit.nom)"
                :alt="kit.nom"
                class="kit__photo-img"
                @error="onErreurImageKit"
              />
            </button>
            <label v-if="kitsActifs[kit.id]" class="champ">
              Quantité
              <input
                type="number"
                min="1"
                :max="kit.quantiteMax"
                v-model.number="quantitesKits[kit.id]"
              />
            </label>
          </li>
        </ul>
      </section>

      <section class="form-section">
        <h2 class="form-section__titre">Chandeliers</h2>
        <label class="champ-inline"><input type="radio" name="chandeliers" value="oui" v-model="chandeliers" /> Oui</label>
        <label v-if="chandeliers === 'oui'" class="champ">Quantité
          <input type="number" min="1" v-model.number="quantiteChandeliers" />
        </label>
        <label class="champ-inline"><input type="radio" name="chandeliers" value="non" v-model="chandeliers" /> Non</label>
      </section>

      <section class="form-section">
        <h2 class="form-section__titre">Flûtes et verres</h2>
        <label class="champ-inline"><input type="radio" name="flutesVerres" value="flutes" v-model="flutesVerres" /> Louer uniquement des flûtes</label>
        <label class="champ-inline"><input type="radio" name="flutesVerres" value="verres" v-model="flutesVerres" /> Louer uniquement des verres</label>
        <label class="champ-inline"><input type="radio" name="flutesVerres" value="les2" v-model="flutesVerres" /> Louer les 2</label>
        <label v-if="flutesVerres" class="champ">Quantité
          <input type="number" min="1" v-model.number="quantiteFlutesVerres" />
        </label>
      </section>

      <section class="form-section">
        <h2 class="form-section__titre">Livraison<span class="obligatoire">*</span></h2>
        <label class="champ-inline"><input type="radio" name="livraison" value="oui" v-model="livraison" /> Oui</label>
        <template v-if="livraison === 'oui'">
          <label class="champ">Ville de livraison<span class="obligatoire">*</span>
            <RechercheVille v-model="villeSelectionneeId" :villes="villes" />
            <span v-if="erreurs.villeId" class="champ__erreur">{{ erreurs.villeId }}</span>
          </label>
        </template>
        <label class="champ-inline"><input type="radio" name="livraison" value="non" v-model="livraison" /> Non</label>
        <span v-if="erreurs.livraison" class="champ__erreur">{{ erreurs.livraison }}</span>
      </section>

      <section class="form-section">
        <h2 class="form-section__titre">Vaisselle à nettoyer<span class="obligatoire">*</span></h2>
        <label class="champ-inline">
          <input type="checkbox" v-model="vaisselleANettoyer" />
          Vaisselle à nettoyer
        </label>
        <span v-if="erreurs.vaisselleANettoyer" class="champ__erreur">{{ erreurs.vaisselleANettoyer }}</span>
        <label class="champ">Date de rentrée<span class="obligatoire">*</span>
          <input type="date" v-model="dateRentree" />
          <span v-if="erreurs.dateRentree" class="champ__erreur">{{ erreurs.dateRentree }}</span>
        </label>
      </section>

      <section class="form-section">
        <h2 class="form-section__titre">{{ libelleRecap }}</h2>
        <div class="recap-grille">
          <label class="champ">Kits — Prix (€)
            <input type="text" :value="formaterPrix(prix.prixKits)" readonly />
          </label>
          <label class="champ">Livraison — Prix (€)
            <input type="text" :value="formaterPrix(prix.prixLivraison)" readonly />
          </label>
          <label class="champ">Lavage — Prix (€)
            <input type="text" :value="formaterPrix(prix.prixLavage)" readonly />
          </label>
          <label v-if="type === 'devis'" class="champ">Caution — Prix (€)
            <input type="text" :value="formaterPrix(prix.prixCaution)" readonly />
          </label>
          <label v-if="type === 'devis'" class="champ">Arrhes — Prix (€)
            <input type="text" :value="formaterPrix(prix.prixArrhes)" readonly />
          </label>
          <label class="champ">Prix final (€)
            <input type="text" :value="formaterPrix(prix.prixFinal)" readonly />
          </label>
        </div>
      </section>

      <section class="form-section">
        <label class="champ">Notes de commande
          <textarea v-model.trim="noteCommande"></textarea>
        </label>
      </section>

      <section class="form-section">
        <h2 class="form-section__titre">Conditions de vente</h2>
        <label class="champ-inline">
          <input type="checkbox" v-model="conditionCasse" />
          Toute casse ou perte sera facturée
        </label>
        <span v-if="erreurs.conditionCasse" class="champ__erreur">{{ erreurs.conditionCasse }}</span>
        <label class="champ-inline">
          <input type="checkbox" v-model="conditionCaution" />
          La caution sera restituée après vérification du matériel
        </label>
        <span v-if="erreurs.conditionCaution" class="champ__erreur">{{ erreurs.conditionCaution }}</span>
        <label class="champ-inline">
          <input type="checkbox" v-model="conditionReservation" />
          La réservation est validée après arrhes
        </label>
        <span v-if="erreurs.conditionReservation" class="champ__erreur">{{ erreurs.conditionReservation }}</span>
      </section>

      <section class="form-section">
        <h2 class="form-section__titre">Validation</h2>
        <label class="champ-inline">
          <input type="checkbox" v-model="bonPourAccord" />
          Bon pour accord
        </label>
        <span v-if="erreurs.bonPourAccord" class="champ__erreur">{{ erreurs.bonPourAccord }}</span>
        <div class="form-submit-sticky">
          <button type="submit" class="bouton bouton--bloc" :disabled="envoiEnCours">
            {{ envoiEnCours ? 'Envoi…' : libelleSubmit }}
          </button>
        </div>
      </section>
    </form>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref, watch, nextTick } from 'vue'
import { useRouter } from 'vue-router'
import api from '../api'
import { calculerPrix, formaterPrix } from '../composables/calculPrix'
import { formaterDate, formaterDateHeure } from '../composables/date'
import { imageKit, onErreurImageKit } from '../composables/kitImages'
import { useNotification } from '../composables/notification'
import RechercheVille from './RechercheVille.vue'

const props = defineProps({
  type: {
    type: String,
    required: true,
    validator: (v) => ['devis', 'facture'].includes(v),
  },
  titre: { type: String, required: true },
  endpoint: { type: String, required: true },
  libelleSubmit: { type: String, required: true },
  libelleRecap: { type: String, required: true },
  retourApresEnvoi: { type: String, default: '' },
})

const router = useRouter()
const { notifier } = useNotification()

const clients = reactive([
  { nom: '', prenom: '', adresse: '', telephone: '' },
  { nom: '', prenom: '', adresse: '', telephone: '' },
])
const deuxiemeClientActif = ref(false)

const adresseEvenement = ref('')
const dateReservation = ref('')
const evenementsDisponibles = ref([])
const evenementSelectionneId = ref('')
const kits = ref([])
const villes = ref([])
const kitsActifs = reactive({})
const quantitesKits = reactive({})
const chandeliers = ref('')
const quantiteChandeliers = ref('')
const flutesVerres = ref('')
const quantiteFlutesVerres = ref('')
const livraison = ref('')
const villeSelectionneeId = ref('')
const nombreKm = ref('')
const vaisselleANettoyer = ref(false)
const dateRentree = ref('')
const noteCommande = ref('')
const conditionCasse = ref(false)
const conditionCaution = ref(false)
const conditionReservation = ref(false)
const bonPourAccord = ref(false)

const erreurs = ref({})
const messageErreur = ref('')
const envoiEnCours = ref(false)

const totalKits = computed(() =>
  kits.value.reduce((total, kit) => {
    if (!kitsActifs[kit.id]) {
      return total
    }
    const quantite = Number(quantitesKits[kit.id]) || 0
    return total + quantite
  }, 0),
)

const prix = computed(() =>
  calculerPrix({
    totalKits: totalKits.value,
    livraison: livraison.value,
    km: Number(nombreKm.value) || 0,
    vaisselleANettoyer: vaisselleANettoyer.value,
    avecArrhes: props.type === 'devis',
    avecCaution: props.type === 'devis',
  }),
)

function libelleEvenement(evt) {
  const titre = evt.titre || evt.adresseEvenement || 'Événement'
  const date = evt.dateReservation ? formaterDate(evt.dateReservation) : ''
  return date ? `${titre} — ${date}` : titre
}

function appliquerEvenement(evt) {
  if (!evt) return
  adresseEvenement.value = evt.adresseEvenement || ''
  dateReservation.value = evt.dateReservation || ''
  dateRentree.value = evt.dateRentree || ''
  if (evt.note && !noteCommande.value) {
    noteCommande.value = evt.note
  }
}

function toggleKit(kitId) {
  kitsActifs[kitId] = !kitsActifs[kitId]
  if (!kitsActifs[kitId]) {
    quantitesKits[kitId] = ''
  }
}

function ajouterSecondClient() {
  deuxiemeClientActif.value = true
}

function retirerSecondClient() {
  deuxiemeClientActif.value = false
  clients[1].nom = ''
  clients[1].prenom = ''
  clients[1].adresse = ''
  clients[1].telephone = ''
  delete erreurs.value.client2Nom
  delete erreurs.value.client2Prenom
}

function clientsPourEnvoi() {
  const liste = [{ ...clients[0] }]
  if (deuxiemeClientActif.value) {
    liste.push({ ...clients[1] })
  }
  return liste
}

function validerCoteClient() {
  const e = {}

  if (!clients[0].nom) e.client1Nom = 'Le nom du client est obligatoire.'
  if (!clients[0].prenom) e.client1Prenom = 'Le prénom du client est obligatoire.'
  if (!clients[0].telephone) e.client1Telephone = 'Le numéro de téléphone (référence) est obligatoire.'

  if (deuxiemeClientActif.value) {
    if (!clients[1].nom) e.client2Nom = 'Le nom du second client est obligatoire.'
    if (!clients[1].prenom) e.client2Prenom = 'Le prénom du second client est obligatoire.'
  }
  if (!adresseEvenement.value) e.adresseEvenement = "L'adresse de l'événement est obligatoire."
  if (!dateReservation.value) e.dateReservation = 'La date de réservation est obligatoire.'

  if (totalKits.value <= 0) {
    e.kits = 'Sélectionnez au moins un kit avec une quantité.'
  } else {
    for (const kit of kits.value) {
      if (!kitsActifs[kit.id]) continue
      const quantite = Number(quantitesKits[kit.id]) || 0
      if (quantite > kit.quantiteMax) {
        e.kits = `La quantité pour « ${kit.nom} » dépasse le maximum (${kit.quantiteMax}).`
        break
      }
    }
  }

  if (livraison.value !== 'oui' && livraison.value !== 'non') {
    e.livraison = 'Indiquez si une livraison est nécessaire.'
  } else if (livraison.value === 'oui' && !villeSelectionneeId.value) {
    e.villeId = 'La ville de livraison est obligatoire.'
  }

  if (!dateRentree.value) {
    e.dateRentree = 'La date de rentrée est obligatoire.'
  }

  if (!conditionCasse.value) e.conditionCasse = 'Cette condition doit être acceptée.'
  if (!conditionCaution.value) e.conditionCaution = 'Cette condition doit être acceptée.'
  if (!conditionReservation.value) e.conditionReservation = 'Cette condition doit être acceptée.'
  if (!bonPourAccord.value) e.bonPourAccord = 'Le bon pour accord est obligatoire.'

  return e
}

function construirePayload() {
  const kitsSelectionnes = kits.value
    .filter((kit) => kitsActifs[kit.id] && (Number(quantitesKits[kit.id]) || 0) > 0)
    .map((kit) => ({
      kitId: kit.id,
      quantite: Number(quantitesKits[kit.id]),
    }))

  return {
    clients: clientsPourEnvoi(),
    adresseEvenement: adresseEvenement.value,
    dateReservation: dateReservation.value,
    kits: kitsSelectionnes,
    chandeliers: chandeliers.value === 'oui',
    quantiteChandeliers: chandeliers.value === 'oui' ? Number(quantiteChandeliers.value) || 0 : null,
    flutesVerresOption: flutesVerres.value || null,
    quantiteFlutesVerres: flutesVerres.value ? Number(quantiteFlutesVerres.value) || 0 : null,
    livraison: livraison.value === 'oui',
    villeId: livraison.value === 'oui' ? Number(villeSelectionneeId.value) : null,
    vaisselleANettoyer: vaisselleANettoyer.value,
    dateRentree: dateRentree.value || null,
    noteCommande: noteCommande.value,
    conditionCasse: conditionCasse.value,
    conditionCaution: conditionCaution.value,
    conditionReservation: conditionReservation.value,
    bonPourAccord: bonPourAccord.value,
  }
}

async function scrollVersPremiereErreur() {
  await nextTick()
  const cible = document.querySelector('.champ__erreur, .message--erreur')
  cible?.closest('.champ, .form-section, .message')?.scrollIntoView({ behavior: 'smooth', block: 'center' })
}

async function envoyer() {
  erreurs.value = {}
  messageErreur.value = ''

  const validation = validerCoteClient()
  if (Object.keys(validation).length > 0) {
    erreurs.value = validation
    messageErreur.value = 'Veuillez corriger les champs en erreur.'
    await scrollVersPremiereErreur()
    return
  }

  envoiEnCours.value = true

  try {
    const reponse = await api.post(props.endpoint, construirePayload())

    const { id, numero, createdAt } = reponse.data
    const dateHeure = formaterDateHeure(createdAt)
    const libelleNumero = numero || String(id)

    notifier(
      props.type === 'devis'
        ? `Devis n°${libelleNumero} créé le ${dateHeure}.`
        : `Facture n°${libelleNumero} créée le ${dateHeure}.`,
      'succes',
    )

    if (props.retourApresEnvoi === 'historique') {
      router.push({ name: 'home', query: { section: 'historique' } })
    } else {
      router.push({ name: 'home' })
    }
  } catch (error) {
    if (error.response?.status === 422) {
      erreurs.value = error.response.data.erreurs ?? {}
      messageErreur.value = 'Veuillez corriger les champs en erreur.'
      await scrollVersPremiereErreur()
    } else {
      messageErreur.value = error.response?.data?.message ?? 'Une erreur est survenue.'
    }
  } finally {
    envoiEnCours.value = false
  }
}

onMounted(async () => {
  try {
    const [reponseKits, reponseVilles, reponseEvenements] = await Promise.all([
      api.get('/kits'),
      api.get('/villes'),
      api.get('/evenements'),
    ])
    kits.value = reponseKits.data
    villes.value = reponseVilles.data
    evenementsDisponibles.value = Array.isArray(reponseEvenements.data) ? reponseEvenements.data : []
  } catch {
    kits.value = []
    villes.value = []
    evenementsDisponibles.value = []
  }
})

watch(evenementSelectionneId, (id) => {
  if (!id) return
  const evt = evenementsDisponibles.value.find((item) => String(item.id) === id)
  appliquerEvenement(evt)
})

watch(villeSelectionneeId, (id) => {
  const ville = villes.value.find((v) => v.id === Number(id))
  nombreKm.value = ville ? ville.kilometres : ''
})

watch(livraison, (valeur) => {
  if (valeur !== 'oui') {
    villeSelectionneeId.value = ''
    nombreKm.value = ''
  }
})
</script>
