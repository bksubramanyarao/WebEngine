<?php
namespace Plugin;
class Form {
	
	private $machine;
	private $forms;
	private $formrow_template = '
		<div class="formRow">
			<div class="formLabel">
				{{LABEL}}
			</div>
			<div class="formField">
				{{FIELD}}
			</div>
		</div>
	';
	private $form_template = '
		<div class="formContainer">
			<form method="post" action="{{FORMACTION}}">
				{{FORMROWS}}
				<button type="submit">submit</button>
			</form>
		</div>
	';
	
	function __construct($machine) {
		$this->machine = $machine;
	}
	
	public function addForm($name, $opts) {
		$this->forms[$name] = $opts;
	}
	
	private function getFormLabel($formField) {
		$type = gettype($formField);
		if ($type == "string") {
			return $formField;
		}
		if ($type == "array") {
			return $formField[0];
		}		
	}
	
	private function getFormField($formField) {
		$type = gettype($formField);
		if ($type  == "string") {
			return '<input type="text" name="' . $formField . '" />';
		}
		if ($type == "array") {
			$field_type = $formField[1];
			switch ($field_type) {
				case "password":
					return '<input type="password" name="' . $formField[0] . '" />';
					break;
			}
		}
	}
	
	// tags
	
	public function Render($params) {
		$formName = $params[0];
		
		$html_rows = "";
		foreach ($this->forms[$formName]["fields"] as $formField) {
			$html_rows .= $this->machine->populate_template($this->formrow_template, [
				"LABEL" => $this->getFormLabel($formField),
				"FIELD" => $this->getFormField($formField)
			]);
		}
		
		$html = $this->machine->populate_template($this->form_template, [
			"FORMACTION" => $this->forms[$formName]["action"],
			"FORMROWS" => $html_rows
		]);
		
		return $html;
	}
}