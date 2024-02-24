<?php

namespace KamaGlanceDashboardWidget;

class Section_Row {

	public $class = '';
	public $cap = 'manage_options';
	public $link = '';
	public $amount = 0;
	public $amount_text = '';

	/**
	 * Array of arrays.
	 *
	 * @var \stdClass[]
	 */
	public $extra = [];

	public function __construct( array $data ) {

		$this->class       = $data['class'] ?? $this->class;
		$this->cap         = $data['cap'] ?? $this->cap;
		$this->link        = $data['link'] ?? $this->link;
		$this->amount      = (int) ( $data['amount'] ?? $this->amount );
		$this->amount_text = $data['amount_text'] ?? $this->amount_text;
		$this->extra       = $data['extra'] ?? $this->extra;

		// fill extra with defaults and convert it to object
		foreach( $this->extra as & $item ){
			$item += [
				'class'       => '',
				'link'        => '',
				'amount'      => 0,
				'amount_text' => '',
			];

			$item = (object) $item;
		}
	}

}
