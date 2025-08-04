import { typography } from '@TutorShared/config/typography';
import { type Meta, type StoryObj } from 'storybook-react-rsbuild';

const GettingStartedWithTutor = () => (
  <div>
    <h1 css={typography.heading5('bold')}>Getting Started with Tutor Design Library</h1>
    <p css={typography.body()}>
      The Tutor design library is your foundation for building robust, scalable, and visually consistent UI components.
      By centralizing all design tokens, typography, colors, and layout values in the <code>shared/config</code> folder,
      you ensure every part of your app looks and behaves as intended, regardless of who builds it.
      <br />
      <br />
      <b>Benefits:</b> Consistency, scalability, maintainability, and rapid development.
    </p>
    <h2 css={typography.heading6('medium')}>Config Folder Deep Dive</h2>
    <ul>
      <li>
        <b>config.ts</b>: Contains runtime configuration for Tutor, such as API URLs, feature flags, and global objects.
        <br />
        <code>{`import { apiUrl } from '@TutorShared/config/config'; // Use for API calls`}</code>
      </li>
      <li>
        <b>constants.ts</b>: Defines shared constants, enums, breakpoints, file size limits, and other values used
        across the app.
        <br />
        <code>{`import { BREAKPOINTS, FILE_SIZE_LIMIT } from '@TutorShared/config/constants'; // Use for responsive layouts and uploads`}</code>
      </li>
      <li>
        <b>magic-ai.ts</b>: Holds options and types for AI-powered features, including chat tones and supported
        languages.
        <br />
        <code>{`import { chatTones, supportedLanguages } from '@TutorShared/config/magic-ai'; // Use for AI integrations`}</code>
      </li>
      <li>
        <b>route-configs.ts</b>: Provides utilities for defining and building route templates with typed parameters.
        <br />
        <code>{`import { buildRoute } from '@TutorShared/config/route-configs'; // Use for navigation`}</code>
      </li>
      <li>
        <b>styles.ts</b>: The heart of your design tokens—colors, spacing, font sizes, border radius, shadows,
        breakpoints, and more.
        <br />
        <code>{`import { colorTokens, spacing, borderRadius } from '@TutorShared/config/styles'; // Use for all component styling`}</code>
      </li>
      <li>
        <b>typography.ts</b>: Centralizes all typography styles using EmotionJs, mapped directly to your design tokens.
        <br />
        <code>{`import { typography } from '@TutorShared/config/typography'; // Use for headings and body text`}</code>
      </li>
    </ul>
    <h2 css={typography.heading6('medium')}>Atomic Design & Open/Closed Principle</h2>
    <p css={typography.body()}>
      Tutor follows atomic design: <b>atoms</b> (Button), <b>molecules</b> (Card), and <b>organisms</b> (Form).
      <br />
      Each component should be <b>open for extension</b> (easy to add new features) but <b>closed for modification</b>{' '}
      (internal logic and styling should not be changed directly).
      <br />
      <br />
      <b>Always use config tokens and EmotionJs for styling.</b> Never hardcode values or use inline styles, as this
      breaks consistency and maintainability.
    </p>
    <h3 css={typography.body('medium')}>✅ Correct Usage Example: Atom (Button)</h3>
    <pre>
      {`
import { css } from '@emotion/react';
import { colorTokens, spacing, borderRadius } from '@TutorShared/config/styles';

const buttonStyle = css\`
  background: \${colorTokens.brand.blue};
  color: \${colorTokens.text.white};
  padding: \${spacing[12]} \${spacing[24]};
  border-radius: \${borderRadius[8]};
  font-family: 'Inter', sans-serif;
\`;

const Button = ({ children }) => (
  <button css={buttonStyle} aria-label="Button" tabIndex={0}>
    {children}
  </button>
);
      `}
    </pre>
    <h3 css={typography.body('medium')}>✅ Correct Usage Example: Molecule (Card)</h3>
    <pre>
      {`
import { css } from '@emotion/react';
import { colorTokens, spacing, borderRadius, shadow } from '@TutorShared/config/styles';

const cardStyle = css\`
  background: \${colorTokens.background.paper};
  border-radius: \${borderRadius[16]};
  box-shadow: \${shadow[2]};
  padding: \${spacing[24]};
\`;

const Card = ({ children }) => (
  <div css={cardStyle} aria-label="Card" tabIndex={0}>
    {children}
  </div>
);
      `}
    </pre>
    <h3 css={typography.body('medium')}>✅ Correct Usage Example: Organism (Form)</h3>
    <pre>
      {`
import { css } from '@emotion/react';
import { spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';

const formStyle = css\`
  display: flex;
  flex-direction: column;
  gap: \${spacing[16]};
\`;

const Form = ({ children }) => (
  <form css={formStyle} aria-label="Signup Form" tabIndex={0}>
    <h2 css={typography.heading4('bold')}>Sign Up</h2>
    {children}
  </form>
);
      `}
    </pre>
    <h3 css={typography.body('medium')}>❌ Incorrect Usage Example (Hardcoded Styles)</h3>
    <pre>
      {`
const Button = ({ children }) => (
  <button style={{ background: '#123456', color: '#fff', padding: '10px 20px', borderRadius: '3px' }}>
    {children}
  </button>
);
// Avoid hardcoded styles and inline styles. Use config tokens and EmotionJs instead.
      `}
    </pre>
    <h3 css={typography.body('medium')}>❌ Incorrect Usage Example (Modifying Internal Logic)</h3>
    <pre>
      {`
// Don't directly change internal logic or styling of shared components.
// Instead, extend or compose them.
const CustomButton = (props) => {
  // Directly changing Button's internals is discouraged.
  // Instead, wrap or extend Button for new features.
};
      `}
    </pre>
    <h2 css={typography.heading6('medium')}>Accessibility & Best Practices</h2>
    <ul>
      <li>
        Always use <code>aria-label</code> and <code>tabIndex</code> for interactive elements to ensure accessibility.
      </li>
      <li>
        Prefer <code>css</code> from EmotionJs over inline styles for maintainability and theme consistency.
      </li>
      <li>Use descriptive variable and function names, and early returns for readability.</li>
      <li>
        Minimize <code>useState</code> and <code>useEffect</code> usage to reduce unnecessary rerenders.
      </li>
      <li>Follow DRY and atomic design principles to keep your codebase clean and scalable.</li>
      <li>Extend components by composition, not by modifying their internals.</li>
    </ul>
  </div>
);

const meta = {
  title: 'Docs/Getting Started',
  component: GettingStartedWithTutor,
  parameters: {
    layout: 'fullscreen',
    docs: {
      page: () => <GettingStartedWithTutor />,
    },
  },
  tags: ['autodocs'],
} satisfies Meta<typeof GettingStartedWithTutor>;

export default meta;
type Story = StoryObj<typeof meta>;
export const Docs = {} satisfies Story;
