import path from 'node:path';

const root = path.resolve(__dirname, '../fixtures');
export const fixtures = {
  image: path.join(root, 'course-featured-image.png'),
  attachment: path.join(root, 'course-attachment.pdf'),
};
