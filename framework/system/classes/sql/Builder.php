<?php namespace classes\sql;

class Builder
{
  
  use \traits\Successable;
  
  //Public properties.
  public
    $component,
    $connection=null,
    $main_model,
    $working_model;
    
  //Private properties.
  private
    $models=[],
    $return_row,
    $return_model,
    $clauses=[],
    $aliases=[];
  
  //Set the amount of rows to select (0 for all), the component and the model name.
  public function __construct($amount=0, \classes\Component $component, $model_name, &$model=null)
  {
    
    //Limit the amount of rows?
    if($amount > 0){
      $this->limit($amount);
    }
    
    //Skip directly to the first row when we execute?
    if($amount == 1){
      $this->returnRow(0);
    }
    
    //Store the component.
    $this->component = $component;
    
    //Add the model.
    $model = $this->addModel($model_name);
    $this->main_model = $this->working_model = $model;
    
  }
  
  
  ##
  ## EXECUTION METHODS
  ##
  
  //Build the query and return an Query object containing it.
  public function done(BuilderModel $model = null)
  {
    
    //Get the connection.
    $connection = ($this->connection instanceof Connection
      ? $this->connection
      : tx('Sql')->connection($this->connection)
    );
    
    //Get the query and the data.
    $this->create($query, $data);
    
    //Create the query object.
    $query_object = (new Query($connection, $query, $data));
    
    //Use the right model.
    $model = ($model ? $model : $this->main_model);
    $this->assertModel($model);
    
    //Get the class to create and the component to pass.
    $class = $model->getClass();
    $component = $model->getComponent();
    
    //Set a creator that will do this.
    $query_object->setCreator(function($row)use($class, $component){
      return new $class($row, $component);
    });
    
    //Return the Query object.
    return $query_object;
    
  }
  
  //Executes the query right away and returns the result.
  public function execute(BuilderModel $model = null)
  {
    
    //Get the result.
    $result = $this->done($model)->execute();
    
    //See if a specific row needs to be returned.
    if(is_int($this->return_row)){
      return $result->idx($this->return_row);
    }
    
    //Return the whole result otherwise.
    return $result;
    
  }
  
  //Creates the query and the data based on the clauses we have and assigns them to the given references.
  public function create(&$query, &$data)
  {
    
    //The empty string that will one day be a full grown query.
    $query = '';
    
    //And the empty array that will grow up together with query.
    $data = [];
    
    //We must have a select clause by now. If we don't we select everything.
    if(!$this->hasClause('select')){
      $this->clause('select')->addColumn('*');
    }
    
    //We must have a from clause. If we don't we select from the main model.
    if(!$this->hasClause('from')){
      $this->clause('from')->addModel($this->main_model);
    }
    
    //A list of all mySQL clauses in the order of markup.
    $clauses = ['select', 'from', 'where', 'group', 'having', 'order', 'limit', 'procedure', 'into', 'for'];
    
    //Add all the clauses.
    foreach($clauses as $clause)
    {
      
      //Skip the clause if we don't have it.
      if(!$this->hasClause($clause)){
        continue;
      }
      
      //Add a space behind the previous clause. Never hurts.
      $query .= ' ';
      
      //Add the clause.
      $this->clause($clause)->extendString($query)->extendData($data);
      
    }
    
    //Trim the spaces off.
    $query = trim($query);
    
    //Enable chaining.
    return $this;
    
  }
  
  //Return a specific row when we execute.
  public function returnRow($index)
  {
    
    $this->return_row = (int) $index;
    
  }
  
  //Set the connection to use.
  public function setConnection($connection)
  {
    
    $this->connection = $connection;
    
  }
  
  //Adds a model and fills the $model variable with it.
  public function add($model_name, &$model=null)
  {
    
    //Is a component name given?
    if(substr_count($model_name, '.') == 1){
      list($component, $model_name) = explode('.', $model_name);
      $component = \classes\Component::get($component);
    }
    
    //Nope.
    else{
      $component = null;
    }
    
    //Fill the referenced model variable.
    $model = $this->addModel($model_name, $component);
    
    //Enable chaining.
    return $this;
    
  }
  
