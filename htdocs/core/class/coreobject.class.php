<?php
/* EXPERIMENTAL
 * 
 * Copyright (C) 2016		ATM Consulting			<support@atm-consulting.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *	\file       htdocs/core/class/coreobject.inventory.php
 *	\ingroup    core
 *	\brief      File of class to manage all object. Might be replace or merge into commonobject
 */
 
require_once DOL_DOCUMENT_ROOT.'/core/class/commonobject.class.php';

class CoreObject extends CommonObject {
	
	public $db;
	
	public $withChild = true;
	
	public $error = '';
	/*
	 *  @var Array $_fields Fields to synchronize with Database
	 */
	protected $__fields=array();
	 /**
	 *  Constructor
	 *
	 *  @param      DoliDB		$db      Database handler
	 */
	function __construct(DoliDB &$db) {
		

	}
	
	protected function init() {
		
		$this->id = 0;
		$this->datec = 0;
		$this->tms = 0;
		
		if(!empty($this->__fields)) {
			foreach ($this->__fields as $field=>$info) {
		
		        if($this->is_date($info)){
					$this->{$field} = time();
		        }
		        elseif($this->is_array($info)){
					$this->{$field} = array();
		        }
		        elseif($this->is_int($info)){
					$this->{$field} = (int)0;
		        }
		        elseif($this->is_float($info)) {
					$this->{$field} = (double)0;
				}
		        else{
					$this->{$field} = '';
		        }
		    }
			
		    $this->to_delete=false;
			
			return true;
		}
		else{
			return false;
		}
			
	}
	
	private function checkFieldType($field, $type) {
		
		if( isset($this->__fields[$field]) && method_exists($this, 'is_'.$type)) {
			return $this->{'is_'.$type}($this->__fields[$field]);
		}
		else return false;
		
	}
	
	private function is_date(Array &$info){
	
		if(isset($info['type']) && $info['type']=='date') return true;
		else return false;
		
	}
	
	private function is_array($info) {
		
	  	if(is_array($info)) {
			if(isset($info['type']) && $info['type']=='array') return true;
			else return false;
		}
		else return false;
	}
	
	
	private function is_null($info){
		if(is_array($info)) {
			if(isset($info['type']) && $info['type']=='null') return true;
			else return false;
		}
		else return false;
	}
	
	private function is_int($info){
	
		if(is_array($info)) {
			if(isset($info['type']) && ($info['type']=='int' || $info['type']=='integer' )) return true;
			else return false;
		}
		else return false;
	}
	private function is_float($info){
		if(is_array($info)) {
			if(isset($info['type']) && $info['type']=='float') return true;
			else return false;
		} else return false;
	}
	
	private function is_text($info){
	  	if(is_array($info)) {
			if(isset($info['type']) && $info['type']=='text') return true;
			else return false;
		} else return false;
	}
	private function is_index($info){
	  	if(is_array($info)) {
			if(isset($info['index']) && $info['index']==true) return true;
			else return false;
		} else return false;
	}
		
	private function set_save_query(){
		
		$query=array();
		
		foreach ($this->__fields as $field=>$info) {
	
			if($this->is_date($info)){
				if(empty($this->{$field})){
					$query[$field] = NULL;
				}
				else{
					$query[$field] = $this->db->idate($this->{$field});
				}
		  	}
		  	else if($this->is_array($info)){
		  		    $query[$field] = serialize($this->{$field});
		  	}
		
		  	else if($this->is_int($info)){
		    	$query[$field] = (int)price2num($this->{$field});
		  	}
		
		  	else if($this->is_float($info)){
		    	$query[$field] = (double)price2num($this->{$field});
		  	}
		
		  	elseif($this->is_null($info)) {
		  		$query[$field] = (is_null($this->{$field}) || (empty($this->{$field}) && $this->{$field}!==0 && $this->{$field}!=='0')?null:$this->{$field});
		    }
		    else{
		       $query[$field] = $this->{$field};
		    }
		
	    }
	
		return $query;
	}
		
	private function get_field_list(){
			
		$keys = array_keys($this->__fields);
		
	    return implode(',', $keys);
	}
	
	private function set_vars_by_db(&$obj){

		foreach ($this->__fields as $field=>$info) {
			if($this->is_date($info)){
				if(empty($obj->{$field}) || $obj->{$field} === '0000-00-00 00:00:00' || $obj->{$field} === '1000-01-01 00:00:00')$this->{$field} = 0;
				else $this->{$field} = strtotime($obj->{$field});
			}
			elseif($this->is_array($info)){
				$this->{$field} = @unserialize($obj->{$field});
				//HACK POUR LES DONNES NON UTF8
				if($this->{$field}===FALSE)@unserialize(utf8_decode($obj->{$field}));
			}
			elseif($this->is_int($info)){
				$this->{$field} = (int)$obj->{$field};
			}
			elseif($this->is_float($info)){
				$this->{$field} = (double)$obj->{$field};
			}
			elseif($this->is_null($info)){
				$val = $obj->{$field};
				// zero is not null 
				$this->{$field} = (is_null($val) || (empty($val) && $val!==0 && $val!=='0')?null:$val);
			}
			else{
				$this->{$field} = $obj->{$field};
			}

		}
	}
	
