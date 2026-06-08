export function formaterDateHeure(iso) {
  if (!iso) return ''
  return new Date(iso).toLocaleString('fr-FR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

export function formaterDate(isoDate) {
  if (!isoDate) return ''
  const [year, month, day] = isoDate.split('-')
  if (!year || !month || !day) return isoDate
  return `${day}/${month}/${year}`
}

export function estAujourdHui(iso) {
  if (!iso) return false
  const date = new Date(iso)
  const aujourdHui = new Date()
  return (
    date.getDate() === aujourdHui.getDate()
    && date.getMonth() === aujourdHui.getMonth()
    && date.getFullYear() === aujourdHui.getFullYear()
  )
}
