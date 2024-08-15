import MagicButton from '@Atoms/MagicButton';
import SVGIcon from '@Atoms/SVGIcon';
import { Breakpoint, borderRadius, colorTokens, spacing, zIndex } from '@Config/styles';
import { typography } from '@Config/typography';
import Show from '@Controls/Show';
import src from '@Images/mock/mock-image-1.png';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useState } from 'react';
import ContentAccordion from './ContentAccordion';
import ContentSkeletonLoader from './ContentSkeletonLoader';

const ContentGeneration = ({ onClose }: { onClose: () => void }) => {
  const [loading, setLoading] = useState(false);
  return (
    <div css={styles.container}>
      <div css={styles.wrapper}>
        <div css={styles.left}>
          <Show
            when={!loading}
            fallback={
              <div css={[styles.leftContentWrapper, css`margin-top: 20px;`]}>
                <ContentSkeletonLoader />
              </div>
            }
          >
            <div css={styles.title}>
              <SVGIcon name="book" width={40} height={40} />
              <h5>Beginner’s Photography: Basic Camera Use and Theory</h5>
            </div>

            <div css={styles.leftContentWrapper}>
              <div css={styles.imageWrapper}>
                <img src={src} alt="course banner" />
              </div>

              <div css={styles.section}>
                <h5>{__('Course Info', 'tutor')}</h5>
                <div css={styles.content}>
                  <div>
                    <h6>About Course</h6>
                    Photography is a diverse and dynamic field, and there are numerous types and genres catering to
                    different subjects, styles, and purposes.Feel free to adapt these quests based on your interests and
                    the equipment you have. They are designed to encourage exploration, creativity.
                  </div>
                  <div>
                    <h6>What will you learn? </h6>
                    Photography is a diverse and dynamic field, and there are numerous types and genres catering to
                    different subjects, styles, and purposes. Feel free to adapt these quests based on your interests
                    and the equipment you have
                  </div>
                </div>
              </div>
              <div css={styles.section}>
                <h5>{__('Course Content', 'tutor')}</h5>
                <div css={styles.content}>
                  <ContentAccordion />
                </div>
              </div>
            </div>
          </Show>
        </div>
        <div css={styles.right}>
          <div css={styles.rightContents}>
            <div css={styles.box({ deactivated: true })}>
              <div>
                <SVGIcon name="magicAiColorize" width={24} height={24} />
              </div>
              <div css={styles.boxContent}>
                <h6>You are all set!</h6>
                <p>Your course outline is ready for you! You have just created these course items.</p>
                <div css={styles.items}>
                  <div css={styles.item}>
                    <SVGIcon name="checkFilledWhite" width={24} height={24} />
                    12 Topics.
                  </div>
                  <div css={styles.item}>
                    <SVGIcon name="checkFilledWhite" width={24} height={24} />
                    124 Lessons in total.
                  </div>
                  <div css={styles.item}>
                    <SVGIcon name="checkFilledWhite" width={24} height={24} />
                    16 Quizzes.
                  </div>
                </div>
              </div>
            </div>
            <div css={styles.box({ deactivated: true })}>
              <SVGIcon name="magicAiColorize" width={24} height={24} />
              <div css={styles.boxContent}>
                <h6>Regenerated!</h6>
                <p>Your course outline is ready for you! You have just created these course items.</p>
                <div css={styles.items}>
                  <div css={styles.item}>
                    <SVGIcon name="checkFilledWhite" width={24} height={24} />
                    12 Topics.
                  </div>
                  <div css={styles.item}>
                    <SVGIcon name="checkFilledWhite" width={24} height={24} />
                    124 Lessons in total.
                  </div>
                  <div css={styles.item}>
                    <SVGIcon name="checkFilledWhite" width={24} height={24} />
                    16 Quizzes.
                  </div>
                </div>
              </div>
            </div>
            <div css={styles.box({ deactivated: false })}>
              <div>
                <SVGIcon name="magicAiColorize" width={24} height={24} />
              </div>
              <div css={styles.boxContent}>
                <h6>A little difference is made!</h6>
                <p>Your course outline is ready for you! You have just created these course items.</p>
                <div css={styles.items}>
                  <div css={styles.item}>
                    <SVGIcon name="checkFilledWhite" width={24} height={24} />
                    12 Topics.
                  </div>
                  <div css={styles.item}>
                    <SVGIcon name="checkFilledWhite" width={24} height={24} />
                    124 Lessons in total.
                  </div>
                  <div css={styles.item}>
                    <SVGIcon name="checkFilledWhite" width={24} height={24} />
                    16 Quizzes.
                  </div>
                </div>
                <div css={styles.boxFooter}>
                  <MagicButton variant="primary_outline">
                    <SVGIcon name="tryAgain" width={24} height={24} />
                    Regenerate course
                  </MagicButton>
                  <MagicButton variant="primary_outline">
                    <SVGIcon name="magicWand" width={24} height={24} />
                    Make a little different
                  </MagicButton>
                </div>
              </div>
            </div>
          </div>

          <div css={styles.rightFooter}>
            <MagicButton variant="primary_outline" onClick={onClose}>
              {__('Cancel', 'tutor')}
            </MagicButton>
            <MagicButton onClick={() => setLoading((previous) => !previous)}>
              {__('Append the course', 'tutor')}
            </MagicButton>
          </div>
        </div>
      </div>
    </div>
  );
};

