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
                // Redirect to member-index
                return $this->redirectToRoute('member-index');
            }
            else {
                // Destroy session
                $session->invalidate();

                // Redirect to account-login
                return $this->redirectToRoute('account-login');
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
                        $response = $this->redirectToRoute('member-index');

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
                // Set Member Parent Folder
                $path = '/Members' . '/' . date('Y');
                $memberParentFolder = DataObject\Service::createFolderByPath($path);
                // Get Member Parent Id
                $memberParentId = $memberParentFolder->getId();

                // Register new member and save it to Members DataObject
                $newMember = new DataObject\Members();
                $newMember->setValues([
                    'o_parentId' => $memberParentId,
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
                $response = $this->redirectToRoute('member-index');

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
        // Remove session values
        $session->remove('firstname');
        $session->remove('lastname');
        $session->remove('email');

        // Redirect to home page
        return $this->redirect('/');
    }
}
