/* eslint-disable @typescript-eslint/no-unsafe-member-access */
import config from '@Config/config';
import { Joomla } from '@Utils/util';
import axios from 'axios';
import * as querystring from 'querystring';

import { convertToFormData, serializeParams } from './form';

axios.defaults.paramsSerializer = (params: Record<string, string>) => {
  return querystring.stringify(params);
};

export const publicApiInstance = axios.create({
  baseURL: config.API_BASE_URL,
});

export const authApiInstance = axios.create({
  baseURL: config.API_BASE_URL,
});

authApiInstance.interceptors.request.use(
  (config) => {
    config.headers ||= {};

    config.headers['X-CSRF-Token'] = Joomla.getOptions('csrf.token');

    if (config.method && ['post', 'put', 'patch'].includes(config.method.toLocaleLowerCase())) {
      if (!!config.data) {
        config.data = convertToFormData(config.data, config.method);
      }

      if (['put', 'patch'].includes(config.method.toLowerCase())) {
        config.method = 'POST';
      }
    }

    if (config.params) {
      config.params = serializeParams(config.params);
    }

    if (config.method && ['get', 'delete'].includes(config.method.toLowerCase())) {
      config.params = { ...config.params, _method: config.method };
    }

    return config;
  },
  (err) => {
    return Promise.reject(err);
  },
);

authApiInstance.interceptors.response.use((response) => {
  return Promise.resolve<{ data: unknown }>(response).then((res) => res.data);
});
