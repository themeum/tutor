# Tutor LMS React Settings

This directory contains the React-based settings page for Tutor LMS, which replaces the traditional PHP template-based settings page.

## Features

- **Modern React Architecture**: Built with React 18, TypeScript, and modern hooks
- **State Management**: Uses React Context API with useReducer for state management
- **API Integration**: Uses TanStack Query (React Query) for efficient data fetching and caching
- **Field Types Support**: Supports all existing Tutor settings field types:
  - Toggle Switch
  - Select Dropdown
  - Number Input
  - Radio Buttons (vertical/horizontal/full)
  - Checkboxes (vertical/horizontal)
  - Text Input (text/email/url/password)
  - Textarea
- **Search Functionality**: Real-time search across all settings with keyboard shortcuts
- **Responsive Design**: Uses existing Tutor CSS classes for consistent styling
- **Error Handling**: Comprehensive error boundaries and loading states

## Architecture

### Components Structure

```
settings/
├── components/
│   ├── App.tsx                 # Main app component with routing
│   ├── SettingsHeader.tsx      # Header with search and save button
│   ├── SettingsSidebar.tsx     # Navigation sidebar
│   ├── SettingsContent.tsx     # Main content area
│   ├── SettingsBlock.tsx       # Settings block container
│   ├── SettingsField.tsx       # Individual field renderer
│   ├── SearchResults.tsx       # Search results dropdown
│   └── fields/                 # Individual field components
│       ├── ToggleSwitch.tsx
│       ├── SelectField.tsx
│       ├── NumberField.tsx
│       ├── RadioField.tsx
│       ├── CheckboxField.tsx
│       ├── TextareaField.tsx
│       └── TextField.tsx
├── contexts/
│   └── SettingsContext.tsx     # Global state management
├── hooks/
│   └── useSettings.ts          # Custom hooks for API calls
├── services/
│   └── settingsApi.ts          # API service layer
├── pages/
│   └── SettingsPage.tsx        # Main page component
└── index.tsx                   # Entry point
```

### State Management

The settings use a centralized state management approach with React Context and useReducer:

```typescript
interface SettingsState {
  sections: Record<string, SettingsSection>;
  currentSection: string;
  values: Record<string, any>;
  isLoading: boolean;
  isSaving: boolean;
  searchQuery: string;
  isDirty: boolean;
}
```

### API Integration

The React app communicates with WordPress through AJAX endpoints:

- `tutor_get_settings_fields` - Get settings structure
- `tutor_get_settings_values` - Get current values
- `tutor_option_save` - Save settings (existing endpoint)
- `tutor_option_search` - Search settings (existing endpoint)
- `reset_settings_data` - Reset settings (existing endpoint)

## Usage

### Accessing React Settings

#### Method 1: Settings 2 Page (Recommended)

The React settings are now available as a dedicated admin page:

- Navigate to **Tutor LMS → Settings 2** in WordPress admin
- No configuration needed - always available
- URL: `admin.php?page=tutor_settings_2`

#### Method 2: Replace Original Settings (Advanced)

You can replace the original settings page with React:

1. **Using the helper function:**

```php
tutor_enable_react_settings();
```

2. **Using the filter directly:**

```php
add_filter( 'tutor_use_react_settings', '__return_true' );
```

3. **Including the demo file:**

```php
include_once TUTOR_PATH . 'demo-react-settings.php';
```

#### Method 3: Demo Notice

Show an admin notice with a link to Settings 2:

```php
include_once TUTOR_PATH . 'demo-settings-2.php';
```

### Adding New Field Types

To add a new field type:

1. Create a new component in `components/fields/`
2. Add the field type to the switch statement in `SettingsField.tsx`
3. Update the `SettingsField` interface in `SettingsContext.tsx` if needed

Example:

```typescript
// components/fields/CustomField.tsx
import React from 'react';
import { SettingsField } from '../../contexts/SettingsContext';

interface CustomFieldProps {
  field: SettingsField;
  value: any;
  onChange: (value: any) => void;
}

const CustomField: React.FC<CustomFieldProps> = ({ field, value, onChange }) => {
  // Your custom field implementation
  return <div>Custom Field</div>;
};

export default CustomField;
```

### Extending the API

To add new API endpoints:

1. Add the endpoint to `services/settingsApi.ts`
2. Create corresponding hooks in `hooks/useSettings.ts`
3. Add the PHP handler to `classes/SettingsAjax.php`

## Development

### Building

The React settings app is built using Rspack. To build:

```bash
npm run build-dev  # Development build
npm run build      # Production build
npm run watch      # Watch mode for development
```

### File Structure

The built files are output to:

- JavaScript: `assets/js/tutor-settings.js`
- CSS: Uses existing Tutor admin styles

### Dependencies

The app uses these external dependencies (externalized in build):

- React & ReactDOM (from WordPress)
- @wordpress/i18n (for translations)

Internal dependencies:

- @tanstack/react-query (for data fetching)
- react-router-dom (for routing)
- axios (for HTTP requests)

## Compatibility

- **WordPress**: 5.3+
- **PHP**: 7.4+
- **Browsers**: Modern browsers with ES6+ support
- **Tutor LMS**: 3.0.0+

## Migration Notes

The React settings page is designed to be a drop-in replacement for the PHP template. All existing:

- Settings fields and structure
- Field types and validation
- Save/load functionality
- Search functionality
- Import/export features

Are preserved and work identically to the PHP version.

## Troubleshooting

### Common Issues

1. **Settings not loading**: Check that the AJAX endpoints are registered and nonces are valid
2. **Build errors**: Ensure all dependencies are installed with `npm install`
3. **Styling issues**: Verify that Tutor admin CSS is loaded
4. **JavaScript errors**: Check browser console for detailed error messages

### Debug Mode

To enable debug mode, add this to your wp-config.php:

```php
define( 'TUTOR_REACT_SETTINGS_DEBUG', true );
```

This will:

- Enable React development mode
- Show additional console logging
- Disable error boundaries for easier debugging
