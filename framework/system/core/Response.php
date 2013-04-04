<?php namespace core;

use \classes\route\Router;
use \classes\Materials;
use \classes\Render;
use \classes\data\UrlWrapper;
use \classes\locators\Template;
use \outputting\nodes\Standard as StandardNode;
use \outputting\error\Standard as StandardError;

class Response
{
  
  //Public properties.
  public
    $redirect_url=false;
  
  //Log the initialization.
  public function init()
  {
    
    //Enter a log entry.
    tx('Log')->message($this, 'Response class initialized.');
    
  }
  
  //Output.
  public function output()
  {
    
    //Get the path.
    $path = $start = tx('Request')->url->getPath();
    
    //Clean the path.
    $path = $path->clean();
    
    //Redirect if the router object changed the path.
    if($path->eq($start)->failure()){
      tx('Log')->message($this, 'clean path redirect', "'$start' -> '$path'");
      return $this->redirect(murl("/$path"))->_handleRedirect();
    }
    
    //Output.
    $this->outputRoute(
      tx('Request')->method(),
      $path->slice(wrap(tx('Config')->urls->path)->length()+2)->unwrap(),
      tx('Request')->data,
      tx('Request')->accept['mimes'][0]['value']
    );
    
  }
  
  //Uses the given Router object to forge a response for our client.
  public function outputRoute($method, $path, StandardNode $data, $mime, $part=null, $to_stream=true)
  {
    
    //Make the Materials and the Router.
    $materials = new Materials($data);
    $router = new Router($method, $path, $materials);
    
    //Try to execute the router.
    try
    {
      
      //Execute the router.
      $router->execute();
      
      //Get required information from the router.
      $part = (is_bool($part) ? $part : $router->isPart());
      
      //Use the mime-type that belongs to the router extension if the endpoint had one.
      if($router->getExt())
      {
        
        //Get the mime type.
        $newmime = tx('Mime')->getMime($router->getExt());
        
        //If no mime-type was found with this extension, the page does not exist.
        if($newmime === false){
          throw new \exception\NotFound('This page does not exist.');
        }
        
        //Actually set the mime type to this.
        $mime = $newmime;
        
      }
      
    }

    //An exception was caught. We will use this to create our page.
    catch(\exception\Exception $e)
    {
      
      $materials->exception($e);
      $part = (is_bool($part) ? $part : false);
      $mime = ($router->getExt()
        ? (tx('Mime')->getMime($router->getExt())
          ? tx('Mime')->getMime($router->getExt())
          : 'text/html')
        : $mime
      );
      
    }
    
    //The mime is now certain.
    $materials->mime = $mime;
    
    //Continue to the next step.
    return $this->outputMaterials($materials, $part, $to_stream);
    
  }
  
  //Use Materials to create an output.
  public function outputMaterials(Materials $materials, $part=null, $to_stream=true)
  {
    
    //Was output generated?
    if(!$materials->output){
      $materials->exception(new \exception\BadImplementation('The endpoint does not generate output.'));
    }
    
    //Get the inner template.
    $inner_template = (is_object($materials->inner_template)
      ? $this->getTemplate($materials->inner_template, $materials->mime)
      : false
    );
    
    //Get the outer template.
    $outer_template = (is_object($materials->outer_template) && !$part
      ? $this->getTemplate($materials->outer_template, $materials->mime)
      : false
    );
    
    //Convert the output using a templator?
    if($inner_template)
    {
      
      //Create the templator.
      $templator = $materials->output->createTemplator($materials);
      
      //Generate the template.
      $output_data = (new Render($templator, $inner_template, [
        'errors' => wrap($materials->errors),
        'warnings' => wrap($materials->warnings)
      ]))->generate();
      
    }
    
    //Convert the output using standard converters.
    else
    {
      
      //Try to get the converter.
      try{
        $converter = $materials->output->createConverterByMime($materials->mime);
      }
      
      //If the converter could not be loaded.
      catch(\exception\ResourceMissing $e)
      {
        
        //Create a NotFound exception.
        $new = new \exception\NotFound('This page does not exist.');
        $new->setPrev($e);
        
        //If we were already dealing with an exception.
        if($materials->output instanceof StandardError)
        {
          
          //If we were already dealing with HTML.
          if($materials->mime == 'text/html'){
            $e;
          }
          
          $materials->mime = 'text/html';
          
        }
        
        //Otherwise there might still be a chance.
        $materials->exception($new);
        return $this->outputMaterials(
          $materials,
          (is_bool($part) ? $part : false),
          $to_stream
        );
        
      }
      
      //Output to this variable.
      $output_data = $converter->output(false);
      
    }
    
    //Wrap output data in an outer template?
    if($outer_template)
    {
      
      //Create the outer-template data.
      $data = tx('Outputting')->standardize($output_data);
      
      //Create the templator.
      $templator = $data->createTemplator($materials);
      
      //Render.
      $output_data = (new Render(
        $templator,
        $outer_template,
        $materials->outer_template_data
      ))->generate();
      
    }
    
    //Output to stream?
    if($to_stream)
    {
      
      $output_data->setHeader('Status', $materials->getStatus());
      $output_data->setHeader('Content-Type', $materials->mime.'; charset=utf-8');
      $output_data->output();
      return;
      
    }
    
    //Return the output data.
    return $output_data;
        
  }
  
  //Return the right template file in given directory based on mime-type.
  public function getTemplate(Template $locator, $mime)
  {
    
    //Get the directory.
    $directory = $locator->locate();
    
    //Get all possible templates.
    $templates = files("$directory/{".implode(',', tx('Mime')->getTypes($mime)).'}.php', GLOB_BRACE);
    
    //This template does not exist.
    if(count($templates) == 0){
      return false;
    }
    
    //Huh?
    if(count($templates) > 1){
      throw new \exception\InternalServerError(
        'There are multiple "%s"-templates in "%s".', $mime, $directory
      );
    }

    return $templates[0];
    
  }
  
  //Set the URL to redirect to.
  public function redirect(UrlWrapper $url)
  {
    
    //Set the redirect URL.
    $this->redirect_url = $url;
    
    //Enable chaining.
    return $this;
    
  }
  
  //Returns true if we redirected, or false otherwise.
  public function redirected()
  {
    
    return ($this->redirect_url !== false);
    
  }
  
  //What to do when we have set a redirect.
  private function _handleRedirect()
  {
    
    if(!$this->redirected()){
      return $this;
    }
    
    header('Location: '.$this->redirect_url);
    
    return $this;
    
  }
  
}
