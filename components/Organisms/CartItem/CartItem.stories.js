import CartItem from './CartItem.html.twig';
import CartItemDelete from './CartItemDelete.html.twig';

export default {
  title: 'Design System/Organisms/CartItem'
};

export const base = {
  render  : (args) => CartItem(args),
  args    : {
    img         : { url: '/images/placeholder2.webp', alt: '' },
    cartItem    : {
      quantity: 1,
      product : {
        i18ns    : {
          title: 'Nom du produit',
          chapo: 'Titre secondaire'
        },
        publicUrl: '#'
      }
    },
    attributesAv: { Taille: 'S' },
    outOfStock  : false
  },
  argTypes: {}
};

export const deleteToast = {
  render  : (args) => CartItemDelete(args),
  args    : {
    cartItemId: 1,
    img       : { url: '/images/placeholder2.webp', alt: '' },
    title     : 'Nom du produit'
  },
  argTypes: {}
};
