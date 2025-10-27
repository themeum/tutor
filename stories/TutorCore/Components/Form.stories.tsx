import { css } from '@emotion/react';
import type { Meta, StoryObj } from '@storybook/react';
import { useState } from 'react';

const meta: Meta = {
  title: 'TutorCore/Components/Form',
  parameters: {
    docs: {
      description: {
        component: `
# Form Components

TutorCore provides a comprehensive set of form components with consistent styling, validation states, and accessibility features. All form elements support RTL layouts and follow WCAG guidelines.

## Features

- **Complete Form Elements**: Inputs, textareas, selects, checkboxes, radios
- **Validation States**: Error, success, warning states with messages
- **Accessibility**: Proper labels, ARIA attributes, keyboard navigation
- **RTL Support**: Automatic adaptation for RTL layouts
- **Responsive**: Mobile-friendly touch targets and layouts

## CSS Classes

\`\`\`css
/* Form elements */
.tutor-form-group
.tutor-label
.tutor-input
.tutor-textarea
.tutor-select
.tutor-checkbox
.tutor-radio

/* Form states */
.tutor-input--error
.tutor-input--success
.tutor-input--disabled

/* Form text */
.tutor-help-text
.tutor-error-text
.tutor-success-text
\`\`\`
        `,
      },
    },
  },
};

export default meta;
type Story = StoryObj;

const formStyles = {
  formGroup: css`
    margin-bottom: 20px;
  `,
  
  label: css`
    display: block;
    margin-bottom: 6px;
    font-size: 14px;
    font-weight: 500;
    color: #333741;
  `,
  
  input: css`
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #cecfd2;
    border-radius: 6px;
    font-size: 14px;
    color: #333741;
    background: white;
    transition: all 0.2s ease;
    
    &:focus {
      outline: none;
      border-color: #4979e8;
      box-shadow: 0 0 0 3px rgba(73, 121, 232, 0.1);
    }
    
    &::placeholder {
      color: #94969c;
    }
    
    &:disabled {
      background: #f5f5f6;
      color: #94969c;
      cursor: not-allowed;
    }
  `,
  
  inputError: css`
    border-color: #d92d20;
    
    &:focus {
      border-color: #d92d20;
      box-shadow: 0 0 0 3px rgba(217, 45, 32, 0.1);
    }
  `,
  
  inputSuccess: css`
    border-color: #4ca30d;
    
    &:focus {
      border-color: #4ca30d;
      box-shadow: 0 0 0 3px rgba(76, 163, 13, 0.1);
    }
  `,
  
  helpText: css`
    margin-top: 6px;
    font-size: 12px;
    color: #61646c;
  `,
  
  errorText: css`
    margin-top: 6px;
    font-size: 12px;
    color: #d92d20;
    display: flex;
    align-items: center;
    gap: 4px;
  `,
  
  successText: css`
    margin-top: 6px;
    font-size: 12px;
    color: #4ca30d;
    display: flex;
    align-items: center;
    gap: 4px;
  `,
  
  checkbox: css`
    appearance: none;
    width: 16px;
    height: 16px;
    border: 1px solid #cecfd2;
    border-radius: 3px;
    background: white;
    cursor: pointer;
    position: relative;
    margin-right: 8px;
    
    &:checked {
      background: #4979e8;
      border-color: #4979e8;
      
      &::after {
        content: '';
        position: absolute;
        top: 1px;
        left: 4px;
        width: 6px;
        height: 10px;
        border: solid white;
        border-width: 0 2px 2px 0;
        transform: rotate(45deg);
      }
    }
    
    &:focus {
      outline: 2px solid #4979e8;
      outline-offset: 2px;
    }
  `,
  
  radio: css`
    appearance: none;
    width: 16px;
    height: 16px;
    border: 1px solid #cecfd2;
    border-radius: 50%;
    background: white;
    cursor: pointer;
    position: relative;
    margin-right: 8px;
    
    &:checked {
      background: #4979e8;
      border-color: #4979e8;
      
      &::after {
        content: '';
        position: absolute;
        top: 3px;
        left: 3px;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: white;
      }
    }
    
    &:focus {
      outline: 2px solid #4979e8;
      outline-offset: 2px;
    }
  `,
  
  select: css`
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #cecfd2;
    border-radius: 6px;
    font-size: 14px;
    color: #333741;
    background: white;
    cursor: pointer;
    
    &:focus {
      outline: none;
      border-color: #4979e8;
      box-shadow: 0 0 0 3px rgba(73, 121, 232, 0.1);
    }
  `,
};

