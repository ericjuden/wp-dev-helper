<?php 

class WDH_List_Table_Column {
    var $name;
    var $label;
    var $is_sortable;
    var $display_order;
    
    function __construct($name, $label, $is_sortable = false, $display_order = 0){
        $this->name = $name;
        $this->label = $label;
        $this->is_sortable = $is_sortable;
        $this->display_order = $display_order;
    }
}
?>