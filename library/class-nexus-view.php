<?php

class Nexus_View {

	private $_path;
	private $_arguments;

	public function __construct($path) {
		$this->_path = $path;
		$this->_arguments = new stdClass();
	}

	public function add($key, $value) {
		$this->$_arguments->{$key} = $value;
	}

	public function render() {

		$View = $this->_arguments;
		include($this->_path);

	}

}