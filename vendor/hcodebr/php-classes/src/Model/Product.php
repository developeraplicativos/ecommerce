<?php

namespace Hcode\Model;

Use \Hcode\DB\Sql;
Use \Hcode\Model;
Use \Hcode\Mailer;

class Product extends Model{

  public static function listAll(){//listAll
    $sql = new Sql();
    return $sql->select("SELECT * FROM tb_products ORDER BY desproduct");
  }

  public function save(){

      $sql = new Sql();

  		$results = $sql->select("CALL sp_products_save(:idproduct, :desproduct, :vlprice, :vlwidth, :vlheight, :vllength, :vlweight, :desurl)", array(
  			":idproduct"=>$this->getidproduct(),
  			":desproduct"=>$this->getdesproduct(),
  			":vlprice"=>$this->getvlprice(),
  			":vlwidth"=>$this->getvlwidth(),
  			":vlheight"=>$this->getvlheight(),
  			":vllength"=>$this->getvllength(),
  			":vlweight"=>$this->getvlweight(),
  			":desurl"=>$this->getdesurl()
  		));
      //AUTO_INCREMENT
  		$this->setData( $results[0] );
  }

  public static function checklist($list){
    foreach ($list as &$row) {
      $p = new Product();
      $p->setData($row);
      $row=$p->getValues();
    }
    return $list;
  }


  public function delete(){
    $sql = new Sql();
    $sql->query("DELETE FROM tb_products WHERE idproduct = :idproduct", [
            ":idproduct"=>$this-> getidproduct()
    ]);


  }

  public function checkPhoto(){
    if(file_exists($_SERVER['DOCUMENT_ROOT'].
    DIRECTORY_SEPARATOR. "res" .
    DIRECTORY_SEPARATOR .  "site" .
    DIRECTORY_SEPARATOR. "img" .
    DIRECTORY_SEPARATOR. "products" .
    DIRECTORY_SEPARATOR. $this->getidproduct() . ".jpg"

    )) {
      $url =  "/res/site/img/products/".$this->getidproduct().".jpg";
    }else{
      $url =  "/res/site/img/product.jpg";
    }

    return $this->setdesphoto($url);
  }

  public function get($idproduct)
  {
    $sql = new Sql();
    $results = $sql->select("SELECT * FROM tb_products WHERE idproduct = :idproduct", [
      ':idproduct'=>$idproduct
    ]);
    $this->setData($results[0]);
  }

  public function getValues(){
    $this->checkPhoto();
    $values = parent::getValues();
    return $values;
  }

  public function setPhoto($file)
  {
    //echo 'chupa'; exit;
    $extension = explode('.', $file['name']);
    $extension = end($extension);
    //var_dump( $file); exit;
    switch ($extension) {
      case 'jpg':
      case 'jpeg':
      $image = imagecreatefromjpeg($file['tmp_name']);

        break;

    case 'gif':
      $image = imagecreatefromgif($file['tmp_name']);
        break;
    case 'png':
      $image = imagecreatefrompng($file['tmp_name']);
        break;
    }

    $destino = $_SERVER['DOCUMENT_ROOT'].
    DIRECTORY_SEPARATOR. "res" .
    DIRECTORY_SEPARATOR ."site" .
    DIRECTORY_SEPARATOR. "img" .
    DIRECTORY_SEPARATOR. "products" .
    DIRECTORY_SEPARATOR. $this->getidproduct() . ".jpg";
    imagejpeg($image, $destino);
    imagedestroy($image);

    $this->checkPhoto();
  }

  public function getFromURL($desurl)//getFromURL
  {
    $sql = new Sql();
    $rows = $sql->select("SELECT * FROM tb_products WHERE desurl = :desurl LIMIT 1", [
      ':desurl'=>$desurl
    ]);

    $this->setData($rows[0]);
  }
  public function getCategories()//getCategories
  {
  //  echo $this->getidproduct()."<------------------------";exit;
    $sql = new Sql();
    return $sql->select("
    SELECT * FROM tb_categories a INNER JOIN tb_productscategories b ON a.idcategory = b.idcategory
    WHERE b.idproduct = :idproduct
    ", [
      ':idproduct'=> $this->getidproduct()
    ]);
  }

  public static function getPage($page, $itemsPerPage = 10)
  {

      $start = ($page -1)* $itemsPerPage;
      $sql = new Sql();
      $results = $sql->select("
        SELECT SQL_CALC_FOUND_ROWS *
        FROM tb_products
        ORDER BY desproduct
        limit $start, $itemsPerPage;
      ");
      $resultTotal = $sql->select("SELECT FOUND_ROWS() AS nrtotal");

      return [
        'data'=>$results,
        'total'=>(int)$resultTotal[0]['nrtotal'],
        'pages'=>ceil($resultTotal[0]['nrtotal']/$itemsPerPage)
      ];
  }
  public static function getPageSearch($search, $page, $itemsPerPage = 10)
  {

      $start = ($page -1)* $itemsPerPage;
      $sql = new Sql();
      $results = $sql->select("
        SELECT SQL_CALC_FOUND_ROWS *
        FROM tb_products
        WHERE desproduct LIKE :search
        ORDER BY desproduct
        limit $start, $itemsPerPage;
      ", [
        ':search'=>'%'.$search.'%'
      ]);
      $resultTotal = $sql->select("SELECT FOUND_ROWS() AS nrtotal");

      return [
        'data'=>$results,
        'total'=>(int)$resultTotal[0]['nrtotal'],
        'pages'=>ceil($resultTotal[0]['nrtotal']/$itemsPerPage)
      ];
  }

}


?>