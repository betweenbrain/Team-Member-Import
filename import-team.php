<?php
PHP_SAPI === 'cli' or die();

/**
 * File       import-team.php
 * Created    12/1/14 11:07 AM
 * Author     Matt Thomas | matt@betweenbrain.com | http://betweenbrain.com
 * Support    https://github.com/betweenbrain/
 * Copyright  Copyright (C) 2014 betweenbrain llc. All Rights Reserved.
 * License    GNU GPL v2 or later
 */

// We are a valid entry point.
const _JEXEC = 1;

// Load system defines
if (file_exists(dirname(__DIR__) . '/defines.php'))
{
	require_once dirname(__DIR__) . '/defines.php';
}

if (!defined('_JDEFINES'))
{
	define('JPATH_BASE', dirname(__DIR__));
	require_once JPATH_BASE . '/includes/defines.php';
}

// Get the framework.
require_once JPATH_LIBRARIES . '/import.legacy.php';

// Bootstrap the CMS libraries.
require_once JPATH_LIBRARIES . '/cms.php';

// Configure error reporting to maximum for CLI output.
error_reporting(E_ALL);
ini_set('display_errors', 1);

/**
 * A command line cron job to attempt to remove files that should have been deleted at update.
 *
 * @package  Joomla.Cli
 * @since    3.0
 */
class ImportTeamCli extends JApplicationCli
{

	/**
	 * Constructor.
	 *
	 * @param   object &$subject The object to observe
	 * @param   array  $config   An optional associative array of configuration settings.
	 *
	 * @since   1.0.0
	 */
	public function __construct()
	{
		parent::__construct();
		$this->db = JFactory::getDbo();

		// Set JFactory::$application object to avoid system using incorrect defaults
		JFactory::$application = $this;
	}

	/**
	 * Entry point for CLI script
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function execute()
	{
		// Test of using config file
		/*
		include_once('import_config.php');

		foreach ($config as $url => $catdid)
		{
			$xml = $this->getFeed($url . '/feed');
			$this->saveItems($xml, 2);
		}
		*/
	}

	/**
	 * Read a CSV file and return it as a multidimensional array
	 *
	 * @return array
	 */
	public function readCSVFile($fileName)
	{
		return array_map('str_getcsv', file($fileName));
	}
}

// Instantiate the application object, passing the class name to JCli::getInstance
// and use chaining to execute the application.
JApplicationCli::getInstance('ImportTeamCli')->execute();