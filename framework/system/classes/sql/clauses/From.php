<?php namespace classes\sql\clauses;

class From extends BaseClause
{
  
  //Private properties.
  private
    $models=[];
    
  //Add a model's table to the from clause.
  public function addModel(\classes\sql\QueryBuilderModel $model)
  {
    
    $this->models[] = $model;
    
  }
  
  //Add a model's table to the from clause using a JOIN.
  public function joinModel(\classes\sql\QueryBuilderModel $foreign, $type = null)
  {
    
    //Get the local model.
    $local = $target = $this->builder->working_model;
    
    //We keep joining the models that lead to the foreign model until the foreign model is joined.
    do
    {
      
      //Get model info.
      $minfo = $target->getMinfo();
      
      //Do we even have relations defined?
      if(!$minfo->arrayExists('relations')){
        throw new \exception\NotFound(
          'The "%s"-model in %s does not define relations.',
          $target->getName(), $target->getComponent()->title
        );
      }
      
      //Find the right relation.
      $match = false;
      $r = $minfo->relations;
      foreach($r as $key => $join_info)
      {
        
        //Get the component name and the model name from the key.
        list($component_name, $model_name) = (substr_count($key, '.') == 1 
          ? explode('.', $key)
          : [$target->getComponent()->name, $key]
        );
        
        //Test if this is the relation we're looking for.
        if($component_name === $foreign->getComponent()->name && $model_name === $foreign->getName()){
          $match = true;
          break;
        }
        
      }
      
      //Check if we have a match.
      if(!$match){
        throw new \exception\NotFound(
          'The "%s"-model in %s does not define relations to the "%s"-model in %s.',
          $target->getName(), $target->getComponent()->title,
          $foreign->getName(), $foreign->getComponent()->title
        );
      }
      
      //Acquire the information.
      list($local_column_name, $foreign_column, $default_type) = $join_info;
      
      //Split some of the information up into even more information..
      $foreign_info = explode('.', $foreign_column);
      
      //Acquire that information too.
      list($foreign_component_name, $foreign_model_name, $foreign_column_name) = (count($foreign_info) == 3
        ? $foreign_info
        : [$target->getComponent()->name, $foreign_info[0], $foreign_info[1]]
      );
      
      //Acquire join-type information.
      $type = (is_null($type) ? (is_null($default_type) ? 'LEFT JOIN' : $default_type) : $type);
      
      //Is this our final foreign?
      if($foreign_model_name === $foreign->getName()
      && $foreign_component_name === $foreign->getComponent()->name)
      {
        $new_target = $foreign;
      }
      
      //Otherwise create a new model based on the acquired information and add it.
      else{
        $new_target = $this->builder->addModel(
          $foreign_model_name, \classes\Component::get($foreign_component_name)
        );
      }
      
      //Add the acquired data to model that we're joining on.
      $target->addJoin(
        $type,
        $new_target,
        $target->{$local_column_name},
        $new_target->{$foreign_column_name}
      );
      
      //Set the new target.
      $target = $new_target;
      
    }
    while($target !== $foreign);
    
    //Everything good? Check if the local model is in the from clause yet.
    if(!in_array($local, $this->models)){
      $this->models[] = $local;
    }
    
    //Enable chaining.
    return $this;
    
  }
  
  //Extend the parents functionality.
  public function getString()
  {
    
    $string = 'FROM';
    $d = ' ';
    
    //Right, so for every model in the from clause...
    foreach($this->models as $model)
    {
      
      //We add that model to the clause.
      $string .= "$d{$model->getMinfo()->table_name} AS ".$this->builder->prepare($model);
      
      //And then check if the model happens to have joins attached to it.
      if($model->hasJoins())
      {
        
        //It has joins! Right, so for every model that needs to be joined...
        foreach($model->getJoins() as $v)
        {
          
          //Acquire data.
          list($type, $foreign_model, $local_column, $foreign_column) = array_values($v);
          
          //Normalize the join-type.
          $type = strtoupper($type);
          $type = (substr($type, -5) != ' JOIN' ? $type.' JOIN' : $type);
          
          //Add it to the clause.
          $string .= 
            " $type {$foreign_model->getMinfo()->table_name} AS ".
            $this->builder->prepare($foreign_model).
            " ON ".$this->builder->prepare($local_column).
            " = ".$this->builder->prepare($foreign_column);
          
        }
        
      }
      
      //Change the delimiter to a comma, in case we are adding more models.
      $d = ', ';
      
    }
    
    //Done!
    return $string;
    
  }
  
}
