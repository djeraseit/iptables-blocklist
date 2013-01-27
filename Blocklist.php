<?php
/**
 * The Blocklist class will allow you to parse a blocklist file in the format <name>:<iprange>
 * into an iptables dump file which can then be loaded with the iptables-restore tool or
 * by copy/pasting or appending the lines to your current config.
 *
 * @version 1.0
 * @copyright (c) 2013, Jan Dorsman
 *
 * Licensed under the The MIT License (MIT) - http://www.opensource.org/licenses/MIT
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated
 * documentation files (the "Software"), to deal in the Software without restriction, including without
 * limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the
 * Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions
 * of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO
 * THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
 * TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
 * DEALINGS IN THE SOFTWARE. 
 */

class Blocklist {
	
	## START OF CONFIG, FEEL FREE TO EDIT ANY OF THE BELOW VALUES TO MATCH YOUR NEEDS ##
	
	/**
	 * Whether or not the blocklist file is compressed and if the output file should be compressed.
	 *
	 * @var bool
	 */
	public $compressed = 1;
	
	/**
	 * The name of the file in which to dump the iptables rules.
	 *
	 * @var string
	 */
	public $iptablesConfig = 'firewall_rules.dump.gz';
	
	/**
	 * The name of the iptables chain to which you want to add the rules (default: INPUT).
	 *
	 * @var string
	 */
	public $iptablesChain = 'INPUT';
	
	/**
	 * Extra options to add to the rules (e.g. interface bindings, destinations)
	 * 
	 * @var string
	 */
	public $iptablesExtraOpts = '-i eth0';

	## END OF CONFIG, ONLY CHANGE ANYTHING BELOW THIS LINE IF YOU WANT TO ALTER THE OUTPUT ##
	
	/**
	 * These variables will hold the file handlers for the blocklist, iptables config and skeleton.
	 */
	public $bl;
	public $ic;

	/**
	 * The class constructor.
	 * 
	 * @throws Exception
	 * @return void
	 */
	public function __construct() {
		// Make sure our config file is writable
		touch($this->iptablesConfig);
		if (!is_writable($this->iptablesConfig)) {
			throw new Exception('The iptables config file is not writable. Check ownership and permissions.');
		}
		
		// If compression is used, use the zlib stream
		if ($this->compressed) {
			$this->iptablesConfig = 'compress.zlib://' . $this->iptablesConfig;
		}
		
		// Create the file handles
		$this->ic = fopen($this->iptablesConfig, 'w');
	}
	
	/**
	 * The class destructor.
	 * 
	 * @return void
	 */
	
	public function __destruct() {
		// Close the iptables config file handle
		fclose($this->ic);
	}
	
	/**
	 * This function will parse the blocklist.
	 * 
	 * @param string $blocklist The blocklist file to parse.
	 *  
	 * @throws Exception
	 * @return void
	 */
	public function parse($blocklist) {
		// If a specific blocklist file was passed use this instead
		if (!is_readable($blocklist)) {
			throw new Exception('Unable to read blocklist file: ' . $blocklist . '. Check ownership and permissions.');
		}
		
		// If compression is used, use the zlib stream
		if ($this->compressed) {
			$blocklist = 'compress.zlib://' . $blocklist;
		}
		
		// Open the file
		$this->bl = fopen($blocklist, 'r');
		
		// Read all the lines in the blocklist file
		while ($line = fgets($this->bl)) {
			// Make sure the line starts with something alphanumeric (no whitespaces or comment hashes)
			if (preg_match('/^[^\s#]/', $line) === 1) {
				// First, trim the line to drop any newlines
				$line = trim($line);
				
				// Split the line on the colon (:)
				list($name, $ipRange) = explode(':', $line);
				
				// Create iptables block line
				fwrite($this->ic, '-A ' . $this->iptablesChain . ' ' . $this->iptablesExtraOpts . ' -s ' . $ipRange . ' -j DROP' . PHP_EOL);
			}
		}
		
		// All done, close the blocklist file handle
		fclose($this->bl);
	}
}
