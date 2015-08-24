<?php

require_once dirname(__FILE__) . '/module_utils.php';
require_once dirname(__FILE__) . '/module_exception.php';
require_once dirname(__FILE__) . '/module_source.php';

define('MODULE_VERSION', '0.0.1');
! defined('MODULE_PATH') AND define('MODULE_PATH', './application/modules');

class Module_CLI {

    private static $commands = array(
        'help' => 'help',
        'install' => 'install',
//        'list' => 'lister',
        'reinstall' => 'reinstall',
        'remove' => 'remove',
//        'search' => 'search',
        'sources' => 'sources',
//        'upgrade-system' => 'upgrade_system',
        'version' => 'version',
        '' => 'index' // default action
    );

    function __construct($spark_sources)
    {
        $this->spark_sources = $spark_sources;
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
        Module_utils::line('Module (v' . MODULE_VERSION . ')');
        Module_utils::line('For help: `php tools/module help`');
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

    private function version()
    {
        Module_utils::line(MODULE_VERSION);
    }

    private function help()
    {
        Module_utils::line('install         # Install a spark');
        Module_utils::line('reinstall       # Reinstall a spark');
        Module_utils::line('remove          # Remove a spark');
//        Module_utils::line('list            # List installed sparks');
//        Module_utils::line('search          # Search for a spark');
//        Module_utils::line('sources         # Display the spark source URL(s)');
//        Module_utils::line('upgrade-system  # Update Sparks Manager to latest version (does not upgrade any of your installed sparks)');
        Module_utils::line('version         # Display the installed spark version');
        Module_utils::line('help            # Print This message');
    }

    private function search($args)
    {
        $term = implode($args, ' ');
        foreach($this->spark_sources as $source)
        {
            $results = $source->search($term);
            foreach ($results as $result)
            {
                $result_line = "\033[33m$result->name\033[0m - $result->summary";
                // only show the source information if there are multiple sources
                if (count($this->spark_sources) > 1) $result_line .= " (source: $source->url)";
                Module_utils::line($result_line);
            }
        }
    }

    private function sources()
    {
        foreach($this->spark_sources as $source)
        {
            Module_utils::line($source->get_url());
        }
    }

    private function failtown($error_message)
    {
        Module_utils::error('Uh-oh!');
        Module_utils::error($error_message);
    }

    private function remove($args)
    {

        list($flats, $flags) = $this->prep_args($args);

        if (count($flats) != 1)
        {
            return $this->failtown('Which module do you want to remove?');
        }
        $module_name = $flats[0];

        // figure out what to remove and make sure its isntalled
        $dir_to_remove = MODULE_PATH . "/$module_name" ;
        if (!file_exists($dir_to_remove))
        {
            return Module_utils::warning('Looks like that module isn\'t installed');
        }

        Module_utils::notice("Removing $module_name from $dir_to_remove");
        if (Module_utils::remove_full_directory($dir_to_remove, true))
        {
            Module_utils::notice('Module removed successfully!');
        }
        else
        {
            Module_utils::warning('Looks like that module isn\'t installed');
        }
        // attempt to clean up - will not remove unless empty
        @rmdir(MODULE_PATH . "/$module_name");
    }

    private function install($args)
    {

        list($flats, $flags) = $this->prep_args($args);

        if (count($flats) != 1)
        {
            return $this->failtown('format: `module install -v1.0.0 name`');
        }

        $module_name = $flats[0];
        $version = array_key_exists('v', $flags) ? $flags['v'] : 'HEAD';

        // retrieve the spark details
        foreach ($this->spark_sources as $source)
        {
            Module_utils::notice("Retrieving module detail from " . $source->get_url());
            $spark = $source->get_module($module_name, $version);
            if ($spark != null) break;
        }

        // did we find the details?
        if ($spark == null)
        {
            throw new Module_exception("Unable to find spark: $module_name ($version) in any sources");
        }

        // verify the spark, and put out warnings if needed
        $spark->verify();

        // retrieve the spark
        Module_utils::notice("From Downtown! Retrieving spark from " . $spark->location_detail());
        $spark->retrieve();

        // Install it
        $spark->install();
        Module_utils::notice('Spark installed to ' . $spark->installed_path() . ' - You\'re on fire!');
    }

    private function reinstall($args)
    {

        list($flats, $flags) = $this->prep_args($args);

        if (count($flats) != 1)
        {
            return $this->failtown('format: `spark reinstall -v1.0.0 name`');
        }

        $spark_name = $flats[0];
        $version = array_key_exists('v', $flags) ? $flags['v'] : null;

        if ($version == null && !array_key_exists('f', $flags))
        {
            throw new Module_exception("Please specify a version to reinstall, or use -f to remove all versions and install latest.");
        }

        $this->remove($args);
        $this->install($args);
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
