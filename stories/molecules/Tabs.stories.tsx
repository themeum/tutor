import { css } from '@emotion/react';
import SVGIcon from '@TutorShared/atoms/SVGIcon';
import Tabs, { type TabItem } from '@TutorShared/molecules/Tabs';
import { useState } from 'react';
import type { Meta, StoryObj } from 'storybook-react-rsbuild';

type TabValue = 'active' | 'profile' | 'settings' | 'disabled';

const tabList: TabItem<TabValue>[] = [
  {
    label: 'Active',
    value: 'active',
  },
  {
    label: 'Profile',
    value: 'profile',
    count: 2,
  },
  {
    label: 'Settings',
    value: 'settings',
    count: 12,
  },
  {
    label: 'Disabled',
    value: 'disabled',
    disabled: true,
  },
];

const meta = {
  title: 'Molecules/Tabs',
  component: Tabs,
  tags: ['autodocs'],
  parameters: {
    layout: 'centered',
    docs: {
      description: {
        component:
          'Tabs is a flexible tab navigation component supporting horizontal/vertical orientation, icons, badges, counts, disabled state, and accessibility features.',
      },
    },
  },
  argTypes: {
    activeTab: {
      control: 'select',
      options: tabList.map((tab) => tab.value),
      description: 'Currently active tab value.',
      defaultValue: 'home',
    },
    tabList: {
      control: false,
      description: 'Array of tab items.',
    },
    orientation: {
      control: 'select',
      options: ['horizontal', 'vertical'],
      description: 'Tabs orientation.',
      defaultValue: 'horizontal',
    },
    disabled: {
      control: 'boolean',
      description: 'Disable all tabs.',
      defaultValue: false,
    },
    wrapperCss: {
      control: false,
      description: 'Custom Emotion CSS for wrapper.',
    },
    onChange: { control: false },
  },
  args: {
    activeTab: 'active',
    tabList,
    orientation: 'horizontal',
    disabled: false,
    wrapperCss: undefined,
    onChange: () => {},
  },
  render: (args) => {
    const [activeTab, setActiveTab] = useState<TabValue>(args.activeTab as TabValue);

    const handleChangeTab = (value: TabValue) => setActiveTab(value);

    return (
      <Tabs {...args} activeTab={activeTab} onChange={handleChangeTab} tabList={args.tabList as TabItem<TabValue>[]} />
    );
  },
} satisfies Meta<typeof Tabs>;

export default meta;

type Story = StoryObj<typeof meta>;

export const Default = {} satisfies Story;

export const Vertical = {
  args: {
    orientation: 'vertical',
  },
} satisfies Story;

export const Disabled = {
  args: {
    disabled: true,
  },
} satisfies Story;

export const WithBadge = {
  args: {
    tabList: tabList.map((tab) => (tab.value === 'profile' ? { ...tab, activeBadge: true } : tab)),
  },
} satisfies Story;

export const WithCount = {
  args: {
    tabList: tabList.map((tab) =>
      tab.value === 'active' || tab.value === 'settings' ? { ...tab, count: tab.value === 'active' ? 2 : 12 } : tab,
    ),
  },
} satisfies Story;

export const WithIcons = {
  args: {
    tabList: tabList.map((tab) => ({
      ...tab,
      icon: (
        <SVGIcon
          name={
            tab.value === 'active'
              ? 'active'
              : tab.value === 'profile'
                ? 'user'
                : tab.value === 'settings'
                  ? 'settings'
                  : 'lock'
          }
          width={18}
          height={18}
        />
      ),
    })),
  },
} satisfies Story;

export const CustomStyle = {
  args: {
    wrapperCss: css`
      background: #f0f4ff;
      border-radius: 12px;
      padding: 24px;
      box-shadow: 0 2px 8px #1976d233;
    `,
  },
} satisfies Story;