export default ContentGeneration;

const styles = {
  container: css`
		position: absolute;
		left: 0;
		top: 0;
		width: 100%;
		height: 100%;
		display: flex;
    justify-content: center;
    align-items: end;
	`,
  wrapper: css`
		display: flex;
		gap: ${spacing[28]};
		height: calc(100vh - ${spacing[56]});
		width: 1300px;
		${Breakpoint.smallTablet} {
			width: 90%;
			gap: ${spacing[16]};
		}
	`,
  leftContentWrapper: css`
		display: flex;
		flex-direction: column;
		gap: ${spacing[16]};
		padding-inline: ${spacing[40]};
		margin-top: ${spacing[8]};
	`,
  box: ({ deactivated }: { deactivated: boolean }) => css`
		width: 100%;
		border-radius: ${borderRadius[8]};
		border: 1px solid ${colorTokens.bg.brand};
		padding: ${spacing[16]} ${spacing[12]};
		display: grid;
		grid-template-columns: 24px auto;
		gap: ${spacing[12]};

		svg {
			flex-shrink: 0;
		}

		${
      deactivated &&
      css`
			svg {
				color: ${colorTokens.icon.disable.muted} !important;
			}
		`
    }
	`,
  boxFooter: css`
		display: flex;
		align-items: center;
		gap: ${spacing[16]};
	`,
  rightContents: css`
		display: flex;
		flex-direction: column;
		gap: ${spacing[16]};
		overflow-y: auto;
		height: 100%;
	`,
  rightFooter: css`
		margin-top: auto;
		padding-top: ${spacing[16]};
		display: flex;
		align-items: center;
		justify-content: center;
		gap: ${spacing[12]};
	`,
  boxContent: css`
		display: flex;
		flex-direction: column;
		gap: ${spacing[12]};

		h6 {
			${typography.body('medium')};
			color: ${colorTokens.color.black.main};
		}

		p {
			${typography.caption('medium')};
			color: ${colorTokens.text.title};
		}
	`,
  items: css`
		display: flex;
		flex-direction: column;
		gap: ${spacing[4]};
	`,
  item: css`
		display: flex;
		align-items: center;
		gap: ${spacing[8]};
		${typography.caption()};
		color: ${colorTokens.text.title};

		svg {
			color: ${colorTokens.stroke.success.fill70};
		}
	`,
  section: css`
		display: flex;
		flex-direction: column;
		gap: ${spacing[16]};

		& > h5 {
			${typography.heading6('medium')};
			color: ${colorTokens.text.primary};
			height: 42px;
			border-bottom: 1px solid ${colorTokens.stroke.border};
		}
	`,
  content: css`
		${typography.caption()};
		color: ${colorTokens.text.hints};
		display: flex;
		flex-direction: column;
		gap: ${spacing[16]};
		
		h6 {
			${typography.caption()};
			color: ${colorTokens.text.primary};
		}
	`,
  left: css`
		width: 792px;
		background-color: ${colorTokens.background.white};
		border-radius: ${borderRadius[12]} ${borderRadius[12]} 0 0;
		display: flex;
		flex-direction: column;
		gap: ${spacing[16]};
		overflow-y: auto;

		${Breakpoint.smallTablet} {
			width: 80%;
		}
	`,
  right: css`
		width: 480px;
		height: 100%;
		background-color: ${colorTokens.background.white};
		border-radius: ${borderRadius[12]} ${borderRadius[12]} 0 0;
		padding: ${spacing[24]} ${spacing[20]};
    display: flex;
    flex-direction: column;
    justify-content: space-between;

		${Breakpoint.smallTablet} {
			width: 20%;
		}
	`,
  title: css`
		display: flex;
		align-items: center;
		gap: ${spacing[8]};
		color: ${colorTokens.icon.default};
		position: sticky;
		top: 0;
		z-index: ${zIndex.header};
		height: 40px;	
		padding: ${spacing[32]} ${spacing[40]} ${spacing[16]} ${spacing[40]};	
		background-color: ${colorTokens.background.white};

		& > h5 {
			${typography.heading5('medium')};
			color: ${colorTokens.text.ai.purple};
		}
	`,
  imageWrapper: css`
		width: 100%;
		height: 390px;
		border-radius: ${borderRadius[10]};
		overflow: hidden;
		position: relative;
		flex-shrink: 0;

		img {
			position: absolute;
			top: 0;
			left: 0;
			width: 100%;
			height: 100%;
			object-fit: cover;
		}
	`,
};
