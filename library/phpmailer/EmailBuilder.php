<?php

class EmailBuilder {

	private $_HTMLTemplate;
	private $_fieldNames;
	private $_values;
	
	
	public function __construct($data, $template){
		$this->setData($data);
		$this->setTemplate($template);
	}
	
	
	public function setData($data){
		if(is_array($data)){
			foreach($data as $field=>$value){
				$this->_fieldNames[] = $field;
				$this->_values[] = $value;
			}
		}
		else{
			throw new Exception("Data supplied is not an array.");
		}
	}
	
	private function generateHTMLRow($field, $value){
		return '<tr><td valign="top"><b>'.$field.':</b></td><td>'.$value.'</td>';
	}
	
	private function generatePlainRow($field, $value){
		return $field.": ".$value."\n";
	}
	
	public function formatHTML(){
		$msg = "";
		foreach($this->_fieldNames as $key=>$field){
			$msg .= $this->generateHTMLRow($field, $this->_values[$key]);
		}
		
		return $this->fillTemplate($msg);
	}
	
	public function formatPlain(){
		$msg = "";
		foreach($this->_fieldNames as $key=>$field){
			$msg .= $this->generatePlainRow($field, $this->_values[$key]);
		}
		return $msg;
	}
	
	private function setTemplate($template){
			$this->_HTMLTemplate = $template;
	}
	
	private function fillTemplate($content){

		ob_start();
		require($this->_HTMLTemplate);
		$output = ob_get_clean();
		return $output;
	}

}



?>
