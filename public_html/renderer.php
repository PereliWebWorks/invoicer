<?php
	class Renderer{
		private $template_dir = "templates/";
		private $data = array();
		public function render($template_file) {
			$template_file .= ".php";
	        if (file_exists($this->template_dir.$template_file)) {
	            include $this->template_dir.$template_file;
	        } else {
	            throw new Exception('No template file ' . $template_file . ' present in directory ' . $this->template_dir);
	        }
	    }
	    public function __set($name, $value) {
        	$this->data[$name] = $value;
   		}
    	public function __get($name) {
        	return $this->data[$name];
    	}
	}
	$renderer = new Renderer();
?>