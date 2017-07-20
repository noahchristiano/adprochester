<div class="container">
    <!-- echo out the system feedback (error and success messages) -->
    <?php $this->renderFeedbackMessages(); ?>
    
    <div class="box">
        <h1><?php echo "Brother " . $this->user_first_name . " " . $this->user_last_name; ?></h1>
        <a href="<?php echo Config::get('URL'); ?>user/changeEmail">
        	<div class="link">Update email</div>
        </a>
        <a href="<?php echo Config::get('URL'); ?>user/changePassword">
        	<div class="link">Update password</div>
        </a>
    </div>
</div>
