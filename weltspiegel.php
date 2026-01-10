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

/**
 * Weltspiegel Editor Button Plugin
 *
 * Adds a button below the editor to insert YouTube video placeholders
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
        $button->icon = 'fab fa-youtube';
        $button->iconSVG = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" width="16" height="16">'
            . '<path d="M8.051 1.999h.089c.822.003 4.987.033 6.11.335a2.01 2.01 0 0 1 1.415 1.42c.101.38.172.883.22 1.402l.01.104.022.26.008.104c.065.914.073 1.77.074 1.957v.075c-.001.194-.01 1.108-.082 2.06l-.008.105-.009.104c-.05.572-.124 1.14-.235 1.558a2.007 2.007 0 0 1-1.415 1.42c-1.16.312-5.569.334-6.18.335h-.142c-.309 0-1.587-.006-2.927-.052l-.17-.006-.087-.004-.171-.007-.171-.007c-1.11-.049-2.167-.128-2.654-.26a2.007 2.007 0 0 1-1.415-1.419c-.111-.417-.185-.986-.235-1.558L.09 9.82l-.008-.104A31.4 31.4 0 0 1 0 7.68v-.123c.002-.215.01-.958.064-1.778l.007-.103.003-.052.008-.104.022-.26.01-.104c.048-.519.119-1.023.22-1.402a2.007 2.007 0 0 1 1.415-1.42c.487-.13 1.544-.21 2.654-.26l.17-.007.172-.006.086-.003.171-.007A99.788 99.788 0 0 1 7.858 2h.193zM6.4 5.209v4.818l4.157-2.408L6.4 5.209z"/>'
            . '</svg>';
        $button->options = [
            'height' => '400px',
            'width' => '800px',
            'bodyHeight' => '70',
            'modalWidth' => '80',
        ];

        return $button;
    }

    /**
     * Handle AJAX request for the modal content
     *
     * @return  string  HTML content for the modal
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

        // Get editor name
        $editor = $app->input->getCmd('editor', '');

        if (empty($editor)) {
            return '';
        }

        // Get default responsive setting
        $responsive = (bool) $this->params->get('yt_responsive_default', 1);

        // Load the template
        ob_start();
        include __DIR__ . '/tmpl/modal.php';
        return ob_get_clean();
    }
}
