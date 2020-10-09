<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Pimcore\Model\DataObject;
use Pimcore\Model\Asset;


/**
 * Class MemberController
 *
 * Controller that handles all member functionality
 */
class MemberController extends BaseController
{
    /**
     * Account Index page
     *
     * @Route("/member/index", name="member-index")
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

        // Get member
        $member = $this->getMember($session->get('email'));

        if ($member) {
            // Get Journal list
            $journalList = $this->getJournalList($member);
        }
        else{
            // Set response
            return $this->redirectToRoute('account-login');
        }

        // Set current Unix timestamp
        $now = time();

        // Set variables to view
        $this->view->email = ($email = $session->get('email')) ? $email : '';
        $this->view->now = $now;
        $this->view->journalList = $journalList;
    }

    /**
     * Add Note
     *
     * @Route("/member/add-note", name="member-add-note")
     *
     * @param Request $request
     *
     * @return  RedirectResponse
     */
    public function addNewNoteAction(
        Request $request
    ) {
        // Get addNewNote_form Data
        $email = $request->get('email');
        $noteType = $request->get('noteType');
        $emoji = $request->get('emoji');
        $dateTime = date('Y-m-d, H:i', strtotime($request->get('dateTime')));
        $image = $request->files->get('image');
        $text = $request->get('text');

        // Get memeber by email
        $member = $this->getMember($email);

        if ($member) {
            // Get image
            if ($image) {
                $originalFilename = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
                $imageName = $originalFilename.'-'.uniqid().'.'.$image->guessExtension();
                $imageRealPath = $image->getRealPath();
                // Set Parent (Asset Folder)
                $parent = Asset::getByPath("/Journal");

                // Creating and saving new asset
                $newAsset = new Asset();
                $newAsset->setFilename($imageName);
                $newAsset->setData(file_get_contents($imageRealPath));
                $newAsset->setParent($parent);
                $newAsset->save();

                // Get image
                $image = Asset\Image::getByPath($parent.'/'.$imageName);
            }

            // Get Journals DataObject Folder ID 
            $journalParentId = DataObject::getByPath("/Journals")->getId();

            // Register new note and save it to Journal DataObject
            $newJournal = new DataObject\Journal();
            $newJournal->setValues([
                'o_parentId' => $journalParentId,
                'o_key' => ($email . time()),
                'o_published' => true,
                'noteType' => $noteType,
                'emoji' => $emoji,
                'dateTime' => $dateTime,
                'image' => $image,
                'text' => $text,
                'member' => [$member]
            ]);
            $newJournal->save();

            // Set response
            return $this->redirectToRoute('member-index');
        }
        else {
            // Set response
            return $this->redirectToRoute('account-login');
        }
    }

    /** ---------------------------------------------------
     * Help functions
     * ---------------------------------------------------- */

    /**
     * Help function
     * Get Journal List
     *
     * @param Object $member
     *
     * @return  Object|null
     */
    private function getJournalList($member)
    {
        $memberId = $member->getId();

        $journalList = new DataObject\Journal\Listing();
        $journalList->setCondition("member LIKE '%," . $memberId . ",%'");
        $journalList->setOrderKey("o_modificationDate");
        $journalList->setOrder("desc");

        return ($journalList = $journalList->load()) ? $journalList : null;
    }









}
