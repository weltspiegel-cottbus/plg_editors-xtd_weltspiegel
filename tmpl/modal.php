<?php

/**
 * @package     Weltspiegel\Plugin\EditorsXtd\Weltspiegel
 *
 * @copyright   Weltspiegel Cottbus
 * @license     MIT; see LICENSE file
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;

/** @var string $editor Editor name */
/** @var bool $responsive Default responsive setting */

$ajaxBase = 'index.php?option=com_ajax&plugin=weltspiegel&group=editors-xtd&format=raw&'
    . Session::getFormToken() . '=1';

?>
<style>
    /* Only custom styles that Bootstrap doesn't cover */
    .gallery-preview__grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
        gap: 0.5rem;
    }

    .gallery-preview__thumb {
        aspect-ratio: 1;
        object-fit: cover;
        width: 100%;
        border-radius: var(--bs-border-radius);
    }

    .folder-icon {
        color: #f29400;
    }

    #preview img {
        max-width: 480px;
    }
</style>

<div class="container-fluid p-4">
    <h2 class="mb-3"><?php echo Text::_('PLG_EDITORS-XTD_WELTSPIEGEL_MODAL_TITLE'); ?></h2>

    <!-- Tab Bar -->
    <ul class="nav nav-tabs mb-3">
        <li class="nav-item">
            <button type="button" class="nav-link active" data-tab="youtube">
                <?php echo Text::_('PLG_EDITORS-XTD_WELTSPIEGEL_TAB_YOUTUBE'); ?>
            </button>
        </li>
        <li class="nav-item">
            <button type="button" class="nav-link" data-tab="gallery">
                <?php echo Text::_('PLG_EDITORS-XTD_WELTSPIEGEL_TAB_GALLERY'); ?>
            </button>
        </li>
    </ul>

    <!-- YouTube Tab -->
    <div class="tab-pane" id="tab-youtube">
        <div class="mb-3">
            <label class="form-label" for="video-id"><?php echo Text::_('PLG_EDITORS-XTD_WELTSPIEGEL_VIDEO_ID_LABEL'); ?></label>
            <input
                type="text"
                class="form-control font-monospace"
                id="video-id"
                placeholder="dQw4w9WgXcQ oder https://youtube.com/watch?v=dQw4w9WgXcQ"
                autocomplete="off"
            >
            <div class="form-text">
                <?php echo Text::_('PLG_EDITORS-XTD_WELTSPIEGEL_VIDEO_ID_HINT'); ?>
            </div>
            <div class="text-danger small mt-1 d-none" id="error-message"></div>

            <div class="mt-3 p-3 bg-body-secondary rounded d-none" id="preview">
                <div class="small fw-medium text-body-secondary mb-2"><?php echo Text::_('PLG_EDITORS-XTD_WELTSPIEGEL_PREVIEW_LABEL'); ?></div>
                <img id="preview-image" class="img-fluid rounded" src="" alt="Video Thumbnail">
            </div>
        </div>

        <div class="mb-3">
            <div class="form-check">
                <input
                    class="form-check-input"
                    type="checkbox"
                    id="responsive"
                    <?php echo $responsive ? 'checked' : ''; ?>
                >
                <label class="form-check-label" for="responsive"><?php echo Text::_('PLG_EDITORS-XTD_WELTSPIEGEL_RESPONSIVE_LABEL'); ?></label>
            </div>
        </div>
    </div>

    <!-- Gallery Tab -->
    <div class="tab-pane d-none" id="tab-gallery">
        <div class="mb-3">
            <label class="form-label"><?php echo Text::_('PLG_EDITORS-XTD_WELTSPIEGEL_GALLERY_SELECT_FOLDER'); ?></label>

            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-transparent" id="gallery-breadcrumb">
                    <li class="breadcrumb-item active" aria-current="page">images</li>
                </ol>
            </nav>

            <!-- Folder List -->
            <div class="list-group" id="gallery-folders" style="max-height: 200px; overflow-y: auto;">
                <div class="text-center text-body-secondary p-3">&hellip;</div>
            </div>
        </div>

        <!-- Image Preview -->
        <div class="d-none" id="gallery-preview">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <code class="small" id="gallery-path"></code>
                <span class="small fw-medium text-body-secondary" id="gallery-count"></span>
            </div>
            <div class="gallery-preview__grid p-2 bg-body-secondary rounded" id="gallery-grid"></div>
        </div>
    </div>

    <!-- Actions -->
    <div class="d-flex gap-2 justify-content-end pt-3 border-top sticky-bottom bg-body mt-4" style="padding-bottom: 1rem;">
        <button type="button" class="btn btn-secondary" onclick="window.parent.Joomla.Modal.getCurrent().close();">
            <?php echo Text::_('JCANCEL'); ?>
        </button>
        <button type="button" class="btn btn-success" id="insert-btn" disabled>
            <?php echo Text::_('PLG_EDITORS-XTD_WELTSPIEGEL_INSERT_BUTTON'); ?>
        </button>
    </div>
