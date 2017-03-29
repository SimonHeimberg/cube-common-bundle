<?php

namespace CubeTools\CubeCommonBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use CubeTools\CubeCommonBundle\DataHandling\DataConversion;

class ColsSelectorController extends Controller
{
    private static $colsButtons = array();

    /**
     * Renders the snippet to include for a ColsSelector button.
     */
    public function nearButtonAction($path, $btnId = '')
    {
        if (isset(static::$colsButtons[$btnId])) {
            ++static::$colsButtons[$btnId];
            $reply = $this->render('CubeToolsCubeCommonBundle:ColsSelector:nearButtonDuplicateId.inc.html.twig', array(
                'btnId' => $btnId,
            ));
        } else {
            static::$colsButtons[$btnId] = 1;
            $reply = new Response('');
        }

        return $reply;
    }

    public function getTablesSettingsAction($path = '')
    {
        $settings = array();
        $errors = array();
        foreach (static::$colsButtons as $btnId => $nr) {
            $saveId = $this->getId($path, $btnId);
            $settings[$btnId] = array('settings' => $this->getColsSettings($saveId));
            if ($nr > 1) {
                $errors[$btnId] = $nr;
            }
        }
        if ($errors) {
            $settings['ERRORS'] = array('msg' => 'id used for several tables:', 'tables' => $errors);
        }

        return new JsonResponse($settings);
    }

    /**
     * Saves column settings.
     *
     * @Route("/colssettings", name="cubecommon.colsselector_send")
     * @Method("PUT")
     */
    public function saveSettingsAction(Request $request)
    {
        $request->setRequestFormat('json');
        $id = $request->request->get('id');
        $fullPath = $request->request->get('fullPath');
        if (!$fullPath) {
            throw new BadRequestHttpException('request fullPath is not set');
        }
        $settings = $request->request->get('settings');

        $path = substr($fullPath, strlen($request->getBaseUrl()));
        $saveId = $this->getId($path, $id);
        $this->saveColsSettings($saveId, $settings);
        $savedSettings = $this->getColsSettings($saveId);

        return new JsonResponse(array('settings' => $savedSettings, 'id' => $id));
    }

    protected function getColsSettings($saveId)
    {
        try {
            return $this->get('app.columnSettingsLoadSave')->getColSettings($saveId);
        } catch (ServiceNotFoundException $se) {
            $msg = 'ERROR: missing service; '.$se->getMessage();
            if (function_exists('dump')) {
                $log = 'dump';
                $log($msg);
            }

            return array($msg);
        }
    }

    protected function saveColsSettings($saveId, array $settings)
    {
        DataConversion::dataTextToDataInArray($settings);

        // error is handled by ajax caller
        return $this->get('app.columnSettingsLoadSave')->setColSettings($saveId, $settings);
    }

    /**
     * Create the saveId for the given ids.
     *
     * @param string $path relative page path
     * @param string $id   id of table (button) on the page
     *
     * @return string
     */
    protected function getId($path, $id)
    {
        if ($id && '~' === $id[0]) {
            return substr($id, 1);
        }

        return $path.'~'.$id;
    }
}
