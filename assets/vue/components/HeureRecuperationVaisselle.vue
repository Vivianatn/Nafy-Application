<template>
  <div class="heure-recup" :class="{ 'heure-recup--active': Boolean(heureAffichee) }">
    <p class="heure-recup__libelle">Heure de récupération de vaisselle</p>

    <p v-if="messageErreur" class="heure-recup__erreur">{{ messageErreur }}</p>

    <p class="heure-recup__apercu" :class="{ 'heure-recup__apercu--vide': !apercu }">
      {{ apercu || '—' }}
    </p>

    <div class="heure-recup__saisie">
      <label class="heure-recup__bloc">
        <span class="heure-recup__unite">Heures</span>
        <input
          type="text"
          inputmode="numeric"
          maxlength="2"
          class="heure-recup__champ"
          :value="champHeure"
          placeholder="00"
          aria-label="Heures (0 à 23)"
          :disabled="enregistrementEnCours"
          @input="saisirHeure"
          @blur="validerSaisie"
        />
      </label>

      <span class="heure-recup__separateur" aria-hidden="true">:</span>

      <label class="heure-recup__bloc">
        <span class="heure-recup__unite">Minutes</span>
        <input
          type="text"
          inputmode="numeric"
          maxlength="2"
          class="heure-recup__champ"
          :value="champMinute"
          placeholder="00"
          aria-label="Minutes (0 à 59)"
          :disabled="enregistrementEnCours"
          @input="saisirMinute"
          @blur="validerSaisie"
        />
      </label>
    </div>

    <button
      v-if="heureAffichee"
      type="button"
      class="heure-recup__effacer"
      :disabled="enregistrementEnCours"
      @click="effacer"
    >
      {{ enregistrementEnCours ? 'Enregistrement…' : 'Effacer l\'heure' }}
    </button>
  </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { useHeuresRecuperation } from '../composables/heuresRecuperation'

const props = defineProps({
  devisId: {
    type: Number,
    default: null,
  },
  evenementId: {
    type: Number,
    default: null,
  },
  heureInitiale: {
    type: String,
    default: '',
  },
})

const emit = defineEmits(['heure-change'])

const entiteId = computed(() => props.evenementId ?? props.devisId)
const entiteType = computed(() => (props.evenementId ? 'evenement' : 'devis'))

const { enregistrerHeure, formaterHeure } = useHeuresRecuperation()

const champHeure = ref('')
const champMinute = ref('')
const heureAffichee = ref('')
const enregistrementEnCours = ref(false)
const messageErreur = ref('')

const apercu = computed(() => {
  if (champHeure.value !== '' && champMinute.value !== '') {
    return formaterHeure(`${normaliser(champHeure.value, 23)}:${normaliser(champMinute.value, 59)}`)
  }

  return heureAffichee.value ? formaterHeure(heureAffichee.value) : ''
})

watch(
  () => props.heureInitiale,
  (valeur) => {
    heureAffichee.value = valeur || ''
    if (!valeur) {
      champHeure.value = ''
      champMinute.value = ''
      return
    }

    const [h, m] = valeur.split(':')
    champHeure.value = h ?? ''
    champMinute.value = m ?? ''
  },
  { immediate: true },
)

function chiffresUniquement(valeur) {
  return valeur.replace(/\D/g, '').slice(0, 2)
}

function normaliser(valeur, maximum) {
  const nombre = Math.min(maximum, Math.max(0, parseInt(valeur || '0', 10)))
  return String(nombre).padStart(2, '0')
}

function saisirHeure(event) {
  champHeure.value = chiffresUniquement(event.target.value)
}

function saisirMinute(event) {
  champMinute.value = chiffresUniquement(event.target.value)
}

