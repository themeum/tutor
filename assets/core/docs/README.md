# Tutor Design System Documentation

A comprehensive, interactive documentation site for the Tutor Design System with live examples, design token references, and developer guides.

## 📁 Documentation Structure

```
docs/
├── index.html              # Main documentation hub with navigation
├── components.html         # Interactive component showcase
├── design-tokens.html      # Complete design token reference
├── assets/
│   ├── docs.css           # Documentation-specific styles
│   └── docs.js            # Documentation functionality
└── README.md              # This file
```

## 🚀 Features

### Interactive Documentation Hub

- **Responsive Navigation**: Collapsible sidebar with search functionality
- **Theme Switching**: Live theme toggle between light and dark modes
- **RTL Support**: Real-time RTL/LTR direction switching
- **Font Scaling**: Accessibility controls for font size adjustment
- **Mobile Optimized**: Fully responsive design for all devices

### Component Showcase

- **Live Examples**: Interactive demonstrations of all components
- **Code Snippets**: Copy-to-clipboard code examples
- **Alpine.js Integration**: Working examples of Alpine.js components
- **State Variations**: Different component states and variants
- **Real-time Theming**: Components update with theme changes

### Design Token Reference

- **Complete Token System**: All design tokens with visual examples
- **Color Scales**: Interactive color palette with hex values
- **Typography System**: Font sizes, weights, and line heights
- **Spacing Scale**: Visual spacing demonstrations
- **Semantic Mappings**: CSS custom properties and their values
- **Copy Functionality**: One-click copying of token values

## 🎨 Design System Coverage

### Components Documented

- **Buttons**: All variants, sizes, and states
- **Cards**: Basic, elevated, and interactive cards
- **Forms**: Input fields, textareas, validation states
- **Alpine.js Components**: Dropdown, modal, tabs, toast, accordion
- **Typography**: Complete heading and paragraph system
- **Utility Classes**: Spacing, colors, layout utilities

### Design Tokens Covered

- **Color System**: Brand, gray, success, warning, error scales
- **Semantic Colors**: Text, surface, action, border mappings
- **Typography**: Font sizes, line heights, weights
- **Spacing**: 22-step spacing scale (0-21)
- **Border Radius**: Complete radius scale
- **Breakpoints**: Responsive design breakpoints

## 🛠 Technical Implementation

### Architecture

- **Alpine.js**: Reactive components and state management
- **CSS Custom Properties**: Theme-aware styling
- **SASS Variables**: Static design tokens
- **Responsive Design**: Mobile-first approach
- **Accessibility**: WCAG compliant with proper focus management

### Key Features

- **Search Functionality**: Debounced search across all documentation
- **Code Copying**: Clipboard API with fallback support
- **Theme Persistence**: LocalStorage for user preferences
- **Performance Optimized**: Lazy loading and efficient rendering
- **Cross-browser Compatible**: Modern browser support

## 📖 Usage Guide

### Getting Started

1. Open `index.html` in a web browser
2. Navigate through sections using the sidebar
3. Use the header controls to test different themes and directions
4. Copy code examples directly from the documentation

### Navigation

- **Sidebar**: Click any section to navigate
- **Search**: Type to filter navigation items
- **Mobile**: Use hamburger menu on mobile devices
- **Breadcrumbs**: Current section shown in header

### Interactive Features

- **Theme Toggle**: Switch between light and dark themes
- **RTL Toggle**: Test right-to-left language support
- **Font Scaling**: Adjust font size for accessibility testing
- **Code Copying**: Click "Copy" buttons to copy code snippets

## 🎯 Developer Benefits

### For Component Development

- **Visual Reference**: See all components in one place
- **Code Examples**: Ready-to-use HTML snippets
- **State Testing**: Test different component states
- **Theme Compatibility**: Verify components work in all themes

### For Design Token Usage

- **Token Discovery**: Find the right token for any use case
- **Visual Context**: See how tokens look in practice
- **Copy Values**: Quickly copy token names or values
- **Semantic Understanding**: Learn the token architecture

### For Integration

- **Setup Guide**: Complete installation instructions
- **Best Practices**: Recommended usage patterns
- **Architecture Guide**: Understanding the token system
- **Troubleshooting**: Common issues and solutions

## 🔧 Customization

### Adding New Components

1. Add component examples to `components.html`
2. Include code snippets with copy functionality
3. Update navigation in `docs.js`
4. Add any component-specific styles

### Adding New Tokens

1. Update token data in `design-tokens.html`
2. Add visual examples and descriptions
3. Include SASS variable and CSS custom property mappings
4. Update the token architecture documentation

### Styling Customization

- Modify `assets/docs.css` for visual changes
- Use existing design system tokens for consistency
- Maintain responsive design principles
- Test in both light and dark themes

## 📱 Responsive Behavior

### Mobile (< 768px)

- Collapsible sidebar with overlay
- Stacked navigation controls
- Touch-friendly interactions
- Optimized typography scaling

### Tablet (768px - 1023px)

- Persistent sidebar
- Responsive grid layouts
- Balanced content spacing
- Touch and mouse support

### Desktop (1024px+)

- Full sidebar navigation
- Multi-column layouts
- Hover interactions
- Keyboard navigation support

## ♿ Accessibility Features

### Keyboard Navigation

- Tab navigation through all interactive elements
- Escape key to close modals and dropdowns
- Arrow keys for component navigation
- Enter/Space for activation

### Screen Reader Support

- Semantic HTML structure
- ARIA labels and descriptions
- Focus management
- Proper heading hierarchy

### Visual Accessibility

- High contrast ratios in all themes
- Font scaling support (80% - 120%)
- Clear focus indicators
- Sufficient color contrast

## 🚀 Performance Optimizations

### Loading Performance

- Minimal external dependencies
- Optimized CSS and JavaScript
- Efficient Alpine.js components
- Lazy loading where appropriate

### Runtime Performance

- Debounced search functionality
- Efficient DOM updates
- Minimal re-renders
- Optimized animations

### Memory Management

- Proper event cleanup
- Efficient data structures
- Minimal memory leaks
- Garbage collection friendly

## 🔄 Maintenance

### Regular Updates

- Keep component examples current
- Update design token values
- Maintain code snippet accuracy
- Test across browsers regularly

### Content Management

- Review documentation completeness
- Update screenshots and examples
- Verify all links and references
- Maintain consistent terminology

### Technical Maintenance

- Update dependencies as needed
- Monitor performance metrics
- Fix accessibility issues
- Optimize for new browsers

## 📊 Browser Support

### Fully Supported

- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

### Graceful Degradation

- Older browsers receive basic functionality
- Progressive enhancement approach
- Fallbacks for modern features
- Core content always accessible

## 🤝 Contributing

### Documentation Improvements

1. Identify areas for improvement
2. Create clear, concise content
3. Include visual examples
4. Test across devices and browsers

### Code Examples

1. Ensure examples are complete and working
2. Include proper HTML structure
3. Use design system tokens consistently
4. Provide copy-friendly code snippets

### Design Token Updates

1. Maintain consistency with Figma designs
2. Update both SASS and CSS custom properties
3. Include visual examples
4. Document semantic mappings

## 📄 License

This documentation is part of the Tutor Design System and follows the same licensing terms as the main project.

---

**Built with ❤️ for the Tutor LMS community**

For questions, issues, or contributions, please refer to the main Tutor Design System repository.
