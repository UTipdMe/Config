<?php

use Utipd\Config\ConfigLoader;
use \Exception;
use \PHPUnit_Framework_Assert as PHPUnit;
use \PHPUnit_Framework_TestCase;

/*
* 
*/
class ConfigVersionsTest extends PHPUnit_Framework_TestCase
{


    public function testVersions() {
        // clear any leftover caches to start
        $config_loader = new ConfigLoader(TEST_PATH.'/etc/config-sample', TEST_PATH.'/var/cache', '-ver1', false);
        $config_loader->clearCache('environment-overrides.yaml');
        $config_loader2 = new ConfigLoader(TEST_PATH.'/etc/config-sample', TEST_PATH.'/var/cache', '-ver2', false);
        $config_loader2->clearCache('environment-overrides.yaml');

        putenv("KEY_1=ver1");

        $config_loader = new ConfigLoader(TEST_PATH.'/etc/config-sample', TEST_PATH.'/var/cache', '-ver1', false);
        $data = $config_loader->loadYamlFile('environment-overrides.yaml');
        PHPUnit::assertEquals('ver1', $data['key1']);

        // make sure file exists
        PHPUnit::assertFileExists(TEST_PATH.'/var/cache/environment-overrides.yaml-ver1.php');

        // version 2
        putenv("KEY_1=ver2");
        $config_loader2 = new ConfigLoader(TEST_PATH.'/etc/config-sample', TEST_PATH.'/var/cache', '-ver2', false);
        $data = $config_loader2->loadYamlFile('environment-overrides.yaml');
        PHPUnit::assertEquals('ver2', $data['key1']);
        PHPUnit::assertFileExists(TEST_PATH.'/var/cache/environment-overrides.yaml-ver2.php');

        // reload version 1
        $config_loader = new ConfigLoader(TEST_PATH.'/etc/config-sample', TEST_PATH.'/var/cache', '-ver1', false);
        $data = $config_loader->loadYamlFile('environment-overrides.yaml');
        PHPUnit::assertEquals('ver1', $data['key1']);

        // cleanup
        $config_loader->clearCache('environment-overrides.yaml');
        $config_loader2->clearCache('environment-overrides.yaml');
    } 

    ////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////

}
