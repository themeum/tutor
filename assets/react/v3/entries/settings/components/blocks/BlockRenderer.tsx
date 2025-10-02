import BlockSegments from '@Settings/components/BlockSegments';
import { type SettingsBlock } from '@Settings/contexts/SettingsContext';
import React from 'react';
import {
  ColorPickerBlock,
  CustomBlock,
  IsolateBlock,
  NotificationBlock,
  UniformBlock,
  VisibilityControlBlock,
} from './index';

interface BlockRendererProps {
  block: SettingsBlock;
}

const BlockRenderer: React.FC<BlockRendererProps> = ({ block }) => {
  // Handle blocks with segments (tabs) - these take precedence over block_type
  if (block.segments && Array.isArray(block.segments) && block.segments.length > 0) {
    return <BlockSegments segments={block.segments} blockLabel={block.label} />;
  }

  // Route to appropriate block component based on block_type
  switch (block.block_type) {
    case 'uniform':
      return <UniformBlock block={block} />;

    case 'isolate':
      return <IsolateBlock block={block} />;

    case 'custom':
      return <CustomBlock block={block} />;

    case 'color_picker':
      return <ColorPickerBlock block={block} />;

    case 'visibility-control':
      return <VisibilityControlBlock block={block} />;

    case 'notification':
      return <NotificationBlock block={block} />;

    default:
      // Fallback to uniform block for unknown types
      return <UniformBlock block={block} />;
  }
};

export default BlockRenderer;
