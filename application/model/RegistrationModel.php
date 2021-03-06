<?php

/**
 * Class RegistrationModel
 *
 * Everything registration-related happens here.
 */
class RegistrationModel
{
	/**
	 * Handles the entire registration process for DEFAULT users (not for people who register with
	 * 3rd party services, like facebook) and creates a new user in the database if everything is fine
	 *
	 * @return boolean Gives back the success status of the registration
	 */
	public static function preRegisterNewUser()
	{
		// clean the input
		$user_email = strip_tags(Request::post('user_email'));
		$user_first_name = strip_tags(Request::post('user_first_name'));
		$user_last_name = strip_tags(Request::post('user_last_name'));

		// stop registration flow if registrationInputValidation() returns false (= anything breaks the input check rules)
		$validation_result = self::preRegistrationInputValidation($user_email, $user_first_name, $user_last_name);
		if (!$validation_result) {
			return false;
		}

		// check if email already exists
		if (UserModel::doesEmailAlreadyExist($user_email)) {
			Session::add('feedback_negative', Text::get('FEEDBACK_USER_EMAIL_ALREADY_TAKEN'));
			return false;
		}

		// generate random hash for email verification (40 char string)
		$user_activation_hash = sha1(uniqid(mt_rand(), true));

		// write user data to database
		if (!self::preWriteNewUserToDatabase($user_email, $user_first_name, $user_last_name, time(), $user_activation_hash)) {
			Session::add('feedback_negative', Text::get('FEEDBACK_ACCOUNT_CREATION_FAILED'));
            return false; // no reason not to return false here
		}

		// get user_id of the user that has been created, to keep things clean we DON'T use lastInsertId() here
		$user_id = UserModel::getUserIdByEmailAddress($user_email);

		if (!$user_id) {
			Session::add('feedback_negative', Text::get('FEEDBACK_UNKNOWN_ERROR'));
			return false;
		}

		// send verification email
		if (self::sendVerificationEmail($user_id, $user_email, $user_activation_hash)) {
			Session::add('feedback_positive', Text::get('FEEDBACK_ACCOUNT_SUCCESSFULLY_CREATED'));
			return true;
		}

		// if verification email sending failed: instantly delete the user
		self::rollbackRegistrationByUserId($user_id);
		return false;
	}

	/**
	 * Validates the registration input
	 *
	 * @param $user_email
	 * @return bool
	 */
	public static function preRegistrationInputValidation($user_email, $user_first_name, $user_last_name)
	{
        // if username, email and password are all correctly validated, but make sure they all run on first sumbit
        if (self::validateUserEmail($user_email) AND self::validateUserName($user_first_name, $user_last_name)) {
            return true;
        }

		// otherwise, return false
		return false;
	}

    /**
     * Validates the email
     *
     * @param $user_email
     * @return bool
     */
    public static function validateUserEmail($user_email)
    {
        if (empty($user_email)) {
            Session::add('feedback_negative', Text::get('FEEDBACK_EMAIL_FIELD_EMPTY'));
            return false;
        }

        // validate the email with PHP's internal filter
        // side-fact: Max length seems to be 254 chars
        // @see http://stackoverflow.com/questions/386294/what-is-the-maximum-length-of-a-valid-email-address
        if (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
            Session::add('feedback_negative', Text::get('FEEDBACK_EMAIL_DOES_NOT_FIT_PATTERN'));
            return false;
        }

        return true;
    }

    /**
     * Validates the names
     *
     * @param $user_first_name, $user_last_name
     * @return bool
     */
    public static function validateUserName($user_first_name, $user_last_name)
    {
        if (empty($user_first_name) OR empty($user_last_name)) {
            Session::add('feedback_negative', Text::get('FEEDBACK_NAME_FIELD_EMPTY'));
            return false;
        }

        return true;
    }

    /**
     * Validates the password
     *
     * @param $user_password_new
     * @param $user_password_repeat
     * @return bool
     */
    public static function validateUserPassword($user_password_new, $user_password_repeat)
    {
        if (empty($user_password_new) OR empty($user_password_repeat)) {
            Session::add('feedback_negative', Text::get('FEEDBACK_NAME_FIELD_EMPTY'));
            return false;
        }

        return true;
    }

	/**
	 * Writes the new user's data to the database
	 *
	 * @param $user_name
	 * @param $user_password_hash
	 * @param $user_email
	 * @param $user_creation_timestamp
	 * @param $user_activation_hash 
	 *
	 * @return bool
	 */
	public static function preWriteNewUserToDatabase($user_email, $user_first_name, $user_last_name, $user_creation_timestamp, $user_activation_hash)
	{
		$database = DatabaseFactory::getFactory()->getConnection();
		// write new users data into database
		$sql = "INSERT INTO users (user_email, user_first_name, user_last_name, user_creation_timestamp, user_activation_hash, user_provider_type)
                    VALUES (:user_email, :user_first_name, :user_last_name, :user_creation_timestamp, :user_activation_hash, :user_provider_type)";
		$query = $database->prepare($sql);
		$query->execute(array(':user_email' => $user_email,
							  ':user_first_name' => $user_first_name,
							  ':user_last_name' => $user_last_name,
		                      ':user_creation_timestamp' => $user_creation_timestamp,
		                      ':user_activation_hash' => $user_activation_hash,
		                      ':user_provider_type' => 'DEFAULT'));
		$count =  $query->rowCount();
		if ($count == 1) {
			return true;
		}

		return false;
	}

