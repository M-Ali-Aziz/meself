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
     * Member Index page
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

        if (!$member) {
            // Set response
            return $this->redirectToRoute('account-login');
        }
    }

    /**
     * Member Diary page
     *
     * @Route("/member/diary", name="member-diary")
     *
     * @param Request $request
     * @param SessionInterface $session
     */
    public function diaryAction (
        Request $request,
        SessionInterface $session
    ) {
        // Satrt session
        $session->start();

        // Get member
        $member = $this->getMember($session->get('email'));

        if ($member) {
            // Get Diary list
            $diaryList = $this->getDiaryList($member);
        }
        else{
            // Set response
            return $this->redirectToRoute('account-login');
        }

        // Set current Unix timestamp
        $now = time();

        // Set variables to view
        $this->view->now = $now;
        $this->view->diaryList = $diaryList;
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
            // Get and save image
            if ($image) {
                $originalFilename = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
                $imageName = $originalFilename.'-'.uniqid().'.'.$image->guessExtension();
                $imageRealPath = $image->getRealPath();
                // Set Parent (Asset Folder)
                $parent = Asset::getByPath("/Diary");

                // Creating and saving new asset
                $newAsset = new Asset();
                $newAsset->setFilename($imageName);
                $newAsset->setData(file_get_contents($imageRealPath));
                $newAsset->setParent($parent);
                $newAsset->save();

                // Get image
                $image = Asset\Image::getByPath($parent.'/'.$imageName);
            }

            // Set Diarys Parent Folder
            $path = '/Diarys' . '/' . $email;
            $diaryParentFolder = DataObject\Service::createFolderByPath($path);
            // Get Diarys Parent Id
            $dairyParentId = $diaryParentFolder->getId();

            // Register new note and save it to Diary DataObject
            $newDiary = new DataObject\Diary();
            $newDiary->setValues([
                'o_parentId' => $dairyParentId,
                'o_key' => ($email . time()),
                'o_published' => true,
                'noteType' => $noteType,
                'emoji' => $emoji,
                'dateTime' => $dateTime,
                'image' => $image,
                'text' => $text,
                'member' => [$member]
            ]);
            $newDiary->save();

            // Set response
            return $this->redirectToRoute('member-diary');
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
     * Get Diary List
     *
     * @param Object $member
     *
     * @return  Object|null
     */
    private function getDiaryList($member)
    {
        $memberId = $member->getId();

        $diaryList = new DataObject\Diary\Listing();
        $diaryList->setCondition("member LIKE '%," . $memberId . ",%'");
        $diaryList->setOrderKey("o_modificationDate");
        $diaryList->setOrder("desc");

        return $diaryList->load() ?? null;
    }









}
