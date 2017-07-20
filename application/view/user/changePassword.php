<div class="container">
    <?php $this->renderFeedbackMessages(); ?>

    <div class="box">
        <h1>Set new password</h1>
        <form method="post" action="<?php echo Config::get('URL'); ?>user/changePassword_action" name="new_password_form">
            <input id="change_input_password_current" class="reset_input" type='password'
                   name='user_password_current' pattern=".{6,}" placeholder="Enter Current Password" required autocomplete="off"  />
            <input id="change_input_password_new" class="reset_input" type="password"
                   name="user_password_new" pattern=".{6,}" placeholder="New password (min. 6 characters)" required autocomplete="off" />
            <input id="change_input_password_repeat" class="reset_input" type="password"
                   name="user_password_repeat" pattern=".{6,}" placeholder="Repeat new password" required autocomplete="off" />
            <input type="submit"  name="submit_new_password" value="Submit new password" />
        </form>
    </div>
</div>