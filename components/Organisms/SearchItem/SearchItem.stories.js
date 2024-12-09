import SearchItem from './SearchItem.html.twig';

export default {
  title: 'Design System/Organisms/SearchItem'
};

export const base = {
  render    : (args) => SearchItem(args),
  args      : {
    vertical      : false,
    img           : { url: '/images/placeholder2.webp', alt: '' },
    productTitle  : 'Nom du produit',
    secondaryTitle: 'Titre secondaire',
    price         : '50,00€',
    isPromo       : false,
    promoPrice    : '30,00€'
  },
  argTypes  : {},
  parameters: {
    backgrounds: { default: 'theme-lighter' }
  }
};
