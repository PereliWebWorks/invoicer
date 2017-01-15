<?php
	class Renderer{
		const TEMPLATE_DIR = __DIR__ . "/templates/";
		private $data = array();
		public function render() {
			$file_names = array_keys($this->data);
			$last_key = end($file_names); 
			$template_file = $last_key;
	        if (file_exists(static::TEMPLATE_DIR.$template_file)) {
	            include static::TEMPLATE_DIR.$template_file;
	            array_pop($this->data);
	        } else {
	            throw new Exception('No template file ' . $template_file . ' present in directory ' . static::TEMPLATE_DIR);
	        }
	    }
	    public function render_string() {
	    	$file_names = array_keys($this->data);
			$last_key = end($file_names); 
			$template_file = $last_key;
	        if (file_exists(static::TEMPLATE_DIR.$template_file)) {
	        	$filename = static::TEMPLATE_DIR.$template_file;
	        	ob_start();
        		include $filename;
        		array_pop($this->data);
        		$contents = ob_get_clean();
	            return $contents;
	        } else {
	            throw new Exception('No template file ' . $template_file . ' present in directory ' . static::TEMPLATE_DIR);
	        }
	    }
	    function prepare_template($template_file)
	    {
	    	$template_file .= ".php";
	        if (file_exists(static::TEMPLATE_DIR.$template_file)) {
	            $this->data[$template_file] = array();
	        } else {
	            throw new Exception('No template file ' . $template_file . ' present in directory ' . static::TEMPLATE_DIR);
	        }
	    }
	    public function __set($name, $value) {
	    	if (sizeof($this->data) === 0)
	    	{
	    		throw new Exception("No template prepared.");
	    		return;
	    	}
	    	$file_names = array_keys($this->data);
			$last_key = end($file_names); 
        	$this->data[$last_key][$name] = $value;
   		}
    	public function __get($name) {
    		if (sizeof($this->data) === 0)
	    	{
	    		throw new Exception("No template prepared.");
	    		return;
	    	}
	    	$file_names = array_keys($this->data);
			$last_key = end($file_names); 
        	return $this->data[$last_key][$name];
    	}
	}
	$renderer = new Renderer();
?>