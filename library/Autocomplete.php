<?php

	class Autocomplete{
	
		protected $table;
		protected $pk;
		protected $value;
		protected $fields = array();
		protected $id = null;		
		
		private function __construct(){}
		
		protected function setTable($table){
			$this->table = $table;
		}
		
		protected function setPK($pk){
			$this->pk = $pk;
		}
		
		protected function setValue($value){
			$arrk = array_keys($this->fields);
			$this->fields[$arrk[0]] = $value;
		}
		
		protected function setField($field){
			$this->addField($field);
		}
		
		protected function addField($f, $v = null){
			$this->fields[$f] = $v;
		}
		
		protected function clearFields(){
			$this->fields = array();
		}
		
		
		
		public function getId(){
			if($this->id !== null) return $this->id;
			
			$db = DB::getInstance();
			$sql = "SELECT `{$this->pk}` FROM `{$this->table}` WHERE ";
			
			foreach($this->fields as $f=>$v){
				$sql .= "`$f` = '$v' AND ";
			}
			$sql .= "1";
			$id = $db->fetchOne($sql);
			
			if(!$id){
				$id= $db->insert($this->fields, $this->table);
			}
			$this->id = $id;
			return $id;
		}
		
		
		public function getValue(){
			if(count($this->fields) == 1){
				$ak = array_keys($this->fields);
				return $this->fields[$ak[0]];
			}
			else{
				return $this->fields;
			}
		}
		
		
	}