const ErrorIcon = () => (
  <svg width="12" height="12" viewBox="0 0 12 12" fill="currentColor">
    <path d="M6 0a6 6 0 100 12A6 6 0 006 0zM5 3h2v4H5V3zm0 5h2v2H5V8z"/>
  </svg>
);

const SuccessIcon = () => (
  <svg width="12" height="12" viewBox="0 0 12 12" fill="currentColor">
    <path d="M6 0a6 6 0 100 12A6 6 0 006 0zm2.5 4.5L5 8 3.5 6.5l1-1L5 6l2.5-2.5 1 1z"/>
  </svg>
);

export const BasicInputs: Story = {
  render: () => (
    <div css={css`max-width: 400px;`}>
      <div css={formStyles.formGroup}>
        <label css={formStyles.label} htmlFor="email">
          Email Address
        </label>
        <input
          type="email"
          id="email"
          css={formStyles.input}
          placeholder="Enter your email"
        />
        <div css={formStyles.helpText}>
          We'll never share your email with anyone else.
        </div>
      </div>

      <div css={formStyles.formGroup}>
        <label css={formStyles.label} htmlFor="password">
          Password
        </label>
        <input
          type="password"
          id="password"
          css={formStyles.input}
          placeholder="Enter your password"
        />
      </div>

      <div css={formStyles.formGroup}>
        <label css={formStyles.label} htmlFor="message">
          Message
        </label>
        <textarea
          id="message"
          css={[formStyles.input, css`min-height: 100px; resize: vertical;`]}
          placeholder="Enter your message"
        />
      </div>

      <div css={formStyles.formGroup}>
        <label css={formStyles.label} htmlFor="country">
          Country
        </label>
        <select id="country" css={formStyles.select}>
          <option value="">Select a country</option>
          <option value="us">United States</option>
          <option value="uk">United Kingdom</option>
          <option value="ca">Canada</option>
          <option value="au">Australia</option>
        </select>
      </div>
    </div>
  ),
  parameters: {
    docs: {
      description: {
        story: 'Basic form inputs including text input, password, textarea, and select dropdown.',
      },
    },
  },
};

export const ValidationStates: Story = {
  render: () => (
    <div css={css`max-width: 400px;`}>
      <div css={formStyles.formGroup}>
        <label css={formStyles.label} htmlFor="valid-email">
          Valid Email
        </label>
        <input
          type="email"
          id="valid-email"
          css={[formStyles.input, formStyles.inputSuccess]}
          value="user@example.com"
          readOnly
        />
        <div css={formStyles.successText}>
          <SuccessIcon />
          Email format is valid
        </div>
      </div>

      <div css={formStyles.formGroup}>
        <label css={formStyles.label} htmlFor="invalid-email">
          Invalid Email
        </label>
        <input
          type="email"
          id="invalid-email"
          css={[formStyles.input, formStyles.inputError]}
          value="invalid-email"
          readOnly
        />
        <div css={formStyles.errorText}>
          <ErrorIcon />
          Please enter a valid email address
        </div>
      </div>

      <div css={formStyles.formGroup}>
        <label css={formStyles.label} htmlFor="disabled-input">
          Disabled Input
        </label>
        <input
          type="text"
          id="disabled-input"
          css={formStyles.input}
          value="This field is disabled"
          disabled
        />
        <div css={formStyles.helpText}>
          This field cannot be edited
        </div>
      </div>
    </div>
  ),
  parameters: {
    docs: {
      description: {
        story: 'Form validation states showing success, error, and disabled states with appropriate messaging.',
      },
    },
  },
};

