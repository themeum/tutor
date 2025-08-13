import { type Meta, type StoryObj } from 'storybook-react-rsbuild';
import type ComponentsOverviewStories from './ComponentsOverview';
import { ComponentsOverview } from './ComponentsOverview';

const meta = {
  title: 'Docs/Components Overview',
  component: ComponentsOverview,
  parameters: {
    layout: 'fullscreen',
    docs: {
      page: () => <ComponentsOverview />,
    },
  },
  tags: ['autodocs'],
} satisfies Meta<typeof ComponentsOverviewStories>;

export default meta;

type Story = StoryObj<typeof meta>;

export const Docs = {} satisfies Story;
