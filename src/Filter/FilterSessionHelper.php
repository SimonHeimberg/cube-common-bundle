<?php

namespace CubeTools\CubeCommonBundle\Filter;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Used by FilterService.
 */
class FilterSessionHelper
{
    /**
     * Load filter from session.
     *
     * @param SessionInterface $session
     * @param string           $pageName
     *
     * @return array of string
     */
    public static function getFilterDataFromSession(SessionInterface $session, $pageName)
    {
        $filter = $session->get($pageName.'_filter');

        return $filter;
    }

    /**
     * Save filter to session.
     *
     * @param SessionInterface $session
     * @param string           $pageName
     * @param array            $filter
     */
    public static function setFilterDataToSession(SessionInterface $session, $pageName, array $filter)
    {
        $hashCtx = hash_init('md5');
        foreach ($filter as $n => $f) {
            if (is_subclass_of($f, FormInterface::class)) {
                $f = $f->getViewData();
                $filter[$n] = $f;
            }
            hash_update($hashCtx, '!'); // hash depends on field position
            hash_update($hashCtx, is_array($f) ? implode(';', $f) : $f);
        }
        $hash = hash_final($hashCtx);
        if ($session->get($pageName.'_filter_Hash') !== $hash) {
            //filter has changed
            $session->remove($pageName.'_page');
            $session->set($pageName.'_filter', $filter);
            $session->set($pageName.'_filter_Hash', $hash);
        }
    }

    public function saveFilterData(Request $request, FormInterface $form, array $data, $pageName)
    {
        $form->submit($data);
        if ($form->isValid()) {
            static::setFilterDataToSession($request->getSession(), $pageName, $data);

            return true;
        }

        return false;
    }

    /**
     *  Get filter data from request or session.
     *
     * @param Request       $request
     * @param FormInterface $form     filter form generated from this class
     * @param string|null   $pageName for storing filter and page no in session
     *
     * @return array with redirect (URL or null), filter (FilterQueryCondition) and page nr (int)
     */
    public static function getFilterData(Request $request, FormInterface $form, $pageName = null)
    {
        $session = $request->getSession();
        if ('1' == $request->query->get('filter_reset')) {
            static::setFilterDataToSession($session, $pageName, array());

            return array('redirect' => $request->getBaseUrl().$request->getPathInfo());
        }
        $form->handleRequest($request);
        if ($form->isValid()) {
            // use all() because getViewData() does not work as expected
            static::setFilterDataToSession($session, $pageName, $form->all());
            if ($request->getMethod() !== 'GET') {
                return array('redirect' => $request->getBaseUrl().$request->getPathInfo());
                    // do not use getRequestUri because includes the query parameter (?page=3)
            }
            $filter = $form->getData();
        } else {
            $data = static::getFilterDataFromSession($session, $pageName);
            if ($data && $form->isSubmitted()) {
                $formClass = get_class($form);
                $tmpForm = new $formClass($form->getConfig()); // to get the filter data without changing the form data
                $tmpForm->submit($data);
                $filter = $tmpForm->getData();
            } elseif ($data) {
                $form->submit($data);
                $filter = $form->getData();
            } else {
                $filter = array();
            }
        }

        $fData = static::prepareFilterData($request, $pageName, $form->getConfig()->getOptions());
        $fData['filter'] = new FilterQueryCondition($filter);

        return $fData;
    }

    public static function readFilterData(FormInterface $form)
    {
        if (!$form->isValid()) {
            throw new \LogicException('form to read is invalid');
        }
        $data = array();
        foreach ($form as $n => $f) {
            $data[$n] = $f->getViewData();
        }

        return $data;
    }

    private static function prepareFilterData(Request $request, $pageName, array $options)
    {
        $session = $request->getSession();

        $page = $request->query->get('page', false); // set to false if none set
        if (!$page) {
            $page = $session->get($pageName.'_page', 1); // if not set in session, set first page
        }
        $session->set($pageName.'_page', $page);

        $sortField = $request->query->get('sort', false);
        if (!$sortField) {
            if (false === $sortField) {
                $sort = $session->get($pageName.'_sort', false);
            } else {
                $sort = false;
            }
            if (false === $sort && !empty($options['defaultSort'])) {
                $dSort = $options['defaultSort'];
                $sortDirArr = array_intersect($dSort, array('asc', 'desc'));
                if ($sortDirArr) {
                    $dSort = array_values(array_diff($dSort, array('asc', 'desc')));
                    $sortDir = reset($sortDirArr); // first element independent of index no
                } else {
                    $sortDir = 'asc';
                }
                $sort = array('defaultSortFieldName' => $dSort, 'defaultSortDirection' => $sortDir);
            } elseif (false === $sort) {
                $sort = array();
            }
        } else {
            SortingHelper::validateSortField($sortField);
            $sortDir = $request->query->get('direction', 'asc');
            $sort = array('defaultSortFieldName' => $sortField, 'defaultSortDirection' => $sortDir);
            $session->set($pageName.'_sort', $sort);
        }

        return array('filter' => null, 'page' => $page, 'redirect' => null, 'options' => $sort);
    }
}
