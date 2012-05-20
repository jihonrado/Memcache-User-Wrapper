# Memcache User Wrapper

##Description
PHP class that wraps all the typical operations in memcache using pseudo-namespacing to have independent user data.

##Requirements
- Memcache server
- Memcache PHP extension

## Installation
Put the PHP class somewhere and include it wherever you need it.

	require_once('path/to/class/memcacheuserwrapper.php');

## Configuration
Edit memcacheuserwrapper.php and customize the constants to fit your memcache installation and preferences.

	const MEMCACHE_HOST = 'localhost';
	const MEMCACHE_PORT = 11211;
	const NS_CUSTOM_PREFIX = 'my_ns_';

## Usage

### Typical fetch operation
Try to get the data from memcache and if it is not there, fetch from DB or wherever.

	$userID = currentUserID();
	$memcache = MemcacheUserWrapper::instance($userID);
		
	$key = 'key_to_fetch';
	if (($items = $memcache->get($key)) === false) {
		// Obtain items from DB
		$items = db_query('SELECT foo FROM bar');
		
		// Store items in memcache
		$memcache->set($key, $items);
	}
	
	// Use $items

### Item replacement
When an item has changed we should call "replace" instead of "set".

	$userID = currentUserID();
	$memcache = MemcacheUserWrapper::instance($userID);

	// User changed one of his items
	$item = $newItem;

	// Update stored item
	$memcache->replace('item_x', $item);
	
### Item deletion
When an item has been deleted we must delete its cached data.

	$userID = currentUserID();
	$memcache = MemcacheUserWrapper::instance($userID);
	
	// User deleted one of his items
	$item->delete();

	// Delete cached data related to that item
	$memcache->delete('list_of_items');
	$memcache->delete('item_x');
	
### Setting expiry time and compression
When setting or replacing data we could specify its time-to-live (max. 30 days). Default is 0 (never expire).

	$userID = currentUserID();
	$memcache = MemcacheUserWrapper::instance($userID);

	// Store compressed $data for 1 week
	$data = obtainWeekReport();
	$memcache->set('my_week_report', $data, MEMCACHE_COMPRESSED, 20160);
	
### Clearing namespace
Instead of deleting all user data stored one by one, that even we may don't know all its keys, we "delete" all user data.

	$userID = currentUserID();
	$memcache = MemcacheUserWrapper::instance($userID);

	// Significant user data changed
	...

	// Invalidate all stored user data
	$memcache->clearNamespace();
	
# License
This code is distributed under the terms and conditions of the MIT license.

Copyright (c) 2012 Jose Ignacio Honrado

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.