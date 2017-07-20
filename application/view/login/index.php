<div class="container">

    <!-- echo out the system feedback (error and success messages) -->
    <?php $this->renderFeedbackMessages(); ?>

    <div class="box">
        <form action="<?php echo Config::get('URL'); ?>login/login" method="post">
            <input type="text" name="user_email" placeholder="Email" required />
            <input type="password" name="user_password" placeholder="Password" required />
            <label class="remember-me">
                <input type="checkbox" name="set_remember_me_cookie" class="remember-me-checkbox" />
                Remember me
            </label>
            <div class="link-forgot-my-password">
                <a href="<?php echo Config::get('URL'); ?>login/requestPasswordReset">I forgot my password</a>
            </div>
            <?php if (!empty($this->redirect)) { ?>
                <input type="hidden" name="redirect" value="<?php echo $this->encodeHTML($this->redirect); ?>" />
            <?php } ?>
			<input type="hidden" name="csrf_token" value="<?= Csrf::makeToken(); ?>" />
            <input type="submit" class="login-submit-button" value="Log in"/>
        </form>
    </div>
</div>
