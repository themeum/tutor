import SVGIcon from '@Atoms/SVGIcon';
import { colorPalate, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import { css } from '@emotion/react';
import { useFormatters } from '@Hooks/useFormatters';
import { styleUtils } from '@Utils/style-utils';
import { ReactNode } from 'react';
import { Bar, BarChart, CartesianGrid, ResponsiveContainer, Tooltip, XAxis, YAxis } from 'recharts';

import Card, { CardHeader } from './Card';

interface PVOrUVData {
  amount: number;
  text: string;
}
interface BarGraphProps<T> {
  data: T[];
  width: number;
  height: number;
  title: string;
  xAxisKey: string;
  action?: ReactNode;
  pvData: PVOrUVData;
  uvData: PVOrUVData;
}

const BarGraph = <T,>({ data, width, height, title, xAxisKey, action, pvData, uvData }: BarGraphProps<T>) => {
  const { priceFormat } = useFormatters();

  return (
    <Card>
      <CardHeader title={<GraphTitle title={title} />} noSeparator actionTray={action} />
      <div css={[styleUtils.cardInnerSection]}>
        <ResponsiveContainer width="100%" height="100%" minHeight={height}>
          <BarChart width={width} height={height} data={data}>
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
            <Bar name="Previous Record" dataKey="previousRecord" fill={colorPalate.chart.barGraph.pv} />
            <Bar
              name="Current Record"
              dataKey="currentRecord"
              fill={colorPalate.chart.barGraph.uv}
              style={{ transform: 'translateX(-4px)' }}
            />
          </BarChart>
        </ResponsiveContainer>

        <div css={styles.footer}>
          <div>
            <div css={styles.amountContainer}>
              <SVGIcon name="barLegend" style={styles.pvIcon} />
              <div css={styles.footerAmount}>{pvData.amount}</div>
            </div>
            <div css={styles.footerCaption}>{pvData.text}</div>
          </div>
          <div>
            <div css={styles.amountContainer}>
              <SVGIcon name="barLegend" style={styles.uvIcon} />
              <div css={styles.footerAmount}>{uvData.amount}</div>
            </div>
            <div css={styles.footerCaption}>{uvData.text}</div>
          </div>
        </div>
      </div>
    </Card>
  );
};

export default BarGraph;

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

const GraphTitle = ({ title }: { title: string }) => {
  return <div css={styles.titleLabel}>{title}</div>;
};

const styles = {
  titleLabel: css`
    ${typography.body()};
    color: ${colorPalate.text.neutral};
  `,
  xAxisText: css`
    ${typography.tiny()};
    fill: ${colorPalate.text.neutral};
  `,
  yAxisText: css`
    ${typography.caption()};
    fill: ${colorPalate.text.neutral};
  `,
  pvIcon: css`
    color: ${colorPalate.chart.barGraph.pv};
  `,
  uvIcon: css`
    color: ${colorPalate.chart.barGraph.uv};
  `,
  footer: css`
    display: flex;
    justify-content: space-between;
  `,
  amountContainer: css`
    display: flex;
    gap: ${spacing[8]};
  `,
  footerAmount: css`
    ${typography.body('bold')}
    color: ${colorPalate.text.dark};
  `,
  footerCaption: css`
    ${typography.caption()}
    color: ${colorPalate.text.lightDark};
    margin-left: ${spacing[24]};
  `,
};
