import Button from "@Atoms/Button";
import CanvasHead from "@CourseBuilderComponents/layouts/CanvasHead";
import EmptyState from "@CourseBuilderComponents/curriculum/EmptyState";
import { css } from "@emotion/react";
import { __ } from "@wordpress/i18n";
import { spacing } from "@Config/styles";
import SVGIcon from "@Atoms/SVGIcon";

const Curriculum = () => {
  return (
    <div css={styles.wrapper}>
      <CanvasHead title={__("Curriculum", "tutor")} rightButton={<Button variant='text'>Expand All</Button>} />

      <div css={styles.topicsWrapper}>
        <EmptyState
          imageAltText='gg'
          emptyStateImage='https://s3-alpha-sig.figma.com/img/0c34/4cc3/a96680ac703fe93a2a04958b75d9f8f2?Expires=1709510400&Key-Pair-Id=APKAQ4GOSFWCVNEHN3O4&Signature=nqocug~SIKom1VO2aN9vnsEwTxtVBK10htc1MkSyVXqU7nDplqbHs~3GSGlJHbVYCtpPQUcM3k48ldDRFDybKJx5rgiYaFQeNMNEreRYQ1YrmhiIozN0dRkqEWZEKVxsbT9N9xF0IxpuOnbE1lxwmWbOQn7xIAE6Z67fpdVAB8O7quBRV-59VJJm1znVOpDlRu-ztn~QLNDh~Y5d16BFGbsYawRBYOJOHtiVMyWSFCC9I77cd~gTtXJYKdaNSekfE0IgiZnJSg8QXWyo6NPbhDDSnuz~AcbX~mKDER317kX10xa9J~InN4tTsGU6uVpU1polBjiH7pivQqFH4HAG9A__'
          title='Create the course journey from here!'
          description='when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries'
          action={
            <Button variant='secondary' icon={<SVGIcon name='plus' />}>
              Add Topic
            </Button>
          }
        />
      </div>
    </div>
  );
};

export default Curriculum;

const styles = {
  wrapper: css`
    padding: ${spacing[24]} ${spacing[64]};
  `,
  topicsWrapper: css`
    margin-top: ${spacing[32]};
  `,
};
