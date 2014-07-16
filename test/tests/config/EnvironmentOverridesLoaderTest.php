<?php

use Utipd\Config\ConfigLoader;
use \Exception;
use \PHPUnit_Framework_Assert as PHPUnit;
use \PHPUnit_Framework_TestCase;

/*
* 
*/
class EnvironmentOverridesLoaderTest extends PHPUnit_Framework_TestCase
{


    public function testWithoutEnvironment() {
        $config_loader = new ConfigLoader(TEST_PATH.'/etc/config-sample', TEST_PATH.'/var/cache', true);
        $data = $config_loader->loadYamlFile('environment-overrides.yaml');
        PHPUnit::assertEquals('foo', $data['key1']);

        // don't load allowedEnvironmentOverrides
        PHPUnit::assertArrayNotHasKey('allowedEnvironmentOverrides', $data);

        // cleanup
        $config_loader->clearCache('environment-overrides.yaml');
    } 

    public function testWithEnvironment() {
        putenv("KEY_1=bar");

        $config_loader = new ConfigLoader(TEST_PATH.'/etc/config-sample', TEST_PATH.'/var/cache', true);
        $data = $config_loader->loadYamlFile('environment-overrides.yaml');
        PHPUnit::assertEquals('bar', $data['key1']);

        // don't load allowedEnvironmentOverrides
        PHPUnit::assertArrayNotHasKey('allowedEnvironmentOverrides', $data);

        // cleanup
        $config_loader->clearCache('environment-overrides.yaml');
    } 

    public function testInheritanceWithEnvironment() {
        putenv("KEY_1=bar");
        putenv("KEY_2=bar");

        $config_loader = new ConfigLoader(TEST_PATH.'/etc/config-sample', TEST_PATH.'/var/cache', true);
        $data = $config_loader->loadYamlFile('environment-overrides-child.yaml');
        PHPUnit::assertEquals('bar', $data['key2']);
        PHPUnit::assertEquals('bar', $data['key1']);

        // don't load allowedEnvironmentOverrides
        PHPUnit::assertArrayNotHasKey('allowedEnvironmentOverrides', $data);

        // cleanup
        $config_loader->clearCache('environment-overrides-child.yaml');
    } 


    ////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////

}