  //Adds a builder-model to our collection of models and returns it.
  public function addModel($model_name, \classes\Component $alternative_component=null)
  {
    
    //Create the model
    $model = new BuilderModel(
      $model_name,
      (is_null($alternative_component) ? $this->component : $alternative_component),
      $this
    );
    
    //Add it to the collection.
    $this->models[$model->getUnique()] = $model;
    
    //Return the model.
    return $model;
    
  }
  
  
  ##
  ## CLAUSE MODIFIERS
  ##
  
  //Add a column to the select clause.
  public function select($column, $alias=null)
  {
    
    $this->clause('select')->addColumn($column, $alias);
    
    return $this;
    
  }
  
  //Add a model to the from clause.
  public function from($model, &$class=null)
  {
    
    #TODO: Use a utility function to handle string input
    
    //Add to the clause.
    $this->clause('from')->addModel($model);
    
    //Add to reference.
    $class = $model;
    
    //Enable chaining.
    return $this;
    
  }
  
  //Join a foreign model.
  public function join($model, $type = null, &$class=null)
  {
    
    #TODO: Put in utility method
    #TODO: Use present models
    //We can give the model as a string to internally call addModel.
    if(is_string($model))
    {
      
      //Get the component name and the model name from the key.
      list($component_name, $model_name) = (substr_count($model, '.') == 1 
        ? explode('.', $model)
        : [$this->working_model->getComponent()->name, $model]
      );
      
      //Create the model.
      $model = $this->addModel($model_name, \classes\Component::get($component_name));
      
    }
    
    //We must now have an instance of BuilderModel.
    if(!($model instanceof BuilderModel)){
      throw new \exception\InvalidArgument(
        'Expecting an instance of BuilderModel. %s given.', typeof($model)
      );
    }
    
    //Add the join to the FROM clause.
    $this->clause('from')->joinModel($model, $type);
    
    //Add to reference.
    $class = $model;
    
    //Enable chaining.
    return $this;
    
  }
  
  //Add more conditions to the where clause.
  public function where()
  {
    
    //A condition object was provided. Pass it directly to the where clause.
    if(func_get_arg(0) instanceof BuilderCondition){
      $this->clause('where')->addCondition(func_get_arg(0));
    }
    
    //Create a factory, call it to create a condition and pass that on to the where clause.
    else{
      $this->clause('where')->addCondition(call_user_func_array($this->conditionFactory(), func_get_args()));
    }
    
    //Enable chaining.
    return $this;
    
  }
  
  //Add to the group clause.
  public function group($by, $direction=null)
  {
    
    //Add to the clause.
    $this->clause('group')->by($by, $direction);
    
    //Enable chaining.
    return $this;
    
  }
  
  //Add the roll-up modifier to the group clause.
  public function groupRollup()
  {
    
    //Add to the clause.
    $this->clause('group')->withRollup();
    
    //Enable chaining.
    return $this;
    
  }
  
  //Add more conditions to the having clause.
  public function having()
  {
    
    //A condition object was provided. Pass it directly to the having clause.
    if(func_get_arg(0) instanceof BuilderCondition){
      $this->clause('having')->addCondition(func_get_arg(0));
    }
    
    //Create a factory, call it to create a condition and pass that on to the having clause.
    else{
      $this->clause('having')->addCondition(call_user_func_array($this->conditionFactory(), func_get_args()));
    }
    
    //Enable chaining.
    return $this;
    
  }
  
  //Add to the order clause.
  public function order($by, $direction=null)
  {
    
    //Add to the clause.
    $this->clause('order')->by($by, $direction);
    
    //Enable chaining.
    return $this;
    
  }
  
  //Set the limit clause.
  public function limit($input, $offset=null)
  {
    
    //Set the limit.
    $clause = $this->clause('limit');
    $clause->setLimit($input);
    
    //Optionally set the offset.
    if(!is_null($offset)){
      $clause->setOffset($offset);
    }
    
    //Enable chaining.
    return $this;
    
  }
  
  
  ##
  ## FACTORY METHODS
  ##
  
