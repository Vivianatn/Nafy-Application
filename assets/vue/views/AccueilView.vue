<template>
  <div class="accueil anim-page">
    <h1 class="page-titre">Accueil</h1>

    <section class="bloc">
      <h2 class="section-titre">Calendrier</h2>

      <div class="calendrier">
        <header class="calendrier__nav">
          <button type="button" class="calendrier__nav-btn" @click="moisPrecedent" aria-label="Mois précédent">‹</button>
          <span class="calendrier__titre">{{ nomMois }} {{ annee }}</span>
          <button type="button" class="calendrier__nav-btn" @click="moisSuivant" aria-label="Mois suivant">›</button>
        </header>

        <div class="calendrier__corps" :key="cleMois">
          <div class="calendrier__entete-jours">
            <span v-for="jour in joursSemaine" :key="jour" class="calendrier__nom-jour">{{ jour }}</span>
          </div>

          <div class="calendrier__grille">
            <button
              v-for="(cellule, index) in cellulesMois"
              :key="index"
              type="button"
              class="calendrier__jour"
              :class="{
                'calendrier__jour--vide': !cellule,
                'calendrier__jour--aujourdhui': cellule && estAujourdHui(cellule),
                'calendrier__jour--selection': cellule && estSelectionne(cellule),
                'calendrier__jour--evenement': cellule && aEvenement(cellule),
              }"
              :disabled="!cellule"
              :aria-label="cellule ? `Sélectionner le ${cellule} ${nomMois}` : undefined"
              @click="selectionnerJour(cellule)"
            >
              {{ cellule }}
              <span
                v-if="cellule && heureJour(cellule)"
                class="calendrier__jour-heure"
                :title="'Récupération à ' + formaterHeure(heureJour(cellule))"
                aria-hidden="true"
              >
                <svg viewBox="0 0 24 24" fill="none">
                  <circle cx="12" cy="12" r="8" stroke="currentColor" stroke-width="1.5" />
                  <path d="M12 8v4l2.5 1.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                </svg>
                {{ formaterHeure(heureJour(cellule)) }}
              </span>
            </button>
          </div>
        </div>

        <Transition name="calendrier-detail">
          <div v-if="dateSelectionnee" class="calendrier__detail">
            <h3 class="calendrier__detail-titre">{{ titreDateSelectionnee }}</h3>

            <p v-if="evenementsJourSelectionne.length === 0" class="historique__etat">
              Aucun événement ce jour-là.
            </p>
            <ul v-else class="historique">
              <li
                v-for="item in evenementsJourSelectionne"
                :key="item.sourceType + '-' + item.id"
                class="historique__item"
              >
                <div class="historique__entete">
                  <span
                    class="historique__type"
                    :class="item.sourceType !== 'calendrier' ? 'historique__type--' + item.sourceType : undefined"
                  >
                    {{ libelleTypeCalendrier(item) }}
                  </span>
                  <strong class="historique__numero">{{ libelleEnteteCalendrier(item) }}</strong>
                </div>
                <p v-if="noteCalendrier(item)" class="historique__note">{{ noteCalendrier(item) }}</p>
                <p v-if="item.sourceType === 'devis' || item.sourceType === 'facture'" class="historique__ligne">
                  Créé le {{ formaterDateHeure(item.createdAt) }}
                </p>
                <p v-if="item.adresseEvenement" class="historique__ligne">{{ item.adresseEvenement }}</p>
                <p v-if="item.dateRentree" class="historique__ligne">
                  Rentrée le {{ formaterDate(item.dateRentree) }}
                </p>
                <HeureRecuperationVaisselle
                  v-if="item.sourceType === 'devis'"
                  :devis-id="item.id"
                  :heure-initiale="item.heureRecuperationVaisselle ?? ''"
                  @heure-change="mettreAJourHeureEvenement"
                />
                <HeureRecuperationVaisselle
                  v-else
                  :evenement-id="item.id"
                  :heure-initiale="item.heureRecuperationVaisselle ?? ''"
                  @heure-change="mettreAJourHeureCalendrier"
                />
                <button
                  type="button"
                  class="bouton bouton--secondaire historique__supprimer"
                  :disabled="suppressionEnCours === item.sourceType + '-' + item.id"
                  @click="supprimerItemCalendrier(item)"
                >
                  {{ suppressionEnCours === item.sourceType + '-' + item.id ? 'Suppression…' : 'Supprimer' }}
                </button>
              </li>
            </ul>

            <form class="calendrier__ajout" @submit.prevent="creerEvenementCalendrier">
              <h4 class="calendrier__ajout-titre">Ajouter un événement</h4>
              <label class="champ">Titre
                <input type="text" v-model.trim="nouvelEvenement.titre" placeholder="Ex. Mariage Dupont" />
              </label>
              <label class="champ">Adresse de l'événement
                <input type="text" v-model.trim="nouvelEvenement.adresseEvenement" />
              </label>
              <label class="champ">Date de rentrée
                <input type="date" v-model="nouvelEvenement.dateRentree" />
              </label>
              <label class="champ">Note
                <textarea v-model.trim="nouvelEvenement.note" rows="2"></textarea>
              </label>
              <button type="submit" class="bouton bouton--bloc" :disabled="creationEvenementEnCours">
                {{ creationEvenementEnCours ? 'Création…' : 'Ajouter au calendrier' }}
              </button>
            </form>
          </div>
        </Transition>
      </div>
    </section>

    <section id="historique-commandes" class="bloc">
      <h2 class="section-titre">Historique des commandes</h2>
      <p v-if="chargement" class="historique__etat historique__etat--chargement">
        <span class="spinner" aria-hidden="true"></span>
        Chargement…
      </p>
      <p v-else-if="commandes.length === 0" class="historique__etat">Aucun devis ni facture enregistré.</p>
      <ul v-else class="historique historique--commandes anim-list">
        <li
          v-for="commande in commandes"
          :key="commande.type + '-' + commande.id"
          class="historique__carte"
          :class="{ 'historique__carte--aujourdhui': estCreeAujourdHui(commande.createdAt) }"
        >
          <div class="historique__entete">
            <span class="historique__type" :class="'historique__type--' + commande.type">
              {{ commande.type === 'devis' ? 'Devis' : 'Facture' }}
            </span>
            <strong class="historique__numero">n°{{ libelleNumero(commande) }}</strong>
          </div>
          <p v-if="commande.noteCommande" class="historique__note">{{ commande.noteCommande }}</p>
          <div class="historique__corps">
            <p class="historique__ligne">Créé le {{ formaterDateHeure(commande.createdAt) }}</p>
            <p v-if="commande.dateReservation" class="historique__ligne">
              Réservation le {{ formaterDate(commande.dateReservation) }}
            </p>
          </div>
          <div class="historique__actions">
            <button
              v-if="commande.type === 'devis'"
              type="button"
              class="bouton historique__action historique__action--facture"
              :disabled="factureEnCours === 'devis-' + commande.id"
              @click="creerFactureDepuisDevis(commande.id, libelleNumero(commande))"
            >
              {{ factureEnCours === 'devis-' + commande.id ? 'Création…' : 'Créer facture' }}
            </button>
            <button
              type="button"
              class="bouton bouton--secondaire historique__action"
              :disabled="telechargementEnCours === 'dl-' + commande.type + '-' + commande.id"
              @click="telechargerCommande(commande)"
            >
              {{ telechargementEnCours === 'dl-' + commande.type + '-' + commande.id ? 'Téléchargement…' : 'Télécharger' }}
            </button>
            <button
              type="button"
              class="bouton bouton--secondaire historique__action"
              :disabled="suppressionEnCours === commande.type + '-' + commande.id"
              @click="supprimerCommande(commande.type, commande.id, libelleNumero(commande))"
            >
              {{ suppressionEnCours === commande.type + '-' + commande.id ? 'Suppression…' : 'Supprimer' }}
            </button>
          </div>
        </li>
      </ul>
    </section>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick, reactive, watch } from 'vue'
