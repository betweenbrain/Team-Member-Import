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
	 * For column name mapping
	 *
	 * @var null
	 */
	private $column = null;

	/**
	 * The CSV file
	 *
	 * @var null
	 */
	private $csvfile = null;

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

		if (!$this->input->get('file'))
		{
			$this->out('You must enter the name as follows:');
			$this->out(' --file foo.csv');
			exit;
		}

		$this->csvfile = $this->readCSVFile($this->input->get('file'));
		$this->column  = $this->mapColumnNames($this->csvfile);
	}

	/**
	 * Checks if an article already exists based on the article alias derived from the column "name"
	 *
	 * @param $article
	 *
	 * @return bool
	 */
	private function isDuplicate($article)
	{
		$query = $this->db->getQuery(true);
		$query
			->select($this->db->quoteName('id'))
			->from($this->db->quoteName('#__content'))
			->where($this->db->quoteName('alias') . ' = ' . $this->db->quote(JFilterOutput::stringURLSafe($article[$this->column->name])));
		$this->db->setQuery($query);

		return $this->db->loadResult() ? true : false;
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

		$this->out(JProfiler::getInstance('Application')->mark('Starting script.'));

		foreach ($this->csvfile as $row)
		{
			$this->saveItem($row);
		}

		$this->out(JProfiler::getInstance('Application')->mark('Finished script.'));

		// Load global config
		// $this->out(print_r($this->loadConfiguration(null), true));
	}

	/**
	 * Retrieve the admin user id.
	 *
	 * @return  int|bool One Administrator ID
	 *
	 * @since   3.2
	 */
	private function getAdminId()
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the updates table
		$query
			->clear()
			->select('u.id')
			->from('#__users as u')
			->join('LEFT', '#__user_usergroup_map AS map ON map.user_id = u.id')
			->join('LEFT', '#__usergroups AS g ON map.group_id = g.id')
			->where('g.title = ' . $db->q('Super Users'));

		$db->setQuery($query);
		$id = $db->loadResult();

		if (!$id || $id instanceof Exception)
		{
			return false;
		}

		return $id;
	}

	/**
	 * Read the first row of a CSV to create a name based mapping of column values
	 *
	 * @param $csvfile
	 *
	 * @return mixed
	 */
	private function mapColumnNames($csvfile)
	{
		$return = new stdClass;

		foreach ($csvfile[0] as $key => $value)
		{
			$return->{strtolower($value)} = $key;
		}

		return $return;
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

	/**
	 * Saves each non-duplicated item as a Joomla article
	 *
	 * @param $xml
	 * @param $catId
	 */
	private function saveItem($item)
	{

		// The item being imported is not a duplicate
		if (!$this->isDuplicate($item))
		{

			$this->out('Processing ' . $item[$this->column->name]);

			$table = JTable::getInstance('content', 'JTable');

			$date = JFactory::getDate();

			$article = array(
				'access'       => 1,
				'alias'        => JFilterOutput::stringURLSafe($item[$this->column->name]),
				'catid'        => '2',
				'created'      => $date->toSQL(),
				'created_by'   => $this->getAdminId(),
				'introtext'    => '',
				'language'     => '*',
				'metadata'     => '{"robots":"","author":"","rights":"","xreference":"","tags":null}',
				'publish_up'   => JFactory::getDate()->toSql(),
				'publish_down' => $this->db->getNullDate(),
				'state'        => 1,
				'title'        => $item[$this->column->name]
			);

			try
			{
				$this->out('Saving ' . $item[$this->column->name], true);

				$table->save($article);
			} catch (RuntimeException $e)
			{
				$this->out('Saving ' . $item[$this->column->name] . ' failed', true);

				$this->out($e->getMessage(), true);
				$this->close($e->getCode());
			}

			$this->out('Saving ' . $item[$this->column->name] . ' done', true);
		}
	}
}

// Instantiate the application object, passing the class name to JCli::getInstance
// and use chaining to execute the application.
JApplicationCli::getInstance('ImportTeamCli')->execute();