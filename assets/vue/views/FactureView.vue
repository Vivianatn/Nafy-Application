<template>
  <FormulaireCommande
    type="facture"
    titre="Facture"
    endpoint="/factures"
    libelle-submit="Confirmer la facture"
    libelle-recap="Récapitulatif de la facture"
    retour-apres-envoi="historique"
  />
</template>

<script setup>
import FormulaireCommande from '../components/FormulaireCommande.vue'
</script>
