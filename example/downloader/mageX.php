<?php
/** Store and unset $argv so that mage.php will not execute */
$storedArgv = $argv;
unset($argv);

/**
 * Include mage.php and suppress output and php notices to the console
 * The goal is to pull in the __cli_Mage_Connect class while virtually
 * disabling mage.php
 */
$storedErrorReportingSetting = ini_get('error_reporting');
error_reporting(E_ERROR);
ob_start();
include 'mage.php';
ob_end_clean();

/** Restore Error Reporting to default settings */
error_reporting($storedErrorReportingSetting);

/** Restore $argv values */
$argv = $storedArgv;

class __cli_Mage_ConnectX extends __cli_Mage_Connect
{
    /**
     * This is a private method set in the parent class
     * It must be declared here as well
     * */
    private static $_instance;

    /**
     * I could not get parent::instance() to work, possibly
     * because of self::$_instance, so it is redeclared.
     * */
    public static function instance()
    {
        if(!self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }


    public function setIncludes()
    {
        parent::setIncludes();
        $this->setExtendedIncludes();
    }

    public function setExtendedIncludes()
    {
        if(defined('DEVELOPMENT_MODE')) {
            $libPath = PS . dirname(BP) . DS . 'lib';
        } else {
            $libPath = PS . BP . DS . 'downloader' . DS . 'lib';
        }
        $includePath = BP . DS . 'app' . $libPath . DS . 'local' . PS . get_include_path();
        set_include_path($includePath);
    }

    /*public function run()
    {
        $testClass = new My_Class_Name_Is_Here();
        $testClass->killAndOutput("this");
        parent::run();

    }*/

}

if(defined('STDIN') && defined('STDOUT') && (defined('STDERR'))) {
    __cli_Mage_ConnectX::instance()->init($argv)->run();
}
