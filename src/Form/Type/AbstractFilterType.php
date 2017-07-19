<?php

namespace CubeTools\CubeCommonBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use CubeTools\CubeCommonBundle\Filter\FilterConstants;

class AbstractFilterType extends AbstractType
{
    const WHERE_IS_SET = FilterConstants::WHERE_IS_SET;
    const WHERE_IS_NOT_SET = FilterConstants::WHERE_IS_NOT_SET;

    /** set css class on active filter elements
     *
     * {@inheritDoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        foreach ($form as $child) {
            $cData = $child->getData();
            if ('' !== $cData && count($cData)) { // count returns 0 on empty Array(Collection) and on null
                $cView = $view->children[$child->getName()];
                $a = $cView->vars['attr'];
                $c = isset($a['class']) ? $a['class'].' ' : '';
                if (false === strpos($c, 'notFilterElement')) {
                    // element has data and has filtering functionality
                    $a['class'] = $c.'activeFilter'; // append css class
                    $cView->vars['attr'] = $a;
                }
            }
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'csrf_protection'   => false,
                'validation_groups' => array('filtering'), // avoid NotBlank() constraint-related message
                'data' => array(),
                'defaultSort' => array(),
            )
        );
    }
}
