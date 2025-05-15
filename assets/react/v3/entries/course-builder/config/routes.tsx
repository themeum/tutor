import Layout from '@CourseBuilderComponents/layouts/Layout';
import RouteSuspense from '@TutorShared/components/RouteSuspense';
import React from 'react';
import { Navigate, type RouteObject } from 'react-router-dom';
import { CourseBuilderRouteConfigs } from './route-configs';
const CourseBasicPage = React.lazy(() => import('@CourseBuilderPages/CourseBasic'));
const CourseCurriculumPage = React.lazy(() => import('@CourseBuilderPages/Curriculum'));
const CourseAdditionalPage = React.lazy(() => import('@CourseBuilderPages/Additional'));
const IconListPage = React.lazy(() => import('@CourseBuilderPages/IconList'));

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
