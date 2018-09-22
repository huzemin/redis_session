<?php
namespace OCA\RedisSession;

use OC\SystemConfig;

class RedisFactory {
	/** @var  \Redis */
	private $instance;

	/** @var  SystemConfig */
	private $config;

	/**
	 * RedisFactory constructor.
	 *
	 * @param SystemConfig $config
	 */
	public function __construct(SystemConfig $config) {
		$this->config = $config;
	}

	private function create() {
		if ($config = $this->config->getValue('session.redis.cluster', [])) {
			if (!class_exists('RedisCluster')) {
				throw new \Exception('Redis Cluster support is not available');
			}
			// cluster config
			if (isset($config['timeout'])) {
				$timeout = $config['timeout'];
			} else {
				$timeout = null;
			}
			if (isset($config['read_timeout'])) {
				$readTimeout = $config['read_timeout'];
			} else {
				$readTimeout = null;
			}
			$this->instance = new \RedisCluster(null, $config['seeds'], $timeout, $readTimeout);

			if (isset($config['failover_mode'])) {
				$this->instance->setOption(\RedisCluster::OPT_SLAVE_FAILOVER, $config['failover_mode']);
			}
		} else {

			$this->instance = new \Redis();
			$config = $this->config->getValue('session.redis', []);
			if (isset($config['host'])) {
				$host = $config['host'];
			} else {
				$host = '127.0.0.1';
			}
			if (isset($config['port'])) {
				$port = $config['port'];
			} else {
				$port = 6379;
			}
			if (isset($config['timeout'])) {
				$timeout = $config['timeout'];
			} else {
				$timeout = 0.0; // unlimited
			}

			$this->instance->connect($host, $port, $timeout);
			if (isset($config['password']) && $config['password'] !== '') {
				$this->instance->auth($config['password']);
			}

			if (isset($config['dbindex'])) {
				$this->instance->select($config['dbindex']);
			}
		}
	}

	public function getInstance() {
		if (!$this->isAvailable()) {
			throw new \Exception('Redis support is not available');
		}
		if (!$this->instance instanceof \Redis) {
			$this->create();
		}

		return $this->instance;
	}

	public function isAvailable() {
		return extension_loaded('redis')
			&& version_compare(phpversion('redis'), '2.2.5', '>=');
	}
}
