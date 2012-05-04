<?php namespace interfaces;

interface Outputter
{
  
  private $output;
  
  public function __construct($output);
  
  public function output();
  
}
