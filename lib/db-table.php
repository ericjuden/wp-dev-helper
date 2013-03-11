<?php 

class WDH_Table {
	var $name;
	var $columns;
	var $keys;
	
	function __construct($name, $columns, $keys){
		$this->name = $name;
		$this->columns = $columns;
		$this->keys = $keys;
	}
}

class WDH_Table_Column {
	var $name;
	var $data_type;
	var $options;
	
	function __construct($name, $data_type, $options = '', $auto_increment = false){
		$this->name = $name;
		$this->data_type = $data_type;
		$this->options = $options;
		$this->auto_increment = $auto_increment;
	}
}

class WDH_Table_Key {
    var $name;
    var $columns;
    var $is_unique;
    var $is_primary;
    
    /**
     * 
     * Constructor
     * @param string $name
     * @param string $columns: comma separated
     * @param bool $is_primary
     * @param bool $is_unique
     */
    function __construct($name, $columns, $is_primary = false, $is_unique = false){
        $this->name = $name;
        $this->columns = $columns;
        $this->is_primary = $is_primary;
        $this->is_unique = $is_unique;
    }
}
?>