  //Sets $factory to a condition factory.
  public function conditionFactory(&$factory)
  {
    
    //Create the factory.
    $factory = function(){
      return new \classes\sql\BuilderCondition($this, func_get_args());
    };
    
    //Enable chaining.
    return $this;
    
  }
  
  //Sets $factory to a function factory.
  public function functionFactory(&$factory)
  {
    
    #TODO: Create the functionFactory method.
    
  }
  
  ##
  ## UTILITY METHODS
  ##
  
  //Detect what kind of input has been given and use it to create a string usable by the query.
  public function prepare($input)
  {
    
    //This just stays untouched.
    if($input === '*'){
      return $input;
    }
    
    //Use a model.
    if($input instanceof BuilderModel){
      return "`{$input->getUnique()}`";
    }
    
    //Use a column.
    if($input instanceof BuilderColumn){
      return $this->prepare($input->model).'.'.$input->getString();
    }
    
    //Use a sub query.
    if($input instanceof \Closure)
    {
      
      //Get the sub-query.
      $query = $input();
      
      //Test if it's OK.
      if(!($query instanceof Query)){
        throw new \exception\Restriction(
          'Sub-queries must return an instance of Query. %s given.',
          typeof($query)
        );
      }
      
      //Return the string value of it.
      return "(".$query->getQuery().")";
      
    }
    
    //Get the string of a condition or function.
    if($input instanceof BuilderCondition || $input instanceof BuilderFunction){
      return $input->getString();
    }
    
    //A string input must be handled by the method preparing it.
    return false;
    
  }
  
  //Validate if a model is in fact a model, and if it's being used in this Builder.
  public function assertModel($model)
  {
    
    //Must be an BuilderModel.
    if(!($model instanceof BuilderModel)){
      throw new \exception\InvalidArgument(
        'Expecting an instance of SqlBuilderModel. %s given.', typeof($model)
      );
    }
    
    //Must use this.
    if($model->getBuilder() !== $this){
      throw new \exception\Restriction(
        'Only models that are being used in this builder (%s) can be used. You used one for %s.',
        get_object_name($this), get_object_name($model)
      );
    }
    
    //Enable chaining.
    return $this;
    
  }
  
  //Validate if an alias has the right format.
  public function validateAlias($alias)
  {
    
    //It must be a string.
    if(!is_string($alias)){
      throw new \exception\InvalidArgument('Expecting $alias to be string. %s given.', typeof($alias));
    }
    
    //It must be of the following format.
    if(preg_match('~^[a-zA-Z0-9_]+$~', $alias) !== 1){
      throw new \exception\InvalidArgument('$alias Is not the right format.');
    }
    
    //Enable chaining.
    return $this;
    
  }
  
  //Add an alias to the builder.
  public function addAlias($alias)
  {
    
    //Is it even available.
    if(in_array($alias, $this->aliases)){
      throw new \exception\Restriction('The "%s"-alias is already being used.', $alias);
    }
    
    //Add it.
    $this->aliases[] = $alias;
    
    //Enable chaining.
    return $this;
    
  }
  
  //Return the clause-object for this clause.
  public function clause($name)
  {
    
    //It's easy when we already have this clause.
    if($this->hasClause($name)){
      return $this->clauses[$name];
    }
    
    //Otherwise we need to figure out the class name.
    $class_name = 'classes\\sql\\clauses\\'.ucfirst($name);
    
    //Then check if it even exists.
    if(!class_exists($class_name)){
      throw new \exception\Programmer('No clause called %s.', $name);
    }
    
    //Then create an instance of it.
    $this->clauses[$name] = (new $class_name($this));
    
    //Then return it.
    return $this->clause($name);
    
  }
  
  //Return true if we created the given clause in this query. False otherwise.
  public function hasClause($name)
  {
    
    return array_key_exists($name, $this->clauses);
    
  }
  
}
