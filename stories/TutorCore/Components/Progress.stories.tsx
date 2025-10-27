import { css } from '@emotion/react';
import type { Meta, StoryObj } from '@storybook/react';
import { useEffect, useState } from 'react';

const meta: Meta = {
  title: 'TutorCore/Components/Progress',
  parameters: {
    docs: {
      description: {
        component: `
# Progress Components

TutorCore provides various progress indicators including progress bars, circular progress, and step indicators. All components support animations and different states.

## Features

- **Progress Bars**: Linear progress with labels and animations
- **Circular Progress**: Circular indicators for compact spaces
- **Step Indicators**: Multi-step process visualization
- **Animated**: Smooth transitions and loading states
- **Customizable**: Different sizes, colors, and styles

## CSS Classes

\`\`\`css
/* Progress bars */
.tutor-progress
.tutor-progress__bar
.tutor-progress__fill
.tutor-progress__label

/* Circular progress */
.tutor-progress-circle
.tutor-progress-circle__svg
.tutor-progress-circle__track
.tutor-progress-circle__fill

/* Step indicators */
.tutor-steps
.tutor-step
.tutor-step__icon
.tutor-step__content
.tutor-step--completed
.tutor-step--active
\`\`\`
        `,
      },
    },
  },
};

export default meta;
type Story = StoryObj;
const progressStyles = {
  // Linear Progress
  progressContainer: css`
    width: 100%;
    margin-bottom: 16px;
  `,
  
  progressLabel: css`
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
    font-size: 14px;
    font-weight: 500;
  `,
  
  progressBar: css`
    width: 100%;
    height: 8px;
    background: #f0f1f1;
    border-radius: 4px;
    overflow: hidden;
  `,
  
  progressFill: css`
    height: 100%;
    background: linear-gradient(90deg, #4979e8, #3e64de);
    border-radius: 4px;
    transition: width 0.3s ease;
  `,
  
  // Circular Progress
  circularProgress: css`
    position: relative;
    display: inline-flex;
    align-items: center;
    justify-content: center;
  `,
  
  circularLabel: css`
    position: absolute;
    font-size: 12px;
    font-weight: 600;
    color: #333741;
  `,
  
  // Steps
  stepsContainer: css`
    display: flex;
    align-items: center;
    width: 100%;
  `,
  
  step: css`
    display: flex;
    flex-direction: column;
    align-items: center;
    flex: 1;
    position: relative;
    
    &:not(:last-child)::after {
      content: '';
      position: absolute;
      top: 16px;
      left: 50%;
      right: -50%;
      height: 2px;
      background: #e0e0e0;
      z-index: 0;
    }
    
    &.completed:not(:last-child)::after {
      background: #4979e8;
    }
  `,
  
  stepIcon: css`
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #f0f1f1;
    border: 2px solid #e0e0e0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    font-weight: 600;
    color: #94969c;
    position: relative;
    z-index: 1;
    transition: all 0.2s ease;
    
    &.completed {
      background: #4979e8;
      border-color: #4979e8;
      color: white;
    }
    
    &.active {
      background: white;
      border-color: #4979e8;
      color: #4979e8;
      box-shadow: 0 0 0 4px rgba(73, 121, 232, 0.1);
    }
  `,
  
  stepContent: css`
    margin-top: 8px;
    text-align: center;
  `,
  
  stepTitle: css`
    font-size: 12px;
    font-weight: 500;
    color: #333741;
    margin-bottom: 2px;
  `,
  
  stepDescription: css`
    font-size: 11px;
    color: #94969c;
  `,
};

