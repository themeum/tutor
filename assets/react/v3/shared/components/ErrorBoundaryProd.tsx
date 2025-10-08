import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import type React from 'react';
import { Component, type ErrorInfo } from 'react';

import Button from '@TutorShared/atoms/Button';
import SVGIcon from '@TutorShared/atoms/SVGIcon';
import config, { tutorConfig } from '@TutorShared/config/config';
import { borderRadius, colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import Show from '@TutorShared/controls/Show';
import { styleUtils } from '@TutorShared/utils/style-utils';

import productionError2x from '@SharedImages/production-error-2x.webp';
import productionError from '@SharedImages/production-error.webp';

interface ErrorBoundaryProps {
  children: React.ReactNode;
}

interface ErrorBoundaryState {
  hasError: boolean;
}

class ErrorBoundaryProd extends Component<ErrorBoundaryProps, ErrorBoundaryState> {
  constructor(props: ErrorBoundaryProps) {
    super(props);
    this.state = {
      hasError: false,
    };
  }

  static getDerivedStateFromError() {
    return { hasError: true };
  }

  componentDidCatch(error: Error, errorInfo: ErrorInfo) {
    // eslint-disable-next-line no-console
    console.error(error, errorInfo);
  }

  render() {
    if (this.state.hasError) {
      return (
        <div css={styles.container}>
          <div css={styles.productionErrorWrapper}>
            <div css={styles.productionErrorHeader}>
              <img src={productionError} srcSet={`${productionError2x} 2x`} alt={__('Error', __TUTOR_TEXT_DOMAIN__)} />
              <h5 css={typography.heading5('medium')}>{__('Oops! Something went wrong', __TUTOR_TEXT_DOMAIN__)}</h5>

              <div css={styles.instructions}>
                <p>{__('Try the following steps to resolve the issue:', __TUTOR_TEXT_DOMAIN__)}</p>
                <ul>
                  <li>{__('Refresh the page.', __TUTOR_TEXT_DOMAIN__)}</li>
                  <li>{__('Clear your browser cache.', __TUTOR_TEXT_DOMAIN__)}</li>
                  <Show when={tutorConfig.tutor_pro_url}>
                    <li>{__('Ensure the Free and Pro plugins are on the same version.', __TUTOR_TEXT_DOMAIN__)}</li>
                  </Show>
                </ul>
              </div>
            </div>

            <div css={styles.productionFooter}>
              <div>
                <Button
                  variant="secondary"
                  icon={<SVGIcon name="refresh" height={24} width={24} />}
                  onClick={() => window.location.reload()}
                >
                  {__('Reload', __TUTOR_TEXT_DOMAIN__)}
                </Button>
              </div>
              <div css={styles.support}>
                <span>{__('Still having trouble?', __TUTOR_TEXT_DOMAIN__)}</span>
                <span>{__('Contact', __TUTOR_TEXT_DOMAIN__)}</span>
                <a href={config.TUTOR_SUPPORT_PAGE_URL}>{__('Support', __TUTOR_TEXT_DOMAIN__)}</a>
                <span>{__('for assistance.', __TUTOR_TEXT_DOMAIN__)}</span>
              </div>
            </div>
          </div>
        </div>
      );
    }

    return this.props.children;
  }
}

export default ErrorBoundaryProd;

const styles = {
  container: css`
    width: 100%;
    height: auto;
    display: flex;
    justify-content: center;
    align-items: center;
  `,
  productionErrorWrapper: css`
    ${styleUtils.display.flex('column')};
    gap: ${spacing[20]};
    max-width: 500px;
    width: 100%;
  `,
  productionErrorHeader: css`
    ${styleUtils.display.flex('column')};
    align-items: center;
    padding: ${spacing[32]};
    background: ${colorTokens.background.white};
    border-radius: ${borderRadius[12]};
    box-shadow: 0px -4px 0px 0px #ff0000;
    gap: ${spacing[16]};

    h5 {
      text-align: center;
    }

    img {
      height: 104px;
      width: 101px;
      object-position: center;
      object-fit: contain;
    }
  `,
  instructions: css`
    width: 100%;
    max-width: 333px;
    p {
      width: 100%;
      ${typography.caption()};
      margin-bottom: ${spacing[4]};
    }

    ul {
      padding-left: ${spacing[16]};
      li {
        ${typography.caption()};
        color: ${colorTokens.text.title};
        list-style: unset;
        margin-bottom: ${spacing[2]};

        &::marker {
          color: ${colorTokens.icon.default};
        }
      }
    }
  `,
  productionFooter: css`
    ${styleUtils.display.flex('column')};
    align-items: center;
    gap: ${spacing[12]};
  `,
  support: css`
    ${styleUtils.flexCenter('row')};
    text-align: center;
    flex-wrap: wrap;
    gap: ${spacing[4]};
    ${typography.caption()};
    color: ${colorTokens.text.title};

    a {
      color: ${colorTokens.text.brand};
      text-decoration: none;
    }
  `,
};
