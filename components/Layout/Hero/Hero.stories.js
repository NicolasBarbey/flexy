import Hero from './Hero.html.twig';

export default {
  title: 'Design System/Layout/Hero'
};

export const Base = {
  render: (args) => Hero(args),
  args: {
    blocks: [
      {
        image:
          'https://picsum.photos/780/480',
        title: 'Ici une phrase d’accroche pour accompagner le visuel',
        href: 'https://example.com/',
        linkLabel: 'Je découvre'
      },
      {
        image: '',
        title: 'Ici une phrase d’accroche pour accompagner le visuel',
        href: 'http://example.com',
        linkLabel: 'Je découvre'
      },
      {
        image: '',
        title: 'Ici une phrase d’accroche pour accompagner le visuel',
        href: 'http://example.com',
        linkLabel: 'Je découvre'
      }
    ]
  },
  argTypes: {
    blocks: { control: 'object' }
  }
};
