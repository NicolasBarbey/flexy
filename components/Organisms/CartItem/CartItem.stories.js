import CartItem from './CartItem.html.twig';

export default {
  title: 'Design System/Organisms/CartItem'
};

export const base = {
  render  : (args) => CartItem(args),
  args    : {
    cartItem      : 1,
    img           : { url: '/images/placeholder2.webp', alt: '' },
    url           : '#',
    title         : 'Nom du produit',
    secondaryTitle: 'Titre secondaire',
    quantity      : 1,
    attributesAv  : { Taille: 'S' },
    outOfStock    : false
  },
  argTypes: {}
};
