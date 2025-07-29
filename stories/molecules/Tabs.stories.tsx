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
    icon: <SVGIcon name="active" width={18} height={18} />,
    activeBadge: true,
  },
  {
    label: 'Profile',
    value: 'profile',
    count: 2,
    icon: <SVGIcon name="user" width={18} height={18} />,
  },
  {
    label: 'Settings',
    value: 'settings',
    icon: <SVGIcon name="settings" width={18} height={18} />,
    count: 12,
  },
  {
    label: 'Disabled',
    value: 'disabled',
    icon: <SVGIcon name="lock" width={18} height={18} />,
    disabled: true,
  },
];

const meta: Meta<typeof Tabs> = {
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
    activeTab: 'home',
    tabList,
    orientation: 'horizontal',
    disabled: false,
    wrapperCss: undefined,
  },
  render: (args) => {
    const [activeTab, setActiveTab] = useState<TabValue>(args.activeTab as TabValue);

    const handleChangeTab = (value: TabValue) => setActiveTab(value);

    return (
      <Tabs
        {...args}
        activeTab={activeTab}
        onChange={handleChangeTab}
        tabList={
          args.tabList.map((tab) => ({
            ...tab,
            activeBadge: tab.value === activeTab,
          })) as TabItem<TabValue>[]
        }
      />
    );
  },
};
export default meta;

type Story = StoryObj<typeof meta>;

export const Default: Story = {};

export const Vertical: Story = {
  args: {
    orientation: 'vertical',
  },
};

export const Disabled: Story = {
  args: {
    disabled: true,
  },
};

export const WithBadge: Story = {
  args: {
    tabList: tabList.map((tab) => (tab.value === 'profile' ? { ...tab, activeBadge: true } : tab)),
  },
};

export const WithCount: Story = {
  args: {
    tabList: tabList.map((tab) =>
      tab.value === 'active' || tab.value === 'settings' ? { ...tab, count: tab.value === 'active' ? 2 : 12 } : tab,
    ),
  },
};

export const WithIcons: Story = {
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
};

export const CustomStyle: Story = {
  args: {
    wrapperCss: css`
      background: #f0f4ff;
      border-radius: 12px;
      padding: 24px;
      box-shadow: 0 2px 8px #1976d233;
    `,
  },
};
