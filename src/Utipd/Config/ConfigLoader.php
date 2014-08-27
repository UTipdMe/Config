<?php

namespace Utipd\Config;

use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Yaml\Yaml;
use Exception;

/*
* ConfigLoader
* This loads a yaml config file and caches it
* It supports extended yaml files
*/
class ConfigLoader
{

    protected $config_directory = null;
    protected $cache_directory = null;
    protected $suffix = null;
    protected $debug = false;

    public function __construct($config_directory, $cache_directory, $suffix=null, $debug=false) {
        $this->config_directory = $config_directory;
        $this->cache_directory = $cache_directory;
        $this->suffix = $suffix;
        $this->debug = $debug;
    }

    ////////////////////////////////////////////////////////////////////////

    public function loadYamlFile($name, $relative_dir=null) {
        $cache_path = $this->buildCacheFilepath($name, $relative_dir);
        $my_cache = new ConfigCache($cache_path, $this->debug); // debug mode is true
        if (!$my_cache->isFresh()) {
            $chain_entries = $this->parseConfigChain($name, $relative_dir);
            $data = $this->resolveConfigChain($chain_entries);
            $my_cache->write("<?php\n\n"."// generated ".date("Y-m-d H:i:s")."\n\n".'return '.var_export($data, true).';');
        }

        return require $cache_path;
    }

    public function clearCache($name, $relative_dir=null) {
        $chain_entries = $this->parseConfigChain($name, $relative_dir);
        foreach($chain_entries as $chain_entry) {
            $cache_filepath = $this->buildCacheFilepath($chain_entry['name'], $chain_entry['relative_dir']);
            if (file_exists($cache_filepath)) {
                unlink($cache_filepath);
            }
        }
    }

    ////////////////////////////////////////////////////////////////////////


    protected function parseConfigChain($name, $relative_dir) {
        $config_chain_entries = [];

        $data = Yaml::parse(file_get_contents($this->buildConfigFilepath($name, $relative_dir)));

        $my_entry = [
            'name'            => $name,
            'relative_dir'    => $relative_dir,
            'data'            => $data,
        ];

        if (isset($data['__extends'])) {
            unset($my_entry['data']['__extends']);

            // recursively load the chain and add this entry to the end
            $config_chain_entries = $this->parseConfigChain($data['__extends'], $relative_dir);
            $config_chain_entries[] = $my_entry;
        } else {
            $config_chain_entries = [$my_entry];
        }

        return $config_chain_entries;
    }

    protected function resolveConfigChain($config_chain_entries) {
        $data = [];
        foreach($config_chain_entries as $entry) {
            $data = array_replace_recursive($data, $entry['data']);
        }

        // resolve environment
        $data = $this->resolveEnvironmentOverrides($data);

        return $data;
    }

    protected function resolveEnvironmentOverrides($data) {
        if (isset($data['allowedEnvironmentOverrides'])) {
            foreach ($data['allowedEnvironmentOverrides'] as $env_var_name => $replacement_config_key) {
                $new_value = getenv($env_var_name);
                if ($new_value !== false) {
                    $data[$replacement_config_key] = $new_value;
                }
            }


            unset($data['allowedEnvironmentOverrides']);
        }

        return $data;
    }

    protected function buildCacheFilepath($name, $relative_dir) {
        return $this->cache_directory.(rtrim('/'.$relative_dir, '/')).'/'.$name.$this->suffix.'.php';
    }
    protected function buildConfigFilepath($name, $relative_dir) {
        return $this->config_directory.(rtrim('/'.$relative_dir, '/')).'/'.$name;
    }

}

