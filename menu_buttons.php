<?php include('approval_count.php'); ?>
<?php include('reject_count.php'); ?>

                <div class="col-6 col-md-3">
                    <a href ="index.php" class="btn btn-outline-primary w-100
                    <?= ($current_page == 'index.php') ? 'active' : '' ?>">
                    📦 รายการสินค้าทั้งหมด</a>
                </div>

                <div class="col-6 col-md-3">
                    <a href ="create_gr.php" class="btn btn-outline-primary w-100
                    <?= ($current_page == 'create_gr.php') ? 'active' : '' ?>">
                    🚚 รับสินค้าเข้า</a>
                </div>

                <div class="col-6 col-md-3">
                    <a href="approval_requests.php"
                    class="btn btn-outline-primary w-100 position-relative
                    <?= ($current_page == 'approval_requests.php') ? 'active' : '' ?>">
                        📝 รายการคำขออนุมัติ

                        <?php if ($total_approval_count > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                <?= $total_approval_count ?>
                            </span>
                        <?php endif; ?>
                    </a>
                </div>
        
                <div class="col-6 col-md-3">
                    <a href ="create_gi.php" class="btn btn-outline-primary w-100
                    <?= ($current_page == 'create_gi.php') ? 'active' : '' ?>">
                    📤 เบิกสินค้าออก</a>
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
                    <a href ="transaction.php" class="btn btn-outline-primary w-100
                    <?= ($current_page == 'transaction.php') ? 'active' : '' ?>">
                    📜 ประวัติการเคลื่อนไหว</a>
                </div>

                <div class="col-6 col-md-3">
                    <a href ="approval_status.php" 
                    class="btn btn-outline-primary w-100 position-relative
                    <?= ($current_page == 'approval_status.php') ? 'active' : '' ?>">
                    ⌛ สถานะรายการขออนุมัติ

                        <?php if ($total_approval_count > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                <?= $total_approval_count ?>
                            </span>
                        <?php endif; ?>

                    </a>
                </div>

                <div class="col-6 col-md-3">
                    <a href ="reject_list.php" class="btn btn-outline-primary w-100
                    <?= ($current_page == 'reject_list.php') ? 'active' : '' ?>">
                    ❌ รายการที่ไม่ได้รับการอนุมัติ
                        <?php if ($total_reject_count > 0): ?>
                                <span class="badge bg-danger ms-2"><?= $total_reject_count ?></span>
                        <?php endif; ?>
                    </a>
                </div>


                <div class="col-6 col-md-3">
                    <a href ="approve_list.php" class="btn btn-outline-primary w-100
                    <?= ($current_page == 'approve_list.php') ? 'active' : '' ?>">
                    ✅ รายการที่ได้รับการอนุมัติแล้ว</a>
                </div>

                <div class="col-6 col-md-3">
                    <a href ="#" class="btn btn-outline-primary w-100"> ⚠️ รายการสินค้าที่ต้องซื้อเพิ่ม</a>
                </div>

                <div class="col-6 col-md-3">
                    <a href ="#" class="btn btn-outline-primary w-100"> 📊 รายงานผล</a>
                </div>