const ProgressBar = ({ 
  value, 
  max = 100, 
  label, 
  showPercentage = true,
  color = '#4979e8',
  size = 'medium' 
}: {
  value: number;
  max?: number;
  label?: string;
  showPercentage?: boolean;
  color?: string;
  size?: 'small' | 'medium' | 'large';
}) => {
  const percentage = Math.round((value / max) * 100);
  
  const sizeStyles = {
    small: css`height: 4px;`,
    medium: css`height: 8px;`,
    large: css`height: 12px;`,
  };

  return (
    <div css={progressStyles.progressContainer}>
      {(label || showPercentage) && (
        <div css={progressStyles.progressLabel}>
          <span>{label}</span>
          {showPercentage && <span>{percentage}%</span>}
        </div>
      )}
      <div css={[progressStyles.progressBar, sizeStyles[size]]}>
        <div 
          css={[
            progressStyles.progressFill,
            css`
              width: ${percentage}%;
              background: ${color};
            `
          ]}
        />
      </div>
    </div>
  );
};

const CircularProgress = ({ 
  value, 
  max = 100, 
  size = 64,
  strokeWidth = 4,
  color = '#4979e8',
  showLabel = true 
}: {
  value: number;
  max?: number;
  size?: number;
  strokeWidth?: number;
  color?: string;
  showLabel?: boolean;
}) => {
  const percentage = (value / max) * 100;
  const radius = (size - strokeWidth) / 2;
  const circumference = radius * 2 * Math.PI;
  const strokeDasharray = circumference;
  const strokeDashoffset = circumference - (percentage / 100) * circumference;

  return (
    <div css={progressStyles.circularProgress} style={{ width: size, height: size }}>
      <svg width={size} height={size}>
        <circle
          cx={size / 2}
          cy={size / 2}
          r={radius}
          fill="none"
          stroke="#f0f1f1"
          strokeWidth={strokeWidth}
        />
        <circle
          cx={size / 2}
          cy={size / 2}
          r={radius}
          fill="none"
          stroke={color}
          strokeWidth={strokeWidth}
          strokeDasharray={strokeDasharray}
          strokeDashoffset={strokeDashoffset}
          strokeLinecap="round"
          transform={`rotate(-90 ${size / 2} ${size / 2})`}
          css={css`
            transition: stroke-dashoffset 0.3s ease;
          `}
        />
      </svg>
      {showLabel && (
        <div css={progressStyles.circularLabel}>
          {Math.round(percentage)}%
        </div>
      )}
    </div>
  );
};

const StepIndicator = ({ 
  steps, 
  currentStep = 0 
}: {
  steps: Array<{ title: string; description?: string }>;
  currentStep?: number;
}) => (
  <div css={progressStyles.stepsContainer}>
    {steps.map((step, index) => (
      <div 
        key={index} 
        css={progressStyles.step}
        className={index < currentStep ? 'completed' : ''}
      >
        <div 
          css={progressStyles.stepIcon}
          className={
            index < currentStep ? 'completed' : 
            index === currentStep ? 'active' : ''
          }
        >
          {index < currentStep ? (
            <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
              <path d="M13 4L6 11 3 8l1.5-1.5L6 8l5.5-5.5L13 4z"/>
            </svg>
          ) : (
            index + 1
          )}
        </div>
        <div css={progressStyles.stepContent}>
          <div css={progressStyles.stepTitle}>{step.title}</div>
          {step.description && (
            <div css={progressStyles.stepDescription}>{step.description}</div>
          )}
        </div>
      </div>
    ))}
  </div>
);

export const LinearProgress: Story = {
  render: () => (
    <div css={css`max-width: 500px;`}>
      <h3 css={css`margin: 0 0 20px 0; font-size: 16px; font-weight: 600;`}>
        Linear Progress Bars
      </h3>
      
      <ProgressBar value={75} label="Project Completion" />
      <ProgressBar value={45} label="Upload Progress" color="#66c61c" />
      <ProgressBar value={90} label="Loading..." color="#f79009" />
      <ProgressBar value={25} label="Storage Used" color="#f04438" />
      
      <h4 css={css`margin: 24px 0 12px 0; font-size: 14px; font-weight: 600;`}>
        Different Sizes
      </h4>
      <ProgressBar value={60} label="Small" size="small" />
      <ProgressBar value={60} label="Medium" size="medium" />
      <ProgressBar value={60} label="Large" size="large" />
      
      <h4 css={css`margin: 24px 0 12px 0; font-size: 14px; font-weight: 600;`}>
        Without Labels
      </h4>
      <ProgressBar value={80} showPercentage={false} />
      <ProgressBar value={35} showPercentage={false} color="#9747ff" />
    </div>
  ),
  parameters: {
    docs: {
      description: {
        story: 'Linear progress bars with different colors, sizes, and label configurations.',
      },
    },
  },
};

