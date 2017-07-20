<div class="container">
    <?php $this->renderFeedbackMessages(); ?>

    <div class="box">
        <h1>Change your email address</h1>
        <form action="<?php echo Config::get('URL'); ?>user/changeEmail_action" method="post">
            <input type="text" name="user_email" placeholder="New email address" required />
            <input type="submit" value="Submit" />
        </form>
    </div>
</div>