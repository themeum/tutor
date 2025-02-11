import config from '@TutorShared/config/config';
import { borderRadius, colorTokens, fontSize, fontWeight, shadow, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import type { AnyObject } from '@TutorShared/utils/form';
import { styleUtils } from '@TutorShared/utils/style-utils';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import ErrorStackParser from 'error-stack-parser';
import type React from 'react';
import { Component, type ErrorInfo } from 'react';
import { SourceMapConsumer } from 'source-map';

import productionError2x from '@SharedImages/production-error-2x.webp';
import productionError from '@SharedImages/production-error.webp';
import Button from '@TutorShared/atoms/Button';
import SVGIcon from '@TutorShared/atoms/SVGIcon';

const errorDisplayWindowWidth = 960;

interface ErrorBoundaryProps {
  children: React.ReactNode;
}

interface ErrorBoundaryState {
  hasError: boolean;
  error?: Error;
  errorInfo?: ErrorInfo;
  stack: ErrorStackParser.StackFrame[];
  source: string[];
  position: {
    line: number | null;
    column: number | null;
    source: string | null;
    startLine: number | null;
  };
}

const cleanPath = (path: string) => {
  const parts = path.split('?');
  return parts[0];
};

const getMapFile = (path: string) => `${cleanPath(path)}.map`;

interface SourceMap {
  file: string;
  mappings: string;
  names: string[];
  sourceRoot: string;
  sources: string[];
  sourcesContent: string[];
  version: number;
}

const getRelativePath = (path: string) => {
  const index = path.indexOf('store');
  return index > -1 ? path.slice(index) : '';
};

const getFilenameFromPath = (path: string) => {
  const parts = path.split('/');
  return parts.length === 0 ? '' : parts[parts.length - 1];
};

class ErrorBoundary extends Component<ErrorBoundaryProps, ErrorBoundaryState> {
  constructor(props: ErrorBoundaryProps) {
    super(props);
    this.state = {
      hasError: false,
      error: undefined,
      errorInfo: undefined,
      stack: [],
      source: [],
      position: { line: null, column: null, startLine: null, source: '' },
    };

    (SourceMapConsumer as AnyObject).initialize({
      'lib/mappings.wasm': 'https://unpkg.com/source-map@0.7.4/lib/mappings.wasm',
    });
  }

  static getDerivedStateFromError() {
    return { hasError: true };
  }

  componentDidCatch(error: Error, errorInfo: ErrorInfo) {
    setTimeout(() => {
      const stackArray = ErrorStackParser.parse(error);
      const mapFileSource = getMapFile(stackArray[0].fileName || '');

      fetch(mapFileSource)
        .then((res) => res.json())
        .then(async (source: SourceMap) => {
          const consumer = await new SourceMapConsumer(source);
          const file = stackArray[0];

          const position = consumer.originalPositionFor({
            line: Number(file.lineNumber),
            column: Number(file.columnNumber),
          });

          let sourceIndex = -1;

          if (position.source) {
            const parts = position.source.split('/');
            const fileName = parts[parts.length - 1] || '';
            sourceIndex = source.sources.findIndex((s) => s.endsWith(fileName));
          }

          if (sourceIndex > -1) {
            const lines = source.sourcesContent[sourceIndex].split(/\r?\n/);

            const { line } = position;

            const startLineNumber = Math.max(0, (line || 0) - 3);
            const endLineNumber = Math.min(lines.length - 1, (line || 0) + 4);

            const sourceCode = lines.slice(startLineNumber, endLineNumber + 1);
            const sourcePath = position.source?.split('/').pop();

            this.setState({
              source: sourceCode,
              position: {
                line: position.line,
                column: position.column,
                source: sourcePath || '',
                startLine: startLineNumber,
              },
            });
          }
        });

      this.setState({
        error,
        errorInfo,
        stack: stackArray,
      });
    }, 100);
  }

  renderErrorSource() {
    const { position } = this.state;

    if (!position.line && !position.column) {
      return <p css={styles.consoleMessage}>Please open the browser console for more information!</p>;
    }

    return (
      <div css={styles.titleAndContent}>
        <h4 css={styles.callStack}>Source</h4>
        <div css={styles.sourceWrapper}>
          <p css={styles.sourceFilePath}>
            {position.source} ({position.line}:{position.column})
          </p>
          <div css={styles.sourceEditor}>
            <div css={styles.sourceLinesWrapper}>
              {this.state.source.map((line, index) => {
                const lineNumber = index + (position.startLine || 0) + 1;
                const isErrorLine = lineNumber === (position.line || 0);
                const lineNumberLength = this.state.source.length + (position.startLine || 0);

                return (
                  <div key={index} css={styles.sourceLine(isErrorLine)}>
                    <div>
                      <span css={styles.angleRight}>{isErrorLine && '>'}</span>
                      <span css={styles.lineNumber}>{lineNumber} | </span>
                      <span css={styles.line}>{line}</span>
                    </div>

                    {isErrorLine && (
                      <div>
                        <span css={styles.angleRight}> </span>
                        <span css={styles.lineNumber}>{' '.repeat(lineNumberLength.toString().length)} | </span>
                        <span css={[styles.line, { color: colorTokens.text.error }]}>
                          {' '.repeat(position.column || 0)}^
                        </span>
                      </div>
                    )}
                  </div>
                );
              })}
            </div>
          </div>
        </div>
      </div>
    );
  }

  renderProductionError() {
    return (
      <div css={styles.container({ inProduction: true })}>
        <div css={styles.productionErrorWrapper}>
          <div css={styles.productionErrorHeader}>
            <img src={productionError} srcSet={`${productionError2x} 2x`} alt={__('Error', 'tutor')} />
            <h5 css={typography.heading5('medium')}>{__('Oops! Something went wrong', 'tutor')}</h5>

            <div css={styles.instructions}>
              <p>{__('Doing one of the following things could help:', 'tutor')}</p>
              <ul>
                <li>{__('Try to refresh the page', 'tutor')}</li>
                <li>{__('Clear your browser cache', 'tutor')}</li>
                <li>{__('Check if you have the correct permissions to access this content', 'tutor')}</li>
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
                {__('Reload', 'tutor')}
              </Button>
            </div>
            <div css={styles.support}>
              <span>{__('Still having trouble? ', 'tutor')}</span>
              <span>{__('Contact our ', 'tutor')}</span>
              <a href={config.TUTOR_SUPPORT_PAGE_URL}>{__('Support')}</a>
            </div>
          </div>
        </div>
      </div>
    );
  }

  renderDevelopmentError() {
    const { error, position, stack } = this.state;

    return (
      <div css={styles.container({ inProduction: false })}>
        <div css={styles.wrapper}>
          <div css={styles.scrollWrapper}>
            <div css={styles.indicator} />
            <h2 css={styles.errorHeading}>Unhandled Runtime Error</h2>
            <p css={styles.errorMessage}>{error?.toString() || 'Something Went Wrong!'}</p>

            {this.renderErrorSource()}

            <div css={styles.callStackWrapper}>
              <h4 css={styles.callStack}>Call Stack</h4>

              {stack.map((info, index) => {
                return (
                  <div key={index} css={styles.stackItem}>
                    <h6 css={styles.functionName}>{info.functionName || getFilenameFromPath(position.source || '')}</h6>
                    <p css={styles.filePath}>
                      {cleanPath(getRelativePath(info.fileName || ''))} ({info.lineNumber}:{info.columnNumber})
                    </p>
                  </div>
                );
              })}
            </div>
          </div>
        </div>
      </div>
    );
  }

  render() {
    if (this.state.hasError) {
      return process.env.NODE_ENV === 'production' ? this.renderProductionError() : this.renderDevelopmentError();
    }

    return this.props.children;
  }
}

export default ErrorBoundary;

const styles = {
  container: ({ inProduction = false }) => css`
    * {
      box-sizing: border-box;
      padding: 0;
      margin: 0;
    }

    width: 100%;
    height: ${inProduction ? 'auto' : '100vh'};
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
    margin-top: ${spacing[20]};
  `,
  support: css`
    ${typography.caption()};
    color: ${colorTokens.text.title};

    a {
      color: ${colorTokens.text.brand};
      text-decoration: none;
    }
  `,
  sourceLinesWrapper: css`
    display: flex;
    flex-direction: column;
  `,
  consoleMessage: css`
    ${typography.heading6('bold')};
    margin-top: ${spacing[24]};
    color: ${colorTokens.text.success};
  `,
  sourceLine: (isErrorLine: boolean) => css`
    white-space: pre-wrap;

    ${isErrorLine &&
    css`
      color: ${colorTokens.text.error};
    `}
  `,
  angleRight: css`
    min-width: 20px;
    display: inline-block;
    margin-right: 12px;
  `,
  lineNumber: css`
    min-width: 50px;
    display: inline-block;
  `,
  line: css`
    width: 100%;
  `,
  wrapper: css`
    max-width: ${errorDisplayWindowWidth}px;
    width: 100%;
    background: ${colorTokens.background.white};
    box-shadow: ${shadow.modal};
    padding: ${spacing[20]};
    border-radius: ${borderRadius[6]};
    position: relative;
    overflow: hidden;
  `,
  scrollWrapper: css`
    height: 80vh;
    overflow-y: auto;
  `,
  indicator: css`
    width: 100%;
    height: 5px;
    background: ${colorTokens.color.danger.main};
    position: absolute;
    top: 0;
    left: 0;
  `,
  errorHeading: css`
    font-size: ${fontSize[20]};
    font-weight: ${fontWeight.bold};
    margin-bottom: ${spacing[4]};
    color: ${colorTokens.text.primary};
  `,
  errorMessage: css`
    font-size: ${fontSize[14]};
    font-weight: ${fontWeight.bold};
    color: ${colorTokens.text.title};
    margin-top: ${spacing[4]};
    color: ${colorTokens.text.error};
  `,
  callStack: css`
    ${typography.heading6('bold')};
    color: ${colorTokens.text.primary};
  `,
  callStackWrapper: css`
    margin-top: ${spacing[48]};
    font-size: ${fontSize[18]};
    display: flex;
    flex-direction: column;
    gap: ${spacing[20]};
  `,
  stackItem: css`
    font-size: ${fontSize[18]};
    display: flex;
    flex-direction: column;
    gap: ${spacing[4]};
  `,
  functionName: css`
    ${typography.heading6()};
    color: ${colorTokens.text.primary};
  `,
  filePath: css`
    color: ${colorTokens.text.subdued};
    margin-left: ${spacing[12]};
    font-size: ${fontSize[14]};
  `,
  sourceEditor: css`
    padding: ${spacing[8]} ${spacing[32]};
  `,
  sourceWrapper: css`
    margin-top: ${spacing[4]};

    background-color: #292929;
    border-radius: ${borderRadius[6]};
    color: ${colorTokens.text.white};
  `,
  sourceFilePath: css`
    padding: ${spacing[8]} ${spacing[16]};
    border-bottom: 1px solid #cccccc;
    color: #cccccc;
  `,
  titleAndContent: css`
    margin-top: ${spacing[24]};
  `,
};
