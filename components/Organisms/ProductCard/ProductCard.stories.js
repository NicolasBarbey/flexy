import ProductCard from '@components/Organisms/ProductCard/ProductCard.html.twig';
import Standard from '@components/Organisms/ProductCardOld/Standard.html.twig';

export default {
  title: 'Design System/Organisms/ProductCard'
};

export const base = {
  render  : (args) =>
    `<div class='max-w-[187px] sm:max-w-[340px] lg:max-w-[400px]'>${ProductCard(args)}</div>`,
  args    : {

    img           : { url: '/images/placeholder2.webp', alt: '' },
    url           : '#',
    title         : 'Nom du produit',
    secondaryTitle: 'Titre secondaire',
    quantity      : 1,
    attributesAv  : { Taille: 'S' },
    price         : '1000,00€',
    promoPrice    : '900,00€',
    rate          : 4,
    reviewCount   : 12,
    wishButton    : false,
    colors        : [
      '#667761',
      '#84DCC6',
      '#C17767',
      '#DD6E42',
      '#5F0F40',
      '#6969B3',
      '#2E3438'
    ]
  },
  argTypes: {}
};
