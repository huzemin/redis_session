<?php
namespace OCA\RedisSession\AppInfo;

use OCA\RedisSession\RedisFactory;
use \OCP\AppFramework\App;
use OCA\RedisSession\Handler;

class Application extends App
{
	/**
	 * @var Boolean If enable redis session.
	 */
	private $enabledRedisSession;


	public function __construct()
	{
		 parent::__construct('redis_session');
		 $this->enabledRedisSession = \OC::$server->getSystemConfig()->getValue('session.redis.enabled', false);
	}

	public static function initSession()
	{
		try{
			$client = (new RedisFactory(\OC::$server->getSystemConfig()))->getInstance();
			if($client) {
				$sessionHandler = new Handler($client, \OC::$server->getSystemConfig()->getValue('session.options', []));
				$sessionHandler->register();
			}
		} catch (\Exception $e) {
			\OC::$server->getLogger()->error($e->getMessage());
		}
	}

	public function registerHooks()
	{
		if($this->enabledRedisSession) {
			\OCP\Util::connectHook('OC', 'initSession', $this, 'initSession');
		}
	}

}