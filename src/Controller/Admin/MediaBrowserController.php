<?php

namespace App\Controller\Admin;

use App\Util\SiteUtil;
use App\Manager\AuthManager;
use App\Manager\DatabaseManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Class MediaBrowserController
 *
 * Media browser controller provides an image-uploader browser.
 *
 * @package App\Controller\Admin
 */
class MediaBrowserController extends AbstractController
{
    private SiteUtil $siteUtil;
    private AuthManager $authManager;
    private DatabaseManager $databaseManager;

    public function __construct(
        SiteUtil $siteUtil,
        AuthManager $authManager,
        DatabaseManager $databaseManager
    ) {
        $this->siteUtil = $siteUtil;
        $this->authManager = $authManager;
        $this->databaseManager = $databaseManager;
    }

    /**
     * Display the media browser with image-uploader.
     *
     * @param Request $request object representing the HTTP request.
     *
     * @return Response object representing the HTTP response.
     */
    #[Route('/admin/media/browser', methods: ['GET'], name: 'admin_media_browser')]
    public function mediaBrowser(Request $request): Response
    {
        // get page
        $page = intval($this->siteUtil->getQueryString('page', $request));

        // get images data
        $media = $this->databaseManager->getImages($page);

        // render media browser view
        return $this->render('admin/media-browser.html.twig', [
            // user data
            'user_name' => $this->authManager->getUsername(),
            'user_role' => $this->authManager->getUserRole(),
            'user_pic' => $this->authManager->getUserProfilePic(),

            // media browser data
            'page' => $page,
            'media_data' => $media,
            'media_count' => count($media),
            'media_limit' => $_ENV['ITEMS_PER_PAGE']
        ]);
    }
}