import { useRoute } from 'vue-router'
import api from '../api'
import { telechargerPdfDevis, telechargerPdfFacture, messageTelechargement } from '../composables/telechargement'
import { estAujourdHui as estCreeAujourdHui, formaterDate, formaterDateHeure } from '../composables/date'
import { useNotification } from '../composables/notification'
import { useHeuresRecuperation } from '../composables/heuresRecuperation'
import HeureRecuperationVaisselle from '../components/HeureRecuperationVaisselle.vue'

const { notifier } = useNotification()
const { formaterHeure } = useHeuresRecuperation()
const route = useRoute()

const joursSemaine = ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim']
const nomsMois = [
  'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
  'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre',
]

const aujourdHui = new Date()
const annee = ref(aujourdHui.getFullYear())
const mois = ref(aujourdHui.getMonth())

const nomMois = computed(() => nomsMois[mois.value])
const cleMois = computed(() => `${annee.value}-${mois.value}`)

const cellulesMois = computed(() => {
  const premierJour = new Date(annee.value, mois.value, 1)
  const nombreDeJours = new Date(annee.value, mois.value + 1, 0).getDate()

  let decalage = premierJour.getDay() - 1
  if (decalage < 0) decalage = 6

  const cases = []
  for (let i = 0; i < decalage; i++) {
    cases.push(null)
  }
  for (let jour = 1; jour <= nombreDeJours; jour++) {
    cases.push(jour)
  }
  while (cases.length % 7 !== 0) {
    cases.push(null)
  }

  return cases
})

