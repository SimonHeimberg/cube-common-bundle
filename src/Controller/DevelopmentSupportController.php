<?php

namespace CubeTools\CubeCommonBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * Help controller.
 *
 * @Route("_profiler/development")
 */
class DevelopmentSupportController extends Controller
{
    /**
     * Simplify reporting of a bug by prefilling the form.
     *
     * @Route("/reportbug", name="cube_common.reportbug")
     * @Method("GET")
     */
    public function reportBugAction(Request $request)
    {
        $projVer = $this->get('cube_common.project_version');
        $githubProjectUrl = $projVer->getGitRepoUrl();
        $version = $projVer->getVersionString();
        $verHash = $projVer->getGitHash();

        $relatedUrl = $request->query->get('relatedUrl', $this);
        $baseUrl = $request->getHttpHost().$request->getBaseUrl();
        if ($this === $relatedUrl) {
            $relatedUrl = $request->headers->get('referer');
        }
        $urlOffset = strpos($relatedUrl, $baseUrl);
        if (false === $urlOffset) {
            // not enough information
            return $this->render('CubeToolsCubeCommonBundle:DevelopmentSupport:reportBug.html.twig', array(
                'baseUrl' => $baseUrl,
                'relatedUrl' => $relatedUrl,
                'projectName' => basename($githubProjectUrl),
                'directLink' => $this->generateBugLink($githubProjectUrl, $version, $verHash),
            ));
        }

        // $module = guess module from prev url?
        $link = $this->generateBugLink($githubProjectUrl, $version, $verHash, $relatedUrl);

        return $this->redirect($link);
    }

    private function generateBugLink($githubProjectUrl, $version, $verHash, $relatedUrl = 'XXurlXX', $module = 'XXmoduleXX')
    {
        return $githubProjectUrl.'/issues/new?HINT= SIGN IN! &title='.urlencode('['.$module.']').'&body='.
            urlencode("\n\n<hr/>\n\nversion = ".$version.'  '.substr($verHash, 0, 8)."\nurl = ".$relatedUrl);
    }
}
