<?php namespace classes\sql;

use \classes\Component;

class BuilderModel
{
  
  //Public properties.
  private
    $name,
    $component,
    $builder,
    $class,
    $minfo,
    $tinfo,
    $unique,
    
    $joins=[];
  
  //Start from here.
  public function __construct($name, Component $component, Builder $builder)
  {
    
    $this->name = $name;
    $this->component = $component;
    $this->builder = $builder;
    $this->class = $class = $component->loadModel($name);
    $this->minfo = $class::modelInfo();
    $this->tinfo = $class::tableInfo();
    $this->unique = uniqid($name);
    
  }
  
  //Return a string representation of this model.
  public function __toString()
  {
    
    return $this->unique;
    
  }
  
  //Return a BuilderColumn object.
  public function __get($key)
  {
    
    if(!wrap($this->tinfo->fields)->arrayExists($key)){
      throw new \exception\Sql(
        'The "%s"-model in %s does not have field "%s" in its table (%s).',
        $this->name, $this->component->title, $key, $this->minfo->table_name
      );
    }
    
    return new BuilderColumn($key, $this);
    
  }
  
  //Forward calls to the Builder.
  public function __call($method, $args)
  {
    
    //For certain methods.
    if(!in_array($method, ['join'])){
      throw new \exception\NonExistent('Method "%s" does not exist.', $method);
    }
    
    //Go!
    call_user_func_array([$this->builder, $method], array_merge([$this], $args));
    
    //Enable chaining.
    return $this;
    
  }
  
  //Returns the base class name of this model.
  public function getName()
  {
    
    return $this->name;
    
  }
  
  //Returns the unique name of this model.
  public function getUnique()
  {
    
    return $this->unique;
    
  }
  
  //Returns the model info of this model.
  public function getMinfo()
  {
    
    return $this->minfo;
    
  }
  
  //Returns the builder that this model belongs to.
  public function getBuilder()
  {
    
    return $this->builder;
    
  }
  
  //Returns the string that contains the class-name of this model.
  public function getClass()
  {
    
    return $this->class;
    
  }
  
  //Returns the component that this model belongs to.
  public function getComponent()
  {
    
    return $this->component;
    
  }
  
  //Get joined models.
  public function getJoins()
  {
    
    return $this->joins;
    
  }
  
  //Adds join-info to our join-info-array.
  public function addJoin(
    $type,
    self $foreign_model,
    BuilderColumn $local_column,
    BuilderColumn $foreign_column
  ){
    
    $this->joins[] = [
      'type' => $type,
      'foreign_model' => $foreign_model,
      'local_column' => $local_column,
      'foreign_column' => $foreign_column
    ];
    
  }
  
  //Returns true if this model has other models joined on it. False otherwise.
  public function hasJoins()
  {
    
    return !empty($this->joins);
    
  }
  
  //Returns true if the given builderModel uses the same model internally. False otherwise.
  public function compare(self $model)
  {
    
    return $model->class === $this->class;
    
  }
  
  //Create an instance of the model.
  public function createInstance()
  {
    
    return $this->component->createModel($this->name);
    
  }
  
  //Returns a string that can be used in the query to access a column in this model.
  public function getColumnString($column_name)
  {
    
    //Check if the column name exists.
    if(!$this->tinfo->fields->has($column_name)){
      throw new \exception\NonExistent(
        'The column "%s" does not exist in the model "%s" of %s.',
        $column_name, $this->name, $this->component->title
      );
    }
    
    return "`{$this->unique}.$column_name`";
    
  }
  
}