async function persister(heure) {
  enregistrementEnCours.value = true
  messageErreur.value = ''

  try {
    const enregistree = await enregistrerHeure(entiteId.value, heure, entiteType.value)
    heureAffichee.value = enregistree
    emit('heure-change', {
      devisId: props.devisId,
      evenementId: props.evenementId,
      heure: enregistree,
    })
  } catch {
    messageErreur.value = 'Impossible d\'enregistrer l\'heure.'
  } finally {
    enregistrementEnCours.value = false
  }
}

async function validerSaisie() {
  if (champHeure.value === '' && champMinute.value === '') {
    await persister('')
    return
  }

  const h = normaliser(champHeure.value || '0', 23)
  const m = normaliser(champMinute.value || '0', 59)

  champHeure.value = h
  champMinute.value = m
  await persister(`${h}:${m}`)
}

async function effacer() {
  champHeure.value = ''
  champMinute.value = ''
  await persister('')
}
</script>

<style lang="scss" scoped>
@use '../../styles/variables' as *;

.heure-recup {
  margin-top: $space-sm;
  padding: $space-sm $space-md $space-md;
  border: 1px solid rgba(204, 167, 97, 0.35);
  border-radius: calc($radius + 2px);
  background: linear-gradient(180deg, rgba(204, 167, 97, 0.07) 0%, $color-bg 100%);
  transition:
    border-color $transition,
    box-shadow $transition;

  &--active {
    border-color: $color-gold;
    box-shadow: 0 0 0 2px $color-gold-ghost;
  }

  &__libelle {
    font-size: var(--fs-petit);
    color: $color-muted;
    margin-bottom: $space-xs;
  }

  &__erreur {
    font-size: var(--fs-petit);
    color: #b42318;
    margin-bottom: $space-xs;
  }

  &__apercu {
    font-size: calc(var(--fs-grand) + 8px);
    line-height: 1;
    letter-spacing: 0.04em;
    color: $color-gold-dark;
    text-align: center;
    margin-bottom: $space-sm;

    &--vide {
      color: rgba(204, 167, 97, 0.45);
    }
  }

  &__saisie {
    display: flex;
    align-items: flex-end;
    justify-content: center;
    gap: $space-sm;
  }

  &__bloc {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    flex: 1;
    max-width: 120px;
  }

  &__unite {
    font-size: var(--fs-petit);
    color: $color-muted;
    text-transform: uppercase;
    letter-spacing: 0.06em;
  }

  &__champ {
    width: 100%;
    min-height: 52px;
    padding: $space-sm;
    border: 1px solid rgba(204, 167, 97, 0.45);
    border-radius: $radius;
    background: $color-bg;
    color: $color-text;
    font-family: inherit;
    font-size: calc(var(--fs-grand) + 2px);
    line-height: 1;
    text-align: center;
    transition:
      border-color $transition,
      background-color $transition,
      box-shadow $transition,
      transform $transition;

    &::placeholder {
      color: rgba(107, 107, 107, 0.45);
    }

    &:hover:not(:disabled) {
      border-color: $color-gold;
      background-color: rgba(204, 167, 97, 0.06);
    }

    &:focus {
      outline: none;
      border-color: $color-gold;
      box-shadow: 0 0 0 3px $color-gold-ghost;
      transform: translateY(-1px);
    }

    &:disabled {
      opacity: 0.7;
    }
  }

  &__separateur {
    flex-shrink: 0;
    padding-bottom: 14px;
    font-size: calc(var(--fs-grand) + 6px);
    line-height: 1;
    color: $color-gold-dark;
  }

  &__effacer {
    display: block;
    width: 100%;
    margin-top: $space-sm;
    padding: 6px 0 0;
    border: 0;
    border-top: 1px solid rgba(204, 167, 97, 0.2);
    background: none;
    color: $color-muted;
    font-family: inherit;
    font-size: var(--fs-petit);
    cursor: pointer;
    transition: color $transition;

    &:hover:not(:disabled) {
      color: $color-text;
    }

    &:disabled {
      opacity: 0.7;
      cursor: wait;
    }
  }
}
</style>
