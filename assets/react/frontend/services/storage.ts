// Storage Service
// Handles localStorage and sessionStorage with type safety and error handling

interface StorageOptions {
  encrypt?: boolean;
  expiry?: number; // Expiry time in milliseconds
}

interface StorageItem<T = any> {
  value: T;
  timestamp: number;
  expiry?: number;
}

class StorageService {
  private prefix: string;

  constructor(prefix: string = 'tutor_') {
    this.prefix = prefix;
  }

  // LocalStorage methods
  set<T>(key: string, value: T, options: StorageOptions = {}): boolean {
    try {
      const item: StorageItem<T> = {
        value,
        timestamp: Date.now(),
        expiry: options.expiry ? Date.now() + options.expiry : undefined,
      };

      const serialized = JSON.stringify(item);
      const finalValue = options.encrypt ? this.encrypt(serialized) : serialized;
      
      localStorage.setItem(this.getKey(key), finalValue);
      return true;
    } catch (error) {
      console.error('Failed to set localStorage item:', error);
      return false;
    }
  }

  get<T>(key: string, defaultValue?: T): T | null {
    try {
      const stored = localStorage.getItem(this.getKey(key));
      if (!stored) return defaultValue || null;

      // Try to decrypt if it looks encrypted
      const decrypted = this.isEncrypted(stored) ? this.decrypt(stored) : stored;
      if (!decrypted) return defaultValue || null;

      const item: StorageItem<T> = JSON.parse(decrypted);

      // Check expiry
      if (item.expiry && Date.now() > item.expiry) {
        this.remove(key);
        return defaultValue || null;
      }

      return item.value;
    } catch (error) {
      console.error('Failed to get localStorage item:', error);
      return defaultValue || null;
    }
  }

  remove(key: string): boolean {
    try {
      localStorage.removeItem(this.getKey(key));
      return true;
    } catch (error) {
      console.error('Failed to remove localStorage item:', error);
      return false;
    }
  }

  // SessionStorage methods
  setSession<T>(key: string, value: T): boolean {
    try {
      const item: StorageItem<T> = {
        value,
        timestamp: Date.now(),
      };

      sessionStorage.setItem(this.getKey(key), JSON.stringify(item));
      return true;
    } catch (error) {
      console.error('Failed to set sessionStorage item:', error);
      return false;
    }
  }

  getSession<T>(key: string, defaultValue?: T): T | null {
    try {
      const stored = sessionStorage.getItem(this.getKey(key));
      if (!stored) return defaultValue || null;

      const item: StorageItem<T> = JSON.parse(stored);
      return item.value;
    } catch (error) {
      console.error('Failed to get sessionStorage item:', error);
      return defaultValue || null;
    }
  }

  removeSession(key: string): boolean {
    try {
      sessionStorage.removeItem(this.getKey(key));
      return true;
    } catch (error) {
      console.error('Failed to remove sessionStorage item:', error);
      return false;
    }
  }

  // Utility methods
  exists(key: string): boolean {
    return localStorage.getItem(this.getKey(key)) !== null;
  }

  existsSession(key: string): boolean {
    return sessionStorage.getItem(this.getKey(key)) !== null;
  }

  clear(): void {
    try {
      const keys = Object.keys(localStorage);
      keys.forEach(key => {
        if (key.startsWith(this.prefix)) {
          localStorage.removeItem(key);
        }
      });
    } catch (error) {
      console.error('Failed to clear localStorage:', error);
    }
  }

  clearSession(): void {
    try {
      const keys = Object.keys(sessionStorage);
      keys.forEach(key => {
        if (key.startsWith(this.prefix)) {
          sessionStorage.removeItem(key);
        }
      });
    } catch (error) {
      console.error('Failed to clear sessionStorage:', error);
    }
  }

  // Get all keys with the prefix
  getAllKeys(): string[] {
    try {
      return Object.keys(localStorage)
        .filter(key => key.startsWith(this.prefix))
        .map(key => key.replace(this.prefix, ''));
    } catch (error) {
      console.error('Failed to get all keys:', error);
      return [];
    }
  }

  // Get storage size in bytes
  getStorageSize(): number {
    try {
      let total = 0;
      Object.keys(localStorage).forEach(key => {
        if (key.startsWith(this.prefix)) {
          total += localStorage.getItem(key)?.length || 0;
        }
      });
      return total;
    } catch (error) {
      console.error('Failed to calculate storage size:', error);
      return 0;
    }
  }

  // Check if storage is available
  isAvailable(): boolean {
    try {
      const test = '__storage_test__';
      localStorage.setItem(test, 'test');
      localStorage.removeItem(test);
      return true;
    } catch (error) {
      return false;
    }
  }

  // Batch operations
  setMultiple(items: Record<string, any>, options: StorageOptions = {}): boolean {
    try {
      Object.entries(items).forEach(([key, value]) => {
        this.set(key, value, options);
      });
      return true;
    } catch (error) {
      console.error('Failed to set multiple items:', error);
      return false;
    }
  }

  getMultiple<T>(keys: string[]): Record<string, T | null> {
    const result: Record<string, T | null> = {};
    
    keys.forEach(key => {
      result[key] = this.get<T>(key);
    });
    
    return result;
  }

  removeMultiple(keys: string[]): boolean {
    try {
      keys.forEach(key => this.remove(key));
      return true;
    } catch (error) {
      console.error('Failed to remove multiple items:', error);
      return false;
    }
  }

  // Private helper methods
  private getKey(key: string): string {
    return `${this.prefix}${key}`;
  }

  private encrypt(value: string): string {
    // Simple base64 encoding (not secure, just obfuscation)
    // In production, use a proper encryption library
    try {
      return btoa(value);
    } catch (error) {
      console.error('Failed to encrypt value:', error);
      return value;
    }
  }

  private decrypt(value: string): string | null {
    try {
      return atob(value);
    } catch (error) {
      console.error('Failed to decrypt value:', error);
      return null;
    }
  }

  private isEncrypted(value: string): boolean {
    // Simple check if value is base64 encoded
    try {
      return btoa(atob(value)) === value;
    } catch (error) {
      return false;
    }
  }
}

// Specialized storage instances
export const storage = new StorageService('tutor_');
export const userStorage = new StorageService('tutor_user_');
export const courseStorage = new StorageService('tutor_course_');
export const tempStorage = new StorageService('tutor_temp_');

// Storage utilities
export const StorageUtils = {
  // Clear expired items
  clearExpired(): void {
    const keys = storage.getAllKeys();
    keys.forEach(key => {
      storage.get(key); // This will automatically remove expired items
    });
  },

  // Get storage usage statistics
  getUsageStats() {
    return {
      total: storage.getStorageSize(),
      user: userStorage.getStorageSize(),
      course: courseStorage.getStorageSize(),
      temp: tempStorage.getStorageSize(),
    };
  },

  // Backup storage to JSON
  backup(): string {
    const data: Record<string, any> = {};
    const keys = storage.getAllKeys();
    
    keys.forEach(key => {
      data[key] = storage.get(key);
    });
    
    return JSON.stringify(data);
  },

  // Restore storage from JSON
  restore(jsonData: string): boolean {
    try {
      const data = JSON.parse(jsonData);
      return storage.setMultiple(data);
    } catch (error) {
      console.error('Failed to restore storage:', error);
      return false;
    }
  },
};

// Export types
export type { StorageItem, StorageOptions };
