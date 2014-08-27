<?php

use Utipd\Config\ConfigLoader;
use \Exception;
use \PHPUnit_Framework_Assert as PHPUnit;
use \PHPUnit_Framework_TestCase;

/*
* 
*/
class ConfigTest extends PHPUnit_Framework_TestCase
{


    public function testConfigLoader() {
        $config_loader = new ConfigLoader(TEST_PATH.'/etc/config-sample', TEST_PATH.'/var/cache', null, true);
        $data = $config_loader->loadYamlFile('test.yaml');
        PHPUnit::assertEquals('bar', $data['greatData']['foo']);

        // cleanup
        $config_loader->clearCache('test.yaml');
    } 

    public function testConfigLoaderInheritance() {
        $config_loader = new ConfigLoader(TEST_PATH.'/etc/config-sample', TEST_PATH.'/var/cache', null, true);
        $data = $config_loader->loadYamlFile('child.yaml');
        PHPUnit::assertEquals('foo', $data['parent']);
        PHPUnit::assertEquals('bar-child', $data['shared']);
        PHPUnit::assertEquals('baz-child', $data['child']);

        // cleanup
        $config_loader->clearCache('child.yaml');
    } 

    public function testClearCache() {
        // not in debug mode
        $config_loader = new ConfigLoader(TEST_PATH.'/etc/config-sample', TEST_PATH.'/var/cache', null, false);
        $config_loader->clearCache('dynamic.yaml');

        $old_val = time()-1;
        $this->changeDynamicVar($old_val, TEST_PATH.'/etc/config-sample/dynamic.yaml');
        $data = $config_loader->loadYamlFile('dynamic.yaml');
        PHPUnit::assertEquals($old_val, $data['dynamic']);

        $new_val = time();
        $this->changeDynamicVar($new_val, TEST_PATH.'/etc/config-sample/dynamic.yaml');
        $config_loader->clearCache('dynamic.yaml');
        $data = $config_loader->loadYamlFile('dynamic.yaml');
        PHPUnit::assertEquals($new_val, $data['dynamic']);

        // cleanup
        $config_loader->clearCache('dynamic.yaml');
    }

    public function testHeirarchicalCacheExpiry() {
        $config_loader = new ConfigLoader(TEST_PATH.'/etc/config-sample', TEST_PATH.'/var/cache', null, false);
        $data = $config_loader->loadYamlFile('child.yaml');

        $old_val = time()-1;
        $this->changeDynamicVar($old_val, TEST_PATH.'/etc/config-sample/parent.yaml');
        $config_loader->clearCache('child.yaml');
        $data = $config_loader->loadYamlFile('child.yaml');
        PHPUnit::assertEquals($old_val, $data['dynamic']);


        // clear cache for child only
        $new_val = time();
        $this->changeDynamicVar($new_val, TEST_PATH.'/etc/config-sample/parent.yaml');
        $config_loader->clearCache('child.yaml');
        $data = $config_loader->loadYamlFile('child.yaml');
        PHPUnit::assertEquals($new_val, $data['dynamic']);

        // cleanup
        $config_loader->clearCache('child.yaml');
    } 

    ////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////

    protected function changeDynamicVar($val, $path) {
        $text = preg_replace('!dynamic\: (\d+)!', "dynamic: ".$val, file_get_contents($path));
        file_put_contents($path, $text);
    }

}
