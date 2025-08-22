import AddToCartToast from './AddToCartToast.html.twig';

export default {
  title: 'Design System/Organisms/AddToCartToast'
};

export const base = {
  render  : (args) => AddToCartToast(args),
  args    : {
    pseId              : 1,
    img                : { url: '/images/placeholder2.webp', alt: '' },
    title              : 'Nom du produit',
    orderSecondaryTitle: 'Titre secondaire',
    size               : 'S-34/36',
    quantity           : 1,
    attributesAv       : {
      Taille: '34'
    },
    attributesAvColor  : {
      name: 'Slate Blue',
      hexa: '#6969B3'
    }
  },
  argTypes: {}
};
