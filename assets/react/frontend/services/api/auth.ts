// Authentication Service
// Handles user authentication and session management

import { storage } from '../storage';
import { apiClient } from './client';

interface LoginCredentials {
  username: string;
  password: string;
  remember?: boolean;
}

interface User {
  id: number;
  username: string;
  email: string;
  display_name: string;
  avatar_url: string;
  roles: string[];
}

interface AuthResponse {
  user: User;
  token?: string;
  expires_in?: number;
}

class AuthService {
  private currentUser: User | null = null;
  private authToken: string | null = null;

  constructor() {
    this.loadStoredAuth();
  }

  // Login user
  async login(credentials: LoginCredentials): Promise<AuthResponse> {
    try {
      const response = await apiClient.post<AuthResponse>('/auth/login', credentials);
      const { user, token, expires_in } = response.data;

      this.currentUser = user;
      
      if (token) {
        this.authToken = token;
        apiClient.setAuthToken(token);
        
        // Store auth data
        storage.set('auth_token', token);
        if (expires_in) {
          const expiresAt = Date.now() + (expires_in * 1000);
          storage.set('auth_expires_at', expiresAt);
        }
      }

      storage.set('current_user', user);
      
      return response.data;
    } catch (error) {
      console.error('Login failed:', error);
      throw error;
    }
  }

  // Logout user
  async logout(): Promise<void> {
    try {
      // Call logout endpoint if token exists
      if (this.authToken) {
        await apiClient.post('/auth/logout');
      }
    } catch (error) {
      console.error('Logout API call failed:', error);
      // Continue with local logout even if API call fails
    } finally {
      this.clearAuth();
    }
  }

  // Check if user is authenticated
  isAuthenticated(): boolean {
    if (!this.authToken || !this.currentUser) {
      return false;
    }

    // Check token expiration
    const expiresAt = storage.get('auth_expires_at');
    if (expiresAt && Date.now() > expiresAt) {
      this.clearAuth();
      return false;
    }

    return true;
  }

  // Get current user
  getCurrentUser(): User | null {
    return this.currentUser;
  }

  // Refresh authentication token
  async refreshToken(): Promise<string | null> {
    try {
      const response = await apiClient.post<{ token: string; expires_in: number }>('/auth/refresh');
      const { token, expires_in } = response.data;

      this.authToken = token;
      apiClient.setAuthToken(token);
      
      storage.set('auth_token', token);
      const expiresAt = Date.now() + (expires_in * 1000);
      storage.set('auth_expires_at', expiresAt);

      return token;
    } catch (error) {
      console.error('Token refresh failed:', error);
      this.clearAuth();
      return null;
    }
  }

  // Update current user data
  async updateCurrentUser(): Promise<User | null> {
    try {
      const response = await apiClient.get<User>('/auth/me');
      this.currentUser = response.data;
      storage.set('current_user', this.currentUser);
      return this.currentUser;
    } catch (error) {
      console.error('Failed to update current user:', error);
      return null;
    }
  }

  // Check if user has specific role
  hasRole(role: string): boolean {
    return this.currentUser?.roles.includes(role) || false;
  }

  // Check if user has any of the specified roles
  hasAnyRole(roles: string[]): boolean {
    if (!this.currentUser) return false;
    return roles.some(role => this.currentUser!.roles.includes(role));
  }

  // Check if user can perform specific action
  can(capability: string): boolean {
    // This would typically check user capabilities
    // For now, just check if user is authenticated
    return this.isAuthenticated();
  }

  // Load stored authentication data
  private loadStoredAuth(): void {
    const token = storage.get('auth_token');
    const user = storage.get('current_user');
    const expiresAt = storage.get('auth_expires_at');

    if (token && user) {
      // Check if token is expired
      if (expiresAt && Date.now() > expiresAt) {
        this.clearAuth();
        return;
      }

      this.authToken = token;
      this.currentUser = user;
      apiClient.setAuthToken(token);
    }
  }

  // Clear authentication data
  private clearAuth(): void {
    this.currentUser = null;
    this.authToken = null;
    
    apiClient.removeAuthToken();
    
    storage.remove('auth_token');
    storage.remove('current_user');
    storage.remove('auth_expires_at');
  }

  // Handle authentication errors
  handleAuthError(): void {
    this.clearAuth();
    
    // Redirect to login page if not already there
    if (!window.location.pathname.includes('/login')) {
      const returnUrl = encodeURIComponent(window.location.href);
      window.location.href = `/login?return_to=${returnUrl}`;
    }
  }

  // Auto-refresh token before expiration
  startTokenRefreshTimer(): void {
    const expiresAt = storage.get('auth_expires_at');
    if (!expiresAt) return;

    const timeUntilExpiry = expiresAt - Date.now();
    const refreshTime = timeUntilExpiry - (5 * 60 * 1000); // Refresh 5 minutes before expiry

    if (refreshTime > 0) {
      setTimeout(() => {
        this.refreshToken().then(() => {
          // Start timer again for the new token
          this.startTokenRefreshTimer();
        });
      }, refreshTime);
    }
  }
}

// Create and export singleton instance
export const authService = new AuthService();

// Export types
export type { AuthResponse, LoginCredentials, User };
