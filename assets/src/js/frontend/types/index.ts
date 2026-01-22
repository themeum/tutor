export interface AjaxResponse<T = unknown> {
  status_code: number;
  success: boolean;
  message: string;
  data?: T;
}
