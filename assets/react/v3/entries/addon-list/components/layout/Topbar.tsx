import { css } from '@emotion/react';
import { Breakpoint, colorTokens, fontSize, fontWeight, lineHeight, spacing } from '@Config/styles';
import { useAddonContext } from '../../contexts/addon-context';
import Container from './Container';
import SVGIcon from '@/v3/shared/atoms/SVGIcon';
import { __ } from '@wordpress/i18n';
import TextInput from '@/v3/shared/atoms/TextInput';

export const TOPBAR_HEIGHT = 80;

function Topbar() {
  const { searchTerm, setSearchTerm } = useAddonContext();

  return (
    <div css={styles.wrapper}>
      <Container>
        <div css={styles.innerWrapper}>
          <div css={styles.left}>
            <SVGIcon name="addons" width={32} height={32} />
            {__('Add-one', 'tutor')}
          </div>
          <div css={styles.right}>
            <TextInput
              variant="search"
              type="text"
              value={searchTerm}
              onChange={setSearchTerm}
              placeholder={__('Search...', 'tutor')}
              isClearable
            />
          </div>
        </div>
      </Container>
    </div>
  );
}

export default Topbar;

const styles = {
  wrapper: css`
    min-height: ${TOPBAR_HEIGHT}px;
  `,
  innerWrapper: css`
    display: flex;
    align-items: center;
    justify-content: space-between;
    height: 100%;
    border-bottom: 1px solid ${colorTokens.stroke.divider};
    padding: ${spacing[20]} 0px;

    ${Breakpoint.mobile} {
      flex-direction: column;
      gap: ${spacing[12]};
    }
  `,
  left: css`
    display: flex;
    align-items: center;
    gap: ${spacing[12]};

    font-size: ${fontSize[20]};
    line-height: ${lineHeight[28]};
    font-weight: ${fontWeight.medium};
    color: ${colorTokens.text.primary};

    svg {
      color: ${colorTokens.icon.hover};
    }
  `,
  right: css`
    min-width: 300px;
  `,
};
