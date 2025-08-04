import { typography } from '@TutorShared/config/typography';
import { type Meta, type StoryObj } from 'storybook-react-rsbuild';

const TypographyDocPage = () => (
  <div>
    <h1 css={typography.heading2('bold')}>Typography System in Tutor</h1>
    <p css={typography.body()}>
      The Tutor typography system provides a consistent, scalable, and maintainable way to style all text elements
      across your application. All typography styles are centralized in <code>@TutorShared/config/typography.ts</code>{' '}
      and mapped to design tokens, ensuring visual harmony and easy updates.
    </p>
    <h2 css={typography.heading4('medium')}>How Typography Works</h2>
    <ul>
      <li>
        <b>Centralized Tokens:</b> All font sizes, line heights, weights, and families are defined in{' '}
        <code>styles.ts</code> and referenced in <code>typography.ts</code>.
      </li>
      <li>
        <b>Typeface Keys:</b> Use <code>&apos;regular&apos;</code>, <code>&apos;medium&apos;</code>,{' '}
        <code>&apos;semiBold&apos;</code>, or <code>&apos;bold&apos;</code> to set font weight for any style.
      </li>
      <li>
        <b>Usage:</b> Apply styles using EmotionJs <code>css</code> and the <code>typography</code> object for headings,
        body, captions, and more.
      </li>
    </ul>
    <h2 css={typography.heading4('medium')}>Usage Examples</h2>
    <h3 css={typography.heading5('medium')}>✅ Correct Usage: Headings</h3>
    <pre>
      {`
import { typography } from '@TutorShared/config/typography';

<h1 css={typography.heading1('bold')}>Heading 1 Bold</h1>
<h2 css={typography.heading2('medium')}>Heading 2 Medium</h2>
<h3 css={typography.heading3('regular')}>Heading 3 Regular</h3>
<h4 css={typography.heading4('semiBold')}>Heading 4 SemiBold</h4>
      `}
    </pre>
    <h3 css={typography.heading5('medium')}>✅ Correct Usage: Body & Caption</h3>
    <pre>
      {`
<p css={typography.body('regular')}>This is body text.</p>
<span css={typography.caption('medium')}>This is a caption.</span>
<small css={typography.small('regular')}>Small text example.</small>
<span css={typography.tiny('bold')}>Tiny bold text.</span>
      `}
    </pre>
    <h3 css={typography.heading5('medium')}>❌ Incorrect Usage: Hardcoded Styles</h3>
    <pre>
      {`
<h1 style={{ fontSize: '32px', fontWeight: 700, color: '#222' }}>Avoid hardcoded styles!</h1>
<p style={{ fontFamily: 'Arial', fontSize: '16px' }}>Don't use inline styles or random fonts.</p>
      `}
    </pre>
    <h2 css={typography.heading4('medium')}>Accessibility & Best Practices</h2>
    <ul>
      <li>
        Use semantic HTML tags (<code>h1</code>–<code>h6</code>, <code>p</code>, <code>span</code>, <code>small</code>)
        for text elements.
      </li>
      <li>
        Always use <code>aria-label</code> for non-semantic or interactive text elements.
      </li>
      <li>
        Prefer EmotionJs <code>css</code> with config tokens over inline styles for maintainability.
      </li>
      <li>Use descriptive variable and function names for readability.</li>
      <li>Follow DRY and atomic design principles.</li>
    </ul>
    <h2 css={typography.heading4('medium')}>All Typography Variants</h2>
    <div>
      <h1 css={typography.heading1('bold')}>Heading 1 Bold</h1>
      <h2 css={typography.heading2('medium')}>Heading 2 Medium</h2>
      <h3 css={typography.heading3('regular')}>Heading 3 Regular</h3>
      <h4 css={typography.heading4('semiBold')}>Heading 4 SemiBold</h4>
      <h5 css={typography.heading5('bold')}>Heading 5 Bold</h5>
      <h6 css={typography.heading6('medium')}>Heading 6 Medium</h6>
      <p css={typography.body('regular')}>Body Regular</p>
      <p css={typography.caption('medium')}>Caption Medium</p>
      <p css={typography.small('regular')}>Small Regular</p>
      <p css={typography.tiny('bold')}>Tiny Bold</p>
    </div>
  </div>
);

const meta = {
  title: 'Docs/Typography',
  component: TypographyDocPage,
  parameters: {
    layout: 'fullscreen',
    controls: { disable: true },
    docs: {
      page: () => <TypographyDocPage />,
    },
  },
  tags: ['autodocs'],
} satisfies Meta<typeof TypographyDocPage>;

export default meta;
type Story = StoryObj<typeof meta>;
export const Docs = {} satisfies Story;
