<template>
  <div class="evenements anim-page">
    <h1 class="page-titre">Événements</h1>
    <p class="page-sous-titre">Récapitulatif chronologique des réservations</p>

    <label class="filtre-ordre">
      <span class="filtre-ordre__libelle">Ordre d'affichage</span>
      <select v-model="ordre" class="filtre-ordre__champ">
        <option value="reservation-asc">Date d'événement — prochains d'abord</option>
        <option value="reservation-desc">Date d'événement — plus anciens d'abord</option>
        <option value="creation-desc">Date de création — récentes d'abord</option>
        <option value="creation-asc">Date de création — anciennes d'abord</option>
      </select>
    </label>

    <p v-if="chargement" class="evenements__etat evenements__etat--chargement">
      <span class="spinner" aria-hidden="true"></span>
      Chargement…
    </p>
    <p v-else-if="erreur" class="evenements__etat evenements__etat--erreur">{{ erreur }}</p>
    <p v-else-if="evenementsTries.length === 0" class="evenements__etat">
      Aucun événement planifié pour le moment.
    </p>

    <div v-else class="evenements__liste">
      <template v-if="afficherParDate">
        <section
          v-for="(groupe, indexGroupe) in groupesParDate"
          :key="groupe.date"
          class="evenements__groupe"
          :style="{ '--groupe-i': indexGroupe }"
        >
          <header class="evenements__groupe-entete">
            <time :datetime="groupe.date" class="evenements__groupe-date">
              {{ titreDate(groupe.date) }}
            </time>
            <span class="evenements__groupe-badge" :class="classeStatutDate(groupe.date)">
              {{ libelleStatutDate(groupe.date) }}
            </span>
          </header>

          <ul class="evenements__timeline">
            <li
              v-for="(evenement, index) in groupe.items"
              :key="evenement.id"
              class="carte"
              :class="classeCarte(evenement)"
              :style="{ '--carte-i': index }"
            >
              <article class="carte__corps">
                <div class="carte__entete">
                  <span class="carte__type">Devis</span>
                  <strong class="carte__numero">n°{{ libelleNumero(evenement) }}</strong>
                </div>
                <dl class="carte__infos">
                  <div class="carte__ligne">
                    <dt>Réservation</dt>
                    <dd>{{ formaterDate(evenement.dateReservation) }}</dd>
                  </div>
                  <div class="carte__ligne">
                    <dt>Créé le</dt>
                    <dd>{{ formaterDateHeure(evenement.createdAt) }}</dd>
                  </div>
                  <div v-if="evenement.heureRecuperationVaisselle" class="carte__ligne">
                    <dt>Récupération vaisselle</dt>
                    <dd>{{ formaterHeureLongue(evenement.heureRecuperationVaisselle) }}</dd>
                  </div>
                  <div v-if="evenement.adresseEvenement" class="carte__ligne">
                    <dt>Adresse</dt>
                    <dd>{{ evenement.adresseEvenement }}</dd>
                  </div>
                  <div v-if="evenement.dateRentree" class="carte__ligne">
                    <dt>Date de rentrée</dt>
                    <dd>{{ formaterDate(evenement.dateRentree) }}</dd>
                  </div>
                  <div v-if="formaterPrix(evenement.prixFinal)" class="carte__ligne">
                    <dt>Montant</dt>
                    <dd>{{ formaterPrix(evenement.prixFinal) }}</dd>
                  </div>
                </dl>
              </article>
            </li>
          </ul>
        </section>
      </template>

      <ul v-else class="evenements__timeline evenements__timeline--plat">
        <li
          v-for="(evenement, index) in evenementsTries"
          :key="evenement.id"
          class="carte"
          :class="classeCarte(evenement)"
          :style="{ '--carte-i': index }"
        >
          <article class="carte__corps">
            <div class="carte__entete">
              <span class="carte__type">Devis</span>
              <strong class="carte__numero">n°{{ libelleNumero(evenement) }}</strong>
            </div>
            <dl class="carte__infos">
              <div class="carte__ligne">
                <dt>Réservation</dt>
                <dd>{{ formaterDate(evenement.dateReservation) }}</dd>
              </div>
              <div class="carte__ligne">
                <dt>Créé le</dt>
                <dd>{{ formaterDateHeure(evenement.createdAt) }}</dd>
              </div>
              <div v-if="evenement.heureRecuperationVaisselle" class="carte__ligne">
                <dt>Récupération vaisselle</dt>
                <dd>{{ formaterHeureLongue(evenement.heureRecuperationVaisselle) }}</dd>
              </div>
              <div v-if="evenement.adresseEvenement" class="carte__ligne">
                <dt>Adresse</dt>
                <dd>{{ evenement.adresseEvenement }}</dd>
              </div>
              <div v-if="evenement.dateRentree" class="carte__ligne">
                <dt>Date de rentrée</dt>
                <dd>{{ formaterDate(evenement.dateRentree) }}</dd>
              </div>
              <div v-if="formaterPrix(evenement.prixFinal)" class="carte__ligne">
                <dt>Montant</dt>
                <dd>{{ formaterPrix(evenement.prixFinal) }}</dd>
              </div>
            </dl>
          </article>
        </li>
      </ul>
    </div>

    <p v-if="!chargement && !erreur && evenementsTries.length > 0" class="evenements__compteur">
      {{ evenementsTries.length }} événement{{ evenementsTries.length > 1 ? 's' : '' }}
    </p>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import api from '../api'
