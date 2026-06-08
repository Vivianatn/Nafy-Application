<template>
  <FormulaireCommande
    type="devis"
    titre="Devis"
    endpoint="/devis"
    libelle-submit="Confirmer le devis"
    libelle-recap="Récapitulatif du devis"
  />
</template>

<script setup>
import FormulaireCommande from '../components/FormulaireCommande.vue'
</script>
