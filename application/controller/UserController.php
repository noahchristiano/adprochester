<?php

/**
 * UserController
 * Controls everything that is user-related
 */
class UserController extends Controller
{
    /**
     * Construct this object by extending the basic Controller class.
     */
    public function __construct()
    {
        parent::__construct();

        // VERY IMPORTANT: All controllers/areas that should only be usable by logged-in users
        // need this line! Otherwise not-logged in users could do actions.
        Auth::checkAuthentication();
    }

    /**
     * Show user's PRIVATE profile
     */
    public function index()
    {
        $this->View->render('user/index', array(
            'user_first_name' => Session::get('user_first_name'),
            'user_last_name' => Session::get('user_last_name'),
            'user_email' => Session::get('user_email')
        ));
    }

    /**
     * Show edit-my-user-email page
     */
    public function changeEmail()
    {
        $this->View->render('user/changeEmail');
    }

    /**
     * Edit user email (perform the real action after form has been submitted)
     */
    // make this POST
    public function changeEmail_action()
    {
        UserModel::changeEmail(Request::post('user_email'));
        Redirect::to('user/changeEmail');
    }
    
    /**
     * Password Change Page
     */
    public function changePassword()
    {
        $this->View->render('user/changePassword');
    }

    /**
     * Password Change Action
     * Submit form, if retured positive redirect to index, otherwise show the changePassword page again
     */
    public function changePassword_action()
    {
        $result = PasswordResetModel::changePassword(
            Session::get('user_email'), Request::post('user_password_current'),
            Request::post('user_password_new'), Request::post('user_password_repeat')
        );

        if($result)
            Redirect::to('user/index');
        else
            Redirect::to('user/changePassword');
    }
}
