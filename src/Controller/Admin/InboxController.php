<?php

namespace App\Controller\Admin;

use App\Util\SiteUtil;
use App\Manager\AuthManager;
use App\Manager\MessagesManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Class InboxController
 *
 * Inbox controller provides contact form message reader/ban/close messages
 *
 * @package App\Controller\Admin
 */
class InboxController extends AbstractController
{
    private SiteUtil $siteUtil;
    private AuthManager $authManager;
    private MessagesManager $messagesManager;

    public function __construct(
        SiteUtil $siteUtil,
        AuthManager $authManager,
        MessagesManager $messagesManager
    ) {
        $this->siteUtil = $siteUtil;
        $this->authManager = $authManager;
        $this->messagesManager = $messagesManager;
    }

    /**
     * Display inbox messages
     *
     * @param Request $request object representing the HTTP request
     *
     * @return Response object representing the HTTP response
     */
    #[Route('/admin/inbox', methods: ['GET'], name: 'admin_inbox')]
    public function inbox(Request $request): Response
    {
        // get page from query string
        $page = intval($this->siteUtil->getQueryString('page', $request));

        // get messages data
        $messages = $this->messagesManager->getMessages('open', $page);

        // render inbox view
        return $this->render('admin/inbox.twig', [
            // user data
            'userName' => $this->authManager->getUsername(),
            'userRole' => $this->authManager->getUserRole(),
            'userPic' => $this->authManager->getUserProfilePic(),

            // inbox data
            'page' => $page,
            'inboxData' => $messages,
            'messageCount' => count($messages),
            'messageLimit' => $_ENV['ITEMS_PER_PAGE']
        ]);
    }

    /**
     * Close a message in the inbox
     *
     * @param Request $request object representing the HTTP request
     *
     * @return Response object representing the HTTP response
     */
    #[Route('/admin/inbox/close', methods: ['GET'], name: 'admin_inbox_close')]
    public function close(Request $request): Response
    {
        // get query parameters
        $page = intval($this->siteUtil->getQueryString('page', $request));
        $id = intval($this->siteUtil->getQueryString('id', $request));

        // close message
        $this->messagesManager->closeMessage($id);

        // get messages count
        $messagesCount = count($this->messagesManager->getMessages('open', 1));

        // check if messages count is 0
        if ($messagesCount == 0) {
            return $this->redirectToRoute('admin_dashboard');
        }

        // redirect back to inbox
        return $this->redirectToRoute('admin_inbox', [
            'page' => $page
        ]);
    }
}
