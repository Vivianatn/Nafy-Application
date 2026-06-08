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
              v-for="evenement in evenementsJourSelectionne"
              :key="'evenement-' + evenement.id"
              class="historique__item"
            >
              <div class="historique__entete">
                <span class="historique__type">Événement</span>
                <strong class="historique__numero">n°{{ libelleNumero(evenement) }}</strong>
              </div>
              <p class="historique__ligne">Créé le {{ formaterDateHeure(evenement.createdAt) }}</p>
              <p v-if="evenement.dateReservation" class="historique__ligne">
                Réservation le {{ formaterDate(evenement.dateReservation) }}
              </p>
              <HeureRecuperationVaisselle :devis-id="evenement.id" />
              <button
                type="button"
                class="bouton bouton--secondaire historique__supprimer"
                :disabled="suppressionEnCours === 'devis-' + evenement.id"
                @click="supprimerCommande('devis', evenement.id, libelleNumero(evenement))"
              >
                {{ suppressionEnCours === 'devis-' + evenement.id ? 'Suppression…' : 'Supprimer' }}
              </button>
            </li>
          </ul>
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
              :disabled="telechargementEnCours === commande.type + '-' + commande.id"
              @click="telechargerCommande(commande.type, commande.id, libelleNumero(commande))"
            >
              {{ telechargementEnCours === commande.type + '-' + commande.id ? 'Téléchargement…' : 'Télécharger' }}
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
import { ref, computed, onMounted, nextTick } from 'vue'
import { useRoute } from 'vue-router'
import api from '../api'
import { estAujourdHui as estCreeAujourdHui, formaterDate, formaterDateHeure } from '../composables/date'
import { useNotification } from '../composables/notification'
import { useHeuresRecuperation } from '../composables/heuresRecuperation'
import HeureRecuperationVaisselle from '../components/HeureRecuperationVaisselle.vue'

const { notifier } = useNotification()
const { formaterHeure, getHeure } = useHeuresRecuperation()
const route = useRoute()

