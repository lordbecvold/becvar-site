<?php

namespace App\Middleware;

use App\Manager\VisitorManager;
use Symfony\Contracts\Translation\LocaleAwareInterface;

/**
 * Class TranslationsMiddleware
 *
 * This middleware sets translations based on the visitor's language.
 */
class TranslationsMiddleware
{
    /** * @var VisitorManager */
    private VisitorManager $visitorManager;

    /** * @var LocaleAwareInterface */
    private LocaleAwareInterface $translator;
    
    /**
     * TranslationsMiddleware constructor.
     *
     * @param VisitorManager       $visitorManager
     * @param LocaleAwareInterface $translator
     */
    public function __construct(
        VisitorManager $visitorManager,
        LocaleAwareInterface $translator 
    ) {
        $this->translator = $translator;
        $this->visitorManager = $visitorManager;
    }

    /**
     * Set translations based on the visitor's language.
     */
    public function onKernelRequest(): void
    {
        // get visitor language
        $language = $this->visitorManager->getVisitorLanguage();

        // check unidentified languages
        if ($language == null or $language == 'host' or $language == 'unknown') {
            $this->translator->setLocale('en');
        } else {
            // set visitor locale
            $this->translator->setLocale($language);
        }
    }
}
