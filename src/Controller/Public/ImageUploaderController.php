<?php

namespace App\Controller\Public;

use App\Entity\Image;
use App\Util\SiteUtil;
use App\Util\SecurityUtil;
use App\Service\Manager\LogManager;
use App\Service\Manager\ErrorManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\String\ByteString;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Class ImageUploaderController
 * 
 * Image uploader/view controller provides image upload/view component
 * Page for storing images in database and sharing via url
 * 
 * @package App\Controller\Public
*/
class ImageUploaderController extends AbstractController
{
    /**
     * @var SiteUtil
     * Instance of the SiteUtil for handling site-related utilities.
     */
    private SiteUtil $siteUtil;

    /**
     * @var LogManager
     * Instance of the LogManager for handling log-related functionality.
     */
    private LogManager $logManager;

    /**
     * @var ErrorManager
     * Instance of the ErrorManager for handling error-related functionality.
     */
    private ErrorManager $errorManager;

    /**
     * @var SecurityUtil
     * Instance of the SecurityUtil for handling security-related utilities.
     */
    private SecurityUtil $securityUtil;

    /**
     * @var EntityManagerInterface
     * Instance of the EntityManagerInterface for interacting with the database.
     */
    private EntityManagerInterface $entityManager;

    /**
     * ImageUploaderController constructor.
     *
     * @param SiteUtil               $siteUtil
     * @param LogManager             $logManager
     * @param ErrorManager           $errorManager
     * @param SecurityUtil           $securityUtil
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        SiteUtil $siteUtil,
        LogManager $logManager,
        ErrorManager $errorManager,
        SecurityUtil $securityUtil, 
        EntityManagerInterface $entityManager
    ) {
        $this->siteUtil = $siteUtil;
        $this->logManager = $logManager;
        $this->errorManager = $errorManager;
        $this->securityUtil = $securityUtil;
        $this->entityManager = $entityManager;
    }

    /**
     * Displays the image view page.
     *
     * @param Request $request The HTTP request.
     * @return Response The response containing the rendered image view page.
     */
    #[Route('/image/view', methods: ['GET'], name: 'public_image_viewer')]
    public function imageView(Request $request): Response
    {
        // get image token
        $token = $this->siteUtil->getQueryString('token', $request);
    
        // escape image token
        $token = $this->securityUtil->escapeString($token);
    
        // get image data
        $imageRepo = $this->entityManager->getRepository(Image::class)->findOneBy(['token' => $token]);
    
        // check if image found
        if ($imageRepo !== null) {
    
            // get image & decrypt
            $image_content = $this->securityUtil->decryptAes($imageRepo->getImage());
    
            // check if image is decrypted
            if ($image_content == null) {
                $this->errorManager->handleError('Error to decrypt aes image', 500);
            }
    
            // set content type header to image/jpeg
            $headers = [
                'Content-Type' => 'image/jpeg',
            ];
    
            // create a streamed response with image content
            $response = new StreamedResponse(function () use ($image_content) {
                echo base64_decode($image_content);
            }, Response::HTTP_OK, $headers);
    
            return $response;
    
        } else {
            return $this->errorManager->handleError('not found error, image: '.$token.', not found in database', 404);
        }
    }

    /**
     * Image upload controller.
     * 
     * @return Response Returns a Response object representing the HTTP response.
     *
     * @throws \Exception Throws an exception if there is an error during the image upload process.
     */
    #[Route('/image/uploader', methods: ['GET', 'POST'], name: 'public_image_uploader')]
    public function uploadImage(): Response
    {
        $error_msg = null;

        // check if form is submited
        if (isset($_POST['submitUpload'])) { 
        
            // extract file extension
            $ext = substr(strrchr($_FILES['userfile']['name'], '.'), 1);      
            
            // check if file is image
            if ($ext == 'gif' or $ext == 'jpg' or $ext == 'jpeg' or $ext == 'png') {		
                
                // generate img_spec value
                $token = ByteString::fromRandom(32)->toByteString();
                
                // get image file
                $image_file = file_get_contents($_FILES['userfile']['tmp_name']);
    
                // encode file
                $image_file = base64_encode($image_file);

                // escape image string
                $image_file = $this->securityUtil->escapeString($image_file);
    
                // get current data
                $date = date('d.m.Y H:i:s');
    
                // init image entity
                $image = new Image();

                // encrypt image
                $image_file = $this->securityUtil->encryptAes($image_file);

                // set image data
                $image->setToken($token);
                $image->setImage($image_file);
                $image->setTime($date);

                // try to upload image
                try {
                    $this->entityManager->persist($image);
                    $this->entityManager->flush();
                } catch (\Exception $e) {
                    return $this->errorManager->handleError('error to upload image: '.$token.', '.$e->getMessage(), 400);
                }

                // log image upload
                $this->logManager->log('image-uploader', 'uploaded new image: '.$token);	

                // redirect to image view
                return $this->redirectToRoute('public_image_viewer', ['token' => $token]);

            } else {
                // handle error (translation key)
                $error_msg = 'image.uploader.file.format.error';
            }
        }

        return $this->render('public/image-uploader.html.twig', [
            'instagram_link' => $_ENV['INSTAGRAM_LINK'],
            'telegram_link' => $_ENV['TELEGRAM_LINK'],
            'contact_email' => $_ENV['CONTACT_EMAIL'],
            'twitter_link' => $_ENV['TWITTER_LINK'],
            'github_link' => $_ENV['GITHUB_LINK'],
            'error_msg' => $error_msg
        ]);
    }
}