import { formaterDate, formaterDateHeure } from '../composables/date'
import { useHeuresRecuperation } from '../composables/heuresRecuperation'

const { formaterHeureLongue } = useHeuresRecuperation()

const ordre = ref('reservation-asc')
const devis = ref([])
const chargement = ref(true)
const erreur = ref('')

const evenements = computed(() =>
  devis.value.filter((item) => item.dateReservation),
)

const afficherParDate = computed(() =>
  ordre.value === 'reservation-asc' || ordre.value === 'reservation-desc',
)

const evenementsTries = computed(() => {
  const liste = [...evenements.value]

  switch (ordre.value) {
    case 'reservation-desc':
      return liste.sort((a, b) => b.dateReservation.localeCompare(a.dateReservation))
    case 'creation-desc':
      return liste.sort((a, b) => new Date(b.createdAt) - new Date(a.createdAt))
    case 'creation-asc':
      return liste.sort((a, b) => new Date(a.createdAt) - new Date(b.createdAt))
    case 'reservation-asc':
    default:
      return liste.sort((a, b) => a.dateReservation.localeCompare(b.dateReservation))
  }
})

const groupesParDate = computed(() => {
  const groupes = new Map()

  for (const evenement of evenementsTries.value) {
    const cle = evenement.dateReservation
    if (!groupes.has(cle)) {
      groupes.set(cle, [])
    }
    groupes.get(cle).push(evenement)
  }

  return [...groupes.entries()].map(([date, items]) => ({ date, items }))
})

function parseDateIso(isoDate) {
  const [year, month, day] = isoDate.split('-').map(Number)
  return new Date(year, month - 1, day)
}

function aujourdHuiMinuit() {
  const date = new Date()
  date.setHours(0, 0, 0, 0)
  return date
}

function titreDate(isoDate) {
  return parseDateIso(isoDate).toLocaleDateString('fr-FR', {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  })
}

function classeStatutDate(isoDate) {
  const date = parseDateIso(isoDate)
  const aujourdHui = aujourdHuiMinuit()

  if (date.getTime() === aujourdHui.getTime()) {
    return 'evenements__groupe-badge--aujourdhui'
  }

  if (date < aujourdHui) {
    return 'evenements__groupe-badge--passe'
  }

  return 'evenements__groupe-badge--avenir'
}

function libelleStatutDate(isoDate) {
  const date = parseDateIso(isoDate)
  const aujourdHui = aujourdHuiMinuit()

  if (date.getTime() === aujourdHui.getTime()) {
    return "Aujourd'hui"
  }

  if (date < aujourdHui) {
    return 'Passé'
  }

  return 'À venir'
}

function classeCarte(evenement) {
  const date = parseDateIso(evenement.dateReservation)
  const aujourdHui = aujourdHuiMinuit()

  if (date.getTime() === aujourdHui.getTime()) {
    return 'carte--aujourdhui'
  }

  if (date < aujourdHui) {
    return 'carte--passe'
  }

  return 'carte--avenir'
}

function libelleNumero(evenement) {
  return evenement.numero || String(evenement.id)
}

function formaterPrix(prix) {
  if (prix === null || prix === undefined || prix === '') {
    return null
  }

  const valeur = Number(prix)
  if (Number.isNaN(valeur)) {
    return null
  }

  return new Intl.NumberFormat('fr-FR', {
    style: 'currency',
    currency: 'EUR',
  }).format(valeur)
}

async function chargerEvenements() {
  chargement.value = true
  erreur.value = ''

  try {
    const { data } = await api.get('/devis')
    devis.value = Array.isArray(data) ? data : []
  } catch {
    devis.value = []
    erreur.value = 'Impossible de charger les événements.'
  } finally {
    chargement.value = false
  }
}

onMounted(chargerEvenements)
</script>

<style lang="scss" scoped>
@use '../../styles/variables' as *;

