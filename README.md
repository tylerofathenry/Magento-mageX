# Magento-mageX
Extend ./mage without modifying core Magento files

There still seems to be interest in extending Magento's mage file with new features while keeping Magento upgradeable. This is my solution for this.

This repository will contian the basic files and folder structure needed to implement this solution.

A use case for this is, and provided in this repository, is resolving Magento's cipher error (**failed: Unknown cipher in list: TLSv1**) when installing a module from commandline. Explained here: 

http://magento.stackexchange.com/questions/32101/extend-magento-downloader

Objective of project:

 - Extend ./mage without touching core files.

If you adhere to Magento's recommended file/folder permissions this shouldn't open up any vulnerabilities - I'd love an extra set of eyes on this to confirm this statement.

Once implemented, just replace ./mage with ./mageX when installing from the commandline.

i.e. `$ ./mageX install community Community_Package`

How to extend Magento's downloader autoload classes
= 

All file paths in the steps below reference the Magento root.

 1. Copy **./mage** file to **./mageX**
 2. In **./mageX**, replace `MAGE_PHP_SCRIPT="mage.php"` with `MAGE_PHP_SCRIPT="mageX.php"`
 3. Create this directory: **./downloader/lib/local** 
 4. Create a file called **./downloader/mageX.php** and place the following contents in that file:

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
                $testClass->killAndOutput("This");*/
                parent::run(); 
            }*/
        
        }
        
        if(defined('STDIN') && defined('STDOUT') && (defined('STDERR'))) {
            __cli_Mage_ConnectX::instance()->init($argv)->run();
        }



We're all set! **./downloader/lib/local** is now a referenced include directory. So, when you call `new My_Class_Name_Is_Here()` it will check 

1. **./app**/My/Class/Name/Is/Here.php
2. **./downloader/lib**/My/Class/Name/Is/Here.php
3. **./downloader/lib/local**/My/Class/Name/Is/Here.php

If you want to override the core file **./downloader/lib/Mage/HTTP/Client/Curl.php** just copy the file to **./downloader/lib/local/Mage/HTTP/Client/Curl.php**

A little more insight into ./mageX
==================================
The run() method does not need to be included as shown above. It can be removed. It is included as proof of concept. If you want to test it yourself go ahead and uncomment the run() method, create **./downloader/lib/local/My/Class/Name/Is/Here.php** and fill it with the contents below.


        <?php
        
        class My_Class_Name_Is_Here extends Mage_HTTP_Client_Curl
        {
            public function killAndOutput($text = "")
            {
                die($text);
            }
        }
