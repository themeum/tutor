import BlockRenderer from '@Settings/components/blocks/BlockRenderer';
import { type SettingsBlock as SettingsBlockType } from '@Settings/contexts/SettingsContext';
import React from 'react';

interface SettingsBlockProps {
  block: SettingsBlockType;
}

const SettingsBlock: React.FC<SettingsBlockProps> = ({ block }) => {
  return <BlockRenderer block={block} />;
};

export default SettingsBlock;
