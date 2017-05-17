<?php

namespace PHPAnt\Core;

class AuthenticationWhitelistManager
{
	var $Configs = NULL;

	public function __construct($args) {
		$this->Configs = $args['AE']->Configs;
		$this->['AE']->log( "AuthenticationWhitelistManager"
						  , "Hello. I exist."
						  );
	}

	private function getList() {
		return json_decode($this->Configs->getConfigs(['uri-whitelist'])['uri-whitelist']);
	}

	public function add($regex) {

		//Get the existing list.
		$list = $this->getList();

		if(is_null($list)) $list = [];

		//Add to it.
		array_push($list, $regex);

		//Save it.
		$this->save($list);

	}

	private function errorOut($message) {
		print $message . PHP_EOL;
		return ['success' => false];
	}

	public function remove($num) {

		if(!is_numeric($num)) return $this->errorOut("You must tell me the numeric index of the regex to delete. Try 'authentication uri whitelist show' to get a list to chose from, then execute 'authentication uri whitelist remove X' where 'X' is the index from the 'show' command.");

		//get the existing list.
		$list = $this->getList();

		if($num > count($list)) return $this->errorOut("You've entered a number that is bigger than the size of the URI Whitelist. Try again with a number that actually appears in 'authentication uri whitelist show'");

		$regex = $list[$num];

		print "Removing entry $num ($regex) from URI Whitelist Registry" . PHP_EOL;

		//Find the one we want to remove.
		array_splice($list, $num,1);

		//Save it.
		$this->save($list);

	}

	public function show() {
		$list = $this->getList();	
		$TL = new TableLog();
		$TL->setHeader(['num','URI Regex']);
		for($x = 0; $x < count($list); $x++) {
			$TL->addRow([$x,$list[$x]]);
		}
		$TL->showTable();
	}

	private function save($list) {
		$this->Configs->setConfig('uri-whitelist',json_encode($list));
	}

	public function isWhitelisted($uri) {
		$list = $this->getList();

		foreach($list as $regex) {
			if(preg_match($regex, $uri)) return true;
		}

		return false;
	}
}