</div>

<script>
    (function() {
        'use strict';

        const editorName = <?php echo json_encode($editor); ?>;
        const ajaxBase = <?php echo json_encode($ajaxBase); ?>;
        const insertBtn = document.getElementById('insert-btn');

        // ── Tab Switching ────────────────────────────────────────────

        let activeTab = 'youtube';
        const navLinks = document.querySelectorAll('.nav-link[data-tab]');
        const tabPanes = {
            youtube: document.getElementById('tab-youtube'),
            gallery: document.getElementById('tab-gallery')
        };

        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                const target = this.dataset.tab;
                if (target === activeTab) return;

                activeTab = target;
                navLinks.forEach(l => l.classList.toggle('active', l.dataset.tab === target));
                Object.entries(tabPanes).forEach(([key, el]) => {
                    el.classList.toggle('d-none', key !== target);
                });

                updateInsertButton();

                if (target === 'gallery' && !galleryInitialized) {
                    loadFolders('');
                    galleryInitialized = true;
                }
                if (target === 'youtube') {
                    document.getElementById('video-id').focus();
                }
            });
        });

        // ── YouTube Tab ──────────────────────────────────────────────

        const videoIdInput = document.getElementById('video-id');
        const errorMessage = document.getElementById('error-message');
        const preview = document.getElementById('preview');
        const previewImage = document.getElementById('preview-image');

        let debounceTimer;
        let currentVideoId = null;

        const YOUTUBE_REGEX = /^(?:https?:\/\/|\/\/)?(?:www\.|m\.|.+\.)?(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|shorts\/|feeds\/api\/videos\/|watch\?v=|watch\?.+&v=))([\w-]{11})(?![\w-])/;

        function parseYoutubeId(input) {
            if (!input) return null;
            if (/^[a-zA-Z0-9_-]{11}$/.test(input)) return input;
            const matches = input.match(YOUTUBE_REGEX);
            return matches ? matches[1] : null;
        }

        function showError(message) {
            errorMessage.textContent = message;
            errorMessage.classList.remove('d-none');
            preview.classList.add('d-none');
        }

        function hideError() {
            errorMessage.classList.add('d-none');
        }

        function showPreview(videoId) {
            hideError();
            previewImage.src = `https://img.youtube.com/vi/${videoId}/hqdefault.jpg`;
            preview.classList.remove('d-none');
        }

        videoIdInput.addEventListener('input', function() {
            const input = this.value.trim();
            clearTimeout(debounceTimer);

            if (input === '') {
                currentVideoId = null;
                hideError();
                preview.classList.add('d-none');
                updateInsertButton();
                return;
            }

            const videoId = parseYoutubeId(input);

            if (!videoId) {
                currentVideoId = null;
                updateInsertButton();
                showError(<?php echo json_encode(Text::_('PLG_EDITORS-XTD_WELTSPIEGEL_ERROR_INVALID_ID')); ?>);
                return;
            }

            currentVideoId = videoId;
            debounceTimer = setTimeout(() => {
                updateInsertButton();
                showPreview(videoId);
            }, 500);
        });

        videoIdInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter' && !insertBtn.disabled) {
                insertBtn.click();
            }
        });

        // ── Gallery Tab ──────────────────────────────────────────────

        let galleryInitialized = false;
        let currentGalleryPath = [];
        let selectedGalleryPath = null;

        const foldersContainer = document.getElementById('gallery-folders');
        const breadcrumbContainer = document.getElementById('gallery-breadcrumb');
        const galleryPreview = document.getElementById('gallery-preview');
        const galleryPath = document.getElementById('gallery-path');
        const galleryCount = document.getElementById('gallery-count');
        const galleryGrid = document.getElementById('gallery-grid');

        const folderIcon = '<svg class="folder-icon flex-shrink-0" width="16" height="16" viewBox="0 0 16 16" fill="currentColor">'
            + '<path d="M.54 3.87.5 3a2 2 0 0 1 2-2h3.672a2 2 0 0 1 1.414.586l.828.828A2 2 0 0 0 9.828 3H13.5a2 2 0 0 1 2 2v1H8.032a2 2 0 0 0-1.414.586L5.5 7.707V4.5a1 1 0 0 0-1-1H1.996L.54 3.87z"/>'
            + '<path d="M16 6.5a2 2 0 0 0-2-2H8.032a1 1 0 0 0-.707.293L5.5 6.618V12.5a1 1 0 0 0 1 1H14a2 2 0 0 0 2-2V6.5z"/>'
            + '</svg>';

        function getRelativePath() {
            return currentGalleryPath.join('/');
        }

        function loadFolders(path) {
            foldersContainer.innerHTML = '<div class="text-center text-body-secondary p-3">&hellip;</div>';

            fetch(ajaxBase + '&action=folders&path=' + encodeURIComponent(path))
                .then(r => r.json())
                .then(folders => {
                    foldersContainer.innerHTML = '';
                    if (folders.length > 0) {
                        folders.forEach(name => {
                            const btn = document.createElement('button');
                            btn.type = 'button';
                            btn.className = 'list-group-item list-group-item-action d-flex align-items-center gap-2';
                            btn.innerHTML = folderIcon + '<span>' + escapeHtml(name) + '</span>';
                            btn.addEventListener('click', () => navigateInto(name));
                            foldersContainer.appendChild(btn);
                        });
                    }
                })
                .catch(() => {
                    foldersContainer.innerHTML = '<div class="text-center text-body-secondary p-4 small">Fehler beim Laden</div>';
                });

            loadImages(path);
        }

        function loadImages(path) {
            if (path === '') {
                galleryPreview.classList.add('d-none');
                selectedGalleryPath = null;
                updateInsertButton();
                return;
            }

            fetch(ajaxBase + '&action=images&path=' + encodeURIComponent(path))
                .then(r => r.json())
                .then(images => {
                    galleryPreview.classList.remove('d-none');
                    galleryPath.textContent = 'images/' + path;

                    if (images.length === 0) {
                        galleryCount.textContent = <?php echo json_encode(Text::_('PLG_EDITORS-XTD_WELTSPIEGEL_GALLERY_NO_IMAGES')); ?>;
                        galleryGrid.innerHTML = '<div class="text-center text-body-secondary p-4 small" style="grid-column:1/-1">'
                            + <?php echo json_encode(Text::_('PLG_EDITORS-XTD_WELTSPIEGEL_GALLERY_NO_IMAGES')); ?>
                            + '</div>';
                        selectedGalleryPath = null;
                    } else {
                        galleryCount.textContent = images.length + ' <?php echo Text::_('PLG_EDITORS-XTD_WELTSPIEGEL_GALLERY_IMAGE_COUNT'); ?>';
                        galleryGrid.innerHTML = '';
                        images.forEach(src => {
                            const img = document.createElement('img');
                            img.className = 'gallery-preview__thumb';
                            img.src = '<?php echo \Joomla\CMS\Uri\Uri::root(); ?>' + src;
                            img.alt = src.split('/').pop();
                            img.loading = 'lazy';
                            galleryGrid.appendChild(img);
                        });
                        selectedGalleryPath = 'images/' + path;
                    }

                    updateInsertButton();
                })
                .catch(() => {
                    galleryPreview.classList.add('d-none');
                    selectedGalleryPath = null;
                    updateInsertButton();
                });
        }

        function navigateInto(folderName) {
            currentGalleryPath.push(folderName);
            renderBreadcrumb();
            loadFolders(getRelativePath());
        }

        function navigateTo(index) {
            currentGalleryPath = currentGalleryPath.slice(0, index);
            renderBreadcrumb();
            loadFolders(getRelativePath());
        }

        function renderBreadcrumb() {
            breadcrumbContainer.innerHTML = '';

            // Root "images" item
            const rootLi = document.createElement('li');
            rootLi.className = 'breadcrumb-item';
            if (currentGalleryPath.length === 0) {
                rootLi.classList.add('active');
                rootLi.setAttribute('aria-current', 'page');
                rootLi.textContent = 'images';
            } else {
                const rootLink = document.createElement('a');
                rootLink.href = '#';
                rootLink.textContent = 'images';
                rootLink.addEventListener('click', (e) => { e.preventDefault(); navigateTo(0); });
                rootLi.appendChild(rootLink);
            }
            breadcrumbContainer.appendChild(rootLi);

            currentGalleryPath.forEach((segment, i) => {
                const li = document.createElement('li');
                li.className = 'breadcrumb-item';
                const isLast = i === currentGalleryPath.length - 1;

                if (isLast) {
                    li.classList.add('active');
                    li.setAttribute('aria-current', 'page');
                    li.textContent = segment;
                } else {
                    const link = document.createElement('a');
                    link.href = '#';
                    link.textContent = segment;
                    link.addEventListener('click', (e) => { e.preventDefault(); navigateTo(i + 1); });
                    li.appendChild(link);
                }

                breadcrumbContainer.appendChild(li);
            });
        }

        function escapeHtml(text) {
            const el = document.createElement('span');
            el.textContent = text;
            return el.innerHTML;
        }

        // ── Insert Button (shared) ───────────────────────────────────

        function updateInsertButton() {
            if (activeTab === 'youtube') {
                insertBtn.disabled = !currentVideoId;
                insertBtn.textContent = <?php echo json_encode(Text::_('PLG_EDITORS-XTD_WELTSPIEGEL_INSERT_BUTTON')); ?>;
            } else {
                insertBtn.disabled = !selectedGalleryPath;
                insertBtn.textContent = <?php echo json_encode(Text::_('PLG_EDITORS-XTD_WELTSPIEGEL_GALLERY_INSERT')); ?>;
            }
        }

        insertBtn.addEventListener('click', function() {
            let placeholder;

            if (activeTab === 'youtube') {
                if (!currentVideoId) {
                    showError(<?php echo json_encode(Text::_('PLG_EDITORS-XTD_WELTSPIEGEL_ERROR_INVALID_ID')); ?>);
                    return;
                }
                placeholder = `{ytvideo ${currentVideoId}}`;
            } else {
                if (!selectedGalleryPath) return;
                placeholder = `{gallery ${selectedGalleryPath}}`;
            }

            if (window.parent.Joomla && window.parent.Joomla.editors && window.parent.Joomla.editors.instances[editorName]) {
                window.parent.Joomla.editors.instances[editorName].replaceSelection(placeholder);
            }

            window.parent.Joomla.Modal.getCurrent().close();
        });

        // ── Init ─────────────────────────────────────────────────────

        videoIdInput.focus();
    })();
</script>
