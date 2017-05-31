<?php
/**
 * @package    Joomla.Libraries
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Class JNamespaceMap
 *
 * @since  __DEPLOY_VERSION__
 */
class JNamespacePsr4Map
{
	/**
	 * Path to the autoloader
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	protected static $file = JPATH_LIBRARIES . '/autoload_psr4.php';

	/**
	 * Check if the file exists
	 *
	 * @return  bool
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function exists()
	{
		if (!file_exists(self::$file))
		{
			return false;
		}

		return true;
	}

	/**
	 * Check if the namespace mapping file exists, if not create it
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function init()
	{
		if (self::exists())
		{
			return;
		}

		self::create();
	}

	/**
	 * Create the namespace file
	 *
	 * @return  bool
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function create()
	{
		$extensions = self::getNamespacedExtensions();

		$elements = array();

		foreach ($extensions as $extension)
		{
			$element       = $extension->element;
			$baseNamespace = str_replace("\\", "\\\\", $extension->namespace);

			if (file_exists(JPATH_ADMINISTRATOR . '/components/' . $element))
			{
				$elements[$baseNamespace . '\\\\Administrator'] = array('/administrator/components/' . $element);
			}

			if (file_exists(JPATH_ROOT . '/components/' . $element))
			{
				$elements[$baseNamespace . '\\\\Site'] = array('/components/' . $element);
			}
		}

		self::writeNamespaceFile($elements);

		return true;
	}

	/**
	 * Write the Namespace mapping file
	 *
	 * @param   array  $elements  Array of elements
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected static function writeNamespaceFile($elements)
	{
		$content   = array();
		$content[] = "<?php";
		$content[] = 'return array(';

		foreach ($elements as $namespace => $paths)
		{
			$pathString = '';

			foreach ($paths as $path)
			{
				$pathString .= '"' . $path . '",';
			}

			$content[] = "\t'" . $namespace . "'" . ' => array(JPATH_ROOT . ' . $pathString . '),';
		}

		$content[] = ');';

		file_put_contents(self::$file, implode("\n", $content));
	}

	/**
	 * Get all namespaced extensions from the database
	 *
	 * @return  mixed|false
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected static function getNamespacedExtensions()
	{
		$db = JFactory::getDbo();

		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('extension_id', 'element', 'namespace')))
			->from($db->quoteName('#__extensions'))
			->where($db->quoteName('namespace') . ' IS NOT NULL AND ' . $db->quoteName('namespace') . ' != ""');

		$db->setQuery($query);

		$extensions = $db->loadObjectList();

		return $extensions;
	}
}