<div class="container">
    <?php $this->renderFeedbackMessages(); ?>

    <div class="box">
        <h1>Register a new account</h1>
        <form method="post" action="<?php echo Config::get('URL'); ?>register/pre_register_action">
            <input type="text" name="user_email" placeholder="email address (a real address)" required />
            <input type="text" name="user_first_name" placeholder="first name" required />
            <input type="text" name="user_last_name" placeholder="last name" required />
            <input type="submit" value="Register" />
        </form>
    </div>
</div>