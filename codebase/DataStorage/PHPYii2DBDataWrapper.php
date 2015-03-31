<?php
namespace DHTMLX\Connector\DataStorage;

class PHPYii2DBDataWrapper extends ArrayDBDataWrapper {

	public function select($sql){
		if (is_array($this->connection))	//result of findAll
			$res = $this->connection;
		else
			$res = $this->connection->find()->all();

		$temp = array();
		if (sizeof($res)){
			foreach ($res as $obj)
				$temp[]=$obj->getAttributes();
		}
        //die(var_dump($temp));
		return new ArrayQueryWrapper($temp);
	}

	protected function getErrorMessage(){
		$errors = $this->connection->getErrors();
		$text = array();
		foreach ($errors as $key => $value){
			$text[] = $key." - ".$value[0];
		}
		return implode("\n", $text);
	}
	public function insert($data,$source){
		$name = get_class($this->connection);
		$obj = new $name();
        //die(var_dump($data));
		$this->fill_model_and_save($obj, $data);
	}
	public function delete($data,$source){
		$obj = $this->connection->findOne($data->get_id());
		if ($obj->delete()){
			$data->success();
			$data->set_new_id($obj->getPrimaryKey());
		} else {
			$data->set_response_attribute("details", $this->errors_to_string($obj->getErrors()));
			$data->invalid();
		}
	}
	public function update($data,$source){
        //$obj = get_class($this->connection);
        //$obj->setAttribute($obj->getPrimaryKey);

		$obj = $this->connection->findOne($data->get_id());
;
		$this->fill_model_and_save($obj, $data);
	}

	protected function fill_model_and_save($obj, $data){

		//map data to model object
		for ($i=0; $i < sizeof($this->config->text); $i++){
			$step=$this->config->text[$i];
			$obj->setAttribute($step["name"], $data->get_value("c".$i)); //TODO make array with corresponding names
		}

		if ($relation = $this->config->relation_id["db_name"])
			$obj->setAttribute($relation, $data->get_value($relation));

		//save model
		if ($obj->save()){
			$data->success();
			$data->set_new_id($obj->getPrimaryKey());
		} else {
			$data->set_response_attribute("details", $this->errors_to_string($obj->getErrors()));
			$data->invalid();
		}
	}

	protected function errors_to_string($errors){
		$text = array();
		foreach($errors as $value)
			$text[]=implode("\n", $value);
		return implode("\n",$text);
	}
	public function escape($str){
		throw new Exception("Not implemented");
	}
	public function query($str){
		throw new Exception("Not implemented");
	}
	public function get_new_id(){
		throw new Exception("Not implemented");
	}

}