function estAujourdHui(jour) {
  return (
    jour === aujourdHui.getDate() &&
    mois.value === aujourdHui.getMonth() &&
    annee.value === aujourdHui.getFullYear()
  )
}

const dateSelectionnee = ref(null)
const nouvelEvenement = reactive({
  titre: '',
  adresseEvenement: '',
  dateRentree: '',
  note: '',
})
const creationEvenementEnCours = ref(false)

function selectionnerJour(jour) {
  if (!jour) return
  dateSelectionnee.value = new Date(annee.value, mois.value, jour)
}

function estSelectionne(jour) {
  const date = dateSelectionnee.value
  return (
    !!date &&
    jour === date.getDate() &&
    mois.value === date.getMonth() &&
    annee.value === date.getFullYear()
  )
}

function moisPrecedent() {
  if (mois.value === 0) {
    mois.value = 11
    annee.value--
  } else {
    mois.value--
  }
}

function moisSuivant() {
  if (mois.value === 11) {
    mois.value = 0
    annee.value++
  } else {
    mois.value++
  }
}

const chargement = ref(true)
const suppressionEnCours = ref('')
const factureEnCours = ref('')
const telechargementEnCours = ref('')
const devis = ref([])
const factures = ref([])
const evenementsCalendrier = ref([])

const evenementsDevis = computed(() =>
  devis.value.filter((item) => item.dateReservation),
)

const evenementsFactures = computed(() =>
  factures.value.filter((item) => item.dateReservation),
)

const commandes = computed(() =>
  [...devis.value.map((item) => ({ ...item, type: 'devis' })), ...factures.value.map((item) => ({ ...item, type: 'facture' }))].sort(
    (a, b) => new Date(b.createdAt) - new Date(a.createdAt),
  ),
)

function cleDate(year, monthIndex, day) {
  const month = String(monthIndex + 1).padStart(2, '0')
  const dayStr = String(day).padStart(2, '0')
  return `${year}-${month}-${dayStr}`
}

