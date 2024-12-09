import CheckoutSteps from './CheckoutSteps.html.twig';

export default {
  title: 'Design System/Layout/CheckoutSteps'
};

export const base = {
  render  : (args) => CheckoutSteps(args),
  args    : {
    steps  : ['Votre panier', 'Votre livraison', 'Paiement', 'Confirmation'],
    current: 1
  },
  argTypes: {}
};
