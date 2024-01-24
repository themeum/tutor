import { colorPalate, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import { css } from '@emotion/react';
import { useFormatters } from '@Hooks/useFormatters';
import { styleUtils } from '@Utils/style-utils';
import { ReactNode } from 'react';
import { CartesianGrid, LineChart, XAxis, YAxis, Tooltip, Line, ResponsiveContainer } from 'recharts';

import Card, { CardHeader } from './Card';

interface LineGraphProps<T> {
  data: T[];
  width: number;
  height: number;
  title: string;
  total: number;
  xAxisKey: string;
  action?: ReactNode;
}

const LineGraph = <T,>({ data, width, height, title, total, xAxisKey, action }: LineGraphProps<T>) => {
  const { priceFormat } = useFormatters();

  return (
    <Card>
      <CardHeader title={<GraphTitle value={total} title={title} />} actionTray={action} noSeparator />

      <div css={styleUtils.cardInnerSection}>
        <ResponsiveContainer width="100%" height="100%" minHeight={height}>
          <LineChart width={width} height={height} data={data}>
            <CartesianGrid stroke="#EEF1F3" vertical={false} />
            <XAxis
              dataKey={xAxisKey}
              axisLine={false}
              tickLine={false}
              tick={(props) => <CustomXAxisTick {...props} />}
            />
            <YAxis
              axisLine={false}
              tickFormatter={(value) => priceFormat(value, { fractionDigits: 0 })}
              tickLine={false}
              tick={(props) => <CustomYAxisTick {...props} />}
            />
            <Tooltip />
            <Line type="monotone" dataKey="amount" stroke="#3366FF" strokeWidth={1.68} activeDot={{ r: 4 }} />
          </LineChart>
        </ResponsiveContainer>
      </div>
    </Card>
  );
};

export default LineGraph;

interface CustomAxisTickProps {
  x: number;
  y: number;
  payload: {
    value: number | string;
  };
}

const CustomXAxisTick = ({ x, y, payload }: CustomAxisTickProps) => {
  return (
    <g transform={`translate(${x - 10}, ${y + 24})`}>
      <text x={0} y={0} css={styles.yAxisText}>
        {payload.value}
      </text>
    </g>
  );
};
const CustomYAxisTick = ({ x, y, payload }: CustomAxisTickProps) => {
  const { priceFormat } = useFormatters();
  return (
    <g transform={`translate(${x - 48}, ${y})`}>
      <text x={0} y={0} css={styles.xAxisText}>
        {priceFormat(Number(payload.value), { fractionDigits: 0 })}
      </text>
    </g>
  );
};

const GraphTitle = ({ value, title }: { value: number; title: string; action?: ReactNode }) => {
  const { priceFormat } = useFormatters();

  return (
    <div>
      <p css={styles.titleLabel}>{title}</p>
      <span css={typography.heading4('medium')}>{priceFormat(value, { fractionDigits: 0 })}</span>
    </div>
  );
};

const styles = {
  wrapper: css`
    margin-top: ${spacing[32]};
  `,
  titleContainer: css`
    display: flex;
    justify-content: space-between;
  `,
  titleLabel: css`
    ${typography.body()};
    color: ${colorPalate.text.neutral};
  `,
  sectionTitle: css`
    ${typography.heading4('medium')};
    margin-bottom: ${spacing[16]};
  `,
  xAxisText: css`
    ${typography.tiny()};
    fill: ${colorPalate.text.neutral};
  `,
  yAxisText: css`
    ${typography.caption()};
    fill: ${colorPalate.text.neutral};
  `,
};