	public function fetch($id, $loadChild = true) {
		
		if(empty($id)) return false;

		$sql = 'SELECT '.$this->get_field_list().',datec,tms
						FROM '.MAIN_DB_PREFIX.$this->table_element.'
						WHERE rowid='.$id;
		
		$res = $this->db->query( $sql );
		if($obj = $this->db->fetch_object($res)) {
				$this->id=$id;
			
				$this->set_vars_by_db($obj);

				$this->datec=$this->db->idate($obj->datec);
				$this->tms=$this->db->idate($obj->tms);
				
				if($loadChild) $this->fetchChild();

				return $this->id;
		}
		else {
				$this->error = $this->db->lasterror();
				
				return false;
		}
		
	}
	public function addChild($tabName, $id='', $key='id', $try_to_load = false) {
		if(!empty($id)) {
			foreach($this->{$tabName} as $k=>&$object) {
				if($object->{$key} === $id) return $k;
	
			}
		}
	
		$k = count($this->{$tabName});
	
		$className = ucfirst($tabName);
		$this->{$tabName}[$k] = new $className($this->db);
		if($id>0 && $key==='id' && $try_to_load) { 
			$this->{$tabName}[$k]->fetch($id); 
		}
	
	
		return $k;
	}
	
	public function removeChild($tabName, $id, $key='id') {
		foreach($this->{$tabName} as &$object) {
	
			if($object->{$key} == $id) {
				$object->to_delete = true;
				return true;
			}
	
	
		}
		return false;
	}
	
	public function fetchChild() {

		if($this->withChild && !empty($this->childtables) && !empty($this->fk_element)) {
			foreach($this->childtables as &$childTable) {
					
					$className = ucfirst($childTable);
					
					$this->{$className}=array();
					
					$sql = " SELECT rowid FROM ".MAIN_DB_PREFIX.$childTable." WHERE ".$this->fk_element."=".$this->id;
					$res = $this->db->query($sql);
					
					if($res) {
						$Tab=array();
						while($obj = $this->db->fetch_object($res)) {
							
							$o=new $className($this->db);	
							$o->fetch($obj->rowid);
							
							$this->{$className}[] = $o;
							
						}
						
					}

			}

		}

	}
	
	public function saveChild(User &$user) {
	
		if($this->withChild && !empty($this->childtables) && !empty($this->fk_element)) {
			foreach($this->childtables as &$childTable) {
	
				$className = ucfirst($childTable);
				if(!empty($this->{$className})) {
					foreach($this->{$className} as $i => &$object) {
		
						$object->{$this->fk_element} = $this->id;
						
						$object->update($user);
						if($this->unsetChildDeleted && isset($object->to_delete) && $object->to_delete==true) unset($this->{$className}[$i]);
					}
				}
	
			}
		}
	}
	public function update(User &$user) {
		if(empty($this->id )) return $this->create($user); // To test, with that, no need to test on high level object, the core decide it, update just needed
		
		if(isset($this->to_delete) && $this->to_delete==true) {
			$this->delete($user);
		}
		else {
		
			$query = array();
			
			$query = $this->set_save_query();
			$query['rowid']=$this->id;
				
			$res = $this->db->update($this->table_element,$query,array('rowid'));
			
			if($res) {
			
				$result = $this->call_trigger(strtoupper($this->element). '_UPDATE', $user);
				
				$this->saveChild($user);
			
				return true;
			}
			else{
				$this->error = $this->db->lasterror();
				
				return false;
			}
		
		}
		return $this->id;
		
		
	}
	public function create(User &$user) {
		if($this->id>0) return $this->update($user);
		
		$query = array();
		$query = $this->set_save_query();
		$query['datec'] = date("Y-m-d H:i:s",$this->datec);
		
		$res = $this->db->insert($this->table_element,$query);
	
		if($res) {
			$this->id = $this->db->last_insert_id($this->table_element);
			
			$result = $this->call_trigger(strtoupper($this->element). '_CREATE', $user);
			
			$this->saveChild($user);
			
			return $this->id;
		}
		else{
			
			$this->error = $this->db->lasterror();
			
			return false;
		}
	
	}
	public function delete(User &$user){
		if($this->id>0){
			$this->call_trigger(strtoupper($this->element). '_DELETE', $user);
			
			$this->db->delete($this->table_element,array('rowid'=>$this->id),array('rowid'));
			
			if($this->withChild && !empty($this->childtables)) {
				foreach($this->childtables as &$childTable) {
						
					$className = ucfirst($childTable);
					if(!empty($this->{$className})) {
						foreach($this->{$className} as &$object) {
				
							$object->delete($user);
				
						}
					}
			
				}
			}
			
		
			
		}
	
		
	
	}
	public function get_date($field,$format='') {
		if(empty($this->{$field})) return '';
		else {
			
			return dol_print_date($this->{$field}, $format);
		}
	
	}
	
	public function set_date($field,$date){

	  	if(empty($date)) {
	  		$this->{$field} = 0;
	  	}
		else {
			require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';
			$this->{$field} = dol_stringtotime($date);
		}

		return $this->{$field};
	}
	
	public function set_values(&$Tab) {
		
		foreach ($Tab as $key=>$value) {
	
			if($this->checkFieldType($key,'date')) {
				$this->set_date($key, $value);
			}
			else if( $this->checkFieldType($key,'array')){
				$this->{$key} = $value;
			}
			else if( $this->checkFieldType($key,'float') ) {
				$this->{$key} = (double)price2num($value);
			}
			else if( $this->checkFieldType($key,'int') ) {
				$this->{$key} = (int)price2num($value);
			}
			else {
				$this->{$key} = @stripslashes($value);
			}
			
		}
	}
	
}
