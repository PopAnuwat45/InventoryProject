<?php include('approval_count.php'); ?>

                <div class="col-6 col-md-3">
                    <a href ="create_gr.php" class="btn btn-outline-primary w-100">🚚 รับสินค้าเข้า</a>
                </div>

                <div class="col-6 col-md-3">
                    <a href="approval_requests.php"
                    class="btn btn-outline-primary w-100 position-relative">
                        ✅ รายการคำขออนุมัติ

                        <?php if ($total_approval_count > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                <?= $total_approval_count ?>
                            </span>
                        <?php endif; ?>
                    </a>
                </div>
        
                <div class="col-6 col-md-3">
                    <a href ="create_gi.php" class="btn btn-outline-primary w-100">📤 เบิกสินค้าออก</a>
                </div>
                
                <div class="col-6 col-md-3">
                    <a href ="#" class="btn btn-outline-primary w-100">👤 จัดการผู้ใช้</a>
                </div>

                <div class="col-6 col-md-3">
                    <a href ="#" class="btn btn-outline-primary w-100">🛠 จัดการสินค้า</a>
                </div>

                <div class="col-6 col-md-3">
                    <a href ="#" class="btn btn-outline-primary w-100">🗄️ จัดการชั้นวางของ</a>
                </div>

                <div class="col-6 col-md-3">
                    <a href ="transaction.php" class="btn btn-outline-primary w-100">📜 ประวัติการเคลื่อนไหว</a>
                </div>

                <div class="col-6 col-md-3">
                    <a href ="#" class="btn btn-outline-primary w-100">⌛ สถานะรายการขออนุมัติ</a>
                </div>

                <div class="col-6 col-md-3">
                    <a href ="#" class="btn btn-outline-primary w-100">❌ รายการที่ไม่ได้รับการอนุมัติ</a>
                </div>