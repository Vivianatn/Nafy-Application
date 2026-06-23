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

  async function enregistrerHeure(entiteId, heure, type = 'devis') {
    const endpoint = type === 'evenement'
      ? `/evenements/${entiteId}/heure-recuperation`
      : `/devis/${entiteId}/heure-recuperation`

    const { data } = await api.patch(endpoint, {
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
