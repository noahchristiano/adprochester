<div class="container">
    <?php $this->renderFeedbackMessages(); ?>

    <div class="box">
        <h1>Complete registration</h1>
        <form method="post" action="<?php echo Config::get('URL'); ?>register/post_register_action" enctype="multipart/form-data">
            <input type='hidden' name='user_id' value='<?php echo $this->user_id;?>'/>
            <input type='hidden' name='user_activation_verification_code' value='<?php echo $this->user_activation_verification_code;?>'/>
            <input type="password" name="user_password_new" pattern=".{6,}" placeholder="Password (6+ characters)" required autocomplete="off" />
            <input type="password" name="user_password_repeat" pattern=".{6,}" required placeholder="Repeat your password" autocomplete="off" />

            <input type="submit" value="Register" />
        </form>
    </div>
</div>