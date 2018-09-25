<?php
	/**
	 * Class that generates HTML
	 */
	class HTMLMaker extends HTMLWriter {

		protected $content = '';
		protected $opened = array();

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
				   $content = isset($args[1]) ? $args[1] : false;

				   $this->content .= $this->create_element($name, $attr, $content);
			   }
		   }

		   /**
			* Creates HTML Element with the attributes if applicable
			* Will close the element if closeable, and will insert the content if needed
			* @param  string $element Element type ex. div
			* @param  string $attr    Attributes ex. class=sample-class btn|id=stuff
			* @param  string $content Content if applicable
			* @return string          Element
			*/
		   public function create_element($element, $attr = '', $content = '') {
			   if (in_array($element, $this->closeable)) {
				   $this->opened[] = $element;
				   if (is_bool($content)) {
					   return $this->open($element, $attr);
				   } else {
					   return $this->open($element, $attr) . $content . $this->close($element);
				   }
			   } elseif (in_array($element, $this->emptytags)) {
				   return $this->open($element, $attr);
			   } else {
				   $this->error("This element $element is not defined to be called as a closing or open ended element.");
				   return false;
			   }
		   }


			/* =============================================================
				ELEMENT SPECIFIC FUNCTIONS
			============================================================ */
			/**
			 * Returns HTML select element
			 * @param  string $attr        Attributes ex. class=sample-class btn|id=stuff
			 * @param  array  $keyvalues   Array of Key Values for the options
			 * @param  string $selectvalue Value to be selected
			 * @return string              Returns String to the code, or adds the string to $this->content
			 */
			public function select($attr = '', array $keyvalues, $selectvalue = '') {
				$options = '';

				foreach ($keyvalues as $key => $value) {
					$optionattr = "value=$key";
					$optionattr .= ($key == $selectvalue) ? "|selected=noparam" : '';
					$options .= $this->create_element('option',$optionattr, $value);
				}
				$this->content .=  $this->create_element('select', $attr, $options);
			}

			/**
			 * Makes an aria-hidden icon that can accept icons that need content
			 * @param  string $class   CSS Class needed for the icon
			 * @param  string $content optional
			 * @return string         <i class="[class]" aria-hidden="true">[content]</i>
			 */
			public function icon($class, $content = '') {
				$this->content .= parent::icon($class, $content);
			}

			/**
			 * Creates an aria-hidden span class
			 * @param  string $content
			 * @return string          <span aria-hidden="true">[content]</span>
			 */
			public function ariahidden($content) {
				$this->content .= parent::ariahidden($content);
			}

			/**
			 * Returns span that has the Bootstrap CSS class sr-only to be visible only to Screen Readers
			 * @param  string $content
			 * @return        <span class="sr-only">[content]</span>
			 */
			public function sronly($content = '') {
				$this->content .= parent::sronly($content);
			}

			/**
			 * Creates Bootstrap Alert Panel
			 * @param  string  $type      Alert type Ex. info|warning|danger|success
			 * @param  string  $msg       Message to display in the body
			 * @param  bool    $showclose Display close button?
			 * @return string             <div class="alert alert-$type" role="alert">[msg]</div>
			 */
			public function alertpanel($type, $msg, $showclose = true) {
				$this->content .= parent::alertpanel($type, $msg, $showclose);
			}

			/**
			 * Empty the content string
			 * @return void
			 */
			public function empty() {
				$this->content = '';
			}

			/**
			 * Returns $this->content while also emptying it out
			 * @return string Content
			 */
			public function _toString() {
				$content = $this->content;
				$this->empty();
				return $content;
			}
	}
