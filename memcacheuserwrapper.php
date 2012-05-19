<?php 

/**
 *
 *	Memcache User Wrapper
 *	PHP class that wraps all the typical operations in memcache using pseudo-namespacing to have
 *	independent user data.
 *	https://bitbucket.org/jihonrado/memcache-user-wrapper
 *
 *	This code is distributed under the terms and conditions of the MIT license.
 *
 *	Copyright © 2012 Jose Ignacio Honrado
 *
 *	Permission is hereby granted, free of charge, to any person obtaining a copy of this software and
 *	associated documentation files (the "Software"), to deal in the Software without restriction,
 *	including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense,
 *	and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so,
 *	subject to the following conditions:
 *
 *	The above copyright notice and this permission notice shall be included in all copies or substantial
 *	portions of the Software.
 *
 *	THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT 
 *	LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
 *	IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *	LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION 
 *	WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
 */

class MemcacheUserWrapper
{

	const MEMCACHE_HOST = 'localhost';
	const MEMCACHE_PORT = 11211;
	const NS_CUSTOM_PREFIX = 'my_ns_';

	// Singleton instances
	private static $instance = array();
	// User id for all stored data
	private $user_id = null;
	// Server connection status flag
	private $connected = false;
	// Memcache PHP object
	private $server = null;
	// Current namespace suffix
	private $ns_val = 0;

	/**
	* Singleton method
	*/
	public static function instance($user_id='default')
	{
		if (!self::$instance[$user_id])
		{
			self::$instance[$user_id] = new MemcacheUserWrapper($user_id);
		}
		return self::$instance[$user_id];
	}

	/**
	* Constructor
	*/
	private function __construct($user_id)
	{
		$this->user_id = $user_id;

		$this->connect();

		if ($this->connected)
		{
			$this->ns_val = $this->server->get($this->getKeyPrefix());

			// If namespace not set, initialize it
			if ($this->ns_val === false)
			{
				// Let's generating random numbers until we found a not used one
				do $random_num = rand(1, 10000);
				while ($this->server->get($this->getKeyPrefix().'_'.$random_num) == true);

				// Store random number used and namespace key
				$this->server->set($this->getKeyPrefix().'_'.$random_num, true);
				$this->server->set($this->getKeyPrefix(), $random_num);
			}
		}
	}

	/**
	* Destructor
	*/
	function __destruct()
	{
		// Close connection to memcache server
		$this->server->close();
	}

	/**
	* Connect to memcache server
	*/
	private function connect()
	{
		if ($this->connected) return;

		if (class_exists('Memcache'))
		{
			$this->connected = true;

			$this->server = new Memcache();
			@$this->server->connect(self::MEMCACHE_HOST, self::MEMCACHE_PORT) || $this->connected = false;
		}
	}

	/**
	* Returns the key prefix
	*
	* @return string key prefix
	*/
	private function getKeyPrefix()
	{
		return self::NS_CUSTOM_PREFIX . $this->user_id;
	}

	/**
	* Returns the full key (with prefix and suffix)
	*
	* @param string $key key of data to be retrieved
	* @return string full key
	*/
	private function getFullKey($key)
	{
		return $this->getKeyPrefix() . '_' . $this->ns_val . '_' . $key;
	}

	/**
	* Returns current user id
	*
	* @return string user id
	*/
	public function getUserId()
	{
		return $this->user_id;
	}

	/**
	* Reset namespace, which invalidates all stored data
	*
	* @return boolean true if success, false if error
	*/
	public function clearNameSpace()
	{
		if (!$this->connected) return false;

		// Increment key
		$result = $this->server->increment($this->getKeyPrefix());
		if ($result === false) return false;

		// Store new ns
		$this->ns_val = $result;

		return true;
	}

	/**
	* Get data from server for a specified key
	*
	* @param string $key key of data to be retrieved
	* @return mixed $data data stored, false if key not found or error
	*/
	public function get($key)
	{
		if (!$this->connected) return false;

		return $this->server->get($this->getFullKey($key));
	}

	/**
	* Set data on server for a specified key
	*
	* @param string $key key of data to be stored	
	* @param mixed $data data to be stored
	* @param boolean $flag memcache_set flag
	* @param int $expire expiry time
	* @return boolean true if data was stored, false if error
	*/
	public function set($key, $data, $expire=0, $flag=0)
	{
		if (!$this->connected) return false;

		return $this->server->set($this->getFullKey($key), $data, $flag, $expire);
	}
	
	/**
	* Replace data on server for a specified key
	*
	* @param string $key key of data to be stored	
	* @param mixed $data data to be stored
	* @param boolean $flag memcache_set flag
	* @param int $expire expiry time
	* @return boolean true if data was stored, false if key not found or error
	*/
	public function replace($key, $data, $expire=0, $flag=0)
	{
		if (!$this->connected) return false;

		return $this->server->replace($this->getFullKey($key), $data, $flag, $expire);
	}

	/**
	* Delete data on server for a specified key
	*
	* @param string $key key of data to be stored	
	* @return boolean true if data was deleted, false if key not found or error
	*/
	public function delete($key)
	{
		if (!$this->connected) return false;

		return $this->server->delete($this->getFullKey($key));
	}

}

?>