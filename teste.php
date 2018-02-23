<?php
class teste{
private $values = [];

public function __call($name, $args){
//  echo $args;
  $metodo = substr($name,0,3);
  $val = substr($name,3,strlen($name));
//  echo "</br>".$val;
switch ($metodo) {
  case 'set':
  //  echo $args;
    $this->values[$val]=$args;
    break;
  case 'get':
    return (isset( $this->values[$val] )) ? $this->values[$val] : NULL;
    break;
  default:
    # code...
    break;
}
}

}
$teste = new teste();
$teste->setAmor(array("bussula"));
var_dump( $teste->getAmor());
//$teste->print();
 ?>
