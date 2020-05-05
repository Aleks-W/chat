<?php
require_once '../vendor/autoload.php';

if ($_POST) {
    $post = filter_input_array(INPUT_POST, $_POST);

    $errors = [];

    //require field username
    if (empty($post['username'])) {
        $errors['username'] = 'Username is required';
    }

    //require email
    if (empty($post['email'])) {
        $errors['email'] = 'Email is required';
    }

    //require password
    if (empty($post['password'])) {
        $errors['password'] = 'Password is required';
    }

    if (empty($errors)) {
    	try {
		    $db = new \db\Db();
		    //unique username
		    if ($db->getUserByUsername($post['username'])) {
			    $errors['username'] = 'Not unique user!';
		    }
		    //unique email
		    if ($db->getUserByEmail($post['email'])) {
			    $errors['email'] = 'Not unique email!';
		    }
	    } catch(Throwable $e){
    		$errors['server'] = 'Internal error';
	    }
    }

    if (empty($errors)) {
        //save user
        $password_hash = password_hash($post['password'], PASSWORD_DEFAULT);
        $db->saveUser($post['username'], $post['email'], $password_hash);
        echo json_encode(['result' => true]);
        return;
    }
    echo json_encode(['result' => false,'errors' => $errors]);
}