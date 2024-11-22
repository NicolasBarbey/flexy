import Search from './Search.html.twig';
import Order from './Order.html.twig';
import PurchaseFunnel from './PurchaseFunnel.html.twig';
import AddToCartConfirmation from './AddToCartConfirmation.html.twig';
import RemoveProduct from './RemoveProduct.html.twig';
import progressBar from './RemoveProduct.js';

export default {
  title: 'Design System/Organisms/ProductCardOld'
};

export const search = {
  render: (args) => Search(args),
  args  : {
    productTitle: 'Nom du produit',
    price       : '1000,00€'
  }
};

export const order = {
  render: (args) => Order(args),
  args  : {
    productTitle       : 'Nom du produit',
    orderSecondaryTitle: 'Titre secondaire',
    size               : 'S-34/36',
    quantity           : 1,
    price              : '50,00€'
  }
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
