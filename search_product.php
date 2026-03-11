<?php
include('server.php');

if(isset($_POST['query'])){
    $query = $conn->real_escape_string($_POST['query']);

    $sql = "SELECT p.product_id, p.product_id_full, p.product_name, u.unit_name
            FROM product p
            LEFT JOIN unit u ON p.unit_id = u.unit_id
            WHERE product_id_full LIKE '%$query%' OR product_name LIKE '%$query%'
            ORDER BY product_id_full ASC LIMIT 10";
    $result = $conn->query($sql);

    if($result->num_rows > 0){
        while($row = $result->fetch_assoc()){
            echo '<div class="product-item" 
                    data-id="'.$row['product_id'].'" 
                    data-code="'.$row['product_id_full'].'" 
                    data-name="'.htmlspecialchars($row['product_name']).'" 
                    data-unit="'.htmlspecialchars($row['unit_name']).'">
                    '.$row['product_id_full'].' - '.htmlspecialchars($row['product_name']).'
                  </div>';
        }
    } else {
        echo '<div class="product-item text-muted">ไม่พบสินค้า</div>';
    }
}
?>