	/**
	 * Deletes the user from users table. Currently used to rollback a registration when verification mail sending
	 * was not successful.
	 *
	 * @param $user_id
	 */
	public static function rollbackRegistrationByUserId($user_id)
	{
		$database = DatabaseFactory::getFactory()->getConnection();

		$query = $database->prepare("DELETE FROM users WHERE user_id = :user_id");
		$query->execute(array(':user_id' => $user_id));
	}

	/**
	 * Sends the verification email (to confirm the account).
	 * The construction of the mail $body looks weird at first, but it's really just a simple string.
	 *
	 * @param int $user_id user's id
	 * @param string $user_email user's email
	 * @param string $user_activation_hash user's mail verification hash string
	 *
	 * @return boolean gives back true if mail has been sent, gives back false if no mail could been sent
	 */
	public static function sendVerificationEmail($user_id, $user_email, $user_activation_hash)
	{
		$body = Config::get('EMAIL_VERIFICATION_CONTENT') . Config::get('URL') . Config::get('EMAIL_VERIFICATION_URL')
		        . '/' . urlencode($user_id) . '/' . urlencode($user_activation_hash);

		$mail = new Mail;
		$mail_sent = $mail->sendMail($user_email, Config::get('EMAIL_VERIFICATION_FROM_EMAIL'),
			Config::get('EMAIL_VERIFICATION_FROM_NAME'), Config::get('EMAIL_VERIFICATION_SUBJECT'), $body
		);
		
		if ($mail_sent) {
			return true;
		} else {
			Session::add('feedback_negative', Text::get('FEEDBACK_VERIFICATION_MAIL_SENDING_ERROR') . $mail->getError() );
			return false;
		}
	}

	/**
	 * checks the email/verification code combination in the database
	 *
	 * @param int $user_id user id
	 * @param string $user_activation_verification_code verification token
	 *
	 * @return bool success status
	 */
	public static function verifyNewUser($user_id, $user_activation_verification_code)
	{
		$database = DatabaseFactory::getFactory()->getConnection();

		$sql = "SELECT user_id FROM users WHERE user_active = '0' AND user_id = :user_id AND user_activation_hash = :user_activation_hash LIMIT 1";
		$query = $database->prepare($sql);
		$query->execute(array(':user_id' => $user_id, ':user_activation_hash' => $user_activation_verification_code));

		if ($query->rowCount() == 1) {
			return true;
		}

		Session::add('feedback_negative', Text::get('FEEDBACK_ACCOUNT_ACTIVATION_FAILED'));
		return false;
	}

	/**
	 * TODO: images should be regenerated to protect against malicious injection.
	 * Handles the entire registration process for DEFAULT users (not for people who register with
	 * 3rd party services, like facebook) and creates a new user in the database if everything is fine
	 *
	 * @return boolean Gives back the success status of the registration
	 */
	public static function postRegisterNewUser()
	{
		// clean the input
		$user_id = Request::post('user_id');
		$user_activation_verification_code = Request::post('user_activation_verification_code');
        $user_password_new = Request::post('user_password_new');
        $user_password_repeat = Request::post('user_password_repeat');

        // stop registration flow if registrationInputValidation() returns false (= anything breaks the input check rules)
        $validation_result = self::verifyNewUser($user_id, $user_activation_verification_code);

        if(!$validation_result) {
        	// TODO: throw no user exits
        	return false;
        }

        // crypt the password with the PHP 5.5's password_hash() function, results in a 60 character hash string.
        // @see php.net/manual/en/function.password-hash.php for more, especially for potential options
        $user_password_hash = password_hash($user_password_new, PASSWORD_DEFAULT);

        $validation_result = self::postRegistrationInputValidation(/*$user_image,*/ $user_password_new, $user_password_repeat);

        if (!$validation_result) {
            return false;
        }

		// write user data to database
		if (!self::postWriteNewUserToDatabase($user_id, $user_password_hash, $user_activation_verification_code)) {
			Session::add('feedback_negative', Text::get('FEEDBACK_ACCOUNT_CREATION_FAILED'));
            return false;
		}

		return true;
	}

	/**
	 * Validates the registration input
	 *
	 * @param $user_email
	 * @return bool
	 */
	public static function postRegistrationInputValidation($user_password_new, $user_password_repeat)
	{
        // if username, email and password are all correctly validated, but make sure they all run on first sumbit
        if (self::validateUserPassword($user_password_new, $user_password_repeat)) {
            return true;
        }

		return false;
	}

		/**
	 * Writes the new user's data to the database
	 *
	 * @param $user_password_hash
	 * @param $user_email
	 * @param $user_creation_timestamp
	 * @param $user_activation_hash 
	 *
	 * @return bool
	 */
	public static function postWriteNewUserToDatabase($user_id, $user_password_hash, $user_activation_hash)
	{
        $database = DatabaseFactory::getFactory()->getConnection();
        
        // user_image = :user_image,
        $sql = "UPDATE users SET user_active = 1, user_activation_hash = NULL, user_password_hash = :user_password_hash 
                WHERE user_id = :user_id AND user_activation_hash = :user_activation_hash LIMIT 1";
      
        $query = $database->prepare($sql);
        $query->execute(array(
        	':user_id' => $user_id, 
			':user_activation_hash' => $user_activation_hash,
			'user_password_hash' => $user_password_hash
		));
  
        if ($query->rowCount() == 1) {
            Session::add('feedback_positive', Text::get('FEEDBACK_ACCOUNT_ACTIVATION_SUCCESSFUL'));
            return true;
        }
        Session::add('feedback_negative', Text::get('FEEDBACK_ACCOUNT_ACTIVATION_FAILED'));
        return false;
	}
}