export const CheckboxesAndRadios: Story = {
  render: () => {
    const [selectedRadio, setSelectedRadio] = useState('option1');
    const [checkboxes, setCheckboxes] = useState({
      option1: true,
      option2: false,
      option3: true,
    });

    return (
      <div css={css`max-width: 400px;`}>
        <div css={formStyles.formGroup}>
          <label css={formStyles.label}>Checkboxes</label>
          <div css={css`display: flex; flex-direction: column; gap: 8px;`}>
            {Object.entries(checkboxes).map(([key, checked]) => (
              <label key={key} css={css`display: flex; align-items: center; cursor: pointer;`}>
                <input
                  type="checkbox"
                  css={formStyles.checkbox}
                  checked={checked}
                  onChange={(e) => setCheckboxes(prev => ({ ...prev, [key]: e.target.checked }))}
                />
                <span css={css`font-size: 14px; color: #333;`}>
                  Checkbox Option {key.slice(-1)}
                </span>
              </label>
            ))}
          </div>
        </div>

        <div css={formStyles.formGroup}>
          <label css={formStyles.label}>Radio Buttons</label>
          <div css={css`display: flex; flex-direction: column; gap: 8px;`}>
            {['option1', 'option2', 'option3'].map((option) => (
              <label key={option} css={css`display: flex; align-items: center; cursor: pointer;`}>
                <input
                  type="radio"
                  name="radio-group"
                  css={formStyles.radio}
                  checked={selectedRadio === option}
                  onChange={() => setSelectedRadio(option)}
                />
                <span css={css`font-size: 14px; color: #333;`}>
                  Radio Option {option.slice(-1)}
                </span>
              </label>
            ))}
          </div>
        </div>

        <div css={formStyles.formGroup}>
          <label css={css`display: flex; align-items: center; cursor: pointer;`}>
            <input
              type="checkbox"
              css={formStyles.checkbox}
            />
            <span css={css`font-size: 14px; color: #333;`}>
              I agree to the terms and conditions
            </span>
          </label>
        </div>
      </div>
    );
  },
  parameters: {
    docs: {
      description: {
        story: 'Custom styled checkboxes and radio buttons with proper focus states and accessibility.',
      },
    },
  },
};

export const FormLayouts: Story = {
  render: () => (
    <div css={css`display: flex; flex-direction: column; gap: 32px;`}>
      {/* Inline Form */}
      <div>
        <h3 css={css`margin: 0 0 16px 0; font-size: 16px; font-weight: 600;`}>
          Inline Form
        </h3>
        <div css={css`
          display: flex;
          gap: 12px;
          align-items: end;
          flex-wrap: wrap;
        `}>
          <div css={css`flex: 1; min-width: 200px;`}>
            <label css={formStyles.label} htmlFor="inline-email">
              Email
            </label>
            <input
              type="email"
              id="inline-email"
              css={formStyles.input}
              placeholder="Enter email"
            />
          </div>
          <div css={css`flex: 1; min-width: 150px;`}>
            <label css={formStyles.label} htmlFor="inline-name">
              Name
            </label>
            <input
              type="text"
              id="inline-name"
              css={formStyles.input}
              placeholder="Enter name"
            />
          </div>
          <button css={css`
            padding: 10px 20px;
            background: #4979e8;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            cursor: pointer;
            height: 40px;
          `}>
            Subscribe
          </button>
        </div>
      </div>

      {/* Two Column Form */}
      <div>
        <h3 css={css`margin: 0 0 16px 0; font-size: 16px; font-weight: 600;`}>
          Two Column Form
        </h3>
        <div css={css`
          display: grid;
          grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
          gap: 20px;
        `}>
          <div css={formStyles.formGroup}>
            <label css={formStyles.label} htmlFor="first-name">
              First Name
            </label>
            <input
              type="text"
              id="first-name"
              css={formStyles.input}
              placeholder="Enter first name"
            />
          </div>
          <div css={formStyles.formGroup}>
            <label css={formStyles.label} htmlFor="last-name">
              Last Name
            </label>
            <input
              type="text"
              id="last-name"
              css={formStyles.input}
              placeholder="Enter last name"
            />
          </div>
          <div css={formStyles.formGroup}>
            <label css={formStyles.label} htmlFor="phone">
              Phone Number
            </label>
            <input
              type="tel"
              id="phone"
              css={formStyles.input}
              placeholder="Enter phone number"
            />
          </div>
          <div css={formStyles.formGroup}>
            <label css={formStyles.label} htmlFor="company">
              Company
            </label>
            <input
              type="text"
              id="company"
              css={formStyles.input}
              placeholder="Enter company name"
            />
          </div>
        </div>
      </div>

      {/* Form with Sections */}
      <div>
        <h3 css={css`margin: 0 0 16px 0; font-size: 16px; font-weight: 600;`}>
          Sectioned Form
        </h3>
        <div css={css`
          background: white;
          border: 1px solid #e0e0e0;
          border-radius: 8px;
          padding: 24px;
        `}>
          <div css={css`margin-bottom: 24px;`}>
            <h4 css={css`
              margin: 0 0 16px 0;
              font-size: 14px;
              font-weight: 600;
              color: #333;
              padding-bottom: 8px;
              border-bottom: 1px solid #e0e0e0;
            `}>
              Personal Information
            </h4>
            <div css={css`display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;`}>
              <div css={formStyles.formGroup}>
                <label css={formStyles.label} htmlFor="section-email">
                  Email Address
                </label>
                <input
                  type="email"
                  id="section-email"
                  css={formStyles.input}
                  placeholder="Enter email"
                />
              </div>
              <div css={formStyles.formGroup}>
                <label css={formStyles.label} htmlFor="section-phone">
                  Phone Number
                </label>
                <input
                  type="tel"
                  id="section-phone"
                  css={formStyles.input}
                  placeholder="Enter phone"
                />
              </div>
            </div>
          </div>

          <div>
            <h4 css={css`
              margin: 0 0 16px 0;
              font-size: 14px;
              font-weight: 600;
              color: #333;
              padding-bottom: 8px;
              border-bottom: 1px solid #e0e0e0;
            `}>
              Address Information
            </h4>
            <div css={formStyles.formGroup}>
              <label css={formStyles.label} htmlFor="address">
                Street Address
              </label>
              <input
                type="text"
                id="address"
                css={formStyles.input}
                placeholder="Enter street address"
              />
            </div>
            <div css={css`display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 16px;`}>
              <div css={formStyles.formGroup}>
                <label css={formStyles.label} htmlFor="city">
                  City
                </label>
                <input
                  type="text"
                  id="city"
                  css={formStyles.input}
                  placeholder="Enter city"
                />
              </div>
              <div css={formStyles.formGroup}>
                <label css={formStyles.label} htmlFor="state">
                  State
                </label>
                <select id="state" css={formStyles.select}>
                  <option value="">Select state</option>
                  <option value="ca">California</option>
                  <option value="ny">New York</option>
                  <option value="tx">Texas</option>
                </select>
              </div>
              <div css={formStyles.formGroup}>
                <label css={formStyles.label} htmlFor="zip">
                  ZIP Code
                </label>
                <input
                  type="text"
                  id="zip"
                  css={formStyles.input}
                  placeholder="12345"
                />
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  ),
  parameters: {
    docs: {
      description: {
        story: 'Different form layouts including inline forms, two-column grids, and sectioned forms.',
      },
    },
  },
};

