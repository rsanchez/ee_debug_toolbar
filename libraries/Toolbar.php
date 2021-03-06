<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

 /**
 * mithra62 - EE Debug Toolbar
 *
 * @package		mithra62:EE_debug_toolbar
 * @author		Eric Lamb
 * @copyright	Copyright (c) 2012, mithra62, Eric Lamb.
 * @link		http://mithra62.com/
 * @updated		1.0
 * @filesource 	./system/expressionengine/third_party/nagger/
 */

 /**
 * Toolbar Library
 *
 * Extension class
 *
 * @package 	mithra62:EE_debug_toolbar
 * @author		Eric Lamb
 * @filesource 	./system/expressionengine/third_party/ee_debug_toolbar/libraries/Toolbar.php
 */
class Toolbar
{	
	public function __construct()
	{
		$this->EE = &get_instance();
	}
	
	public function setup_files(array $files)
	{
		sort($files);
		
		$path_third = realpath(PATH_THIRD);
		$path_ee = realpath(APPPATH);
		$path_first_modules = realpath(PATH_MOD);
		$bootstrap_file = FCPATH.SELF;
		$return = array();
		foreach($files AS $file)
		{
			if(strpos($file, $path_third) === 0)
			{
				$return['third_party_addon'][] = $file;
				continue;
			}

			if(strpos($file, $path_first_modules) === 0)
			{
				$return['first_party_modules'][] = $file;
				continue;
			}

			if(strpos($file, $bootstrap_file) === 0)
			{
				$return['bootstrap_file'] = $file;
				continue;
			}			

			if(strpos($file, $path_ee) === 0)
			{
				$return['expressionengine_core'][] = $file;
				continue;
			}	

			$return['other_files'][] = $file;
			
		}
		
		return $return;
	}
	
	public function setup_queries()
	{
		$dbs = array();
	
		// Let's determine which databases are currently connected to
		foreach (get_object_vars($this->EE) as $EE_object)
		{
			if (is_object($EE_object) && is_subclass_of(get_class($EE_object), 'CI_DB') )
			{
				$dbs[] = $EE_object;
			}
		}

		$output = array();
		if (count($dbs) == 0)
		{
			return $output;
		}
	
		// Load the text helper so we can highlight the SQL
		$this->EE->load->helper('text');
	
		// Key words we want bolded
		$highlight = array('SELECT', 'DISTINCT', 'FROM', 'WHERE', 'AND', 'LEFT&nbsp;JOIN', 'ORDER&nbsp;BY', 'GROUP&nbsp;BY', 'LIMIT', 'INSERT', 'INTO', 'VALUES', 'UPDATE', 'OR&nbsp;', 'HAVING', 'OFFSET', 'NOT&nbsp;IN', 'IN', 'LIKE', 'NOT&nbsp;LIKE', 'COUNT', 'MAX', 'MIN', 'ON', 'AS', 'AVG', 'SUM', '(', ')');
	
		$count = 0;
		$total_time = 0;
		foreach ($dbs as $db)
		{
			//exit;			
			$count++;
				
			//$output .= "\n\n<table style='width:100%;{$hide_queries}' id='ci_profiler_queries_db_{$count}'>\n";
	
			if (count($db->queries) != 0)
			{
				foreach ($db->queries as $key => $val)
				{
					$total_time = $total_time+$db->query_times[$key];
					$time = number_format($db->query_times[$key], 4);
					$output['queries'][] = array('query' => highlight_code($val, ENT_QUOTES), 'time' => $time);
				}
			}
	
		}
		
		$output['total_time'] = number_format($total_time, 4);
		return $output;
	}

	public function setup_benchmarks()
	{
		$profile = array();
		foreach ($this->EE->benchmark->marker as $key => $val)
		{
			// We match the "end" marker so that the list ends
			// up in the order that it was defined
			if (preg_match("/(.+?)_end/i", $key, $match))
			{
				if (isset($this->EE->benchmark->marker[$match[1].'_end']) AND isset($this->EE->benchmark->marker[$match[1].'_start']))
				{
					$profile[$match[1]] = $this->EE->benchmark->elapsed_time($match[1].'_start', $key);
				}
			}
		}
		
		return $profile;		
	}
	
	/**
	 * Format a number of bytes into a human readable format.
	 * Optionally choose the output format and/or force a particular unit
	 *
	 * @param   int     $bytes      The number of bytes to format. Must be positive
	 * @param   string  $format     Optional. The output format for the string
	 * @param   string  $force      Optional. Force a certain unit. B|KB|MB|GB|TB
	 * @return  string              The formatted file size
	 */
	public function filesize_format($val, $digits = 3, $mode = "SI", $bB = "B"){ //$mode == "SI"|"IEC", $bB == "b"|"B"
	
		$si = array("", "k", "M", "G", "T", "P", "E", "Z", "Y");
		$iec = array("", "Ki", "Mi", "Gi", "Ti", "Pi", "Ei", "Zi", "Yi");
		switch(strtoupper($mode)) {
			case "SI" :
				$factor = 1000;
				$symbols = $si;
				break;
			case "IEC" :
				$factor = 1024;
				$symbols = $iec;
				break;
			default :
				$factor = 1000;
				$symbols = $si;
				break;
		}
		switch($bB) {
			case "b" :
				$val *= 8;
				break;
			default :
				$bB = "B";
				break;
		}
		for($i=0;$i<count($symbols)-1 && $val>=$factor;$i++) {
			$val /= $factor;
		}
		$p = strpos($val, ".");
		if($p !== false && $p > $digits) {
			$val = round($val);
		} elseif($p !== false) {
			$val = round($val, $digits-$p);
		}
	
		return round($val, $digits) . " " . $symbols[$i] . $bB;
	}	
}