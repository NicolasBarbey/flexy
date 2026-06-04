/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './components/**/*.{twig,ts,js,json}',
    './src/UiComponents/**/*.{twig,ts,js,json}',
    './form/**/*.twig',
    './*.twig'
  ],
  theme: {
    extend: {
      screens: {
        xxs: '320px',
        '3xl': '1920px',
        max2xl: { max: '1680px' },
        maxXl: { max: '1366px' },
        maxLg: { max: '1024px' },
        maxMd: { max: '768px' },
        maxSm: { max: '390px' },
        maxXs: { max: '321px' }
      }
    },
    fontSize: {
      '2xs': 'var(--font-size-2xs)',
      xs: 'var(--font-size-xs)',
      sm: 'var(--font-size-sm)',
      base: 'var(--font-size-base)',
      lg: 'var(--font-size-lg)',
      xl: 'var(--font-size-xl)',
      '2xl': 'var(--font-size-2xl)',
      '3xl': 'var(--font-size-3xl)',
      '4xl': 'var(--font-size-4xl)'
    }
  }
};