export const CircularProgressIndicators: Story = {
  render: () => (
    <div>
      <h3 css={css`margin: 0 0 20px 0; font-size: 16px; font-weight: 600;`}>
        Circular Progress Indicators
      </h3>
      
      <div css={css`display: flex; gap: 24px; align-items: center; flex-wrap: wrap; margin-bottom: 24px;`}>
        <div css={css`text-align: center;`}>
          <CircularProgress value={75} />
          <div css={css`margin-top: 8px; font-size: 12px; color: #666;`}>
            Project Progress
          </div>
        </div>
        
        <div css={css`text-align: center;`}>
          <CircularProgress value={45} color="#66c61c" />
          <div css={css`margin-top: 8px; font-size: 12px; color: #666;`}>
            Success Rate
          </div>
        </div>
        
        <div css={css`text-align: center;`}>
          <CircularProgress value={90} color="#f79009" />
          <div css={css`margin-top: 8px; font-size: 12px; color: #666;`}>
            CPU Usage
          </div>
        </div>
        
        <div css={css`text-align: center;`}>
          <CircularProgress value={25} color="#f04438" />
          <div css={css`margin-top: 8px; font-size: 12px; color: #666;`}>
            Error Rate
          </div>
        </div>
      </div>
      
      <h4 css={css`margin: 0 0 12px 0; font-size: 14px; font-weight: 600;`}>
        Different Sizes
      </h4>
      <div css={css`display: flex; gap: 16px; align-items: center; flex-wrap: wrap;`}>
        <CircularProgress value={60} size={48} strokeWidth={3} />
        <CircularProgress value={60} size={64} strokeWidth={4} />
        <CircularProgress value={60} size={80} strokeWidth={5} />
        <CircularProgress value={60} size={96} strokeWidth={6} />
      </div>
    </div>
  ),
  parameters: {
    docs: {
      description: {
        story: 'Circular progress indicators with different colors, sizes, and stroke widths.',
      },
    },
  },
};

export const StepIndicators: Story = {
  render: () => (
    <div css={css`max-width: 600px;`}>
      <h3 css={css`margin: 0 0 20px 0; font-size: 16px; font-weight: 600;`}>
        Step Indicators
      </h3>
      
      <div css={css`margin-bottom: 32px;`}>
        <h4 css={css`margin: 0 0 16px 0; font-size: 14px; font-weight: 600;`}>
          Checkout Process (Step 2 of 4)
        </h4>
        <StepIndicator 
          currentStep={2}
          steps={[
            { title: 'Cart', description: 'Review items' },
            { title: 'Shipping', description: 'Address info' },
            { title: 'Payment', description: 'Payment method' },
            { title: 'Confirmation', description: 'Order review' }
          ]}
        />
      </div>
      
      <div css={css`margin-bottom: 32px;`}>
        <h4 css={css`margin: 0 0 16px 0; font-size: 14px; font-weight: 600;`}>
          Account Setup (Step 3 of 5)
        </h4>
        <StepIndicator 
          currentStep={3}
          steps={[
            { title: 'Profile' },
            { title: 'Verification' },
            { title: 'Preferences' },
            { title: 'Security' },
            { title: 'Complete' }
          ]}
        />
      </div>
      
      <div>
        <h4 css={css`margin: 0 0 16px 0; font-size: 14px; font-weight: 600;`}>
          Project Phases (Completed)
        </h4>
        <StepIndicator 
          currentStep={4}
          steps={[
            { title: 'Planning', description: 'Requirements' },
            { title: 'Design', description: 'UI/UX Design' },
            { title: 'Development', description: 'Implementation' },
            { title: 'Testing', description: 'QA & Testing' }
          ]}
        />
      </div>
    </div>
  ),
  parameters: {
    docs: {
      description: {
        story: 'Step indicators for multi-step processes with different completion states.',
      },
    },
  },
};

