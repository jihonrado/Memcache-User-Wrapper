<?php

/**
 *
 *	Memcache User Wrapper (Unit Tests)
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

require_once('memcacheuserwrapper.php');

class ArrayTest extends PHPUnit_Framework_TestCase
{
	protected $memcache;

 	protected function setUp()
	{
		// Create instance
		$this->memcache = MemcacheUserWrapper::instance('my_user');
	}

 	protected function tearDown()
	{
		// Destroy instance
		unset($this->memcache);
	}

	public function testSetAndGet()
	{
		$key = __FUNCTION__;
		$value = 'my_value';

		// Store value
		$this->memcache->set($key, $value);

		// Get stored value
		$stored_value = $this->memcache->get($key);

		// Assert if obtain the value we stored
		$this->assertEquals($stored_value, $value);
	}

	public function testDelete()
	{
		$key = __FUNCTION__;
		$value = 'my_value';

		// Store value
		$this->memcache->set($key, $value);

		// Delete value
		$this->memcache->delete($key);

		// Get stored value
		$stored_value = $this->memcache->get($key);

		// Assert if obtain "false" as $key doesn't exists
		$this->assertFalse($stored_value);
	}

	public function testReplace()
	{
		$key = __FUNCTION__;
		$first_value = 'first_value';
		$second_value = 'second_value';

		// Store value
		$this->memcache->set($key, $first_value);

		// Replace value
		$this->memcache->replace($key, $second_value);

		// Get stored value
		$stored_value = $this->memcache->get($key);

		// Assert if obtain the second value
		$this->assertEquals($stored_value, $second_value);
	}

	public function testClearNamespace()
	{
		$key = __FUNCTION__;
		$value = 'my_value';

		// Store value
		$this->memcache->set($key, $value);

		// Replace value
		$this->memcache->clearNamespace();

		// Get stored value
		$stored_value = $this->memcache->get($key);

		// Assert if obtain "false" as $key doesn't exists
		$this->assertFalse($stored_value);
	}

	public function testNamespaces()
	{
		$key = __FUNCTION__;
		$value = 'my_value';

		// Store value
		$this->memcache->set($key, $value);

		// Create another instance
		$local_memcache = MemcacheUserWrapper::instance('another_user');

		// Get stored value new instance
		$stored_value = $local_memcache->get($key);

		// Assert if obtain different values
		$this->assertNotEquals($stored_value, $value);
	}

	public function testSingleton()
	{
		// Get instance using the same user identifier
		$local_memcache = MemcacheUserWrapper::instance('my_user');

		// Assert if obtain the same instance
		$this->assertEquals($local_memcache, $this->memcache);
	}

	public function testExpiryTime()
	{
		$key = __FUNCTION__;
		$value = 'my_value';
		$expiry_time = 1;

		// Store value
		$this->memcache->set($key, $value, 0, $expiry_time);
		
		sleep($expiry_time);

		// Get stored value
		$stored_value = $this->memcache->get($key);

		// Assert if obtain "false" as $key has expired
		$this->assertFalse($stored_value);
	}
}

?>