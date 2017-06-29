<?php

namespace CubeTools\CubeCommonBundle\Filter;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * For easier filtering
 */
class FilterQueryCondition implements \ArrayAccess, \Countable
{
    /**
     *
     * @var array  of form elements to use for filtering
     */
    private $filter = array();

    /**
     * @var QueryBuilder
     */
    private $qb;

    /**
     * @param array $filter  array of form elements (returned by $mainForm->getData())
     */
    public function __construct(array $filter = array())
    {
        $this->filter = $filter;
    }

    /**
     * when called: isset($fqd['x'])
     *
     * @param string $name name of the element
     *
     * @return bool
     */
    public function offsetExists($name)
    {
        return isset($this->filter[$name]);
    }

    /**
     * when called: $fqd['x'] = y
     *
     * @param string $name  name of the element
     * @param any    $value value to set
     *
     * @return any   the value, for chaining
     */
    public function offsetSet($name, $value)
    {
        $this->filter[$name] = $value;

        return $value;
    }

    /**
     * when called: z = $fqd['x']
     *
     * @param string $name  name of the element
     *
     * @return any   the value
     */
    public function offsetGet($name)
    {
        if (isset($this->filter[$name])) {
            return $this->filter[$name];
        }

        // for when called like isset($fqc['x']['y']) and element x does not exist
        return null;
    }

    /**
     * when called: unset($fqd['x'])
     *
     * @param string $name  name of the element
     */
    public function offsetUnset($name)
    {
        unset($this->filter[$name]);
    }

    /**
     * when called: count($fqd['x'])
     *
     * @return int number of elements
     */
    public function count()
    {
        return count($this->filter);
    }

    /**
     * Get set filter elements as parameters for Doctrine Query::setParameters().
     *
     * @param array $skip names of elements to skip
     *
     * @return array
     */
    public function getAsParameters(array $skip = array())
    {
        $filter = array_filter($this->filter, 'count');
        if ($skip) {
            $filter = array_diff_key($filter, array_fill_keys($skip, null));
        }

        return $filter;
    }

    /**
     * Checks if the filter element is active.
     *
     * @param string name filter element name
     *
     * @return boolean    true when the filter elemment is active
     */
    public function isActive($name)
    {
        return isset($this->filter[$name]) && '' !== $this->filter[$name] && count($this->filter[$name]);
    }

    /**
     * Checks if any filter is active.
     *
     * @return boolen true when any filter active
     */
    public function anyActive()
    {
        return !empty($this->filter);
    }

    /**
     * sets the query builder for creating filter queries later
     *
     * @param QueryBuilder $qb
     */
    public function setQuerybuilder($qb)
    {
        $this->qb = $qb;

        return $this;
    }

    /**
     * Filters the data which is equal to the filter value.
     *
     * It filters in $dbColumn of $table for equality with the values set in filter[$flterName]
     *
     * @param string $table       name of database table
     * @param string $filterName  name of filter element
     * @param string $dbColumn    name of database column, defaults to $filterName
     *
     * @return $this
     */
    public function andWhereEqual($table, $filterName, $dbColumn = null)
    {
        if ($this->isActive($filterName)) {
            $value = $this->filter[$filterName];
            $dbColName = $this->getDbColumn($table, $filterName, $dbColumn);
            $param = $filterName;
            $this->qb->andWhere($dbColName.' = :'.$param)->setParameter($param, $value);
        }

        return $this;
    }

    public function andWhereLike($table, $filterName, $dbColumn = null)
    {
        if ($this->isActive($filterName)) {
            $value = $this->filter[$filterName];
            $dbColName = $this->getDbColumn($table, $filterName, $dbColumn);
            $param = $filterName;
            $this->qb->andWhere($dbColName.' LIKE :'.$param)->setParameter($param, $value);
        }

        return $this;
    }

    public function andWhereIn($table, $filterName, $dbColumn = null)
    {
        if ($this->isActive($filterName)) {
            $value = $this->filter[$filterName];
            if ($value instanceof ArrayCollection) {
                $value = $value->toArray(); // see #DDC-2319
            }
            $dbColName = $this->getDbColumn($table, $filterName, $dbColumn);
            $param = $filterName;
            $this->qb->andWhere($dbColName.' IN (:'.$param.')')->setParameter($param, $value);
        }

        return $this;
    }

    public function andWhereDaterange($table, $filterName, $dbColumn = null)
    {
        if ($this->isActive($filterName)) {
            $value = $this->filter[$filterName];
            $dbColName = $this->getDbColumn($table, $filterName, $dbColumn);
            $param = $filterName;
            if ($value['from']) {
                $this->qb->andWhere($dbColName.' >= :'.$param.'From')->setParameter($param.'From', $value['from']);
            }
            if ($value['to']) {
                $this->qb->andWhere($dbColName.' < DATE_ADD(:'.$param."To, 1, 'DAY')")->setParameter($param.'To', $value['to']);
            }
        }

        return $this;
    }

    public function andWhereIsSetIsNotSet($table, $filterName, $dbColumn = null)
    {
        if ($this->isActive($filterName)) {
            $value = $this->filter[$filterName];
            $dbColName = $this->getDbColumn($table, $filterName, $dbColumn);
            if (FilterConstants::SOME_ASSIGNED == $value) {
                $this->qb->andWhere($dbColName.' IS NOT NULL');
            } else { // FilterConstants::NONE_ASSIGNED === $value
                $this->qb->andWhere($dbColName.' IS NULL');
            }
        }
    }

    public function andWhereCheckedValue($table, $filterName, $dbColumn = null)
    {
        if ($this->isActive($filterName)) {
            $value = $this->filter[$filterName];
            $dbColName = $this->getDbColumn($table, $filterName, $dbColumn);
            if ($value) {
                $this->qb->andWhere($dbColName.' <> 0');
            } else {
                $this->qb->andWhere($dbColName.' = 0 OR '.$dbColName.' IS NULL');
            }
        }
    }

    private function getDbColumn($table, $filterName, $dbColumn)
    {
        if (null === $dbColumn) {
            $dbColumn = $filterName;
        }

        return ltrim($table.'.'.$dbColumn, '.');
    }
}
