<?php
include('server.php');

$sql_alert = "

SELECT COUNT(*) total FROM
(
SELECT
p.product_id,

IFNULL(SUM(
CASE
WHEN sm.movement_type='IN' THEN sm.movement_qty
WHEN sm.movement_type='OUT' THEN -sm.movement_qty
END
),0) stock_qty,

p.reorder_point

FROM product p

LEFT JOIN stock_movement sm
ON p.product_id = sm.product_id

GROUP BY p.product_id

HAVING stock_qty <= reorder_point

) t

";

$total_reorder_count = $conn->query($sql_alert)->fetch_assoc()['total'];
?>
