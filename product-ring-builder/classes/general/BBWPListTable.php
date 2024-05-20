<?php
namespace OTW\GeneralWooRingBuilder;
if ( ! defined( 'ABSPATH' ) )	exit;

class BBWPListTable{

  private $columns = array();
  private $items = array();
  private $sortable = array();
  public $actions = array();
  public $bulk_actions = array();
  public $wp_sortable = true;


/******************************************/
/***** get_columns **********/
/******************************************/
	public function get_columns($columns = array()){

    if(isset($columns) && is_array($columns) && count($columns) >= 1)
      $this->columns = $columns;

	}// get_columns method end here

/******************************************/
/***** prepare_items **********/
/******************************************/
	public function prepare_items($data = array()) {

    if(isset($data) && is_array($data) && count($data) >= 1)
      $this->items = $data;

	}// prepare_items method end here

  /******************************************/
  /***** prepare_items **********/
  /******************************************/
  	public function display() {
      //db($this->$columns);
      if(isset($this->items) && count($this->items) >= 1 && isset($this->columns) && count($this->columns) >= 1){

        $tablenavtop = '<div class="tablenav top">';
        if(count($this->bulk_actions) >= 1){
          $tablenavtop .= '<div class="alignleft actions bulkactions">'; 
          $tablenavtop .= '<select name="bulk_action" id="bulk-action-selector-top"><option value="">Bulk Actions</option>';
          foreach ($this->bulk_actions as $key => $value) {
            $tablenavtop .= '<option value="'.$key.'">'.$value.'</option>';
          }
          $tablenavtop .= '</select><input type="submit" id="doaction" class="button action" value="Apply">';
          $tablenavtop .= '</div><!-- bulkactions -->';
        }
        $tablenavtop .= '</div><!-- tablenav -->';


        $thead = '<tr><td id="cb" class="manage-column column-cb check-column"><label class="screen-reader-text" for="cb-select-all-1">Select All</label><input id="bb-select-all-checkbox" data-name="fields" type="checkbox"></td>';
        $i = 1;
        foreach($this->columns as $key=>$value){
          $primarycolumn = '';
          if($i == 1)
            $primarycolumn = 'column-primary';

          if(is_array($this->sortable) && in_array($key,$this->sortable))
            $thead .= '<th scope="col" id="'.$key.'" class="sortable asc manage-column column-'.$key.' '.$primarycolumn.'"><a href="#"><span>'.$value.'</span><span class="sorting-indicator"></span></a></th>';
          else
            $thead .= '<th scope="col" id="'.$key.'" class="manage-column column-'.$key.' '.$primarycolumn.'">'.$value.'</th>';
          $i++;
        }
        $thead .= "</tr>";

        $i = 1;
        $tbody = "";
        foreach($this->items as $values){
          if(is_array($values) && count($values) >= 1){
            $tbody .= '<tr>';
            //$tbody .= '<tr class="ui-state-default">';
            $j = 1;
            foreach($values as $key=>$value){

              if(!array_key_exists($key, $this->columns)){
                continue;
              }
              /*if(is_array($value)){ $value = implode(', ', $value); }
              if(!$value){ $value = '&nbsp;'; }*/
              $actions_html = array(
                // 'delete' => '<span class="delete"><a href="'.add_query_arg( $_GET, '').'?page='.$_REQUEST['page'].'&action=delete&'.$key."=".$value.'">Delete</a></span>',
                'delete' => '<span class="delete"><a href="'.add_query_arg( array('action' => 'delete', $key=>$value), add_query_arg( $_GET, '')).'">Delete</a></span>',
                'edit' => '<span class="edit"><a href="'.add_query_arg( array('action' => 'edit', $key=>$value), add_query_arg( $_GET, '')).'">Edit</a></span>',
              );

              $primarycolumn = '';
              $action = "";
              if($j == 1){
                $primarycolumn = 'column-primary';
                $tbody .= '<th scope="row" class="check-column">';
                $tbody .= apply_filters('bbwp_list_table_before_primary_input', '<input id="cb-select-'.$value.'" type="checkbox" name="fields[]" value="'.$value.'">',  $value, $key);
                $tbody .= '<div class="locked-indicator"></div>
                </th>';
              }
              // if(isset($this->actions) && is_array($this->actions) && count($this->actions) >= 1 && array_key_exists($key,$this->actions)){
              //   $action .= '<div class="row-actions">';
              //   foreach($this->actions[$key] as $action_key => $action_value){
              //       if($action_key == 'delete')
              //         $action .= '<input type="hidden" name="sort_field[]" value="'.$value.'" />';
              //       $action .= $actions_html[$action_value]."  | ";
              //   }
              //   $action = trim($action, "| ");
              //   $action .= '</div>';
              // }
              $actions = $this->ExtraActions($key, $value, $values);
              

              $tbody .= '<td class="'.$key.' column-'.$key.' has-row-actions '.$primarycolumn.'" data-colname="'.$key.'">'.$value.$actions['crud_html'].'</td>';
              $j++;
            }
            $tbody .= "</tr>";
          }
          $i++;
        }

        $sortable = '';
        if($this->wp_sortable)
          $sortable = 'bytebunch-wp-sortable';
        
        echo $tablenavtop.'<table class="wp-list-table widefat fixed striped"><thead>'.$thead.'</thead><tbody class="'.$sortable.'">'.$tbody.'</tbody><tfoot>'.$thead.'</tfoot></table>';

      }
  	}// prepare_items method end here


/******************************************/
/***** get_sortable_columns **********/
/******************************************/
public function get_sortable_columns($column = false) {
  if(isset($column) && count($column) >= 1){
    $this->sortable = $column;
  }
}

/******************************************/
/***** ExtraActions **********/
/******************************************/
public function ExtraActions($key = NULL, $value = NULL, $values = NULL) {
	$actions = array('crud_html' => '', 'custom_function' => $value);
	$actions_html = '';

	if(isset($this->actions) && is_array($this->actions) && count($this->actions) >= 1){
		
		foreach($this->actions as $action_values){

			if(isset($action_values['on_column']) && $action_values['on_column'] == $key){
				if(isset($action_values['type']) && $action_values['type'] == 'crud_html'){
					$url_args = array();
					
					if(isset($action_values['url_args']) && is_array($action_values['url_args'])){
						foreach($action_values['url_args'] as $urk_key=>$url_value){
							if(array_key_exists($url_value, $values))
								$url_args[$urk_key] = $values[$url_value];
							else
								$url_args[$urk_key] = $url_value;
						}
						
					}
						
					$url_args[$action_values['column_url_key_name']] = $values[$action_values['column']];
					
					if(isset($action_values['url']))						
						$url = add_query_arg($url_args, $action_values['url']);
					else{
            if(isset($_REQUEST['page']))
              $url_args['page'] = $_REQUEST['page'];
						$url = add_query_arg($url_args, '');
          }
					if(isset($action_values['user_permissions'])){
						if(current_user_can($action_values['user_permissions']))
							$actions_html .= '<span class="'.$action_values['span_classes'].'"><a href="'.$url.'">'.$action_values['title'].'</a> | </span>';
					}
					else
						$actions_html .= '<span class="'.$action_values['span_classes'].'"><a href="'.$url.'">'.$action_values['title'].'</a> | </span>';

				}
				elseif(isset($action_values['type']) && $action_values['type'] == 'custom_function'){
					$actions['custom_function'] = call_user_func($action_values['function_name'], $value);
				}
				
			}

		}

		if($actions_html && $actions_html != ''){
			$actions_html = str_lreplace('</a> | </span>', '</a></span>', $actions_html);
			$actions['crud_html'] = '<div class="row-actions">'.$actions_html.'</div>';			
		}

	}

	return $actions;

}

/******************************************/
/***** Edit and dlete button on id column **********/
/******************************************/
/*
function column_ID($item) {

  $actions = array(
            //'edit'      => sprintf('<a href="?page=%s&action=%s&id=%s">Edit</a>',$_REQUEST['page'],'edit',$item['ID']),
            'delete'    => sprintf('<a href="?page=%s&action=%s&id=%s">Delete</a>',$_REQUEST['page'],'delete',$item['ID']),
        );
  return sprintf('%1$s %2$s', $item['ID'], $this->row_actions($actions) );
}*/

}// class Booking_List_Table end here

/*
  $data = array();
  $data[] = array("ID" => "1", "title" => 'its good', "date" => "22");
  $data[] = array("ID" => "2", "title" => 'its very bad', "date" => "ss22");
  $tableColumns = array("ID" => "ID", "title" => "Title", "date" => "Date");
  if(count($user_registered_pages) >= 1){
      $ListTableByteBunch = new ListTableByteBunch();
      $ListTableByteBunch->prepare_items($data, $tableColumns);
      $ListTableByteBunch->display();
  }*/
