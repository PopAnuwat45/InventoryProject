<?php
include('server.php');

if(isset($_POST['query'])){
    $query = $conn->real_escape_string($_POST['query']);

    $sql = "SELECT customer_id, customer_id_full, customer_name, contract_name, phone, email, payment_term
            FROM customer
            WHERE customer_id_full LIKE '%$query%' 
               OR customer_name LIKE '%$query%' 
               OR contract_name LIKE '%$query%'
            ORDER BY customer_name ASC
            LIMIT 10";
    $result = $conn->query($sql);

    if($result->num_rows > 0){
        while($row = $result->fetch_assoc()){
            echo '<div class="customer-item"
                    data-id="'.$row['customer_id'].'"
                    data-code="'.$row['customer_id_full'].'"
                    data-name="'.htmlspecialchars($row['customer_name']).'"
                    data-contract="'.htmlspecialchars($row['contract_name']).'"
                    data-phone="'.htmlspecialchars($row['phone']).'"
                    data-email="'.htmlspecialchars($row['email']).'"
                    data-term="'.htmlspecialchars($row['payment_term']).'">
                    '.$row['customer_id_full'].' - '.htmlspecialchars($row['customer_name']).' ('.htmlspecialchars($row['contract_name']).')
                  </div>';
        }
    } else {
        echo '<div class="customer-item text-muted">ไม่พบลูกค้า</div>';
    }
}
?>
