// API Client
// HTTP client setup with authentication and error handling

interface RequestConfig {
  headers?: Record<string, string>;
  timeout?: number;
}

interface ApiResponse<T = any> {
  data: T;
  status: number;
  statusText: string;
  headers: Record<string, string>;
}

class ApiClient {
  private baseURL: string;
  private defaultHeaders: Record<string, string>;
  private timeout: number;

  constructor() {
    // Get base URL from WordPress localized data or fallback
    this.baseURL = (window as any).tutorApiSettings?.root || '/wp-json/tutor/v1';
    this.timeout = 30000; // 30 seconds
    
    this.defaultHeaders = {
      'Content-Type': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
    };

    // Add nonce if available
    const nonce = (window as any).tutorApiSettings?.nonce;
    if (nonce) {
      this.defaultHeaders['X-WP-Nonce'] = nonce;
    }
  }

  private async request<T>(
    method: string,
    endpoint: string,
    data?: any,
    config: RequestConfig = {}
  ): Promise<ApiResponse<T>> {
    const url = `${this.baseURL}${endpoint}`;
    
    const headers = {
      ...this.defaultHeaders,
      ...config.headers,
    };

    const requestConfig: RequestInit = {
      method,
      headers,
      credentials: 'same-origin',
    };

    // Add body for POST, PUT, PATCH requests
    if (data && !['GET', 'DELETE'].includes(method)) {
      if (data instanceof FormData) {
        // Remove Content-Type header for FormData (browser will set it with boundary)
        delete headers['Content-Type'];
        requestConfig.body = data;
      } else {
        requestConfig.body = JSON.stringify(data);
      }
    }

    // Add timeout
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), config.timeout || this.timeout);
    requestConfig.signal = controller.signal;

    try {
      const response = await fetch(url, requestConfig);
      clearTimeout(timeoutId);

      // Parse response
      let responseData;
      const contentType = response.headers.get('content-type');
      
      if (contentType && contentType.includes('application/json')) {
        responseData = await response.json();
      } else {
        responseData = await response.text();
      }

      // Handle HTTP errors
      if (!response.ok) {
        throw new ApiError(
          responseData.message || `HTTP ${response.status}: ${response.statusText}`,
          response.status,
          responseData
        );
      }

      return {
        data: responseData,
        status: response.status,
        statusText: response.statusText,
        headers: this.parseHeaders(response.headers),
      };

    } catch (error) {
      clearTimeout(timeoutId);
      
      if (error instanceof ApiError) {
        throw error;
      }
      
      if (error.name === 'AbortError') {
        throw new ApiError('Request timeout', 408);
      }
      
      throw new ApiError('Network error', 0, error);
    }
  }

  private parseHeaders(headers: Headers): Record<string, string> {
    const parsed: Record<string, string> = {};
    headers.forEach((value, key) => {
      parsed[key] = value;
    });
    return parsed;
  }

  // HTTP Methods
  async get<T>(endpoint: string, config?: RequestConfig): Promise<ApiResponse<T>> {
    return this.request<T>('GET', endpoint, undefined, config);
  }

  async post<T>(endpoint: string, data?: any, config?: RequestConfig): Promise<ApiResponse<T>> {
    return this.request<T>('POST', endpoint, data, config);
  }

  async put<T>(endpoint: string, data?: any, config?: RequestConfig): Promise<ApiResponse<T>> {
    return this.request<T>('PUT', endpoint, data, config);
  }

  async patch<T>(endpoint: string, data?: any, config?: RequestConfig): Promise<ApiResponse<T>> {
    return this.request<T>('PATCH', endpoint, data, config);
  }

  async delete<T>(endpoint: string, config?: RequestConfig): Promise<ApiResponse<T>> {
    return this.request<T>('DELETE', endpoint, undefined, config);
  }

  // Utility methods
  setAuthToken(token: string): void {
    this.defaultHeaders['Authorization'] = `Bearer ${token}`;
  }

  removeAuthToken(): void {
    delete this.defaultHeaders['Authorization'];
  }

  setBaseURL(url: string): void {
    this.baseURL = url;
  }

  setTimeout(timeout: number): void {
    this.timeout = timeout;
  }
}

// Custom error class for API errors
export class ApiError extends Error {
  public status: number;
  public data?: any;

  constructor(message: string, status: number, data?: any) {
    super(message);
    this.name = 'ApiError';
    this.status = status;
    this.data = data;
  }

  get isNetworkError(): boolean {
    return this.status === 0;
  }

  get isTimeoutError(): boolean {
    return this.status === 408;
  }

  get isClientError(): boolean {
    return this.status >= 400 && this.status < 500;
  }

  get isServerError(): boolean {
    return this.status >= 500;
  }

  get isAuthError(): boolean {
    return this.status === 401 || this.status === 403;
  }
}

// Create and export singleton instance
export const apiClient = new ApiClient();

// Export types
export type { ApiResponse, RequestConfig };
