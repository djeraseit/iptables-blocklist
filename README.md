iptables-blocklist
==================

A PHP class to parse IP blocklists into an iptables dump file.

Setup
-----

Before you start using the script, take a moment to setup the variables in the configuration section
of the Blocklist.php file. The variables that can be set are:

**$compressed**
Set this to 1 if your blocklists are (g)zipped. The script will use a zip streamer to read the lists.
Setting this option will also compress the iptables dump file that is generated, to save space.

**$iptablesConfig**
Set this to the name of the output file you want the script to generate.

**$iptablesChain**
This refers to the iptables chain to which you want to append the entries from the blocklist.
Usually, the default value of INPUT will do.

**$iptablesExtraOpts**
Set any additional options you want to be included in the rules as well, such as interface bindings.
The default value is set to bind all rules just to interface `eth0`, but this might differ or
not apply at all to your setup.

Usage
-----

Once you have the script setup, you will need to retrieve and save the blocklist(s) you want to your machine.
When you have done so, you can start using the script. From a PHP file, initiate the Blocklist class,
after that you can parse the blocklist(s) with the parse() method.

You can check the added update.php example script to see how to parse 2 blocklist files, called: `bt_level1.gz`
and `bt_level2.gz`.
