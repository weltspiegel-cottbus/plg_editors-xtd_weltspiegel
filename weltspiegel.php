<?php

/**
 * @package     Weltspiegel\Plugin\EditorsXtd\Weltspiegel
 *
 * @copyright   Weltspiegel Cottbus
 * @license     MIT; see LICENSE file
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Session\Session;
use Joomla\Filesystem\Folder;

/**
 * Weltspiegel Editor Button Plugin
 *
 * Adds an editor button to insert media placeholders (YouTube, Gallery)
 *
 * @since 0.1.0
 */
class PlgEditorsXtdWeltspiegel extends CMSPlugin
{
    /**
     * Load the language file on instantiation
     *
     * @var    boolean
     * @since  0.1.0
     */
    protected $autoloadLanguage = true;

    /**
     * Display the button
     *
     * @param   string  $name  The name of the editor field
     *
     * @return  CMSObject|null  Button object or null if not authorized
     *
     * @since   0.1.0
     */
    public function onDisplay($name)
    {
        $user = Factory::getApplication()->getIdentity();

        // Check if user can create content
        if (!$user->authorise('core.create', 'com_content')
            && !$user->authorise('core.edit', 'com_content')
            && !$user->authorise('core.edit.own', 'com_content')) {
            return null;
        }

        // Get the modal URL
        $link = 'index.php?option=com_ajax&plugin=weltspiegel&group=editors-xtd&format=html&tmpl=component&'
            . Session::getFormToken() . '=1&editor=' . $name;

        $button = new CMSObject();
        $button->modal = true;
        $button->link = $link;
        $button->text = Text::_('PLG_EDITORS-XTD_WELTSPIEGEL_BUTTON_TEXT');
        $button->name = $this->_type . '_' . $this->_name;
        $button->icon = 'fas fa-globe';
        $button->iconSVG = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" width="16" height="16">'
            . '<path d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8zm7.5-6.923c-.67.204-1.335.82-1.887 1.855A7.97 7.97 0 0 0 5.145 4H7.5V1.077zM4.09 4a9.267 9.267 0 0 1 .64-1.539 6.7 6.7 0 0 1 .597-.933A7.025 7.025 0 0 0 2.255 4H4.09zm-.582 3.5c.03-.877.138-1.718.312-2.5H1.674a6.958 6.958 0 0 0-.656 2.5h2.49zM4.847 5a12.5 12.5 0 0 0-.338 2.5H7.5V5H4.847zM8.5 5v2.5h2.99a12.495 12.495 0 0 0-.337-2.5H8.5zM4.51 8.5a12.5 12.5 0 0 0 .337 2.5H7.5V8.5H4.51zm3.99 0V11h2.653c.187-.765.306-1.608.338-2.5H8.5zM5.145 12c.138.386.295.744.468 1.068.552 1.035 1.218 1.65 1.887 1.855V12H5.145zm.182 2.472a6.696 6.696 0 0 1-.597-.933A9.268 9.268 0 0 1 4.09 12H2.255a7.024 7.024 0 0 0 3.072 2.472zM3.82 11a13.652 13.652 0 0 1-.312-2.5h-2.49c.062.89.291 1.733.656 2.5H3.82zm6.853 3.472A7.024 7.024 0 0 0 13.745 12H11.91a9.27 9.27 0 0 1-.64 1.539 6.688 6.688 0 0 1-.597.933zM8.5 12v2.923c.67-.204 1.335-.82 1.887-1.855.173-.324.33-.682.468-1.068H8.5zm3.68-1h2.146c.365-.767.594-1.61.656-2.5h-2.49a13.65 13.65 0 0 1-.312 2.5zm2.802-3.5a6.959 6.959 0 0 0-.656-2.5H12.18c.174.782.282 1.623.312 2.5h2.49zM11.27 2.461c.247.464.462.98.64 1.539h1.835a7.024 7.024 0 0 0-3.072-2.472c.218.284.418.598.597.933zM10.855 4a7.966 7.966 0 0 0-.468-1.068C9.835 1.897 9.17 1.282 8.5 1.077V4h2.355z"/>'
            . '</svg>';
        $button->options = [
            'height' => '500px',
            'width' => '900px',
            'bodyHeight' => '70',
            'modalWidth' => '80',
        ];

        return $button;
    }