const joursSemaine = ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim']
const nomsMois = [
  'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
  'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'
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
const telechargementEnCours = ref('')
const factureEnCours = ref('')
const devis = ref([])
const factures = ref([])

const evenements = computed(() =>
  devis.value.filter((item) => item.dateReservation),
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

function aEvenement(jour) {
  if (!jour) return false
  const cle = cleDate(annee.value, mois.value, jour)
  return evenements.value.some((evenement) => evenement.dateReservation === cle)
}

function heureJour(jour) {
  if (!jour) return ''
  const cle = cleDate(annee.value, mois.value, jour)
  const evenementsDuJour = evenements.value.filter((e) => e.dateReservation === cle)

  for (const evenement of evenementsDuJour) {
    const heure = getHeure(evenement.id)
    if (heure) {
      return heure
    }
  }

  return ''
}

const evenementsJourSelectionne = computed(() => {
  if (!dateSelectionnee.value) return []
  const cle = cleDate(
    dateSelectionnee.value.getFullYear(),
    dateSelectionnee.value.getMonth(),
    dateSelectionnee.value.getDate(),
  )
  return evenements.value.filter((evenement) => evenement.dateReservation === cle)
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

async function chargerCommandes() {
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
  } finally {
    chargement.value = false
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
    await chargerCommandes()
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

async function telechargerCommande(type, id, numero) {
  const cle = `${type}-${id}`
  telechargementEnCours.value = cle

  try {
    const endpoint = type === 'devis' ? `/devis/${id}/pdf` : `/factures/${id}/pdf`
    const reponse = await api.get(endpoint, { responseType: 'blob' })
    const url = window.URL.createObjectURL(new Blob([reponse.data], { type: 'application/pdf' }))
    const lien = document.createElement('a')
    lien.href = url
    lien.download = `${type}-${numero}.pdf`
    document.body.appendChild(lien)
    lien.click()
    lien.remove()
    window.URL.revokeObjectURL(url)
    notifier(
      type === 'devis'
        ? `Le devis n°${numero} a bien été téléchargé.`
        : `La facture n°${numero} a bien été téléchargée.`,
      'succes',
    )
  } catch {
    notifier('Le téléchargement a échoué. Réessayez plus tard.', 'erreur')
  } finally {
    telechargementEnCours.value = ''
  }
}

function libelleNumero(commande) {
  return commande.numero || String(commande.id)
}

async function creerFactureDepuisDevis(devisId, numeroDevis) {
  if (!window.confirm(`Créer une facture à partir du devis n°${numeroDevis} ?`)) {
    return
  }

  const cle = `devis-${devisId}`
  factureEnCours.value = cle

  try {
    const { data } = await api.post(`/devis/${devisId}/facture`)
    await chargerCommandes()

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
  await chargerCommandes()

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
      transform $transition-bounce,
      box-shadow $transition;

    &:hover {
      background: $color-gold-ghost;
      transform: scale(1.08);
      box-shadow: $shadow-soft;
    }

    &:active {
      transform: scale(0.94);
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
    animation: fade-up 0.35s cubic-bezier(0.22, 1, 0.36, 1);
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
      transform $transition-bounce,
      box-shadow $transition;

    &--vide {
      cursor: default;
      pointer-events: none;
    }

    &:not(&--vide):hover {
      background: $color-gold-ghost;
      transform: scale(1.12);
    }

    &:not(&--vide):active {
      transform: scale(0.92);
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
}

.calendrier-detail-enter-active,
.calendrier-detail-leave-active {
  transition:
    opacity 0.28s ease,
    max-height 0.35s cubic-bezier(0.22, 1, 0.36, 1),
    transform 0.28s ease;
  overflow: hidden;
}

.calendrier-detail-enter-from,
.calendrier-detail-leave-to {
  opacity: 0;
  max-height: 0;
  transform: translateY(-8px);
}

.calendrier-detail-enter-to,
.calendrier-detail-leave-from {
  max-height: 400px;
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
    transition:
      border-color $transition,
      background-color $transition;

    &:last-child {
      border-bottom: 0;
      padding-bottom: 0;
    }
  }

  &__item--aujourdhui {
    padding-left: $space-xs;
    border-left: 2px solid $color-gold;
  }

  // Section « Historique des commandes » — cartes
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
      transition:
        transform $transition,
        box-shadow $transition,
        border-color $transition;

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
        transform: translateY(-4px);
        border-color: rgba(204, 167, 97, 0.65);
        box-shadow: $shadow-lift;
      }
    }

    .historique__carte--aujourdhui {
      border-color: $color-gold;
      box-shadow:
        0 0 0 2px $color-gold-ghost,
        $shadow-soft;

      &::before {
        opacity: 1;
        width: 6px;
      }

      .historique__numero::after {
        content: ' · Aujourd\'hui';
        font-weight: 400;
        font-size: var(--fs-petit);
        color: $color-gold-dark;
      }
    }

    .historique__entete {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: $space-sm;
      flex-wrap: wrap;
      padding-bottom: $space-sm;
      margin-bottom: 0;
      border-bottom: 1px solid rgba(204, 167, 97, 0.2);
    }

    .historique__type {
      font-size: var(--fs-petit);
      font-weight: 400;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      padding: 4px $space-sm;
      border-radius: $radius;

      &--devis {
        background: rgba(204, 167, 97, 0.28);
        color: $color-text;
      }

      &--facture {
        background: rgba(46, 125, 50, 0.14);
        color: $color-success;
      }
    }

    .historique__numero {
      font-size: var(--fs-base);
      color: $color-text;
    }

    .historique__corps {
      display: flex;
      flex-direction: column;
      gap: $space-xs;
    }

    .historique__ligne {
      margin-top: 0;
      color: $color-muted;
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
      font-size: var(--fs-base);

      &--facture {
        background: $color-gold;
        border: 0;

        &:hover {
          background: $color-gold-dark;
        }
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

  &__type {
    font-size: var(--fs-base);
  }

  &__supprimer {
    margin-top: $space-sm;
    width: 100%;
    max-width: none;
    height: var(--btn-h);
    font-size: var(--fs-base);
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
    font-size: var(--fs-base);
  }

  &__numero {
    font-size: var(--fs-base);
    font-weight: 400;
  }

  &__ligne {
    font-size: var(--fs-base);
    color: $color-text;
    margin-top: $space-xs;

    &--heure {
      color: $color-gold-dark;
    }
  }
}
</style>
