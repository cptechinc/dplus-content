<?php
	/**
	 * Class that generates HTML
	 */
	class HTMLWriter {
		use AttributeParser;
		use ThrowErrorTrait;

		/**
		 * Array of HTML elements that need a closing tag
		 * @var array
		 */
		protected $closeable = array(
			'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'span', 'i', 'b', 'strong', 'code', 'pre', 'small',
			'div', 'nav', 'ol', 'ul', 'li', 'button',
			'table', 'tr', 'td', 'th', 'thead', 'tbody', 'tfoot',
			'textarea', 'option', 'label', 'a', 'form', 'script',
			'header', 'footer', 'article'
		);

		/**
		 * Array of HTML elements that do not need a closing tag
		 * @var array
		 */
		protected $emptytags = array(
			'input', 'img', 'br', 'hr'
		);

	  /* =============================================================
		  GETTER FUNCTIONS
	  ============================================================ */
		  /**
			* If a property is not accessible then try to give them the property through
			* a already defined method or to give them the property value
			* @param  string $property property name
			* @return
			*	  1. Value returned in value call
			*	  2. Returns the value of the property_exists
			*	  3. Throw Error
			*/
			public function __get($property) {
			  $method = "get_{$property}";
			  if (method_exists($this, $method)) {
				  return $this->$method();
			  } elseif (property_exists($this, $property)) {
				  return $this->$property;
			  } else {
				  $this->error("This property ($property) does not exist");
				  return false;
			  }
			}

		/* =============================================================
			CLASS FUNCTIONS
		============================================================ */
			/**
			* For element making functions that doesn't have a function defined, this
			* will handle the element by invoking the create_element function on that element name and providing the
			* parameters given to that function call
			*
			* @param  string $name Name of Element used
			* @param  array  $args array of parameters given to the attempted function call
			* @return string HTML Element
			*/
			public function __call($name, $args) {
				if (!method_exists($this, $name)) {
					$attr = isset($args[0]) ? $args[0] : '';
					$content = isset($args[1]) ? $args[1] : '';
					return $this->create_element($name, $attr, $content);
				}
			}

			/**
			* Creates HTML Element with the attributes if applicable
			* Will close the element if closeable, and will insert the content if needed
			* @param  string $element Element type ex. div
			* @param  string $attr	 Attributes ex. class=sample-class btn|id=stuff
			* @param  string $content Content if applicable
			* @return string			 Element
			*/
			public function create_element($element, $attr = '', $content = '') {
				if (in_array($element, $this->closeable)) {
					return $this->open($element, $attr) . $content . $this->close($element);
				} elseif (in_array($element, $this->emptytags)) {
					return $this->open($element, $attr);
				} else {
					$this->error("This element $element is not defined to be called as a closing or open ended element.");
					return false;
				}
			}

			/**
			* Creates a non closing element tag
			* @param  string $element  element type
			* @param  string $attr	  Attributes ex. class=sample-class btn|id=stuff
			* @return string			  element tag
			*/
			public function open($element, $attr = '') {
				$attr = trim($this->attributes($attr));
				return empty($attr) ? "<$element>" : "<$element $attr>";
			}

			/**
			 * Closes provided element, or closes the current open element
			 * @param  string $element Element type
			 * @return string			 Closing Element Tag
			 */
			public function close($element = '') {
				return !empty($element) ? "</$element>" : '';
			}

			/**
			 * Returns four spaces for indentation
			 * @return string  Indentation
			 */
			public function indent() {
				return '	 ';
			}

			/* =============================================================
				ELEMENT SPECIFIC FUNCTIONS
			============================================================ */
			/**
			 * Returns HTML select element
			 * @param  string $attr         Attributes ex. class=sample-class btn|id=stuff
			 * @param  array  $keyvalues     Array of Key Values for the options
			 * @param  string $selectvalue  Value to be selected
			 * @return string               Returns String to the code, or adds the string to $this->content
			 */
			public function select($attr = '', array $keyvalues, $selectvalue = '') {
				$options = '';

				foreach ($keyvalues as $key => $value) {
					$optionattr = "value=$key";
					$optionattr .= ($key == $selectvalue) ? "|selected=noparam" : '';
					$options .= $this->create_element('option',$optionattr, $value);
				}
				$select = $this->create_element('select', $attr, $options);

				return $select;
			}

			/**
			 * Makes an aria-hidden icon that can accept icons that need content
			 * @param  string $class	CSS Class needed for the icon
			 * @param  string $content optional
			 * @return string			<i class="[class]" aria-hidden="true">[content]</i>
			 */
			public function icon($class, $content = '') {
				return $this->i("class=$class|aria-hidden=true", $content);
			}

			/**
			 * Creates an aria-hidden span class
			 * @param  string $content
			 * @return string			 <span aria-hidden="true">[content]</span>
			 */
			public function ariahidden($content) {
				return $this->span('aria-hidden=true', $content);
			}

			/**
			 * Returns span that has the Bootstrap CSS class sr-only to be visible only to Screen Readers
			 * @param  string $content
			 * @return		  <span class="sr-only">[content]</span>
			 */
			public function sronly($content = '') {
				return $this->span("class=sr-only", $content);
			}

			/**
			 * Creates Bootstrap Alert Panel
			 * @param  string  $type		Alert type Ex. info|warning|danger|success
			 * @param  string  $msg		 Message to display in the body
			 * @param  bool	 $showclose Display close button?
			 * @return string				 <div class="alert alert-$type" role="alert">[msg]</div>
			 */
			public function alertpanel($type, $msg, $showclose = true) {
				$attributes = "class=alert alert-$type|role=alert";
				$closebutton = $this->create_element('button', 'class=close|data-dismiss=alert|aria-label=Close', $this->span('aria-hidden=true', '&times;'));
				$content = ($showclose) ? $closebutton.$msg : $msg;
				return $this->div($attributes, $content);
			}

			/**
			 * Make HTML link to the printable page
			 * @param  string $href URL to the printable page
			 * @param  string $msg  Message to display in the link
			 * @return string		 <a href="[href]" class="h4" target="_blank"><i class="glyphicon glyphicon-print" aria-hidden="true"></i> [msg]</a>
			 */
			public function generate_printlink($href, $msg) {
				$attributes = "href=$href|class=h4|target=_blank";
				$content = $this->icon('glyphicon glyphicon-print') .' '. $msg;
				return $this->create_element('a', $attributes, $content);
			}

			/**
			 * Returns the HTML needed to make the datepicker
			 * @param  string $class	  classes to add to the datepicker input
			 * @param  string $name		Input Name
			 * @param  string $value	  Defined value if applicable
			 * @param  bool	$init		Add the class for JS to auto initialize?
			 * @return string				HTML datepicker
			 */
			public function datepicker($class = '', $name = '', $value = '', $init = false) {
				$class = empty($class) ? 'form-control input-sm date-input' : "$class date-input";
				
				$str = $this->open('div', 'class=input-group datepicker');
					$str .= $this->input("class=$class|name=$name|value=$value");
					$str .= $this->open('div', 'class=input-group-btn');
						$btncontent = $this->icon('glyphicon glyphicon-calendar').$this->sronly('Toggle Calendar');
						$str .= $this->button('type=button|class=btn btn-sm btn-default dropdown-toggle|data-toggle=dropdown', $btncontent);
						$str .= $this->open('div', 'class=dropdown-menu dropdown-menu-right datepicker-calendar-wrapper|role=menu');
						$str .= $this->generate_datepickercalendar();
						$str .= $this->close('div'); //dropdown-menu dropdown-menu-right datepicker-calendar-wrappe
					$str .= $this->close('div');
				$str .= $this->close('div');
				return $str;
			}

			/**
			 * Returns the HTML needed to make the calendar portion of the datepicker
			 * @return string HTML
			 */
			protected  function generate_datepickercalendar() {
				$str = $this->open('div', 'class=datepicker-calendar');
					$str .= $this->open('div', 'class=datepicker-calendar-header');
						$str .= $this->button('type=button|class=prev', $this->icon('glyphicon glyphicon-chevron-left').$this->sronly('Previous Month'));
						$str .= $this->button('type=button|class=next', $this->icon('glyphicon glyphicon-chevron-right').$this->sronly('Next Month'));
						$str.= $this->open('button', 'type=button|class=title');
							$str.= $this->open('span', 'class=month');
								for ($i = 0; $i < 12; $i++) {
									$str .= $this->span("data-month=$i", date('F', mktime(0, 0, 0, ($i + 1), 10)));
								}
							$str .= $this->close('span') . '&nbsp;';
							$str .= $this->span('class=year');
						$str .= $this->close('button');
					$str .= $this->close('div'); // datepicker-calendar-header
					$str .= $this->generate_weektable();
					$str .= $this->create_element('div', 'class=datepicker-calendar-footer', $this->button('type=button|class=date-picker-today', 'Today'));
				$str .= $this->close('div'); // datepicker-calendar
				$str .= $this->generate_datepickerwheels();
				return $str;
			}

			/**
			 * Returns the HTML for the week table
			 * @return string HTML TABLE
			 */
			protected function generate_weektable() {
				$tb = new Table('class=datepicker-calendar-days');
				$tb->tablesection('thead');
					$tb->tr();
					$tb->th('', 'Su')->th('','Mo')->th('','Tu')->th('','We')->th('','Th')->th('','Fr')->th('','Sa');
				$tb->closetablesection('thead');
				$tb->tablesection('tbody');
				$tb->closetablesection('tbody');
				return $tb->close();
			}

			/**
			 * Returns the HTML needed to make the date picker wheels
			 * @return string HTML
			 */
			protected function generate_datepickerwheels() {
				$str = $this->open('div', 'class=datepicker-wheels|aria-hidden=true');
					$str .= $this->open('div', 'class=datepicker-wheels-month');
						$str .= $this->h2('class=header', 'Month');
						$str .= $this->open('ul', '');
						for ($i = 0; $i < 12; $i++) {
							$str .= $this->li("data-month=$i", $this->button('type=button', date('F', mktime(0, 0, 0, ($i + 1), 10))));
						}
						$str .= $this->close('ul');
					$str.= $this->close('div');

					$str .= $this->open('div', 'class=datepicker-wheels-year');
						$str .= $this->h2('class=header', 'Year');
						$str .= $this->ul('', '');
					$str.= $this->close('div');

					$str .= $this->open('div', 'class=datepicker-wheels-footer clearfix');
						$str .= $this->button('class=btn datepicker-wheels-back', $this->icon('glyphicon glyphicon-arrow-left') . $this->sronly('Return to Calendar'));
						$str .= $this->button('class=btn datepicker-wheels-select', 'Select ' . $this->sronly('Return to Calendar'));
					$str.= $this->close('div');
				$str.= $this->close('div');
				return $str;
			}
			
			public static function get_justifyclass($justification = 'left') {
				$justifications = array(
					'left' => 'text-left',
					'right' => 'text-right',
					'center' => 'text-center',
				);
				$justiciation = empty($justiciation) ? 'left' : $justification;
				return $justifications[$justification];
			}
	}