function evenementsPourCle(cle) {
  const calendrier = evenementsCalendrier.value
    .filter((e) => e.dateReservation === cle)
    .map((e) => ({ ...e, sourceType: 'calendrier' }))
  const devisJour = evenementsDevis.value
    .filter((e) => e.dateReservation === cle)
    .map((e) => ({ ...e, sourceType: 'devis' }))
  const facturesJour = evenementsFactures.value
    .filter((e) => e.dateReservation === cle)
    .map((e) => ({ ...e, sourceType: 'facture' }))
  return [...calendrier, ...devisJour, ...facturesJour]
}

function aEvenement(jour) {
  if (!jour) return false
  const cle = cleDate(annee.value, mois.value, jour)
  return evenementsPourCle(cle).length > 0
}

function heureJour(jour) {
  if (!jour) return ''
  const cle = cleDate(annee.value, mois.value, jour)

  for (const evenement of evenementsPourCle(cle)) {
    if (evenement.heureRecuperationVaisselle) {
      return evenement.heureRecuperationVaisselle
    }
  }

  return ''
}

function mettreAJourHeureEvenement({ devisId, heure }) {
  const index = devis.value.findIndex((item) => item.id === devisId)
  if (index === -1) return

  devis.value[index] = {
    ...devis.value[index],
    heureRecuperationVaisselle: heure,
  }
}

function mettreAJourHeureCalendrier({ evenementId, heure }) {
  const index = evenementsCalendrier.value.findIndex((item) => item.id === evenementId)
  if (index === -1) return

  evenementsCalendrier.value[index] = {
    ...evenementsCalendrier.value[index],
    heureRecuperationVaisselle: heure,
  }
}

const evenementsJourSelectionne = computed(() => {
  if (!dateSelectionnee.value) return []
  const cle = cleDate(
    dateSelectionnee.value.getFullYear(),
    dateSelectionnee.value.getMonth(),
    dateSelectionnee.value.getDate(),
  )
  return evenementsPourCle(cle)
})

const titreDateSelectionnee = computed(() => {
  if (!dateSelectionnee.value) return ''
  return dateSelectionnee.value.toLocaleDateString('fr-FR', {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  })
})

watch(dateSelectionnee, (date) => {
  nouvelEvenement.titre = ''
  nouvelEvenement.adresseEvenement = ''
  nouvelEvenement.dateRentree = ''
  nouvelEvenement.note = ''
  if (!date) return
})

async function chargerDonnees() {
  chargement.value = true

  try {
    const [reponseDevis, reponseFactures] = await Promise.all([
      api.get('/devis'),
      api.get('/factures'),
    ])
    devis.value = reponseDevis.data
    factures.value = reponseFactures.data
  } catch {
    devis.value = []
    factures.value = []
    evenementsCalendrier.value = []
    chargement.value = false
    return
  }

  try {
    const reponseEvenements = await api.get('/evenements')
    evenementsCalendrier.value = Array.isArray(reponseEvenements.data) ? reponseEvenements.data : []
  } catch {
    evenementsCalendrier.value = []
  } finally {
    chargement.value = false
  }
}

async function creerEvenementCalendrier() {
  if (!dateSelectionnee.value) return

  const cle = cleDate(
    dateSelectionnee.value.getFullYear(),
    dateSelectionnee.value.getMonth(),
    dateSelectionnee.value.getDate(),
  )

  creationEvenementEnCours.value = true

  try {
    const { data } = await api.post('/evenements', {
      titre: nouvelEvenement.titre,
      adresseEvenement: nouvelEvenement.adresseEvenement,
      dateReservation: cle,
      dateRentree: nouvelEvenement.dateRentree || null,
      note: nouvelEvenement.note,
    })
    evenementsCalendrier.value = [...evenementsCalendrier.value, data]
    nouvelEvenement.titre = ''
    nouvelEvenement.adresseEvenement = ''
    nouvelEvenement.dateRentree = ''
    nouvelEvenement.note = ''
    notifier('Événement ajouté au calendrier.', 'succes')
  } catch {
    notifier('Impossible d\'ajouter l\'événement.', 'erreur')
  } finally {
    creationEvenementEnCours.value = false
  }
}

