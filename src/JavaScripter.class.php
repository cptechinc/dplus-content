<?php
	/**
	 * Class for generating JavaScript
	 */
	class JavaScripter {
		/**
		 * Number of Tabs currently used
		 * @var string
		 */
		public $tabs = 0;
		
		/**
		 * Character Code for Tab
		 * @var string
		 */
		protected $tab = "\t";
		
		/**
		 * Character Code for New Line
		 * @var string
		 */
		protected $newline = "\n";
		
		/**
		 * JavaScript Data
		 * @var string
		 */
		protected $script = "";
		
		/**
		 * Return data?
		 * @var bool
		 */
		protected $return = true;
		
		/**
		 * Constructor
		 * @param bool $return Return data?
		 */
		public function __construct($return = true) {
			$this->return = $return;
		}
		
		/**
		 * Returns JavaScript Data
		 * @return string JavaScript Data
		 */
		public function __toString() {
			return $this->script;
		}
		
		/**
		 * Returns the $(function) { for the on page load javascript 
		 * @return string Javascript Data
		 */
		public function generate_onready() {
			$this->tabs++;
			$content = $this->generate_tabs() . '$(function() {' . $this->newline;
			$this->tabs++;
			
			if ($this->return) {
				return $content;
			} else {
				$this->script .= $content;
			}
		}
		
		/**
		 * Generates a Line for Javascript
		 * @param  string $line Code for the Line
		 * @return string	   JavaScript Data
		 */
		public function line($line) {
			$content = $this->generate_tabs() . $line . $this->newline;
			
			if ($this->return) {
				return $content;
			} else {
				$this->script .= $content;
			}
		}
		
		/**
		 * Generates Line for function call
		 * @param  string $function What Function to call
		 * @return string		   JavaScript Data
		 */
		public function generate_functioncall($function) {
			$content = $this->generate_tabs() . $function . $this->newline;
			$this->tabs++;
			
			if ($this->return) {
				return $content;
			} else {
				$this->script .= $content;
			}
		}
		
		/**
		 * Generates ClosingLine for function call
		 * @return string		   JavaScript Data
		 */
		public function close_functioncall() {
			$this->tabs--;
			$content = $this->generate_tabs() . '});' . $this->newline . $this->newline;
			
			if ($this->return) {
				return $content;
			} else {
				$this->script .= $content;
			}
		}
		
		/**
		 * Adds tab indentation
		 * @return void
		 */
		public function generate_tabs() {
			$content = "";
			if ($this->tabs) {
				for ($i = 0; $i < ($this->tabs + 1); $i++) {
					$content .= "\t";
				}
			}
			return $content;
		}
	}
