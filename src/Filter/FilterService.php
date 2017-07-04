<?php

namespace CubeTools\CubeCommonBundle\Filter;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Knp\Component\Pager\PaginatorInterface;
use CubeTools\CubeCommonBundle\Session\KeepOnSuccess;

/**
 * Service for convenient filtering.
 */
class FilterService
{
    /**
     * @var RequestStack
     */
    private $rStack;

    /**
     * @var PaginatorInterface
     */
    private $paginator;

    public function __construct(RequestStack $requestStack)
    {
        $this->rStack = $requestStack;
    }

    /**
     * Get filter data from request or session.
     *
     * @param FormInterface $form     filter form generated from this class
     * @param string|null   $pageName for storing filter and page no in session
     *
     * @return array with redirect (URL or null), filter (FilterQueryCondition) and page nr (int)
     */
    public function getFilterData(FormInterface $form, $pageName = null)
    {
        $this->updatePageNameFromForm($pageName, $form);
        $onSuccessKeepFn = array(KeepOnSuccess::class, 'markFor');
        $fData = FilterSessionHelper::getFilterData($this->rStack->getCurrentRequest(), $form, $pageName, $onSuccessKeepFn);

        return new FilterData($fData, $this->paginator);
    }

    /**
     * Save data into forms session.
     *
     * @param FormInterface $form
     * @param array         $data
     * @param type          $pageName
     *
     * @return bool false if form is invalid (and was not saved therefore)
     */
    public function saveFilterData(FormInterface $form, array $data, $pageName = null)
    {
        $this->updatePageNameFromForm($pageName, $form);
        $request = $this->rStack->getCurrentRequest();
        $onSuccessKeepFn = array(KeepOnSuccess::class, 'markFor');

        return FilterSessionHelper::saveFilterData($request, $form, $data, $pageName, $onSuccessKeepFn);
    }

    /**
     * Returns the saved filter data (from the session).
     *
     * @param type $formClassOrPageName name of page or class of form
     *
     * @return many[] current saved filter data (in view/session format)
     */
    public function loadFilterData($formClassOrPageName)
    {
        $session = $this->rStack->getCurrentRequest()->getSession();

        return FilterSessionHelper::getFilterDataFromSession($session, $formClassOrPageName);
    }

    /**
     * Read the data from the form (in view/session format).
     *
     * @param FormInterface $form
     *
     * @return many[]
     */
    public function readFilterData(FormInterface $form)
    {
        return FilterSessionHelper::readFilterData($form);
    }

    /**
     * Sets the optional paginator for this service.
     *
     * @param PaginatorInterface $paginator
     */
    public function setPaginator(PaginatorInterface $paginator)
    {
        $this->paginator = $paginator;
    }

    private function updatePageNameFromForm(&$pageName, FormInterface $form)
    {
        if (null === $pageName) {
            $pageName = get_class($form->getConfig()->getType()->getInnerType());
        }
    }
}