export const AnimatedProgress: Story = {
  render: () => {
    const [progress, setProgress] = useState(0);
    const [isLoading, setIsLoading] = useState(false);

    const startAnimation = () => {
      setIsLoading(true);
      setProgress(0);
      
      const interval = setInterval(() => {
        setProgress(prev => {
          if (prev >= 100) {
            clearInterval(interval);
            setIsLoading(false);
            return 100;
          }
          return prev + 2;
        });
      }, 50);
    };

    useEffect(() => {
      startAnimation();
    }, []);

    return (
      <div css={css`max-width: 500px;`}>
        <h3 css={css`margin: 0 0 20px 0; font-size: 16px; font-weight: 600;`}>
          Animated Progress
        </h3>
        
        <div css={css`margin-bottom: 24px;`}>
          <ProgressBar 
            value={progress} 
            label="File Upload Progress" 
            color={progress === 100 ? '#66c61c' : '#4979e8'}
          />
          
          <div css={css`display: flex; gap: 12px; margin-top: 16px;`}>
            <button 
              onClick={startAnimation}
              disabled={isLoading}
              css={css`
                padding: 8px 16px;
                background: ${isLoading ? '#f0f1f1' : '#4979e8'};
                color: ${isLoading ? '#94969c' : 'white'};
                border: none;
                border-radius: 6px;
                font-size: 14px;
                cursor: ${isLoading ? 'not-allowed' : 'pointer'};
                transition: all 0.2s ease;
              `}
            >
              {isLoading ? 'Uploading...' : 'Start Upload'}
            </button>
            
            <button 
              onClick={() => setProgress(0)}
              css={css`
                padding: 8px 16px;
                background: #f5f5f6;
                color: #333741;
                border: 1px solid #cecfd2;
                border-radius: 6px;
                font-size: 14px;
                cursor: pointer;
                transition: all 0.2s ease;
                
                &:hover {
                  background: #ececed;
                }
              `}
            >
              Reset
            </button>
          </div>
        </div>
        
        <div css={css`display: flex; gap: 24px; align-items: center;`}>
          <div css={css`text-align: center;`}>
            <CircularProgress 
              value={progress} 
              color={progress === 100 ? '#66c61c' : '#4979e8'}
            />
            <div css={css`margin-top: 8px; font-size: 12px; color: #666;`}>
              Circular Progress
            </div>
          </div>
          
          <div css={css`
            padding: 16px;
            background: ${progress === 100 ? '#e3fbcc' : '#f6f8fe'};
            border-radius: 8px;
            border-left: 4px solid ${progress === 100 ? '#66c61c' : '#4979e8'};
            flex: 1;
          `}>
            <div css={css`
              font-size: 14px;
              font-weight: 600;
              color: ${progress === 100 ? '#2b5314' : '#4979e8'};
              margin-bottom: 4px;
            `}>
              {progress === 100 ? 'Upload Complete!' : 'Uploading...'}
            </div>
            <div css={css`
              font-size: 12px;
              color: ${progress === 100 ? '#2b5314' : '#4979e8'};
            `}>
              {progress === 100 ? 'File uploaded successfully' : `${progress}% completed`}
            </div>
          </div>
        </div>
      </div>
    );
  },
  parameters: {
    docs: {
      description: {
        story: 'Animated progress indicators with interactive controls and state changes.',
      },
    },
  },
};