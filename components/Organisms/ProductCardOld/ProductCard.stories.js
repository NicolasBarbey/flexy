import PurchaseFunnel from './PurchaseFunnel.html.twig';

export default {
  title: 'Design System/Organisms/ProductCardOld'
};

export const purchaseFunnel = {
  render  : (args) => PurchaseFunnel(args),
  args    : {
    productTitle       : 'Nom du produit',
    orderSecondaryTitle: 'Titre secondaire',
    size               : 'S-34/36',
    quantityChoice     : 1,
    price              : '1000,00€',
    promoPrice         : '900,00€'
  },
  argTypes: {
    isOutOfStock: {
      control: { type: 'boolean' }
    },
    isPromo     : {
      control: { type: 'boolean' }
    }
  }
};
