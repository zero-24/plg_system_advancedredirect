<?php
/**
 * AdvancedRedirect Plugin
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @copyright   Copyright (C) 2019 Tobias Zulauf All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * This plugin is based on the Joomla Core Redirect Plugin and acts as a so-called drop in replacement for the Core Plugin.
 * In addition to the Joomla Core Plugin, it allows you to define your own derivation rules.
*/

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Exception\ExceptionHandler;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;
use Joomla\String\StringHelper;


/**
 * Plugin class for redirect handling.
 *
 * @since  1.0
 */
class PlgSystemAdvancedRedirect extends JPlugin
{
	/**
	 * Affects constructor behavior. If true, language files will be loaded automatically.
	 *
	 * @var    boolean
	 * @since  1.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * The global exception handler registered before the plugin was instantiated
	 *
	 * @var    callable
	 * @since  1.0
	 */
	private static $previousExceptionHandler;

	/**
	 * Constructor.
	 *
	 * @param   object  &$subject  The object to observe
	 * @param   array   $config    An optional associative array of configuration settings.
	 *
	 * @since   1.0
	 */
	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);

		// Starting with J4 we do not need the following two lines anymore
		if (version_compare(JVERSION, '4.0.0', 'ge'))
		{
			return;
		}

		// Set the JError handler for E_ERROR to be the class' handleError method.
		JError::setErrorHandling(E_ERROR, 'callback', array('PlgSystemAdvancedRedirect', 'handleError'));

		// Register the previously defined exception handler so we can forward errors to it
		self::$previousExceptionHandler = set_exception_handler(array('PlgSystemAdvancedRedirect', 'handleException'));

	}

	/**
	 * Internal processor for all error handlers
	 *
	 * @param   $error  The event object
	 *
	 * @return  void
	 *
	 * @since   1.0.5
	 */
	public function onError($error)
	{
		self::doErrorHandling($error);
	}

	/**
	 * Method to handle an error condition from JError.
	 *
	 * @param   JException  $error  The JException object to be handled.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public static function handleError(JException $error)
	{
		self::doErrorHandling($error);
	}

	/**
	 * Method to handle an uncaught exception.
	 *
	 * @param   Exception|Throwable  $exception  The Exception or Throwable object to be handled.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 * @throws  InvalidArgumentException
	 */
	public static function handleException($exception)
	{
		// If this isn't a Throwable then bail out
		if (!($exception instanceof Throwable) && !($exception instanceof Exception))
		{
			throw new InvalidArgumentException(
				sprintf('The error handler requires an Exception or Throwable object, a "%s" object was given instead.', get_class($exception))
			);
		}

		self::doErrorHandling($exception);
	}

	/**
	 * Internal processor for all error handlers
	 *
	 * @param   Exception|Throwable  $error  The Exception or Throwable object to be handled.
	 *
	 * @return  void
	 *
	 * @since   3.5
	 */
	private static function doErrorHandling($error)
	{
		$app = Factory::getApplication();
		$db = Factory::getDbo();

		// We only care about 404 errors here
		if ($app->isClient('administrator') || ((int) $error->getCode() !== 404))
		{
			// Proxy to the previous exception handler if available, otherwise just render the error page
			if (self::$previousExceptionHandler)
			{
				call_user_func_array(self::$previousExceptionHandler, array($error));
			}
			else
			{
				ExceptionHandler::render($error);
			}
		}

		// Get the Plugin Params
		$params = new Registry(JPluginHelper::getPlugin('system', 'advancedredirect')->params);

		// Prepare the current URI
		$uri = Uri::getInstance();

		// These are the original URLs
		$orgurl                = rawurldecode($uri->toString(array('scheme', 'host', 'port', 'path', 'query', 'fragment')));
		$orgurlRel             = rawurldecode($uri->toString(array('path', 'query', 'fragment')));

		// The above doesn't work for sub directories, so do this
		$orgurlRootRel         = str_replace(Uri::root(), '', $orgurl);

		// For when users have added / to the url
		$orgurlRootRelSlash    = str_replace(Uri::root(), '/', $orgurl);
		$orgurlWithoutQuery    = rawurldecode($uri->toString(array('scheme', 'host', 'port', 'path', 'fragment')));
		$orgurlRelWithoutQuery = rawurldecode($uri->toString(array('path', 'fragment')));

		// These are the URLs we save and use
		$url                = StringHelper::strtolower(rawurldecode($uri->toString(array('scheme', 'host', 'port', 'path', 'query', 'fragment'))));
		$urlRel             = StringHelper::strtolower(rawurldecode($uri->toString(array('path', 'query', 'fragment'))));

		// The above doesn't work for sub directories, so do this
		$urlRootRel         = str_replace(Uri::root(), '', $url);

		// For when users have added / to the url
		$urlRootRelSlash    = str_replace(Uri::root(), '/', $url);
		$urlWithoutQuery    = StringHelper::strtolower(rawurldecode($uri->toString(array('scheme', 'host', 'port', 'path', 'fragment'))));
		$urlRelWithoutQuery = StringHelper::strtolower(rawurldecode($uri->toString(array('path', 'fragment'))));

		// Get the configured exclude rules
		$excludes = (array) $params->get('exclude_urls');
		$skipUrl  = false;

		foreach ($excludes as $exclude)
		{
			if (empty($exclude->term))
			{
				continue;
			}

			if (!empty($exclude->regexp))
			{
				// Only check $url, because it includes all other sub urls
				if (preg_match('/' . $exclude->term . '/i', $orgurlRel))
				{
					ExceptionHandler::render($error);
					break;
				}
			}
			else
			{
				if (StringHelper::strpos($orgurlRel, $exclude->term) !== false)
				{
					ExceptionHandler::render($error);
					break;
				}
			}
		}

		$query = $db->getQuery(true);

		$query->select('*')
			->from($db->quoteName('#__redirect_links'))
			->where(
				'('
				. $db->quoteName('old_url') . ' = ' . $db->quote($url) . ' OR '
				. $db->quoteName('old_url') . ' = ' . $db->quote($urlRel) . ' OR '
				. $db->quoteName('old_url') . ' = ' . $db->quote($urlRootRel) . ' OR '
				. $db->quoteName('old_url') . ' = ' . $db->quote($urlRootRelSlash) . ' OR '
				. $db->quoteName('old_url') . ' = ' . $db->quote($urlWithoutQuery) . ' OR '
				. $db->quoteName('old_url') . ' = ' . $db->quote($urlRelWithoutQuery) . ' OR '
				. $db->quoteName('old_url') . ' = ' . $db->quote($orgurl) . ' OR '
				. $db->quoteName('old_url') . ' = ' . $db->quote($orgurlRel) . ' OR '
				. $db->quoteName('old_url') . ' = ' . $db->quote($orgurlRootRel) . ' OR '
				. $db->quoteName('old_url') . ' = ' . $db->quote($orgurlRootRelSlash) . ' OR '
				. $db->quoteName('old_url') . ' = ' . $db->quote($orgurlWithoutQuery) . ' OR '
				. $db->quoteName('old_url') . ' = ' . $db->quote($orgurlRelWithoutQuery)
				. ')'
			);

		$db->setQuery($query);

		$redirect = null;

		try
		{
			$redirects = $db->loadAssocList();
		}
		catch (Exception $e)
		{
			ExceptionHandler::render(new Exception(Text::_('PLG_SYSTEM_ADVANCEDREDIRECT_ERROR_DATABASE'), 500, $e));
		}

		$possibleMatches = array_unique(
			array(
				$url,
				$urlRel,
				$urlRootRel,
				$urlRootRelSlash,
				$urlWithoutQuery,
				$urlRelWithoutQuery,
				$orgurl,
				$orgurlRel,
				$orgurlRootRel,
				$orgurlRootRelSlash,
				$orgurlWithoutQuery,
				$orgurlRelWithoutQuery,
			)
		);

		foreach ($possibleMatches as $match)
		{
			if (($index = array_search($match, array_column($redirects, 'old_url'))) !== false)
			{
				$redirect = (object) $redirects[$index];

				if ((int) $redirect->published === 1)
				{
					break;
				}
			}
		}

		// A redirect object was found and, if published, will be used
		if ($redirect !== null && ((int) $redirect->published === 1))
		{
			if (!$redirect->header || (bool) JComponentHelper::getParams('com_redirect')->get('mode', false) === false)
			{
				$redirect->header = 301;
			}

			if ($redirect->header < 400 && $redirect->header >= 300)
			{
				$urlQuery    = $uri->getQuery();
				$oldUrlParts = parse_url($redirect->old_url);

				if ($urlQuery !== '' && empty($oldUrlParts['query']))
				{
					$redirect->new_url .= '?' . $urlQuery;
				}

				$dest = Uri::isInternal($redirect->new_url) || strpos($redirect->new_url, 'http') === false ?
					Route::_($redirect->new_url) : $redirect->new_url;

				// In case the url contains double // lets remove it
				$destination = str_replace(Uri::root() . '/', Uri::root(), $dest);

				// Always count redirect hits
				$redirect->hits++;

				try
				{
					$db->updateObject('#__redirect_links', $redirect, 'id');
				}
				catch (Exception $e)
				{
					// We don't log issues for now
				}

				$app->redirect($destination, (int) $redirect->header);
			}

			ExceptionHandler::render(new RuntimeException($error->getMessage(), $redirect->header, $error));
		}
		// No redirect object was found so we create an entry in the redirect table
		elseif ($redirect === null)
		{
			if ((bool) $params->get('collect_urls', 1))
			{
				if (!$params->get('includeUrl', 1))
				{
					$url = $urlRel;
				}

				$data = (object) array(
					'id'           => 0,
					'old_url'      => $url,
					'referer'      => $app->input->server->getString('HTTP_REFERER', ''),
					'hits'         => 1,
					'published'    => 0,
					'created_date' => Factory::getDate()->toSql()
				);

				try
				{
					$db->insertObject('#__redirect_links', $data, 'id');
				}
				catch (Exception $e)
				{
					ExceptionHandler::render(new Exception(Text::_('PLG_SYSTEM_ADVANCEDREDIRECT_ERROR_DATABASE'), 500, $e));
				}
			}

			// AdvancedRedirect Code Start
			if ($params->get('redirect_mode', 'auto') === 'static')
			{
				if (!empty($params->get('static_url', '')))
				{
					$newDestination = $params->get('static_url', '');
				}
			}
			elseif ($params->get('redirect_mode', 'auto') === 'url_hopping')
			{
				// Just remove the latest part of the URL
				$explode = explode('/', $url);
				$newDestination = str_replace(array_pop($explode), '', $url);
			}
			else
			{
				$uriObject       = Uri::getInstance($url);
				$routerObject    = CMSApplication::getInstance('site')->getRouter('site');

				try
				{
					$parsedUriObject = $routerObject->parse($uriObject);
				}
				catch (Exception $e)
				{
					$parsedUriObject = false;
				}

				// Check wether we can get the catid or an article id
				$categoryId = isset($parsedUriObject['catid']) ? (integer) $parsedUriObject['catid'] : false;
				$articleId  = isset($parsedUriObject['id']) ? (integer) $parsedUriObject['id'] : false;
				$option     = isset($parsedUriObject['option']) ? (string) $parsedUriObject['option'] : false;

				// Try to get the categoryID from the database using the articleID (when it exists)
				if (!$categoryId && !is_bool($articleId) && $option === 'com_content')
				{
					unset($categoryId);

					$queryCategoryId = $db->getQuery(true)
						->select('catid')
						->from($db->quoteName('#__content'))
						->where($db->quoteName('id') . ' = ' . $db->quote($articleId));

					$db->setQuery($queryCategoryId);

					try
					{
						$categoryId = $db->loadResult();
					}
					catch (Exception $e)
					{
						ExceptionHandler::render(new Exception(Text::_('PLG_SYSTEM_ADVANCEDREDIRECT_ERROR_DATABASE'), 500, $e));
					}
				}

				// Try to generate the redirect URL
				if (isset($categoryId) && $option === 'com_content')
				{
					$newDestination = Route::_(
						'index.php?option=' . $option . '&view=category&id=' . $categoryId
					);
				}
			}

			// Check if we create auto redirects
			if ((bool) $params->get('auto_redirects_create', 1))
			{
				$published = 0;

				// Check if the auto redirects should be published by default
				if ((bool) $params->get('auto_redirects_published', 0))
				{
					$published = 1;
				}

				$queryRedirectId = $db->getQuery(true)
					->select('id')
					->from($db->quoteName('#__redirect_links'))
					->where($db->quoteName('old_url') . ' = ' . $db->quote($url));
				$db->setQuery($queryRedirectId);

				$redirectId = 0;

				try
				{
					$redirectId = $db->loadResult();
				}
				catch (Exception $e)
				{
					ExceptionHandler::render(new Exception(Text::_('PLG_SYSTEM_ADVANCEDREDIRECT_ERROR_DATABASE'), 500, $e));
				}

				$data = (object) array(
					'id'            => $redirectId,
					'old_url'       => $url,
					'new_url'       => $newDestination,
					'referer'       => $app->input->server->getString('HTTP_REFERER', ''),
					'hits'          => 1,
					'published'     => $published,
					'modified_date' => Factory::getDate()->toSql(),
					'created_date'  => Factory::getDate()->toSql()
				);

				try
				{
					if ($redirectId != 0)
					{
						$db->updateObject('#__redirect_links', $data, 'id');
					}
					else
					{
						$db->insertObject('#__redirect_links', $data, 'id');
					}
				}
				catch (Exception $e)
				{
					// We don't log issues for now
				}
			}

			// Write the message and redirect
			$app->enqueueMessage(Text::_('PLG_SYSTEM_ADVANCEDREDIRECT_REDIRECT_MESSAGE'), 'message');
			$app->redirect($newDestination, 301);
			// AdvancedRedirect Code End
		}
		// We have an unpublished redirect object, increment the hit counter
		else
		{
			$redirect->hits++;

			try
			{
				$db->updateObject('#__redirect_links', $redirect, 'id');
			}
			catch (Exception $e)
			{
				// We don't log issues for now
			}
		}

		// Proxy to the previous exception handler if available, otherwise just render the error page
		if (self::$previousExceptionHandler)
		{
			call_user_func_array(self::$previousExceptionHandler, array($error));
		}
		else
		{
			ExceptionHandler::render($error);
		}
	}
}