    /**
     * Allowed image extensions for gallery browsing
     *
     * @var    array
     * @since  0.3.0
     */
    private const array IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

    /**
     * Handle AJAX request for the modal content or folder/image listings
     *
     * @return  string  HTML content for the modal or JSON for AJAX requests
     *
     * @since   0.1.0
     */
    public function onAjaxWeltspiegel()
    {
        $app = Factory::getApplication();

        if (!$app instanceof CMSApplicationInterface) {
            return '';
        }

        // Verify token
        Session::checkToken('get') or jexit(Text::_('JINVALID_TOKEN'));

        $action = $app->input->getCmd('action', 'modal');

        return match ($action) {
            'folders' => $this->handleFolders($app),
            'images' => $this->handleImages($app),
            default => $this->handleModal($app),
        };
    }

    /**
     * Handle modal display action
     *
     * @param   CMSApplicationInterface  $app  The application
     *
     * @return  string  HTML content for the modal
     *
     * @since   0.3.0
     */
    private function handleModal(CMSApplicationInterface $app): string
    {
        $editor = $app->input->getCmd('editor', '');

        if (empty($editor)) {
            return '';
        }

        $responsive = (bool) $this->params->get('yt_responsive_default', 1);

        ob_start();
        include __DIR__ . '/tmpl/modal.php';
        return ob_get_clean();
    }

    /**
     * Handle AJAX request for folder listing
     *
     * @param   CMSApplicationInterface  $app  The application
     *
     * @return  string  JSON array of subdirectory names
     *
     * @since   0.3.0
     */
    private function handleFolders(CMSApplicationInterface $app): string
    {
        $app->setHeader('Content-Type', 'application/json');

        $path = $app->input->getString('path', '');
        $fullPath = $this->resolveImagePath($path);

        if ($fullPath === false || !is_dir($fullPath)) {
            return json_encode([]);
        }

        $folders = Folder::folders($fullPath, '.', false, false);
        sort($folders);

        return json_encode($folders);
    }

    /**
     * Handle AJAX request for image listing
     *
     * @param   CMSApplicationInterface  $app  The application
     *
     * @return  string  JSON array of image file paths (relative to site root)
     *
     * @since   0.3.0
     */
    private function handleImages(CMSApplicationInterface $app): string
    {
        $app->setHeader('Content-Type', 'application/json');

        $path = $app->input->getString('path', '');
        $fullPath = $this->resolveImagePath($path);

        if ($fullPath === false || !is_dir($fullPath)) {
            return json_encode([]);
        }

        $filter = '\.(' . implode('|', self::IMAGE_EXTENSIONS) . ')$';
        $files = Folder::files($fullPath, $filter, false, false);
        sort($files);

        // Return paths relative to site root
        $relativePath = $path !== '' ? 'images/' . $path : 'images';
        $images = array_map(
            fn(string $file): string => $relativePath . '/' . $file,
            $files
        );

        return json_encode($images);
    }

    /**
     * Resolve and validate a path within the images directory
     *
     * @param   string  $path  Relative path within /images/
     *
     * @return  string|false  Full resolved path or false if invalid
     *
     * @since   0.3.0
     */
    private function resolveImagePath(string $path): string|false
    {
        $imagesRoot = JPATH_ROOT . '/images';

        if ($path === '') {
            return $imagesRoot;
        }

        $fullPath = realpath($imagesRoot . '/' . $path);

        // Path traversal protection: ensure resolved path is within images directory
        if ($fullPath === false || !str_starts_with($fullPath, realpath($imagesRoot))) {
            return false;
        }

        return $fullPath;
    }
}
