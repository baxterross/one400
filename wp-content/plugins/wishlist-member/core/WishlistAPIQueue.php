<?php
/**
 * Plugin Methods Class for WishList Member API Queue
 * @author Fel Jun Palawan <feljunpalawan@gmail.com>
 * @package wishlistmember
 *
 * @version $$
 * $LastChangedBy$
 * $LastChangedDate$
 */
class WishlistAPIQueue {

	function __construct(){
		global $wpdb;
		$this->TablePrefix = $wpdb->prefix . 'wlm_';
		$this->Table = $this->TablePrefix . 'api_queue';
	}
	public function generate_tables(){
		global $wpdb;
		$structure = "CREATE TABLE IF NOT EXISTS `{$this->Table}` (
			`ID` bigint(20) NOT NULL AUTO_INCREMENT,
			`name` varchar(64) NOT NULL,
			`value` longtext NOT NULL,
			`notes` varchar(500),
			`tries` int NOT NULL,
			`date_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (`ID`)
		) {$charset_collate}";
		$wpdb->query($structure);
		$wpdb->query("ALTER TABLE `{$this->Table}` DEFAULT CHARACTER SET utf8");
		$wpdb->query("ALTER TABLE `{$this->Table}` MODIFY `value` longtext CHARACTER SET utf8 NOT NULL");
	}

	public function add_queue($name,$value,$notes=""){
		global $wpdb;
		$data = array(
			'name' => $name,
			'value' => $value,
			'notes' => $notes,
			'tries' => 0
		);		
		return $wpdb->insert($this->Table, $data);		
	}

	public function get_queue($name,$limit=null,$tries=null,$sort="ID",$date=null){
		global $wpdb;

		$sort = " ORDER BY {$sort} ASC";
		$limit = (int)$limit;
		$limit = $limit != null ? " LIMIT 0,{$limit}":"";
		$where = " WHERE name LIKE '%{$name}%'";

		if($tries != null){
			$where = $where == "" ? " WHERE tries <= {$tries}" : " {$where} AND tries <= {$tries}";
		}

		if($date != null){
			$where = $where == "" ? " WHERE date_added <= '{$date}'" : " {$where} AND date_added <= '{$date}'";
		}

		$query = "SELECT * FROM {$this->Table} {$where} {$sort} {$limit}";
		return $wpdb->get_results($query);
	}

	public function update_queue($id,$data){
		global $wpdb;
		$where = array('ID' => $id);
		return $wpdb->update($this->Table, $data, $where);
	}

	function delete_queue($id) {
		global $wpdb;
		$wpdb->query($wpdb->prepare("DELETE FROM `{$this->Table}` WHERE `ID`=%d", $id));
	}	
}

?>