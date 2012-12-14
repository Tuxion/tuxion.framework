<?php namespace classes\data;

class NumberWrapper extends BaseScalarData
{
  
  //Validate and set the value.
  public function __construct($value)
  {
    
    raw($value);
    
    if(!(is_int($value) || is_float($value) || is_real($value) || is_long($value))){
      throw new \exception\InvalidArgument('Expecting $value to be a number. %s given.', typeof($value));
    }
    
    $this->value = $value;
    
  }
  
  //Cast the number to string.
  public function toString()
  {
    
    return new StringWrapper((string) $this->value);
    
  }
  
  //Return a StringWrapper containing the visual representation of this number.
  public function visualize()
  {
    
    return $this->toString();
    
  }
  
  //Return the wrapped alternative if this number is zero or lower.
  public function alt($alternative)
  {
    
    return (($this->value > 0) ? $this : wrap($alternative));
    
  }
  
  
  ##
  ## MATH
  ##
  
  //Returns the absolute value of this number.
  public function abs()
  {
    
    return new self(abs($this->value));
    
  }
  
  //Returns the arc-cosine of this number.
  public function acos()
  {
    
    return new self(acos($this->value));
    
  }
  
  //Returns the inverse hyperbolic cosine of this number.
  public function acosh()
  {
    
    return new self(acosh($this->value));
    
  }
  
  //Returns the arcsine of this number.
  public function asin()
  {
    
    return new self(asin($this->value));
    
  }
  
  //Returns the inverse hyperbolic sine of this number.
  public function asinh()
  {
    
    return new self(asinh($this->value));
    
  }
  
  //Returns the arctangent of this number as a numeric value between -PI/2 and PI/2 radians.
  public function atan()
  {
    
    return new self(atan($this->value));
    
  }
  
  //Returns the inverse hyperbolic tangent of this number.
  public function atanh()
  {
    
    return new self(atanh($this->value));
    
  }
  
  //Returns the value of this number rounded upwards to the nearest integer.
  public function ceil()
  {
    
    return new self(ceil($this->value));
    
  }
  
  //Returns the cosine of this number.
  public function cos()
  {
    
    return new self(cos($this->value));
    
  }
  
  //Returns the hyperbolic cosine of this number.
  public function cosh()
  {
    
    return new self(cosh($this->value));
    
  }
  
  //Divide.
  public function divide($n)
  {
    
    return new self($this->value / $n);
    
  }
  
  //Returns the value of Ex.
  public function exp()
  {
    
    return new self(exp($this->value));
    
  }
  
  //Returns the value of Ex - 1.
  public function expm1()
  {
    
    return new self(expm1($this->value));
    
  }
  
  //Returns the value of this number rounded downwards to the nearest integer.
  public function floor()
  {
    
    return new self(floor($this->value));
    
  }
  
  //Returns the value of this number to the power of n.
  public function pow($n)
  {
    
    return new self(pow($this->value, $n));
    
  }
  
  //Converts this number from one base to another, returns the result as string.
  public function rebase($from, $to)
  {
    
    return wrap(base_convert($this->value, $from, $to));
    
  }
  
  //Rounds this number to the nearest integer.
  public function round()
  {
    
    return new self(round($this->value));
    
  }
  
  //Returns the sine of this number.
  public function sin()
  {
    
    return new self(sin($this->value));
    
  }
  
  //Returns the hyperbolic sine of this number.
  public function sinh()
  {
    
    return new self(sinh($this->value));
    
  }
  
  //Returns the square root of this number.
  public function sqrt()
  {
    
    return new self(sqrt($this->value));
    
  }
  
  //Returns the tangent of an angle.
  public function tan()
  {
    
    return new self(tan($this->value));
    
  }
  
  //Multiply.
  public function times($n)
  {
    
    return new self($this->value * $n);
    
  }
  
  //Returns the hyperbolic tangent of an angle.
  public function tanh()
  {
    
    return new self(tanh($this->value));
    
  }
  
  
  ##
  ## BITS
  ##
  
  //Check if a bitwise haystack contains the needle bit.
  function hasBit($needle)
  {
    
    return new BooleanWrapper(($this->value & $needle) === $needle);
    
  }
  
  //Counts the amount of bits set to 1.
  function countBits()
  {
    
    $v = $this->value;
    $v = $v - (($v >> 1) & 0x55555555);
    $v = ($v & 0x33333333) + (($v >> 2) & 0x33333333);
    return new self((($v + ($v >> 4) & 0xF0F0F0F) * 0x1010101) >> 24);
    
  }

  
  ##
  ## INFO
  ##
  
  //Returns true if the number is finite.
  public function isFinite()
  {
    
    return is_finite($this->value);
    
  }
  
  //Returns true if the number is infinite.
  public function isInfinite()
  {
    
    return is_infinite($this->value);
    
  }
  
}
