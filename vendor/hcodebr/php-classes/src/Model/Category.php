<?php

namespace Hcode\Model;

Use \Hcode\DB\Sql;
Use \Hcode\Model;
Use \Hcode\Mailer;

class Category extends Model{

  public static function listAll(){
    $sql = new Sql();
    return $sql->select("SELECT * FROM tb_categories ORDER BY descategory");
  }

  public function save(){
    $sql = new Sql();
    $results = $sql->select("CALL sp_categories_save(:idcategory,  :descategory)",
      array(//sp_categories_save
        ":idcategory"=> $this->getidcategory(),
        ":descategory"=>$this->getdescategory()
      ));

      $this->setData($results[0]);
        category::updateFile();
  }
  public function get($idcatgory){
  //  echo "get------".$idcatgory; exit;
    $sql = new Sql();

    $results = $sql->select("SELECT * FROM tb_categories WHERE idcategory = :idcategory", array(
      ":idcategory"=>$idcatgory
    ));
  //  var_dump($results[0]);
  //  echo "----</br>";
    $this->setData($results[0]);
  //  echo "----</br>";
  // var_dump( $this->getData($results[0])) ;    exit;
  }
  public function delete(){
    $sql = new Sql();
    $sql->query("DELETE FROM tb_categories WHERE idcategory = :idcategory", [
            ":idcategory"=>$this-> getidcategory()
    ]);
    category::updateFile();
    //echo "delete---- ---".$this->getidcategory(); exit;
  }
  public static function updateFile()
  {
    $categories = Category::listAll();
    $html = [];
    foreach ($categories as $row) {
      array_push($html, '<li> <a href="/categories/'.$row['idcategory'].'">'.$row['descategory'].'</a></li>' );
    }
    file_put_contents($_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR."views".DIRECTORY_SEPARATOR."categories-menu.html", implode('',$html));
  }

  public function getProducts($reLated = true)
  {
    $sql = new Sql();

    if($reLated === true){
      return $sql->select("
          SELECT * FROM tb_products where idproduct in(
          	select a.idproduct
          	from tb_products a
          	inner join tb_productscategories b on a.idproduct = b.idproduct
          	where b.idcategory = :idcategory
          );
      ", [
        ":idcategory" => $this->getidcategory()
      ]);
    } else {
      return $sql->select("
        SELECT * FROM tb_products where idproduct not in(
        	select a.idproduct
        	from tb_products a
        	inner join tb_productscategories b on a.idproduct = b.idproduct
        	where b.idcategory = :idcategory
        );
      ",[
        ":idcategory" => $this -> getidcategory()
      ]);
    }
  }


  public function addProduct(Product $product)
  {
    $sql = new Sql();
    $sql->query("INSERT INTO tb_productscategories (idcategory, idproduct) VALUES (:idcategory, :idproduct)",
    [
      ":idcategory"=>$this->getidcategory(),
      "idproduct"=>$product->getidproduct()
    ]);
  }
  public function removeProduct(Product $product){
    $sql = new Sql();
    $sql->query("DELETE FROM tb_productscategories WHERE idcategory = :idcategory AND  idproduct = :idproduct",
    [
      ":idcategory"=>$this->getidcategory(),
      "idproduct"=>$product->getidproduct()
    ]);

  }
  public function getProductsPage($page = 1, $itemsPerPage = 8)
  {
    $start = ($page -1)* $itemsPerPage;
    $sql = new Sql();
    $results = $sql->select("
      SELECT SQL_CALC_FOUND_ROWS * FROM tb_products a
      INNER JOIN tb_productscategories b on a.idproduct = b.idproduct
      INNER JOIN tb_categories c on c.idcategory = b.idcategory
      where c.idcategory  = :idcategory
      limit $start, $itemsPerPage;
    ",[
      ':idcategory'=>$this->getidcategory()
    ]);
    $resultTotal = $sql->select("SELECT FOUND_ROWS() AS nrtotal");

    return [
      'data'=>Product::checkList($results),
      'total'=>(int)$resultTotal[0]['nrtotal'],
      'pages'=>ceil($resultTotal[0]['nrtotal']/$itemsPerPage)
    ];
  }

  public static function getPage($page, $itemsPerPage = 10)
  {

      $start = ($page -1)* $itemsPerPage;
      $sql = new Sql();
      $results = $sql->select("
        SELECT SQL_CALC_FOUND_ROWS *
        FROM tb_categories
        ORDER BY descategory
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
        FROM tb_categories b
        WHERE b.descategory LIKE :search
        ORDER BY descategory
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
