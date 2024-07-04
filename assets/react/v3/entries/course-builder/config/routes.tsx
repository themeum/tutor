import RouteSuspense from '@Components/RouteSuspense';
import Layout from '@CourseBuilderComponents/layouts/Layout';
import React from 'react';
import { Navigate, type RouteObject } from 'react-router-dom';
import { CourseBuilderRouteConfigs } from './route-configs';
const CourseBasicPage = React.lazy(() => import('@CourseBuilderPages/CourseBasic'));
const CourseCurriculumPage = React.lazy(() => import('@CourseBuilderPages/Curriculum'));
const CourseAdditionalPage = React.lazy(() => import('@CourseBuilderPages/Additional'));

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
    ],
  },
];

export default routes;
