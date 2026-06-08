export function calculerPrix({ totalKits, livraison, km, vaisselleANettoyer, avecArrhes = false, avecCaution = true }) {
  const prixKits = totalKits * 4
  const prixLivraison = livraison === 'oui' ? km * 3 : 0
  const prixLavage = vaisselleANettoyer ? totalKits * 2 : 0
  const prixCaution = avecCaution && vaisselleANettoyer ? totalKits * 5 : 0
  const sousTotalPrestations = prixKits + prixLivraison + prixLavage
  const prixArrhes = avecArrhes ? Math.round(sousTotalPrestations * 0.3 * 100) / 100 : 0
  const prixFinal = avecCaution
    ? sousTotalPrestations + prixCaution + prixArrhes
    : sousTotalPrestations + prixArrhes

  return { prixKits, prixLivraison, prixLavage, prixCaution, prixFinal, prixArrhes }
}

export function formaterPrix(valeur) {
  return Number(valeur || 0).toFixed(2)
}
