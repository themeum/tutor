import SVGIcon from '@Atoms/SVGIcon';
import { colorPalate, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import { css } from '@emotion/react';
import { Fragment } from 'react';
import { Link } from 'react-router-dom';

interface Route {
  link: string | null;
  text: string;
}

interface BreadcrumbsProps {
  routes: Route[];
}

const Breadcrumbs = ({ routes }: BreadcrumbsProps) => {
  return (
    <div css={styles.wrapper}>
      {routes.map((route, index) => {
        const isLastChild = index === routes.length - 1;
        return (
          <Fragment key={index}>
            {route.link ? <Link to={route.link}>{route.text}</Link> : <span>{route.text}</span>}
            {!isLastChild && <SVGIcon name="anglesRight" width={9} height={10} />}
          </Fragment>
        );
      })}
    </div>
  );
};

export default Breadcrumbs;

const styles = {
  wrapper: css`
    display: flex;
    align-items: center;
    gap: ${spacing[10]};
    margin-bottom: ${spacing[32]};
    color: ${colorPalate.text.neutral};

    a,
    span {
      ${typography.body('medium')};
      color: inherit;
      text-decoration: none;
    }

    & :last-child {
      font-weight: 400;
    }
  `,
};
