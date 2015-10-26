cd <?php

class Git_module extends Module_type {

    function __construct($name, $version, $base_location) {
        if (!self::git_installed()) {
            throw new Module_exception('You have to have git to install this spark.');
        }
        parent::__construct($name, $version, $base_location);
    }

    static function get_spark($data) {
        if (self::git_installed()) {
            return new Git_module($data);
        } else {
            Module_utils::warning('Git not found - reverting to archived copy');
            return new Zip_spark($data);
        }
    }

    private static function git_installed() {
        return !!`git`;
    }

    function location_detail() {
        return "Git repository at $this->base_location";
    }

    function retrieve() {
        Module_utils::line('installing the module ' . $this->name);
        if (!file_exists("$this->temp_path/$this->name/$this->version")) {
            $cmd = '';
            if (!file_exists($this->temp_path)) {
                $cmd .= "git init $this->temp_path;";
                $existed = false;
//                `git init $this->temp_path`;
            } else {
                $existed = true;
            }
            

            $cmd = "cd $this->temp_path;";

            if (!$existed) {
                $cmd .= "git remote add -f origin $this->base_location; git config core.sparseCheckout true;";
            }
            if (!file_exists("$this->temp_path/$this->name/$this->version")) {
                $cmd .= "echo \"$this->name/$this->version\" >> .git/info/sparse-checkout;";
            }

            $cmd .= "git pull origin master";
            Module_utils::line("executing : $cmd");
            exec($cmd);
        }

        if (!file_exists("$this->temp_path/$this->name/$this->version")) {
            throw new Module_exception('Ooops. It seems that the module or this version of the module doesn\'t exists');
        }

        Module_utils::full_move("$this->temp_path/$this->name/$this->version", "$this->temp_path");

        Module_utils::remove_full_directory("$this->temp_path/$this->name");
        Module_utils::remove_full_directory("$this->temp_path/.git");

        if (!file_exists($this->temp_path)) {
            throw new Module_exception('Failed to retrieve the module ;(');
        }

        return true;
    }

    function exists() {

        Module_utils::line('testing module existency');
        $cmd = '';
		Module_utils::line('os detected : '.(Module_utils::is_os('linux') ? 'linux' : 'windaube'));
		$sep = Module_utils::is_os('linux') ? ';' : '&';
        if (!file_exists($this->temp_path)) {
            $cmd .= "git init $this->temp_path $sep ";
//            `git init $this->temp_path`;
        }
//        `cd $this->temp_path; git remote add -f origin $this->base_location; git config core.sparseCheckout true; echo "$this->name/$this->version" >> .git/info/sparse-checkout; git pull origin master`;

        $cmd .= "cd $this->temp_path $sep git remote add -f origin $this->base_location $sep git config core.sparseCheckout true $sep (echo $this->name/$this->version)>>.git/info/sparse-checkout $sep git pull origin master";
        Module_utils::line("executing $cmd");
        exec($cmd);
        return file_exists("$this->temp_path/$this->name/$this->version");
    }

}
