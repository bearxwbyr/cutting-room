<?

class SinTween
{
  function __construct($qty)
  {
    $this->step = 180/($qty-1);
    $this->i = 90;
  }
  
  function step()
  {
    $next = 100*((sin(deg2rad($this->i))+1)/2);
    $this->i = max(-90,$this->i-$this->step);
    return $next;
  }
}