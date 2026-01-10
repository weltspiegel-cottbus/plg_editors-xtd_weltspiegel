# Weltspiegel Editor Button Plugin

A Joomla 5+ editor button plugin that allows content authors to easily insert YouTube video placeholders into articles via a user-friendly modal interface.

## Features

- **Editor Button**: Adds "YouTube Video" button below the editor (works with TinyMCE, CodeMirror, etc.)
- **Modal Interface**: Clean popup with video ID input and live thumbnail preview
- **Validation**: Automatic validation of YouTube video ID format (11 characters)
- **Live Preview**: Shows YouTube thumbnail as you type
- **Responsive Toggle**: Option to use responsive 16:9 aspect ratio
- **Easy Insert**: One-click insertion of `{ytvideo VIDEO_ID}` placeholder at cursor position
- **Permissions**: Respects Joomla user permissions (only shows for users with content edit rights)

## Requirements

- Joomla 5.0 or higher
- PHP 8.1 or higher
- Content Plugin `plg_content_weltspiegel` (to process the placeholders)
- Weltspiegel Template `tpl_weltspiegel` (for layout rendering)

## Installation

### Via Joomla Administrator

1. Download the latest release ZIP from [GitHub Releases](https://github.com/weltspiegel-cottbus/plg_editors-xtd_weltspiegel/releases)
2. Navigate to **System → Extensions → Install**
3. Upload the ZIP file
4. Enable the plugin at **System → Plugins**
5. Search for "Weltspiegel" and click to enable

### Via Update Server (Automatic Updates)

The plugin includes an update server for automatic updates:

1. Install the plugin as described above
2. The update server is automatically configured
3. Check for updates at **System → Update → Extensions**

## Configuration

After installation, configure the plugin at **System → Plugins → Editor Button - Weltspiegel**:

### Settings

- **Standard Responsive**: Default setting for responsive 16:9 aspect ratio (default: Yes)

## Usage

### For Content Authors

1. **Open an article** in the Joomla editor
2. **Position cursor** where you want to insert the video
3. **Click "YouTube Video" button** below the editor
4. **Enter the video ID**:
   - From URL `https://www.youtube.com/watch?v=dQw4w9WgXcQ`
   - Extract the 11-character ID: `dQw4w9WgXcQ`
5. **Preview appears** automatically as you type
6. **Optional**: Toggle responsive setting
7. **Click "Einfügen"** to insert `{ytvideo dQw4w9WgXcQ}` into your article

### Button Location

The button appears below the editor with other editor buttons like "Article", "Image", "Module", etc. It displays with a YouTube icon for easy recognition.

### Placeholder Format

The plugin inserts placeholders in this format:

```
{ytvideo VIDEO_ID}
```

These placeholders are then processed by the `plg_content_weltspiegel` content plugin to render the actual YouTube embed with consent-aware thumbnails.

## Development

### Prerequisites

- Node.js LTS
- npm

### Setup

```bash
# Clone the repository
git clone https://github.com/weltspiegel-cottbus/plg_editors-xtd_weltspiegel.git
cd plg_editors-xtd_weltspiegel

# Install dependencies
npm install
```

### Build & Package

```bash
# Build and create ZIP for installation
npm run release
```

The packaged ZIP file will be created in the root directory.

### Version Management

```bash
# Patch release (0.1.0 → 0.1.1)
npm run release:patch

# Minor release (0.1.0 → 0.2.0)
npm run release:minor

# Major release (0.1.0 → 1.0.0)
npm run release:major
```

These commands:
- Bump version in `package.json`
- Generate/update `CHANGELOG.md`
- Create git tag
- Push to repository

### Project Structure

```
plg_editors-xtd_weltspiegel/
├── .build/
│   └── package.mjs          # Build script
├── .github/
│   └── workflows/
│       └── release.yml      # GitHub Actions workflow
├── language/
│   └── de-DE/
│       ├── plg_editors-xtd_weltspiegel.ini     # Plugin translations
│       └── plg_editors-xtd_weltspiegel.sys.ini # System translations
├── tmpl/
│   └── modal.php            # Modal template
├── weltspiegel.php          # Main plugin file
├── weltspiegel.xml          # Plugin manifest
├── update-manifest.xml      # Update server manifest
├── package.json             # NPM configuration
└── README.md
```

## Technical Details

### Plugin Type: editors-xtd

This is an `editors-xtd` plugin, which is Joomla's standard way to add buttons below editors. These plugins:
- Appear below the editor field
- Can open modal popups
- Can insert content at the cursor position
- Work with any Joomla editor (TinyMCE, CodeMirror, etc.)

### Modal Implementation

The modal is rendered via AJAX call to maintain security:
- Token verification via Joomla's Session class
- Clean separation between button display and modal content
- Self-contained HTML/CSS/JavaScript in modal template

### JavaScript Integration

The plugin uses Joomla's native editor API:
```javascript
window.parent.Joomla.editors.instances[editorName].replaceSelection(content);
```

This ensures compatibility with all Joomla editors.

## Contributing

Contributions are welcome! Please follow these guidelines:

1. **Fork the repository** on GitHub
2. **Create a feature branch** (`git checkout -b feature/amazing-feature`)
3. **Make your changes** following the code style
4. **Test thoroughly** on a local Joomla installation
5. **Commit your changes** (`git commit -m 'Add amazing feature'`)
6. **Push to the branch** (`git push origin feature/amazing-feature`)
7. **Open a Pull Request**

### Code Style

- Follow PSR-12 coding standards for PHP
- Use Joomla 5 namespaced classes
- Add PHPDoc comments for all methods
- Use type hints and return types
- Keep modal template self-contained (no external dependencies)

### Testing

Before submitting a PR:

1. Test on a clean Joomla 5 installation
2. Verify plugin enables without errors
3. Test button appears below editor
4. Test modal opens and displays correctly
5. Test video ID validation
6. Test preview image loading
7. Test placeholder insertion at cursor position
8. Test with different editors (TinyMCE, CodeMirror)

## Related Projects

- [plg_content_weltspiegel](https://github.com/weltspiegel-cottbus/plg_content_weltspiegel) - Content plugin that processes the placeholders
- [tpl_weltspiegel](https://github.com/weltspiegel-cottbus/tpl_weltspiegel) - Weltspiegel Joomla template (provides YouTube embed layout)
- [com_weltspiegel](https://github.com/weltspiegel-cottbus/com_weltspiegel) - Weltspiegel component for movie/event management

## Workflow

The complete YouTube video integration workflow:

1. **Author** clicks editor button (this plugin)
2. **Modal** opens with video ID input and preview
3. **Placeholder** `{ytvideo VIDEO_ID}` inserted into article
4. **Content Plugin** processes placeholder during rendering
5. **Layout** fetches/caches thumbnail and renders consent-aware embed
6. **User** sees thumbnail, accepts cookies, watches video

## License

MIT License - see [LICENSE](LICENSE) file for details.

Copyright (c) 2025 Weltspiegel Cottbus

## Maintainer

**Michael Buchholz**
[Weltspiegel Cottbus](https://www.weltspiegel-cottbus.de)

## Support

- **Issues**: [GitHub Issues](https://github.com/weltspiegel-cottbus/plg_editors-xtd_weltspiegel/issues)
- **Releases**: [GitHub Releases](https://github.com/weltspiegel-cottbus/plg_editors-xtd_weltspiegel/releases)
