<?php
namespace Plugin;
class Breadcrumb {
	
	private $machine;
	private $breadcrumb_template = '
		<span><a href="{{HREF}}">{{LABEL}}</a></span>
	';
	private $breadcrumb_separator = ' | ';
	
	private $breadcrumbs;
	private $label;
	
	function __construct($machine) {
		$this->machine = $machine;
		$this->breadcrumbs = [];
		$this->label = "";
	}
	public function add($label, $href) {
		$this->breadcrumbs[] = [
			"label" => $label,
			"href" => $href
		];
	}
	
	public function setLabel($label) {
		$this->label = $label;
	}
	
	// tags
	
	public function Render($params) {
		$html = [];
		
		foreach($this->breadcrumbs as $breadcrumb) {
			$html[] = $this->machine->populate_template($this->breadcrumb_template, [
				"LABEL" => $breadcrumb["label"],
				"HREF" => $breadcrumb["href"]
			]);
		}
		
		if ($this->label != "") {
			$html[] = $this->label;
		}
		
		return implode($this->breadcrumb_separator, $html);
	}
}