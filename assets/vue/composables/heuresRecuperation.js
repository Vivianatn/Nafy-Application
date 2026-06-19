import api from '../api'

export function useHeuresRecuperation() {
  function formaterHeure(heure) {
    if (!heure) {
      return ''
    }

    const [h, m] = heure.split(':')
    return `${h}h${m}`
  }

  function formaterHeureLongue(heure) {
    if (!heure) {
      return ''
    }

    const [h, m] = heure.split(':')
    return `${h} h ${m}`
  }

  async function enregistrerHeure(devisId, heure) {
    const { data } = await api.patch(`/devis/${devisId}/heure-recuperation`, {
      heure: heure || '',
    })

    return data.heureRecuperationVaisselle ?? ''
  }

  return {
    enregistrerHeure,
    formaterHeure,
    formaterHeureLongue,
  }
}
