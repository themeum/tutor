import { SerializedStyles } from '@emotion/react';
import { useTranslation } from '@Hooks/useTranslation';

const Trans = ({
  transKey,
  options,
  styles,
}: {
  transKey: string;
  options?: Record<string, string | number>;
  styles?: SerializedStyles;
}) => {
  const t = useTranslation();
  return <div dangerouslySetInnerHTML={{ __html: t(transKey, options) }} css={styles} />;
};

export default Trans;
