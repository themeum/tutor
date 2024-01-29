import { Navigate, RouteObject } from 'react-router-dom';
import { CourseBuilderRouteConfigs } from './route-configs';
import Layout from '@CourseBuilderComponents/layouts/Layout';
import RouteSuspense from '@Components/RouteSuspense';
import React from 'react';
const CourseBasicPage = React.lazy(() => import('@CourseBuilderPages/CourseBasic'));
const CourseCurriculumPage = React.lazy(() => import('@CourseBuilderPages/Curriculum'));
const CourseAdditionalPage = React.lazy(() => import('@CourseBuilderPages/Additional'));
const CourseCertificatePage = React.lazy(() => import('@CourseBuilderPages/Certificate'));

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
      {
        path: CourseBuilderRouteConfigs.CourseCertificate.template,
        element: <RouteSuspense component={<CourseCertificatePage />} />,
      },
    ],
  },
];

export default routes;