export const RTLSupport: Story = {
  render: () => (
    <div css={css`display: flex; flex-direction: column; gap: 24px;`}>
      <div>
        <h4 css={css`margin: 0 0 12px 0; font-size: 14px; font-weight: 600;`}>
          LTR (Left-to-Right)
        </h4>
        <div css={css`direction: ltr; max-width: 400px;`}>
          <div css={formStyles.formGroup}>
            <label css={formStyles.label} htmlFor="ltr-email">
              Email Address
            </label>
            <input
              type="email"
              id="ltr-email"
              css={formStyles.input}
              placeholder="Enter your email"
            />
          </div>
          <div css={formStyles.formGroup}>
            <label css={css`display: flex; align-items: center; cursor: pointer;`}>
              <input type="checkbox" css={formStyles.checkbox} />
              <span css={css`font-size: 14px; color: #333;`}>
                I agree to the terms and conditions
              </span>
            </label>
          </div>
        </div>
      </div>
      
      <div>
        <h4 css={css`margin: 0 0 12px 0; font-size: 14px; font-weight: 600;`}>
          RTL (Right-to-Left)
        </h4>
        <div css={css`direction: rtl; max-width: 400px;`}>
          <div css={formStyles.formGroup}>
            <label css={[formStyles.label, css`text-align: right;`]} htmlFor="rtl-email">
              عنوان البريد الإلكتروني
            </label>
            <input
              type="email"
              id="rtl-email"
              css={formStyles.input}
              placeholder="أدخل بريدك الإلكتروني"
            />
          </div>
          <div css={formStyles.formGroup}>
            <label css={css`display: flex; align-items: center; cursor: pointer; justify-content: flex-end;`}>
              <span css={css`font-size: 14px; color: #333; margin-left: 8px;`}>
                أوافق على الشروط والأحكام
              </span>
              <input type="checkbox" css={[formStyles.checkbox, css`margin-right: 0; margin-left: 8px;`]} />
            </label>
          </div>
        </div>
      </div>
    </div>
  ),
  parameters: {
    docs: {
      description: {
        story: 'Form elements automatically adapt to RTL layouts with proper text alignment and spacing.',
      },
    },
  },
};