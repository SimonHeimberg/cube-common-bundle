<?php

namespace CubeTools\CubeCommonBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
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

        $reqInfo = $this->requestHandling($request);
        
        $link = $this->generateBugLink($githubProjectUrl, $version, $verHash, $reqInfo['profilerToken'], $reqInfo['reallyRelated'], $reqInfo['userAgent']);
        $args = array(
            'redirect' => $reqInfo['redirectDelay'],
            'baseUrl' => $reqInfo['baseUrl'],
            'relatedUrl' => $reqInfo['relatedUrl'],
            'projectName' => basename($githubProjectUrl),
            'profilerToken' => $reqInfo['profilerToken'],
            'directLink' => $link,
        );
        $response = $this->render('CubeToolsCubeCommonBundle:DevelopmentSupport:reportBug.html.twig', $args);
        if (null !== $reqInfo['redirectDelay']) {
            $response->headers->set('refresh', $reqInfo['redirectDelay'].'; url='.$link);
        }

        return $response;
    }

    private function requestHandling(Request $request)
    {
        $relatedUrl = $request->query->get('relatedUrl', $this);
        $profilerToken = $request->query->get('profiler');
        $userAgent = $request->query->get('userAgent');
        // $module = guess module from prev url?

        $baseUrl = $request->getHttpHost().$request->getBaseUrl();
        if ($this === $relatedUrl) {
            $relatedUrl = $request->headers->get('referer');
        }
        $urlOffset = strpos($relatedUrl, $baseUrl);
        if (false !== $urlOffset) {
            $redirectDelay = 2;
            $reallyRelated = $relatedUrl;
        } else {
            $redirectDelay = null;
            $reallyRelated = null;
        }

        return array(
            'relatedUrl' => $relatedUrl,
            'profilerToken' => $profilerToken,
            'userAgent' => $userAgent,
            'baseUrl' => $baseUrl,
            'redirectDelay' => $redirectDelay,
            'reallyRelated' => $reallyRelated,
        );
    }

    private function generateBugLink($githubProjectUrl, $version, $verHash, $profiler, $relatedUrl, $userAgent = null, $module = 'XXmoduleXX')
    {
        if (!$relatedUrl) {
            $relatedUrl = 'XXXurlXXX';
        }
        if (!$userAgent) {
            $userAgent = 'XXbrowserXX';
        }
        $msgBody = "\n\n<hr/>\n\nversion = ".$version.'  '.substr($verHash, 0, 8)."\nurl = ".$relatedUrl."\nbrowser = ".$userAgent;
        if ($profiler) {
            $profilerUrl = $this->generateUrl(
                '_profiler',
                array('token' => $profiler),
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            $msgBody .= "\nprofiler: [".$profiler.']('.$profilerUrl.')';
        }

        return $githubProjectUrl.'/issues/new?HINT= SIGN IN! &title='.urlencode('['.$module.']').
                '&body='.urlencode($msgBody);
    }
}
