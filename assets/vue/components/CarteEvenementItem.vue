<template>
  <article class="carte__corps">
    <div class="carte__entete" :class="{ 'carte__entete--compact': !detailsDeplies }">
      <span class="carte__type" :class="'carte__type--' + evenement.sourceType">
        {{ libelleType(evenement) }}
      </span>
      <strong class="carte__numero">{{ libelleEntete(evenement) }}</strong>
    </div>
    <dl v-show="detailsDeplies" class="carte__infos">
        <div class="carte__ligne">
          <dt>Réservation</dt>
          <dd>{{ formaterDate(evenement.dateReservation) }}</dd>
        </div>
        <div class="carte__ligne">
          <dt>Créé le</dt>
          <dd>{{ formaterDateHeure(evenement.createdAt) }}</dd>
        </div>
        <div v-if="noteEvenement(evenement)" class="carte__ligne carte__ligne--note">
          <dt>Note</dt>
          <dd>{{ noteEvenement(evenement) }}</dd>
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
</template>

<script setup>
import { formaterDate, formaterDateHeure } from '../composables/date'
import { useHeuresRecuperation } from '../composables/heuresRecuperation'

defineProps({
  evenement: {
    type: Object,
    required: true,
  },
  detailsDeplies: {
    type: Boolean,
    default: true,
  },
})

const { formaterHeureLongue } = useHeuresRecuperation()

function libelleNumero(evenement) {
  return evenement.numero || String(evenement.id)
}

function libelleType(evenement) {
  if (evenement.sourceType === 'devis') return 'Devis'
  if (evenement.sourceType === 'facture') return 'Facture'
  return 'Événement'
}

function libelleEntete(evenement) {
  if (evenement.sourceType === 'calendrier') {
    return evenement.titre || 'Sans titre'
  }
  return `n°${libelleNumero(evenement)}`
}

function noteEvenement(evenement) {
  if (evenement.noteCommande) return evenement.noteCommande
  if (evenement.sourceType === 'calendrier' && evenement.note) return evenement.note
  return ''
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
</script>

<style lang="scss" scoped>
@use '../../styles/variables' as *;

.carte__corps {
  border: 1px solid rgba(204, 167, 97, 0.28);
  border-radius: calc($radius + 2px);
  background: linear-gradient(145deg, $color-bg 0%, rgba(204, 167, 97, 0.04) 100%);
  padding: $space-md;
  transition:
    border-color $transition,
    box-shadow $transition,
    transform $transition;
}

.carte__entete {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: $space-sm;
  margin-bottom: $space-sm;

  &--compact {
    margin-bottom: 0;
  }
}

.carte__type {
  font-size: var(--fs-petit);
  letter-spacing: 0.04em;
  text-transform: uppercase;
  color: $color-gold-dark;
  background: rgba(204, 167, 97, 0.12);
  padding: 3px 8px;
  border-radius: 999px;

  &--devis {
    background: rgba(204, 167, 97, 0.12);
    color: $color-gold-dark;
  }

  &--facture {
    background: rgba(46, 125, 50, 0.12);
    color: $color-success;
  }
}

.carte__numero {
  font-size: var(--fs-base);
  font-weight: 400;
}

.carte__infos {
  display: flex;
  flex-direction: column;
  gap: $space-xs;
  margin: 0;
}

.carte__ligne {
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

  &--note dd {
    font-style: italic;
  }
}

@media (min-width: $bp-tablet) {
  .carte__ligne {
    grid-template-columns: 160px 1fr;
  }
}
</style>