async function supprimerItemCalendrier(item) {
  if (item.sourceType === 'devis' || item.sourceType === 'facture') {
    await supprimerCommande(item.sourceType, item.id, libelleNumero(item))
    return
  }

  const libelle = item.titre || 'cet événement'
  if (!window.confirm(`Supprimer ${libelle} ?`)) {
    return
  }

  const cle = `${item.sourceType}-${item.id}`
  suppressionEnCours.value = cle

  try {
    await api.delete(`/evenements/${item.id}`)
    evenementsCalendrier.value = evenementsCalendrier.value.filter((e) => e.id !== item.id)
    notifier('Événement supprimé.', 'succes')
  } catch {
    notifier('La suppression a échoué.', 'erreur')
  } finally {
    suppressionEnCours.value = ''
  }
}

async function supprimerCommande(type, id, numero) {
  const libelle = type === 'devis' ? 'devis' : 'facture'
  if (!window.confirm(`Supprimer le ${libelle} n°${numero} ?`)) {
    return
  }

  const cle = `${type}-${id}`
  suppressionEnCours.value = cle

  try {
    const endpoint = type === 'devis' ? `/devis/${id}` : `/factures/${id}`
    await api.delete(endpoint)
    await chargerDonnees()
    notifier(
      type === 'devis'
        ? `Le devis n°${numero} a bien été supprimé.`
        : `La facture n°${numero} a bien été supprimée.`,
      'succes',
    )
  } catch {
    notifier('La suppression a échoué. Réessayez plus tard.', 'erreur')
  } finally {
    suppressionEnCours.value = ''
  }
}

function libelleNumero(commande) {
  return commande.numero || String(commande.id)
}

function libelleTypeCalendrier(item) {
  if (item.sourceType === 'devis') return 'Devis'
  if (item.sourceType === 'facture') return 'Facture'
  return 'Événement'
}

function libelleEnteteCalendrier(item) {
  if (item.sourceType === 'calendrier') {
    return item.titre || 'Sans titre'
  }
  return `n°${libelleNumero(item)}`
}

function noteCalendrier(item) {
  if (item.noteCommande) return item.noteCommande
  if (item.sourceType === 'calendrier' && item.note) return item.note
  return ''
}

async function telechargerCommande(commande) {
  const cle = `dl-${commande.type}-${commande.id}`
  telechargementEnCours.value = cle
  const numero = libelleNumero(commande)

  try {
    const resultat = commande.type === 'devis'
      ? await telechargerPdfDevis(commande.id, numero)
      : await telechargerPdfFacture(commande.id, numero)

    notifier(messageTelechargement(resultat), 'succes')
  } catch (erreur) {
    if (erreur?.name === 'AbortError') {
      return
    }
    notifier('Le téléchargement a échoué. Vérifiez que vous êtes connecté.', 'erreur')
  } finally {
    telechargementEnCours.value = ''
  }
}

async function creerFactureDepuisDevis(devisId, numeroDevis) {
  if (!window.confirm(`Créer une facture à partir du devis n°${numeroDevis} ?`)) {
    return
  }

  const cle = `devis-${devisId}`
  factureEnCours.value = cle

  try {
    const { data } = await api.post(`/devis/${devisId}/facture`)
    await chargerDonnees()

    const dateHeure = formaterDateHeure(data.createdAt)
    const libelleFacture = data.numero || String(data.id)

    notifier(
      `Facture n°${libelleFacture} créée le ${dateHeure} à partir du devis n°${numeroDevis}.`,
      'succes',
    )
  } catch (error) {
    const message = error.response?.data?.message ?? 'La création de la facture a échoué. Réessayez plus tard.'
    notifier(message, 'erreur')
  } finally {
    factureEnCours.value = ''
  }
}

onMounted(async () => {
  await chargerDonnees()

  if (route.query.section === 'historique') {
    await nextTick()
    document.getElementById('historique-commandes')?.scrollIntoView({
      behavior: 'smooth',
      block: 'start',
    })
  }
})
</script>

