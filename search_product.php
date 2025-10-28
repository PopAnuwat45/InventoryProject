<?php
include('server.php');

if(isset($_POST['query'])){
    $query = $conn->real_escape_string($_POST['query']);

    $sql = "SELECT product_id, product_id_full, product_name, unit 
            FROM product 
            WHERE product_id_full LIKE '%$query%' OR product_name LIKE '%$query%'
            ORDER BY product_id_full ASC LIMIT 10";
    $result = $conn->query($sql);

    if($result->num_rows > 0){
        while($row = $result->fetch_assoc()){
            echo '<div class="product-item" 
                    data-id="'.$row['product_id'].'" 
                    data-code="'.$row['product_id_full'].'" 
                    data-name="'.htmlspecialchars($row['product_name']).'" 
                    data-unit="'.htmlspecialchars($row['unit']).'">
                    '.$row['product_id_full'].' - '.htmlspecialchars($row['product_name']).'
                  </div>';
        }
    } else {
        echo '<div class="product-item text-muted">ไม่พบสินค้า</div>';
    }
}
?>
