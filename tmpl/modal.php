<?php

/**
 * @package     Weltspiegel\Plugin\EditorsXtd\Weltspiegel
 *
 * @copyright   Weltspiegel Cottbus
 * @license     MIT; see LICENSE file
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

/** @var string $editor Editor name */
/** @var bool $responsive Default responsive setting */

?>
<!DOCTYPE html>
<html lang="de" id="modal-html">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo Text::_('PLG_EDITORS-XTD_WELTSPIEGEL_MODAL_TITLE'); ?></title>
    <script>
        // Inherit color scheme from parent Joomla admin
        (function() {
            if (window.parent && window.parent.document && window.parent.document.documentElement) {
                const parentHtml = window.parent.document.documentElement;
                const theme = parentHtml.getAttribute('data-color-scheme') || 'light';
                document.documentElement.setAttribute('data-color-scheme', theme);
                document.documentElement.setAttribute('data-bs-theme', theme);
            }
        })();
    </script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: system-ui, -apple-system, sans-serif;
            padding: 1.5rem;
            background: #f8f9fa;
            color: #212529;
        }

        .container {
            max-width: 700px;
            margin: 0 auto;
            background: white;
            border-radius: 0.5rem;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        h2 {
            margin-bottom: 1.5rem;
            color: #212529;
            font-size: 1.5rem;
        }

        /* Dark mode */
        [data-color-scheme="dark"] body {
            background: #222;
            color: #dee2e6;
        }

        [data-color-scheme="dark"] .container {
            background: #1a1d20;
            box-shadow: 0 2px 4px rgba(0,0,0,0.5);
        }

        [data-color-scheme="dark"] h2 {
            color: #f8f9fa;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #495057;
        }

        [data-color-scheme="dark"] label {
            color: #adb5bd;
        }

        input[type="text"] {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
            font-size: 1rem;
            font-family: monospace;
            background: #fff;
            color: #212529;
        }

        [data-color-scheme="dark"] input[type="text"] {
            background: #2b3035;
            border-color: #495057;
            color: #dee2e6;
        }

        input[type="text"]:focus {
            outline: none;
            border-color: #4CAF50;
            box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.1);
        }

        [data-color-scheme="dark"] input[type="text"]:focus {
            border-color: #4CAF50;
            box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.2);
        }

        .hint {
            margin-top: 0.5rem;
            font-size: 0.875rem;
            color: #6c757d;
        }

        [data-color-scheme="dark"] .hint {
            color: #868e96;
        }

        .hint code {
            background: #f5f5f5;
            padding: 0.125rem 0.25rem;
            border-radius: 0.125rem;
            font-family: monospace;
        }

        [data-color-scheme="dark"] .hint code {
            background: #343a40;
            color: #dee2e6;
        }

        .preview {
            margin-top: 1rem;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 0.25rem;
            display: none;
        }

        [data-color-scheme="dark"] .preview {
            background: #2b3035;
        }

        .preview.active {
            display: block;
        }

        .preview img {
            width: 100%;
            max-width: 480px;
            height: auto;
            border-radius: 0.25rem;
            display: block;
        }

        .preview-label {
            font-size: 0.875rem;
            font-weight: 500;
            color: #6c757d;
            margin-bottom: 0.5rem;
        }

        [data-color-scheme="dark"] .preview-label {
            color: #adb5bd;
        }

        .error {
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 0.5rem;
            display: none;
        }

        [data-color-scheme="dark"] .error {
            color: #ea868f;
        }

        .error.active {
            display: block;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .checkbox-group input[type="checkbox"] {
            width: 1.25rem;
            height: 1.25rem;
            cursor: pointer;
        }

        .checkbox-group label {
            margin: 0;
            cursor: pointer;
        }

        .actions {
            display: flex;
            gap: 0.75rem;
            justify-content: flex-end;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid #dee2e6;
        }

        [data-color-scheme="dark"] .actions {
            border-top-color: #495057;
        }

        button {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 0.25rem;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-primary {
            background: #198754;
            color: white;
        }

        .btn-primary:hover {
            background: #157347;
        }

        .btn-primary:disabled {
            background: #6c757d;
            cursor: not-allowed;
        }

        [data-color-scheme="dark"] .btn-primary:disabled {
            background: #495057;
        }

        .btn-secondary {
            background: #f8f9fa;
            color: #212529;
            border: 1px solid #ced4da;
        }

        .btn-secondary:hover {
            background: #e9ecef;
        }

        [data-color-scheme="dark"] .btn-secondary {
            background: #343a40;
            color: #f8f9fa;
            border-color: #495057;
        }

        [data-color-scheme="dark"] .btn-secondary:hover {
            background: #3d444b;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2><?php echo Text::_('PLG_EDITORS-XTD_WELTSPIEGEL_MODAL_TITLE'); ?></h2>

        <div class="form-group">
            <label for="video-id"><?php echo Text::_('PLG_EDITORS-XTD_WELTSPIEGEL_VIDEO_ID_LABEL'); ?></label>
            <input
                type="text"
                id="video-id"
                placeholder="dQw4w9WgXcQ oder https://youtube.com/watch?v=dQw4w9WgXcQ"
                autocomplete="off"
            >
            <div class="hint">
                <?php echo Text::_('PLG_EDITORS-XTD_WELTSPIEGEL_VIDEO_ID_HINT'); ?>
            </div>
            <div class="error" id="error-message"></div>

            <div class="preview" id="preview">
                <div class="preview-label"><?php echo Text::_('PLG_EDITORS-XTD_WELTSPIEGEL_PREVIEW_LABEL'); ?></div>
                <img id="preview-image" src="" alt="Video Thumbnail">
            </div>
        </div>

        <div class="form-group">
            <div class="checkbox-group">
                <input
                    type="checkbox"
                    id="responsive"
                    <?php echo $responsive ? 'checked' : ''; ?>
                >
                <label for="responsive"><?php echo Text::_('PLG_EDITORS-XTD_WELTSPIEGEL_RESPONSIVE_LABEL'); ?></label>
            </div>
        </div>

        <div class="actions">
            <button type="button" class="btn-secondary" onclick="window.parent.Joomla.Modal.getCurrent().close();">
                <?php echo Text::_('JCANCEL'); ?>
            </button>
            <button type="button" class="btn-primary" id="insert-btn" disabled>
                <?php echo Text::_('PLG_EDITORS-XTD_WELTSPIEGEL_INSERT_BUTTON'); ?>
            </button>
        </div>
    </div>

    <script>
        (function() {
            'use strict';

            const videoIdInput = document.getElementById('video-id');
            const insertBtn = document.getElementById('insert-btn');
            const errorMessage = document.getElementById('error-message');
            const preview = document.getElementById('preview');
            const previewImage = document.getElementById('preview-image');
            const responsiveCheckbox = document.getElementById('responsive');
            const editorName = <?php echo json_encode($editor); ?>;

            let debounceTimer;
            let currentVideoId = null;

            // YouTube URL regex (matches YouTubeHelper::parseYoutubeId regex)
            const YOUTUBE_REGEX = /^(?:https?:\/\/|\/\/)?(?:www\.|m\.|.+\.)?(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|shorts\/|feeds\/api\/videos\/|watch\?v=|watch\?.+&v=))([\w-]{11})(?![\w-])/;

            // Parse YouTube video ID from URL or return input if already an ID
            function parseYoutubeId(input) {
                if (!input) return null;

                // Check if it's already a valid video ID
                if (/^[a-zA-Z0-9_-]{11}$/.test(input)) {
                    return input;
                }

                // Try to parse as URL
                const matches = input.match(YOUTUBE_REGEX);
                return matches ? matches[1] : null;
            }

            // Validate YouTube video ID format
            function isValidVideoId(id) {
                return /^[a-zA-Z0-9_-]{11}$/.test(id);
            }

            // Show error message
            function showError(message) {
                errorMessage.textContent = message;
                errorMessage.classList.add('active');
                preview.classList.remove('active');
            }

            // Hide error message
            function hideError() {
                errorMessage.classList.remove('active');
            }

            // Show preview
            function showPreview(videoId) {
                hideError();
                previewImage.src = `https://img.youtube.com/vi/${videoId}/hqdefault.jpg`;
                preview.classList.add('active');
            }

            // Handle video ID input
            videoIdInput.addEventListener('input', function() {
                const input = this.value.trim();

                // Clear previous debounce
                clearTimeout(debounceTimer);

                if (input === '') {
                    currentVideoId = null;
                    insertBtn.disabled = true;
                    hideError();
                    preview.classList.remove('active');
                    return;
                }

                // Try to parse video ID from input (URL or ID)
                const videoId = parseYoutubeId(input);

                if (!videoId) {
                    currentVideoId = null;
                    insertBtn.disabled = true;
                    showError(<?php echo json_encode(Text::_('PLG_EDITORS-XTD_WELTSPIEGEL_ERROR_INVALID_ID')); ?>);
                    return;
                }

                // Store parsed video ID
                currentVideoId = videoId;

                // Debounce preview loading
                debounceTimer = setTimeout(() => {
                    insertBtn.disabled = false;
                    showPreview(videoId);
                }, 500);
            });

            // Handle insert button
            insertBtn.addEventListener('click', function() {
                if (!currentVideoId) {
                    showError(<?php echo json_encode(Text::_('PLG_EDITORS-XTD_WELTSPIEGEL_ERROR_INVALID_ID')); ?>);
                    return;
                }

                // Build the placeholder with parsed video ID
                const placeholder = `{ytvideo ${currentVideoId}}`;

                // Insert into editor
                if (window.parent.Joomla && window.parent.Joomla.editors && window.parent.Joomla.editors.instances[editorName]) {
                    window.parent.Joomla.editors.instances[editorName].replaceSelection(placeholder);
                }

                // Close modal
                window.parent.Joomla.Modal.getCurrent().close();
            });

            // Handle Enter key
            videoIdInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter' && !insertBtn.disabled) {
                    insertBtn.click();
                }
            });

            // Focus input on load
            videoIdInput.focus();
        })();
    </script>
</body>
</html>
