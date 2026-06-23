<template>
  <nav class="tabbar" aria-label="Navigation principale">
    <router-link
      v-for="onglet in onglets"
      :key="onglet.name"
      :to="onglet.to"
      class="tabbar__item"
      :class="{ 'tabbar__item--actif': estActif(onglet) }"
      :aria-current="estActif(onglet) ? 'page' : undefined"
    >
      <span class="tabbar__icone" aria-hidden="true" v-html="onglet.icone"></span>
      <span class="tabbar__libelle">{{ onglet.label }}</span>
    </router-link>
  </nav>
</template>

<script setup>
import { useRoute } from 'vue-router'

const route = useRoute()

const onglets = [
  {
    name: 'home',
    label: 'Accueil',
    to: { name: 'home' },
    routesActives: ['home'],
    icone: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M4 10.5 12 4l8 6.5V20a1 1 0 0 1-1 1h-5v-6H10v6H5a1 1 0 0 1-1-1v-9.5Z"/></svg>',
  },
  {
    name: 'inventaire',
    label: 'Inventaire',
    to: { name: 'inventaire' },
    routesActives: ['inventaire', 'vaisselle-detail', 'vaisselle-ajouter'],
    icone: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="4" y="4" width="7" height="7" rx="1"/><rect x="13" y="4" width="7" height="7" rx="1"/><rect x="4" y="13" width="7" height="7" rx="1"/><rect x="13" y="13" width="7" height="7" rx="1"/></svg>',
  },
  {
    name: 'devis',
    label: 'Devis',
    to: { name: 'devis' },
    routesActives: ['devis'],
    icone: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M8 4h8l4 4v12a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1Z"/><path d="M16 4v4h4"/><path d="M9 12h6M9 16h6"/></svg>',
  },
  {
    name: 'facture',
    label: 'Facture',
    to: { name: 'facture' },
    routesActives: ['facture'],
    icone: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M7 3h10a2 2 0 0 1 2 2v16l-3-2-3 2-3-2-3 2-3-2V5a2 2 0 0 1 2-2Z"/><path d="M9 8h6M9 12h6M9 16h4"/></svg>',
  },
  {
    name: 'plus',
    label: 'Plus',
    to: { name: 'plus' },
    routesActives: ['plus', 'evenements', 'secretaires', 'ajouter-secretaire', 'ajouter-responsable'],
    icone: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="6" cy="12" r="1.5" fill="currentColor" stroke="none"/><circle cx="12" cy="12" r="1.5" fill="currentColor" stroke="none"/><circle cx="18" cy="12" r="1.5" fill="currentColor" stroke="none"/></svg>',
  },
]

function estActif(onglet) {
  return onglet.routesActives.includes(route.name)
}
</script>

<style lang="scss" scoped>
@use '../../styles/variables' as *;

.tabbar {
  display: none;
  position: fixed;
  left: 0;
  right: 0;
  bottom: 0;
  z-index: 40;
  grid-template-columns: repeat(5, 1fr);
  min-height: $tabbar-height;
  padding: $space-xs $space-xs calc(#{$space-xs} + env(safe-area-inset-bottom, 0px));
  background: $color-bg;
  border-top: 1px solid rgba(204, 167, 97, 0.35);
  box-shadow: 0 -4px 18px rgba(0, 0, 0, 0.06);

  &__item {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 3px;
    min-width: 0;
    padding: 4px 2px;
    color: $color-muted;
    text-decoration: none;
    transition:
      color $transition,
      transform $transition;

    &:active {
      transform: scale(0.97);
    }

    &--actif {
      color: $color-gold-dark;

      .tabbar__icone {
        transform: translateY(-1px);
      }

      .tabbar__libelle {
        font-weight: 500;
      }
    }
  }

  &__icone {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 24px;
    height: 24px;
    transition: transform $transition;

    :deep(svg) {
      width: 22px;
      height: 22px;
    }
  }

  &__libelle {
    font-size: 10px;
    line-height: 1.1;
    letter-spacing: 0.01em;
    text-align: center;
    white-space: nowrap;
  }
}

@media (max-width: #{$bp-desktop - 1px}) {
  .tabbar {
    display: grid;
  }
}
</style>