<style lang="scss" scoped>
@use '../../styles/variables' as *;

.bloc {
  margin-bottom: $space-xl;
}

.calendrier {
  border: 1px solid rgba(204, 167, 97, 0.35);
  border-radius: calc($radius + 2px);
  background: $color-bg;
  box-shadow: $shadow-soft;
  overflow: hidden;
  transition: box-shadow $transition;

  &:hover {
    box-shadow: $shadow-lift;
  }

  &__nav {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: $space-sm;
    padding: $space-sm $space-md;
    border-bottom: 1px solid rgba(204, 167, 97, 0.25);
    background: linear-gradient(180deg, rgba(204, 167, 97, 0.15) 0%, transparent 100%);
  }

  &__nav-btn {
    width: 36px;
    height: 36px;
    padding: 0;
    border: 1px solid rgba(204, 167, 97, 0.4);
    border-radius: 50%;
    background: $color-bg;
    color: $color-text;
    font-size: var(--fs-grand);
    line-height: 1;
    cursor: pointer;
    transition:
      background-color $transition,
      transform $transition,
      box-shadow $transition;

    &:hover {
      background: $color-gold-ghost;
      transform: scale(1.03);
      box-shadow: $shadow-soft;
    }

    &:active {
      transform: scale(0.98);
    }
  }

  &__titre {
    font-size: var(--fs-base);
    text-transform: capitalize;
    text-align: center;
    transition: opacity 0.25s ease;
  }

  &__corps {
    padding: $space-sm $space-md $space-md;
    animation: fade-up 0.45s cubic-bezier(0.33, 1, 0.68, 1);
  }

  &__entete-jours {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 2px;
    margin-bottom: $space-xs;
  }

  &__nom-jour {
    text-align: center;
    font-size: var(--fs-petit);
    color: $color-muted;
  }

  &__grille {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 2px;
  }

  &__jour {
    min-height: 40px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 2px;
    padding: 2px 0;
    border: 0;
    border-radius: $radius;
    background: transparent;
    font-size: var(--fs-base);
    cursor: pointer;
    transition:
      background-color $transition,
      transform $transition,
      box-shadow $transition;

    &--vide {
      cursor: default;
      pointer-events: none;
    }

    &:not(&--vide):hover {
      background: $color-gold-ghost;
      transform: scale(1.04);
    }

    &:not(&--vide):active {
      transform: scale(0.98);
    }

    &--aujourdhui {
      background: $color-gold;
      font-weight: 400;
      box-shadow: 0 2px 10px rgba(204, 167, 97, 0.4);
    }

    &--evenement:not(&--aujourdhui) {
      box-shadow: inset 0 0 0 2px $color-gold;
    }

    &--selection:not(&--aujourdhui) {
      background: $color-gold-ghost;
      box-shadow: inset 0 0 0 2px $color-gold-dark;
    }
  }

  &__jour-heure {
    display: inline-flex;
    align-items: center;
    gap: 2px;
    font-size: 8px;
    line-height: 1;
    color: $color-gold-dark;
    letter-spacing: -0.02em;
    pointer-events: none;

    svg {
      width: 9px;
      height: 9px;
      flex-shrink: 0;
    }
  }

  &__detail {
    padding: $space-md;
    border-top: 1px solid $color-border;
    background: linear-gradient(180deg, rgba(204, 167, 97, 0.06) 0%, $color-bg 100%);
  }

  &__detail-titre {
    font-size: var(--fs-base);
    font-weight: 400;
    margin-bottom: $space-md;
    text-transform: capitalize;
  }

  &__ajout {
    margin-top: $space-lg;
    padding-top: $space-md;
    border-top: 1px solid rgba(204, 167, 97, 0.25);
    display: flex;
    flex-direction: column;
    gap: $space-sm;
  }

  &__ajout-titre {
    font-size: var(--fs-base);
    margin-bottom: $space-xs;
  }
}

