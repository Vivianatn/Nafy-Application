/** Correspondance nom de kit (BDD) → fichier dans public/images/kits/ */
const FICHIERS = {
  'Marie Eve': 'marie-eve.jpg',
  Esther: 'esther.jpg',
  Abigaël: 'abigael.jpg',
  Rachel: 'rachel.jpg',
  Myriam: 'myriam.jpg',
  Ketura: 'ketura.jpg',
  Déborah: 'deborah.jpg',
  Sarah: 'sarah.jpg',
  Ruth: 'ruth.jpg',
}

export function imageKit(nom) {
  const fichier = FICHIERS[nom]
  if (!fichier) {
    return null
  }
  return `/images/kits/${fichier}`
}

export function onErreurImageKit(event) {
  event.target.style.display = 'none'
}
