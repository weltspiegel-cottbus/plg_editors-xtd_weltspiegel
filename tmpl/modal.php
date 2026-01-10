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
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo Text::_('PLG_EDITORS-XTD_WELTSPIEGEL_MODAL_TITLE'); ?></title>
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
            color: #333;
            font-size: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #555;
        }

        input[type="text"] {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 0.25rem;
            font-size: 1rem;
            font-family: monospace;
        }

        input[type="text"]:focus {
            outline: none;
            border-color: #4CAF50;
            box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.1);
        }

        .hint {
            margin-top: 0.5rem;
            font-size: 0.875rem;
            color: #666;
        }

        .hint code {
            background: #f5f5f5;
            padding: 0.125rem 0.25rem;
            border-radius: 0.125rem;
            font-family: monospace;
        }

        .preview {
            margin-top: 1rem;
            padding: 1rem;
            background: #f5f5f5;
            border-radius: 0.25rem;
            display: none;
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
            color: #666;
            margin-bottom: 0.5rem;
        }

        .error {
            color: #d32f2f;
            font-size: 0.875rem;
            margin-top: 0.5rem;
            display: none;
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
            border-top: 1px solid #e0e0e0;
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
            background: #4CAF50;
            color: white;
        }

        .btn-primary:hover {
            background: #45a049;
        }

        .btn-primary:disabled {
            background: #ccc;
            cursor: not-allowed;
        }

        .btn-secondary {
            background: #f5f5f5;
            color: #333;
        }

        .btn-secondary:hover {
            background: #e0e0e0;
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
                placeholder="dQw4w9WgXcQ"
                maxlength="11"
                autocomplete="off"
            >
            <div class="hint">
                <?php echo Text::_('PLG_EDITORS-XTD_WELTSPIEGEL_VIDEO_ID_HINT'); ?>
                <code>youtube.com/watch?v=<strong>dQw4w9WgXcQ</strong></code>
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
                const videoId = this.value.trim();

                // Clear previous debounce
                clearTimeout(debounceTimer);

                if (videoId === '') {
                    insertBtn.disabled = true;
                    hideError();
                    preview.classList.remove('active');
                    return;
                }

                if (!isValidVideoId(videoId)) {
                    insertBtn.disabled = true;
                    showError(<?php echo json_encode(Text::_('PLG_EDITORS-XTD_WELTSPIEGEL_ERROR_INVALID_ID')); ?>);
                    return;
                }

                // Debounce preview loading
                debounceTimer = setTimeout(() => {
                    insertBtn.disabled = false;
                    showPreview(videoId);
                }, 500);
            });

            // Handle insert button
            insertBtn.addEventListener('click', function() {
                const videoId = videoIdInput.value.trim();

                if (!isValidVideoId(videoId)) {
                    showError(<?php echo json_encode(Text::_('PLG_EDITORS-XTD_WELTSPIEGEL_ERROR_INVALID_ID')); ?>);
                    return;
                }

                // Build the placeholder
                const placeholder = `{ytvideo ${videoId}}`;

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
