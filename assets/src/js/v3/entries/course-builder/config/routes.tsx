import Layout from '@CourseBuilderComponents/layouts/Layout';
import RouteSuspense from '@TutorShared/components/RouteSuspense';
import { tutorConfig } from '@TutorShared/config/config';
import { setLocaleData } from '@wordpress/i18n';
import React from 'react';
import { Navigate, type RouteObject } from 'react-router-dom';
import { CourseBuilderRouteConfigs } from './route-configs';

let CourseBasicPage, CourseCurriculumPage, CourseAdditionalPage, IconListPage;

if (process.env.MAKE_POT) {
  // eslint-disable-next-line @typescript-eslint/no-require-imports
  CourseBasicPage = require('@CourseBuilderPages/CourseBasic').default;
  // eslint-disable-next-line @typescript-eslint/no-require-imports
  CourseCurriculumPage = require('@CourseBuilderPages/Curriculum').default;
  // eslint-disable-next-line @typescript-eslint/no-require-imports
  CourseAdditionalPage = require('@CourseBuilderPages/Additional').default;
  // eslint-disable-next-line @typescript-eslint/no-require-imports
  IconListPage = require('@CourseBuilderPages/IconList').default;
} else {
  CourseBasicPage = React.lazy(() => {
    setLocaleData(tutorConfig.course_builder_basic_locales, 'tutor');
    return import(/* webpackChunkName: "tutor-course-builder-basic" */ '@CourseBuilderPages/CourseBasic');
  });
  CourseCurriculumPage = React.lazy(() => {
    setLocaleData(tutorConfig.course_builder_curriculum_locales, 'tutor');
    return import(/* webpackChunkName: "tutor-course-builder-curriculum" */ '@CourseBuilderPages/Curriculum');
  });
  CourseAdditionalPage = React.lazy(() => {
    setLocaleData(tutorConfig.course_builder_additional_locales, 'tutor');
    return import(/* webpackChunkName: "tutor-course-builder-additional" */ '@CourseBuilderPages/Additional');
  });
  IconListPage = React.lazy(
    () => import(/* webpackChunkName: "tutor-course-builder-icon" */ '@CourseBuilderPages/IconList'),
  );
}

const routes: RouteObject[] = [
  {
    path: CourseBuilderRouteConfigs.Home.template,
    element: <Layout />,
    children: [
      {
        index: true,
        element: <Navigate to={CourseBuilderRouteConfigs.CourseBasics.template} replace />,
      },
      {
        path: CourseBuilderRouteConfigs.CourseBasics.template,
        element: <RouteSuspense component={<CourseBasicPage />} />,
      },
      {
        path: CourseBuilderRouteConfigs.CourseCurriculum.template,
        element: <RouteSuspense component={<CourseCurriculumPage />} />,
      },
      {
        path: CourseBuilderRouteConfigs.CourseAdditional.template,
        element: <RouteSuspense component={<CourseAdditionalPage />} />,
      },
      ...(process.env.NODE_ENV === 'development'
        ? [
            {
              path: CourseBuilderRouteConfigs.IconList.template,
              element: <RouteSuspense component={<IconListPage />} />,
            },
          ]
        : []),
    ],
  },
  {
    path: '*',
    element: <Navigate to={CourseBuilderRouteConfigs.Home.template} replace />,
  },
];

export default routes;
