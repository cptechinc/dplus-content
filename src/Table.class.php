<?php

/**
 * Based off Kyle Gadd's Table class http://www.php-ease.com/classes/table.html
 * 	Modified to suit DPluso
 */
	class Table {
		use AttributeParser;

		/**
		 * After the first row in a table this $var stays true
		 * @var bool
		 */
		private $tropen = false;

		/**
		 * After the first cell in a row this $var stays true
		 * @var bool
		 */
		private $tdopen = false;

		/**
		 * After the first cell in a row this $var stays true
		 * @var bool
		 */
		private $thopen = false;
		
		/**
		 * Table Section that is open e.g. thead | tbody | tfoot
		 * @var string
		 */
		private $opensection = false;
		
		/**
		 * HTML for table
		 * @var string
		 */
		private $tablestring = '';
		private static $count = 0;

		/**
		 * Primary Constructor
		 * @param string $attr HTML attributes that the table will need
		 */
		public function __construct($attr = '') {
			self::$count++;
			$this->tablestring = $this->indent() . '<table' . $this->attributes($attr) . '>';
		}

		/**
		 * Add table section like thead | tbody | tfoot
		 * @param  string $section [Which table section to use tbody|thead|tfoot]
		 *
		 */
		public function tablesection($section = 'tbody') {
			$this->opensection = $section;
			$this->tablestring .= $this->indent() . $this->indent() . '<'.$section.'>';
			return $this;
		}

		/**
		 * Add closing element tag for table section e.g. thead | tbody | tfoot
		 * @param string $section [Which table section to close tbody|thead|tfoot]
		 */
		public function closetablesection($section) {
			$add = '';

			if ($this->tropen) {
				if ($this->opensection == 'thead') {
					$add .= '</th></tr>';
				} else {
					$add .= '</td></tr>';
				}
			}
			$this->opensection = false;
			$this->tropen = false;
			$this->tablestring .= $add . $this->indent() . '</'.$section.'>';
			return $this;
		}

		/**
		 * Add table row
		 * @param string $vars string of attribute values separated by |
		 */
		public function tr($vars = '') { // (across the board in every cell)
			$add = '';
			if ($this->tropen) {
				if ($this->opensection == 'thead') {
					$add .= '</th></tr>';
				} else {
					$add .= '</td></tr>';
				}
			}
			$this->tropen = true;
			$this->tdopen = false;
			$this->thopen = false;
			$this->tablestring .= $add . $this->indent() . '<tr' . $this->attributes($vars) . '>';
			return $this;
		}

		/**
		 * Add table cell
		 * @param string $vars    string of attribute values separated by |
		 * @param string $content Content that will be in the cell
		 */
		public function td($vars = '', $content = '&nbsp; ') {
			$add = '';
			if (!$this->tropen) $this->tr();
			if ($this->tdopen) $add .= '</td>';
			$this->tdopen = true;
			$this->tablestring .= $add . $this->indent() . '<td' . $this->attributes($vars) . '>' . $content;
			return $this;
		}

		/**
		 * add table header cell
		 * @param string $vars    string of attribute values separated by |
		 * @param string $content Content that will be in the cell
		 */
		public function th($vars = '', $content='') {
			$add = '';
			if (!$this->tropen) $add .= '<tr>';
			if ($this->thopen) $add .= '</th>';
			$this->thopen = true;
			$this->tablestring .= $add . $this->indent() . '<th' . $this->attributes($vars) . '>' . $content;
			return $this;
		}

		/**
		 * Cose element
		 * @param  string $element Element to close
		 * @return string          html element close
		 */
		public function tclose($element) {
			return '</'.$element.'>';
		}

		/**
		 * does some housekeeping closes out open rows and cells then closesout table string to return it
		 * @return string the tables string
		 */
		public function close() {
			$add = '';
			$add .= $this->indent() . '</table>' . "\n";
			self::$count--;
			$this->tablestring .= $add;
			return $this->tablestring;
		}

		/**
		 * Generates the celldata based of the column, column type and the json array it's in, looks at if the data is numeric
		 * @param string $type   the type of data D = Date, N = Numeric, string
		 * @param string $parent the array in which the data is contained
		 * @param string $column the key in which we use to look up the value
		 */
		static public function generatejsoncelldata($type, $parent, $column) {
			$celldata = '';
			if ($type == 'D') {
				if (strlen($parent[$column['id']]) > 0) {$celldata = date($column['date-format'], strtotime($parent[$column['id']]));} else {$celldata = $parent[$column['id']];}
			} elseif ($type == 'N') {
				if (is_string($parent[$column['id']])) {
					$celldata = number_format(floatval($parent[$column['id']]), $column['after-decimal']);
				} else {
					$celldata = number_format($parent[$column['id']], $column['after-decimal']);
				}
			} else {
				$celldata = $parent[$column['id']];
			}
			return $celldata;
		}

		/**
		 * Makes a new line and adds four spaces to format a string in html
		 * @return string new line and four spaces
		 */
		private function indent() {
			$indent = "\n";
			for ($i = 0; $i < self::$count; $i++) {
				$indent .= '  ';
			}
			return $indent;
		}
	}
