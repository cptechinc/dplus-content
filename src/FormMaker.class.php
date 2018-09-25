<?php 
    class FormMaker extends HTMLWriter {
        
        private $formstring = '';
        private static $count = 0;
        private $openform;
        public $bootstrap = false;
        /* =============================================================
			CONSTRUCTOR FUNCTIONS 
		============================================================ */
        public function __construct($attr = '', $openform = true) {
            $this->bootstrap = new HTMLWriter();
            self::$count++;
            $this->formstring = $this->indent() . $openform ? $this->open('form', $attr) : '';
            $this->openform = $openform;
        }
        
        /* =============================================================
			GETTER FUNCTIONS 
		============================================================ */
        public function __call($name, $args){
            if (in_array($name, $this->closeable)) {
                if (!$args[1]) {
                    $this->formstring .= $this->open($name, $args[0]); // OPEN ONLY
                } else {
                    $this->formstring .= $this->create_element($name, $args[0], $args[1]); // CLOSE ONLY
                }
            } elseif (in_array($name, $this->emptytags)) {
                $this->formstring .= $this->open($name, $args[0]);    
            } else {
                $this->error("This element $name is not defined to be called as a closing or open ended element");
                return false;
            }
        }
        
        /* =============================================================
			CLASS FUNCTIONS 
		============================================================ */
        public function input($attr = '') {
            $this->formstring .= $this->indent() . parent::input($attr);
        }
        
        public function select($attr = '', array $keyvalues, $selectvalue = null) {
            $this->formstring .= $this->indent() . parent::select($attr, $keyvalues, $selectvalue);
        }
        
        public function button($attr = '', $content) {
            $this->formstring .= $this->indent() . parent::button($attr, $content);
        }
        
        public function add($str) {
            $this->formstring .= $str;
        }
        
        public function close($element = '') {
            $this->formstring .= parent::close($element);
        }
        
        public function finish() {
			if ($this->openform) {
				$this->formstring .= parent::close('form');
			}
            return $this->formstring;
        }
        
        public function _toString() {
            return $this->finish();
        }
        
        /** 
    	 * Makes a new line and adds four spaces to format a string in html
    	 * @return string new line and four spaces
    	 */
    	public function indent() {
    		$indent = "\n";
    		for ($i = 0; $i < self::$count; $i++) {
    			$indent .= '  ';
    		}
    		return $indent;
    	}
    }
