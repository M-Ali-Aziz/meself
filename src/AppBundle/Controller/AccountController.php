<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Pimcore\Model\DataObject;
use Pimcore\Tool\Frontend;


/**
 * Class AccountController
 *
 * Controller that handles all account functionality, including register, login and connect to SSO profiles
 */
class AccountController extends BaseController
{
    /**
     * Account Login
     *
     * @Route("/account/login", name="account-login")
     *
     * @param Request $request
     * @param SessionInterface $session
     *
     * @return  Array|RedirectResponse
     */
    public function loginAction(
        Request $request,
        SessionInterface $session
    ) {
        // Start session
        $session->start();

        // Redirect member to index page if logged in
        if ($session->get('email')) {
            // Get member
            $member = $this->getMember($session->get('email'));

            if ($member) {
                // Redirect to account-index
                return $this->redirectToRoute('account-index');
            }
        }
        // Check member login data and then redirect to index page
        elseif ($request->get('email')) {
            // Get member
            $member = $this->getMember($request->get('email'));

            if ($member) {
                if ($request->get('password')) {
                    // Check password
                    if (password_verify($request->get('password'), $member->getPassword())) {

                        // Set Session
                        $session->set('firstname', $member->getFirstname());
                        $session->set('lastname', $member->getLastname());
                        $session->set('email', $request->get('email'));

                        // Set response
                        $response = $this->redirectToRoute('account-index');

                        return $response;
                    }
                    else {
                        $errors[] = 'Invalid email or password, Please check your credentials.';
                    }
                }
            }
            else {
                $errors[] = 'Invalid email or password, Please check your credentials.';
            }
        }

        return [
            'errors' => ($errors) ? $errors : [],
            'email' => ($email = $request->get('email')) ? $email : ''
        ];
    }

    /**
     * Account Register
     *
     * @Route("/account/register", name="account-register")
     *
     * @param Request $request
     * @param SessionInterface $session
     *
     * @return  array|RedirectResponse
     */
    public function registerAction(
        Request $request,
        SessionInterface $session
    ) {
        // Satrt session
        $session->start();

        // Get registration_form 
        $registrationFormData = $request->get('registration_form');

        if ($registrationFormData) {
            // Get registration_form Data
            $firstname = $registrationFormData['firstname'];
            $lastname = $registrationFormData['lastname'];
            $email = $registrationFormData['email'];
            $password = $registrationFormData['password'];

            // Check if member already exists
            $memberEmail = DataObject\Members::getByEmail($email);
            $memberExists = ($memberEmail->count() > 0) ? true : false;

            if ($memberExists == false) {
                // Get Members DataObject Folder ID 
                $membersParentId = Frontend::getWebsiteConfig()->get('membersParentId');

                // Register new member and save it to Members DataObject
                $newMember = new DataObject\Members();
                $newMember->setValues([
                    'o_parentId' => $membersParentId,
                    'o_key' => $email,
                    'o_published' => true,
                    'firstname' => $firstname,
                    'lastname' => $lastname,
                    'email' => $email,
                    'password' => $password
                ]);
                $newMember->save();

                // Set Session
                $session->set('firstname', $firstname);
                $session->set('lastname', $lastname);
                $session->set('email', $email);

                // Set response
                $response = $this->redirectToRoute('account-index');

                return $response;
            }
            else {
                return [
                    'error' => 'A member with email: '. $email . ' already exsist!',
                    'registration_form' => $registrationFormData
                ];
            }
        }
    }

    /**
     * Account Index page
     *
     * @Route("/account/index", name="account-index")
     *
     * @param Request $request
     * @param SessionInterface $session
     */
    public function indexAction (
        Request $request,
        SessionInterface $session
    ) {
        // Satrt session
        $session->start();

        // Set variable
        $memberExists = false;

        // Get member
        $member = $this->getMember($session->get('email'));

        if ($member) {
            // Set variables
            $memberExists = true;
            $firstname = $member->getFirstname();
            $lastname = $member->getLastname();
        }

        // Set variables to view
        $this->view->memberExists = $memberExists;
        $this->view->firstname = ($firstname) ? $firstname : '';
        $this->view->lastname = ($lastname) ? $lastname : '';
        $this->view->email = ($email = $session->get('email')) ? $email : '';
    }

    /**
     * Account Logout
     *
     * @Route("/account/logout", name="account-logout")
     *
     * @param Request $request
     * @param SessionInterface $session
     */
    public function logoutAction (
        Request $request,
        SessionInterface $session
    ) {
        // Satrt session
        $session->start();
        // Destroy session
        $session->invalidate();

        // Redirect to home page
        return $this->redirect('/');
    }


    /**
     * Help function
     * Get member
     *
     * @param String $email
     *
     * @return Object|null
     */
    private function getMember($email)
    {
        // Get member
        $member = DataObject\Members::getByEmail($email, ['limit' => 1]);

        return $member;
    }







}
















