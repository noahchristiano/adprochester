<div class="container">
    <?php $this->renderFeedbackMessages(); ?>

    <div class="box">
        <h1>Request a password reset</h1>
        <form method="post" action="<?php echo Config::get('URL'); ?>login/requestPasswordReset_action">
            <input type="text" name="user_email" placeholder="email address" required />
            <input type="submit" value="Send me a password-reset email" />
        </form>
    </div>
</div>