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
     */
    public function loginAction(Request $request)
    {

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
                // Get Members DataObject ID 
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

                $response = $this->redirectToRoute('account-index', [
                    'email' => $email
                ]);

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
     */
    public function indexAction (Request $request)
    {
        $memberExists = false;
        $email = $request->get('email');

        // Get member
        $member = DataObject\Members::getByEmail($email, ['limit' => 1]);

        if ($member) {
            // Set variables
            $memberExists = true;
            $firstname = $member->getFirstname();
            $lastname = $member->getLastname();
        }

        // Set variables to view
        $this->view->memberExists = $memberExists;
        $this->view->firstname = $firstname;
        $this->view->lastname = $lastname;
        $this->view->email = $email;
    }







}
















