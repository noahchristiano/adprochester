<div class="container">

    <!-- echo out the system feedback (error and success messages) -->
    <?php $this->renderFeedbackMessages(); ?>

    <div class="box">
        <h1>Set new password</h1>
        <form method="post" action="<?php echo Config::get('URL'); ?>login/setNewPassword" name="new_password_form">
            <input type='hidden' name='user_email' value='<?php echo $this->user_email; ?>' />
            <input type='hidden' name='user_password_reset_hash' value='<?php echo $this->user_password_reset_hash; ?>' />
            <input id="reset_input_password_new" class="reset_input" type="password"
                   name="user_password_new" pattern=".{6,}" placeholder="New password (min. 6 characters)" required autocomplete="off" />
            <input id="reset_input_password_repeat" class="reset_input" type="password"
                   name="user_password_repeat" pattern=".{6,}" placeholder="Repeat new password" required autocomplete="off" />
            <input type="submit"  name="submit_new_password" value="Submit new password" />
        </form>
    </div>
</div>