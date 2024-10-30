

export type FilterClassicProps = {
  label: string | null;
  options: any;
  name: string;
  onChange?: (obj: {
    checked: boolean;
    value: string | number;
    name: string;
  }) => void;
  defaultValue?: any;
};
