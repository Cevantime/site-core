<?php

require_once dirname(__FILE__) . '/core_utils.php';
require_once dirname(__FILE__) . '/core_exception.php';
define('CORE_SOURCE', 'https://github.com/Cevantime/site-core.git');
class Core_CLI {

	public $module_sources;
	
    private static $commands = array(
        'help' => 'help',
        'install' => 'install',
		'update' => 'update',
//        'search' => 'search',
        'sources' => 'sources',
//        'upgrade-system' => 'upgrade_system',
//        'version' => 'version',
        '' => 'help' // default action
    );

    function __construct($core_sources)
    {
        $this->module_sources = $core_sources;
    }

    function execute($command, $args = array())
    {
        if (!array_key_exists($command, self::$commands))
        {
            $this->failtown("Unknown action: $command");
            return;
        }
        try
        {
            $method = self::$commands[$command];
            $this->$method($args);
        }
        catch (Exception $ex)
        {
            return $this->failtown($ex->getMessage());
        }
    }

    private function index($args)
    {
        Core_utils::line('Core (v' . CORE_VERSION . ')');
        Core_utils::line('For help: `php tools/module help`');
    }

//    private function upgrade_system() {
//        $tool_dir = dirname(__FILE__) . '/../../';
//        $tool_dir = realpath($tool_dir);
//        // Get version data
//        $source = $this->spark_sources[0];
//        if (!$source) throw new Module_exception('No sources listed - unsure how to upgrade');
//        if (!$source->outdated()) // We have an acceptable version
//        {
//           Module_utils::warning('Spark manager is already up to date');
//           return;
//        }
//        // Build a spark and download it
//        $data = null;
//        $data->name = 'Spark Manager';
//        $data->archive_url = $source->version_data->spark_manager_download_url;
//        $zip_spark = new Zip_spark($data);
//        $zip_spark->retrieve();
//        // Download the new version
//        // Remove the lib directory and the spark
//        unlink($tool_dir . '/spark');
//        Module_utils::remove_full_directory($tool_dir . '/lib');
//        // Link up the new version
//        Module_utils::full_move($zip_spark->temp_path . '/lib', $tool_dir . '/lib');
//        @rename($zip_spark->temp_path . '/spark', $tool_dir . '/spark');
//        @`chmod u+x {$tool_dir}/spark`;
//        // Tell the user the story of what just happened
//        Module_utils::notice('Spark manager has been upgraded to ' . $source->version . '!');
//    }

//    // list the installed sparks
//    private function lister()
//    {
//        if (!is_dir(SPARK_PATH)) return; // no directory yet
//        foreach(scandir(SPARK_PATH) as $item)
//        {
//            if (!is_dir(SPARK_PATH . "/$item") || $item[0] == '.') continue;
//            foreach (scandir(SPARK_PATH . "/$item") as $ver)
//            {
//                if (!is_dir(SPARK_PATH . "/$item/$ver") || $ver[0] == '.') continue;
//                Module_utils::line("$item ($ver)");
//            }
//        }
//    }

//    private function version()
//    {
//        Module_utils::line(CORE_VERSION);
//    }

    private function help()
    {
        Core_utils::line('install         # Install a spark');
        Core_utils::line('reinstall       # Reinstall a spark');
        Core_utils::line('remove          # Remove a spark');
//        Module_utils::line('list            # List installed sparks');
//        Module_utils::line('search          # Search for a spark');
        Core_utils::line('sources         # Display the spark source URL(s)');
//        Module_utils::line('upgrade-system  # Update Sparks Manager to latest version (does not upgrade any of your installed sparks)');
        Core_utils::line('version         # Display the installed spark version');
        Core_utils::line('help            # Print This message');
    }

//    private function search($args)
//    {
//        $term = implode($args, ' ');
//        foreach($this->module_sources as $source)
//        {
//            $results = $source->search($term);
//            foreach ($results as $result)
//            {
//                $result_line = "\033[33m$result->name\033[0m - $result->summary";
//                // only show the source information if there are multiple sources
//                if (count($this->module_sources) > 1) $result_line .= " (source: $source->url)";
//                Module_utils::line($result_line);
//            }
//        }
//    }

    private function sources()
    {
        
        Core_utils::line(CORE_SOURCE);
        
    }

    private function failtown($error_message)
    {
        Core_utils::error('Uh-oh!');
        Core_utils::error($error_message);
	}

    private function install($args)
    {
		$name = realpath(__DIR__.'/../../../');
		Core_utils::line('installing the module in ' . $name);
		$cmd = '';
		$cmd .= "git init $name;";
		$cmd = "cd $name;";

		$cmd .= 'git clone '.CORE_SOURCE.';';

		$cmd .= "git pull origin master";
		Core_utils::line("executing : $cmd");
		exec($cmd);
        

        if (!file_exists("$name/site-core/application")) {
            throw new Core_exception('Ooops. It seems that the core couldn\'t be installed');
        }

	Core_utils::full_move("$name/site-core", "$name/");
	Core_utils::remove_full_directory("$name/site-core");
        Core_utils::remove_full_directory("$name/.git");

        Core_utils::notice('Installation completed - You\'re on fire!');
    }
	
	
    /**
     * Prepares the command line arguments for use.
     *
     * Usage:
     * list($flats, $flags) = $this->prep_args($args);
     *
     * @param   array   the arguments array
     * @return  array   the flats and flags
     */
    private function prep_args($args)
    {

        $flats = array();
        $flags = array();

        foreach($args as $arg)
        {
            preg_match('/^(\-?[a-zA-Z])([^\s]*)$/', $arg, $matches);
            if (count($matches) != 3) continue;
            $matches[0][0] == '-' ? $flags[$matches[1][1]] = $matches[2] : $flats[] = $matches[0];
        }

        return array($flats, $flags);
    }

}
