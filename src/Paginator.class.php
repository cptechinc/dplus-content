<?php
	/**
	 * Class for dealing with Pagination for AJAX or non AJAX pages
	 */
	class Paginator {
		use AttributeParser;

		/**
		 * Page Number
		 * @var int
		 */
		public $pagenbr;

		/**
		 * Number of Items to paginate for
		 * @var int
		 */
		public $count;

		/**
		 * Page URL
		 * @var Purl\Url
		 */
		public $pageurl;

		/**
		 * AJAX DATA
		 * @var string
		 */
		public $ajaxdata;

		/**
		 * Where to insert pagination path segment
		 * @var string
		 */
		public $insertafter;

		/**
		 * CONSTRUCTOR
		 * @param int     $pagenbr     What Page Number the Page is on
		 * @param int     $count       Number of items used to determine the number of pages needed
		 * @param string  $pageurl     pageurl to manipulate
		 * @param string  $insertafter if there isn't a urlsegmnet with page(1,3) in it then
		 * @param string  $ajaxdata    String with ajaxdata needed like data-focus='#ajax'
		 */
		public function __construct($pagenbr, $count, $pageurl, $insertafter, $ajaxdata = false) {
			$this->pagenbr = $pagenbr;
			$this->count = $count;
			$this->pageurl = new \Purl\Url($pageurl);
			$this->insertafter = $insertafter;
			$this->ajaxdata = $this->parse_ajaxdata($ajaxdata);

			$this::setup_displayonpage();
		}

		/* =============================================================
			CLASS FUNCTIONS
		============================================================ */
		/**
		 * This will take the pageurl and append the page number to it or editing the page number in the url
		 * @param  int $pagenbr
		 * @return string         url with page number
		 */
		public function paginate($pagenbr) {
			return self::paginate_url($this->pageurl, $pagenbr, $this->insertafter);
		}

		/**
		 * Creates the dropdown to show how many items are on a page
		 * @return string html select
		 */
		public function generate_showonpage() {
			$url = new \Purl\Url($this->pageurl);
			$url->query->remove('display');
			$href = $url->getUrl();
			$ajaxdata = $this->generate_ajaxdataforcontento();
			$bootstrap = new HTMLWriter();

			$form = $bootstrap->open('div', 'class=form-group');
			$form .= $bootstrap->label('', 'Results Per Page') . '&nbsp; &nbsp;';
			$form .= $bootstrap->open('select', 'class=form-control input-sm results-per-page|name=results-per-page');

			foreach (DplusWire::wire('config')->showonpageoptions as $val) {
				if ($val == DplusWire::wire('session')->display) {
					$form .= $bootstrap->create_element('option',"value=$val|selected", $val);
				} else {
					$form .= $bootstrap->create_element('option',"value=$val", $val);
				}
			}
			$form .= $bootstrap->close('select');
			$form .= $bootstrap->close('div');

			$ajaxload = $this->ajaxdata ? 'ajax-load' : '';
			return $bootstrap->form("action=$href|method=get|class=form-inline results-per-page-form $ajaxload|$ajaxdata", $form);
		}

		/**
		 * Creates the pagination navigation
		 * @return string html of the navigation through pages
		 */
		public function __toString() {
			$bootstrap = new HTMLWriter();
			$list = '';
			$totalpages = ceil($this->count / DplusWire::wire('session')->display);
			$totalpages = $totalpages == 0 ? 1 : $totalpages;

			if ($this->pagenbr == 1) {
				$link = $bootstrap->create_element('a', 'href=#|aria-label=Previous', '<span aria-hidden="true">&laquo;</span>');
				$list .= $bootstrap->create_element('li', 'class=disabled', $link);
			} else {
				$href = $this->paginate($this->pagenbr - 1);
				$ajaxdetails = (!empty($this->ajaxdata)) ? "class=load-link|".$this->generate_ajaxdataforcontento() : '';
				$link = $bootstrap->create_element('a', "href=$href|aria-label=Previous|$ajaxdetails", '<span aria-hidden="true">&laquo;</span>');
				$list .= $bootstrap->create_element('li', '', $link);
			}

			for ($i = ($this->pagenbr - 3); $i < ($this->pagenbr + 4); $i++) {
				if ($i > 0) {
					if ($this->pagenbr == $i) {
						$href = $this->paginate($i);
						$ajaxdetails = (!empty($this->ajaxdata)) ? "class=load-link|".$this->generate_ajaxdataforcontento() : '';
						$link = $bootstrap->a("href=$href|$ajaxdetails", $i);
						$list .= $bootstrap->li('class=active', $link);
					} elseif ($i < ($totalpages + 1)) {
						$href = $this->paginate($i);
						$ajaxdetails = (!empty($this->ajaxdata)) ? "class=load-link|".$this->generate_ajaxdataforcontento() : '';
						$link = $bootstrap->a("href=$href|$ajaxdetails", $i);
						$list .= $bootstrap->li('', $link);
					}
				}
			}

			if ($this->pagenbr == $totalpages) {
				$link = $bootstrap->a('href=#|aria-label=Next', '<span aria-hidden="true">&raquo;</span>');
				$list .= $bootstrap->li('class=disabled', $link);
			} else {
				$href = $this->paginate($this->pagenbr + 1);
				$ajaxdetails = (!empty($this->ajaxdata)) ? "class=load-link|".$this->generate_ajaxdataforcontento() : '';
				$link = $bootstrap->a("href=$href|aria-label=Next|$ajaxdetails", '<span aria-hidden="true">&raquo;</span>');
				$list .= $bootstrap->li('', $link);
			}
			$ul = $bootstrap->ul('class=pagination', $list);
			return $bootstrap->nav('class=text-center', $ul);
		}

		/* =============================================================
			STATIC FUNCTIONS
		============================================================ */
		/**
		* Find what page number the $url is on
		* @param  Purl\Url $url
		* @return int     Page Number
		*/
		public static function generate_pagenbr(\Purl\Url $url) {
			if (preg_match("((page)\d{1,3})", $url->path, $matches)) {
				return str_replace('page', '', $matches[0]);
			} else {
				return 1;
			}
		}

		/**
		 * Return URL after modifying it to change / include the page number
		 * @param  string $url         Destination URL
		 * @param  int    $pagenbr     Page Number to change / include in URL
		 * @param  string $insertafter URL segment to paginate after
		 * @return string              Paginated URL
		 */
		public static function paginate_url($url, $pagenbr, $insertafter) {
			if (strpos($url, 'page') !== false) {
				$regex = "((page)\d{1,3})";
				$replace = ($pagenbr > 1) ? $replace = "page".$pagenbr : "";
				$newurl = preg_replace($regex, $replace, $url);
			} else {
				$insertafter = str_replace('/', '', $insertafter)."/";
				$regex = "(($insertafter))";
				$replace = ($pagenbr > 1) ? $insertafter."page".$pagenbr."/" : $replace = $insertafter;
				$newurl = preg_replace($regex, $replace, $url);
			}
			return $newurl;
		 }

		/**
		 * Paginates the Purl\Url object by adding the page number to its path
		 * @param  Purl\Url $url         URL Object
		 * @param  int      $pagenbr     Page Number to page to
		 * @param  string   $insertafter Path Segment to insert the page number
		 * @return Purl\Url              Url object with the paginated path
		 */
		public static function paginate_purl(Purl\Url $url, $pagenbr, $insertafter) {
			$insertafter = trim($insertafter, '/');
			$path = $url->getPath();

			if (strpos($path, 'page') !== false) {
				$regex = "((page)\d{1,3})";
				$replace = ($pagenbr > 1) ? $replace = "page$pagenbr" : "";
				$newpath = preg_replace($regex, $replace, $path);
			} else {
				$regex = "(($insertafter))";
				$replace = ($pagenbr > 1) ? "{$insertafter}/page{$pagenbr}/" : $insertafter;
				$newpath = preg_replace($regex, $replace, $path);
			}

			$url->path = $newpath;
			return $url;
		}

		/**
		* Initializes the DplusWire::wire('session')->display value;
		*/
		public static function setup_displayonpage() {
			if (DplusWire::wire('input')->get->display) {
				DplusWire::wire('session')->display = DplusWire::wire('input')->get->text('display');
			} else {
				if (!DplusWire::wire('session')->display) {
					DplusWire::wire('session')->display = DplusWire::wire('config')->showonpage;
				}
			}
		}
	}
