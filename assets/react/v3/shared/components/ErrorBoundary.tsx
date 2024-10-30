import { borderRadius, colorTokens, fontSize, fontWeight, shadow, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import type { AnyObject } from '@Utils/form';
import { css } from '@emotion/react';
import ErrorStackParser from 'error-stack-parser';
import type React from 'react';
import { Component, type ErrorInfo } from 'react';
import { SourceMapConsumer } from 'source-map';

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

    // eslint-disable-next-line @typescript-eslint/no-unsafe-call
    (SourceMapConsumer as AnyObject).initialize({
      'lib/mappings.wasm': 'https://unpkg.com/source-map@0.7.3/lib/mappings.wasm',
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
            const sourcePath = position.source?.slice(position.source.indexOf('src'));

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

  render() {
    if (this.state.hasError) {
      const { error, position, stack } = this.state;

      return (
        <div css={styles.container}>
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
                      <h6 css={styles.functionName}>
                        {info.functionName || getFilenameFromPath(position.source || '')}
                      </h6>
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

    return this.props.children;
  }
}

export default ErrorBoundary;

const styles = {
  container: css`
    * {
      box-sizing: border-box;
      padding: 0;
      margin: 0;
    }

    width: 100%;
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
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

    ${
      isErrorLine &&
      css`
      color: ${colorTokens.text.error};
    `
    }
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