.calendrier-detail-enter-active,
.calendrier-detail-leave-active {
  transition:
    opacity 0.38s ease-out,
    max-height 0.45s cubic-bezier(0.33, 1, 0.68, 1),
    transform 0.38s ease-out;
  overflow: hidden;
}

.calendrier-detail-enter-from,
.calendrier-detail-leave-to {
  opacity: 0;
  max-height: 0;
  transform: translateY(-4px);
}

.calendrier-detail-enter-to,
.calendrier-detail-leave-from {
  max-height: 800px;
}

.historique {
  list-style: none;
  display: flex;
  flex-direction: column;
  gap: $space-md;

  &__etat {
    color: $color-muted;
    font-size: var(--fs-base);

    &--chargement {
      display: flex;
      align-items: center;
      gap: $space-sm;
    }
  }

  &__item {
    padding-bottom: $space-md;
    border-bottom: 1px solid $color-border;

    &:last-child {
      border-bottom: 0;
      padding-bottom: 0;
    }
  }

  &--commandes {
    gap: $space-lg;

    .historique__carte {
      position: relative;
      display: flex;
      flex-direction: column;
      gap: $space-sm;
      padding: $space-md $space-md $space-md calc(#{$space-md} + 6px);
      border: 1px solid rgba(204, 167, 97, 0.35);
      border-radius: calc($radius + 4px);
      background: linear-gradient(145deg, $color-bg 0%, rgba(204, 167, 97, 0.04) 100%);
      box-shadow: $shadow-soft;
      overflow: hidden;

      &::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 5px;
        background: linear-gradient(180deg, $color-gold 0%, $color-gold-dark 100%);
        opacity: 0.55;
      }

      &:hover {
        transform: translateY(-2px);
        border-color: rgba(204, 167, 97, 0.65);
      }
    }

    .historique__carte--aujourdhui {
      border-color: $color-gold;
    }

    .historique__entete {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: $space-sm;
      flex-wrap: wrap;
      padding-bottom: $space-sm;
      border-bottom: 1px solid rgba(204, 167, 97, 0.2);
    }

    .historique__type {
      font-size: var(--fs-petit);
      text-transform: uppercase;
      letter-spacing: 0.08em;
      padding: 4px $space-sm;
      border-radius: $radius;

      &--devis {
        background: rgba(204, 167, 97, 0.28);
      }

      &--facture {
        background: rgba(46, 125, 50, 0.14);
        color: $color-success;
      }
    }

    .historique__actions {
      flex-direction: row;
      gap: $space-sm;
      margin-top: $space-md;
      padding-top: $space-sm;
      border-top: 1px solid rgba(204, 167, 97, 0.15);
    }

    .historique__action {
      flex: 1;
      max-width: none;
      height: var(--btn-h);

      &--facture {
        background: $color-gold;
        border: 0;
      }
    }

    @media (max-width: #{$bp-tablet - 1px}) {
      .historique__actions {
        flex-direction: column;
      }
    }
  }

  &__entete {
    display: flex;
    align-items: baseline;
    justify-content: space-between;
    gap: $space-sm;
    margin-bottom: $space-xs;
  }

  &__supprimer {
    margin-top: $space-sm;
    width: 100%;
    max-width: none;
    height: var(--btn-h);
  }

  &__actions {
    display: flex;
    flex-direction: column;
    gap: $space-sm;
    margin-top: $space-sm;
  }

  &__action {
    width: 100%;
    max-width: none;
    height: var(--btn-h);
  }

  &__ligne {
    font-size: var(--fs-base);
    color: $color-text;
    margin-top: $space-xs;
  }

  &__note {
    font-size: var(--fs-base);
    color: $color-text;
    font-style: italic;
    margin-top: $space-xs;
    line-height: 1.4;
  }

  &__type {
    &--devis {
      background: rgba(204, 167, 97, 0.28);
    }

    &--facture {
      background: rgba(46, 125, 50, 0.14);
      color: $color-success;
    }
  }
}
</style>
