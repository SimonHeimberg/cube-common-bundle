<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace CubeTools\CubeCommonBundle\Filter;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class SortingHelper
{
    /**
     * Throws an error when the sort field name is obviously invalid.
     *
     * @param string $name
     *
     * @throws BadRequestHttpException
     */
    public static function validateSortField($name)
    {
        if (false !== strpos($name, ' ')) {
            // prevents sql injection
            throw new BadRequestHttpException('Illegal sort field: '.$name);
        }
    }

    /**
     * Returns a valid sort direction.
     *
     * @param string $dir sort direction
     *
     * @return string asc or desc
     */
    public static function getValidSortDir($dir)
    {
        if (!in_array(strtolower($dir), array('asc', 'desc'))) { // illegal
            $dir = 'ASC';
        }

        return $dir;
    }
}
