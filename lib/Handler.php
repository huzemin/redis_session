<?php
namespace OCA\RedisSession;

class Handler implements \SessionHandlerInterface
{
	protected $client;
	protected $ttl;

	/**
	 * @param \Redis $client  Fully initialized client instance.
	 * @param array           $options Session handler options.
	 */
	public function __construct(\Redis $client, array $options = array())
	{
		$this->client = $client;
		if (isset($options['gc_maxlifetime'])) {
			$this->ttl = (int) $options['gc_maxlifetime'];
		} else {
			$this->ttl = ini_get('session.gc_maxlifetime');
		}
	}
	/**
	 * Registers this instance as the current session handler.
	 */
	public function register()
	{
		if (PHP_VERSION_ID >= 50400) {
			session_set_save_handler($this, true);
		} else {
			session_set_save_handler(
				array($this, 'open'),
				array($this, 'close'),
				array($this, 'read'),
				array($this, 'write'),
				array($this, 'destroy'),
				array($this, 'gc')
			);
		}
	}
	/**
	 * {@inheritdoc}
	 */
	public function open($save_path, $session_id)
	{

		// NOOP
		return true;
	}
	/**
	 * {@inheritdoc}
	 */
	public function close()
	{
		// NOOP
		return true;
	}
	/**
	 * {@inheritdoc}
	 */
	public function gc($maxlifetime)
	{
		// NOOP
		return true;
	}
	/**
	 * {@inheritdoc}
	 */
	public function read($session_id)
	{
		if ($data = $this->client->get($session_id)) {
			return $data;
		}
		return '';
	}
	/**
	 * {@inheritdoc}
	 */
	public function write($session_id, $session_data)
	{
		$this->client->setex($session_id, $this->ttl, $session_data);
		return true;
	}
	/**
	 * {@inheritdoc}
	 */
	public function destroy($session_id)
	{
		$this->client->del($session_id);
		return true;
	}

	/**
	 * Returns the underlying client instance.
	 *
	 * @return \Redis
	 */
	public function getClient()
	{
		return $this->client;
	}

	/**
	 * Returns the session max lifetime value.
	 *
	 * @return int
	 */
	public function getMaxLifeTime()
	{
		return $this->ttl;
	}

	public function isAvailable() {
		return extension_loaded('redis')
			&& version_compare(phpversion('redis'), '2.2.5', '>=');
	}
}