<?php

	class Bin{
	
		protected $data = array();
		
		public function __get($k){
			if(!in_array($k, array_keys($this->data)))
				throw new Exception("Invalid parameter '$k', not found");
			return $this->data[$k];
		}
		
		public function __set($k, $v){
			if(!in_array($k, array_keys($this->data)))
				throw new Exception("Invalid parameter '$k', cannot set value");
			$this->data[$k] = $v;
		}
		
		public function toArray(){
			return $this->data;
		}
	}