.evenements {
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
    display: flex;
    flex-direction: column;
    gap: $space-xl;
  }

  &__groupe {
    animation: evenement-fade-up 0.45s cubic-bezier(0.33, 1, 0.68, 1) backwards;
    animation-delay: calc(0.03s + var(--groupe-i, 0) * 0.04s);
  }

  &__groupe-entete {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: $space-md;
    margin-bottom: $space-md;
    padding-bottom: $space-sm;
    border-bottom: 1px solid rgba(204, 167, 97, 0.3);
  }

  &__groupe-date {
    font-size: var(--fs-base);
    text-transform: capitalize;
    color: $color-text;
  }

  &__groupe-badge {
    flex-shrink: 0;
    padding: 4px 10px;
    border-radius: 999px;
    font-size: var(--fs-petit);
    letter-spacing: 0.02em;
    background: rgba(204, 167, 97, 0.12);
    color: $color-gold-dark;

    &--aujourdhui {
      background: rgba(204, 167, 97, 0.22);
      color: $color-gold-dark;
    }

    &--avenir {
      background: rgba(46, 125, 50, 0.1);
      color: $color-success;
    }

    &--passe {
      background: rgba(107, 107, 107, 0.12);
      color: $color-muted;
    }
  }

  &__timeline {
    list-style: none;
    display: flex;
    flex-direction: column;
    gap: $space-md;
    margin: 0;
    padding: 0 0 0 $space-md;
    border-left: 2px solid rgba(204, 167, 97, 0.28);

    &--plat {
      padding-left: 0;
      border-left: 0;
    }
  }

  &__compteur {
    margin-top: $space-lg;
    color: $color-muted;
    font-size: var(--fs-petit);
    text-align: center;
  }
}

.filtre-ordre {
  display: flex;
  flex-direction: column;
  gap: $space-xs;
  margin-bottom: $space-lg;
  max-width: 420px;

  &__libelle {
    font-size: var(--fs-petit);
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: $color-muted;
  }

  &__champ {
    width: 100%;
    font-family: inherit;
    font-size: var(--fs-base);
    color: $color-text;
    background: $color-bg;
    border: 1px solid rgba(204, 167, 97, 0.35);
    border-radius: $radius;
    padding: $space-sm $space-md;
    cursor: pointer;
    transition:
      border-color $transition,
      box-shadow $transition;

    &:hover {
      border-color: rgba(204, 167, 97, 0.65);
    }

    &:focus {
      outline: none;
      border-color: $color-gold;
      box-shadow: 0 0 0 3px $color-gold-ghost;
    }
  }
}

.carte {
  position: relative;
  animation: evenement-fade-up 0.45s cubic-bezier(0.33, 1, 0.68, 1) backwards;
  animation-delay: calc(0.03s + var(--carte-i, 0) * 0.03s);

  &::before {
    content: '';
    position: absolute;
    left: calc(-1 * #{$space-md} - 5px);
    top: 22px;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: $color-gold;
    box-shadow: 0 0 0 3px $color-gold-ghost;
  }

  .evenements__timeline--plat &::before {
    display: none;
  }

  &--aujourdhui::before {
    background: $color-gold-dark;
    box-shadow: 0 0 0 3px rgba(204, 167, 97, 0.35);
  }

  &--passe::before {
    background: $color-muted;
    box-shadow: 0 0 0 3px rgba(107, 107, 107, 0.15);
  }

  &--avenir::before {
    background: $color-success;
    box-shadow: 0 0 0 3px rgba(46, 125, 50, 0.15);
  }

  &__corps {
    border: 1px solid rgba(204, 167, 97, 0.28);
    border-radius: calc($radius + 2px);
    background: linear-gradient(145deg, $color-bg 0%, rgba(204, 167, 97, 0.04) 100%);
    padding: $space-md;
    transition:
      border-color $transition,
      box-shadow $transition,
      transform $transition;
  }

  &:hover .carte__corps {
    border-color: rgba(204, 167, 97, 0.55);
    box-shadow: $shadow-soft;
    transform: translateY(-1px);
  }

  &--aujourdhui .carte__corps {
    border-color: rgba(204, 167, 97, 0.55);
    box-shadow: 0 0 0 2px $color-gold-ghost;
  }

  &--passe .carte__corps {
    opacity: 0.82;
  }

  &__entete {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: $space-sm;
    margin-bottom: $space-sm;
  }

  &__type {
    font-size: var(--fs-petit);
    letter-spacing: 0.04em;
    text-transform: uppercase;
    color: $color-gold-dark;
    background: rgba(204, 167, 97, 0.12);
    padding: 3px 8px;
    border-radius: 999px;
  }

  &__numero {
    font-size: var(--fs-base);
    font-weight: 400;
  }

  &__infos {
    display: flex;
    flex-direction: column;
    gap: $space-xs;
    margin: 0;
  }

  &__ligne {
    display: grid;
    grid-template-columns: minmax(110px, 38%) 1fr;
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
      color: $color-text;
      word-break: break-word;
    }
  }
}

@keyframes evenement-fade-up {
  from {
    opacity: 0;
    transform: translateY(5px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@media (min-width: $bp-tablet) {
  .carte__ligne {
    grid-template-columns: 160px 1fr;
  }
}

@media (prefers-reduced-motion: reduce) {
  .evenements__groupe,
  .carte {
    animation: none;
  }

  .carte__corps {
    transition-duration: 0.01ms !important;
  }
}
</style>
