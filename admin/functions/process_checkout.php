<?php
include '../db.php';
$data=json_decode($_POST['cart_data'],true);

$conn->begin_transaction();
try{
  foreach($data as $i){
    $stmt=$conn->prepare(
      "UPDATE products 
       SET quantity=quantity-? 
       WHERE id=? AND quantity>=?"
    );
    $stmt->bind_param("iii",$i['qty'],$i['id'],$i['qty']);
    $stmt->execute();
    if($stmt->affected_rows==0){
      throw new Exception("Insufficient stock");
    }
  }
  $conn->commit();
  echo json_encode(["status"=>"success"]);
}catch(Exception $e){
  $conn->rollback();
  echo json_encode(["status"=>"error","message"=>$e->getMessage()]);
}

?>