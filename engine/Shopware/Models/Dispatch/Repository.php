<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace   Shopware\Models\Dispatch;

use Doctrine\ORM\Query\Expr\Join;
use Shopware\Components\Model\ModelRepository;
use Shopware\Components\Model\QueryBuilder;
use Shopware\Models\Customer;

/**
 * Repository for the customer model (Shopware\Models\Dispatch\Dispatch).
 * <br>
 * The dispatch models accumulates all data needed for a specific dispatch service
 */
class Repository extends ModelRepository
{
    /**
     * @param $filter
     * @param $order
     * @param $offset
     * @param $limit
     *
     * @return \Doctrine\ORM\Query
     */
    public function getDispatchesQuery($filter = null, $order = null, $offset = null, $limit = null)
    {
        $builder = $this->getDispatchesQueryBuilder($filter, $order);
        if ($offset !== null && $limit !== null) {
            $builder->setFirstResult($offset)
                    ->setMaxResults($limit);
        }

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getDispatchesQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param null $filter
     * @param null $order
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getDispatchesQueryBuilder($filter = null, $order = null)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select('dispatches');
        $builder->setAlias('dispatches');
        $builder->from('Shopware\Models\Dispatch\Dispatch', 'dispatches');
        $builder->setAlias('dispatches');

        if (!empty($filter)) {
            $builder->addFilter($filter);
        }
        if (!empty($order)) {
            $builder->addOrderBy($order);
        }

        return $builder;
    }

    /**
     * Returns all info about known shipping and dispatch settings
     *
     * @param $dispatchId - If this parameter is given, only one data set will be returned
     * @param null  $filter - Used to search in the name and description of the dispatch data set
     * @param array $order  - Name of the field which should considered as sorting field
     * @param null  $limit  - Reduce the number of returned data sets
     * @param null  $offset - Start the output based on that offset
     *
     * @return \Doctrine\ORM\Query
     */
    public function getShippingCostsQuery($dispatchId = null, $filter = null, $order = [], $limit = null, $offset = null)
    {
        $builder = $this->getShippingCostsQueryBuilder($dispatchId, $filter, $order);
        if (!empty($offset)) {
            $builder->setFirstResult($offset);
        }
        if (!empty($limit)) {
            $builder->setMaxResults($limit);
        }

        return $builder->getQuery();
    }

    /**
     * Returns basic info about known shipping and dispatch settings
     *
     * @param null  $filter - Used to search in the name and description of the dispatch data set
     * @param array $order  - Name of the field which should considered as sorting field
     * @param null  $limit  - Reduce the number of returned data sets
     * @param null  $offset - Start the output based on that offset
     *
     * @return \Doctrine\ORM\Query
     */
    public function getListQuery($filter = null, $order = [], $limit = null, $offset = null)
    {
        $builder = $this->getListQueryBuilder($filter, $order);
        if (!empty($offset)) {
            $builder->setFirstResult($offset);
        }
        if (!empty($limit)) {
            $builder->setMaxResults($limit);
        }

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getShippingCostsQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param $dispatchId - If this parameter is given, only one data set will be returned
     * @param null  $filter - Used to search in the name and description of the dispatch data set
     * @param array $order  - Name of the field which should considered as sorting field
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getShippingCostsQueryBuilder($dispatchId = null, $filter = null, $order = [])
    {
        $builder = $this->createQueryBuilder('dispatch');
        $expr = $this->getEntityManager()->getExpressionBuilder();

        // Build the query
        $builder->select(['dispatch', 'countries', 'categories', 'holidays', 'payments', 'attribute'])
                ->leftJoin('dispatch.countries', 'countries')
                ->leftJoin('dispatch.categories', 'categories')
                ->leftJoin('dispatch.holidays', 'holidays')
                ->leftJoin('dispatch.attribute', 'attribute')
                ->leftJoin('dispatch.payments', 'payments');
        if (null !== $dispatchId) {
            $builder->where($expr->eq('dispatch.id', '?2'))
                    ->setParameter(2, $dispatchId);
        }

        // Set the filtering logic
        if (null !== $filter) {
            $builder->andWhere(
                $expr->orX(
                    $expr->like('dispatch.name', '?1'),
                    $expr->like('dispatch.description', '?1')
                )
            );
            $builder->setParameter(1, '%' . $filter . '%');
        }

        // Set the order logic
        $this->addOrderBy($builder, $order);

        return $builder;
    }

    /**
     * Helper function to create the query builder for the "getShippingCostsQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param null  $filter - Used to search in the name and description of the dispatch data set
     * @param array $order  - Name of the field which should considered as sorting field
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getListQueryBuilder($filter = null, $order = [])
    {
        $builder = $this->createQueryBuilder('dispatch');
        $expr = $this->getEntityManager()->getExpressionBuilder();

        // Build the query
        $builder->select(['dispatch']);

        // Set the filtering logic
        if (null !== $filter) {
            $builder->andWhere(
                $expr->orX(
                    $expr->like('dispatch.name', '?1'),
                    $expr->like('dispatch.description', '?1')
                )
            );
            $builder->setParameter(1, '%' . $filter . '%');
        }

        // Set the order logic
        $this->addOrderBy($builder, $order);

        return $builder;
    }

    /**
     * Get the shipping costs for a dispatch setting.
     *
     * @param int         $dispatchId Unique id
     * @param null|string $filter     string which is filtered
     * @param int|null    $limit      Count of the selected data
     * @param int|null    $offset     Start index of the selected data
     *
     * @return \Doctrine\ORM\Query
     */
    public function getShippingCostsMatrixQuery($dispatchId = null, $filter = null, $limit = null, $offset = null)
    {
        $builder = $this->getShippingCostsMatrixQueryBuilder($dispatchId, $filter);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getShippingCostsMatrixQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param $dispatchId - If this parameter is given, only one data set will be returned
     * @param null  $filter - Used to search in the name and description of the dispatch data set
     * @param array $order  - Name of the field which should considered as sorting field
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getShippingCostsMatrixQueryBuilder($dispatchId = null, $filter = null, $limit = null)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $expr = $this->getEntityManager()->getExpressionBuilder();
        $builder->from('Shopware\Models\Dispatch\ShippingCost', 'shippingcosts')->select(['shippingcosts']);

        // assure that we will get an empty result set when no dispatch ID is provided
        if (is_null($dispatchId) || empty($dispatchId)) {
            $dispatchId = '-1';
        }
        $builder->where($expr->eq('shippingcosts.dispatchId', $dispatchId));
        // we need a hard coded sorting here.
        $builder->orderBy('shippingcosts.from');

        return $builder;
    }

    /**
     * Purges all entries for a given dispatch ID.
     *
     * @param int $dispatchId
     *
     * @return \Doctrine\ORM\AbstractQuery
     */
    public function getPurgeShippingCostsMatrixQuery($dispatchId = null)
    {
        return $this->getEntityManager()
            ->createQuery('delete from Shopware\Models\Dispatch\ShippingCost cm where cm.dispatchId = ?1')
            ->setParameter(1, $dispatchId);
    }

    /**
     * Receives all known means of payment, even disabled ones
     *
     * @param null $filter
     * @param null $order
     * @param null $limit
     * @param null $offset
     *
     * @return \Doctrine\ORM\Query
     */
    public function getPaymentQuery($filter = null, $order = null, $limit = null, $offset = null)
    {
        // get the query and prepare the limit statement
        $builder = $this->getPaymentQueryBuilder($filter, $order);

        if ($offset !== null && $limit !== null) {
            $builder->setFirstResult($offset)
                  ->setMaxResults($limit);
        }

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getPaymentQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param null $filter
     * @param null $order
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getPaymentQueryBuilder($filter = null, $order = null)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $expr = $this->getEntityManager()->getExpressionBuilder();

        $filters = [];
        if (null !== $filter && !empty($filter)) {
            foreach ($filter as $singleFilter) {
                $filters[$singleFilter['property']] = $singleFilter['value'];
            }
        }
        // Build the query
        $builder->from('Shopware\Models\Payment\Payment', 'payment')
                ->select(['payment']);
        // Set the order logic
        $builder = $this->sortOrderQuery($builder, 'payment', $order);
        // use the filter
        if (!empty($filters['usedIds'])) {
            $builder->add('where', $expr->notIn('payment.id', $filters['usedIds']));
        }

        return $builder;
    }

    /**
     * Receives all known countries, even disabled ones
     *
     * @param null $filter
     * @param null $order
     * @param null $limit
     * @param null $offset
     *
     * @return \Doctrine\ORM\Query
     */
    public function getCountryQuery($filter = null, $order = null, $limit = null, $offset = null)
    {
        // get the query and prepare the limit statement
        $builder = $this->getCountryQueryBuilder($filter, $order);

        if ($offset !== null && $limit !== null) {
            $builder->setFirstResult($offset)
                    ->setMaxResults($limit);
        }

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getCountryQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param null $filter
     * @param null $order
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getCountryQueryBuilder($filter = null, $order = null)
    {
        $filters = [];
        if (null !== $filter && !empty($filter)) {
            foreach ($filter as $singleFilter) {
                $filters[$singleFilter['property']] = $singleFilter['value'];
            }
        }
        $builder = $this->getEntityManager()->createQueryBuilder();
        $expr = $this->getEntityManager()->getExpressionBuilder();

        // Build the query
        $builder->from('Shopware\Models\Country\Country', 'country')
                ->select(['country']);

        // Set the order logic
        $builder = $this->sortOrderQuery($builder, 'country', $order);

        // use the filter
        if (!empty($filters['usedIds'])) {
            $builder->add('where', $expr->notIn('country.id', $filters['usedIds']));
        }
        if (!empty($filters['onlyIds'])) {
            $builder->add('where', $expr->in('country.id', $filters['onlyIds']));
        }

        return $builder;
    }

    /**
     * Receives all known countries, even disabled ones
     *
     * @param null $filter
     * @param null $order
     * @param null $limit
     * @param null $offset
     *
     * @return \Doctrine\ORM\Query
     */
    public function getHolidayQuery($filter = null, $order = null, $limit = null, $offset = null)
    {
        // get the query and prepare the limit statement
        $builder = $this->getHolidayQueryBuilder($filter, $order);
        if ($offset !== null && $limit !== null) {
            $builder->setFirstResult($offset)
                   ->setMaxResults($limit);
        }

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getHolidayQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param null $filter
     * @param null $order
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getHolidayQueryBuilder($filter = null, $order = null)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $expr = $this->getEntityManager()->getExpressionBuilder();

        // Build the query
        $builder->from('Shopware\Models\Dispatch\Holiday', 'holiday')
                ->select(['holiday']);

        // Set the order logic
        $builder = $this->sortOrderQuery($builder, 'holiday', $order);
        // use the filter
        if (!empty($filters['usedIds'])) {
            $builder->add('where', $expr->notIn('country.id', $filter['usedIds']));
        }

        return $builder;
    }

    /**
     * Selects all shipping costs with a deleted shop
     *
     * @return \Doctrine\ORM\Query
     */
    public function getDispatchWithDeletedShopsQuery()
    {
        $builder = $this->getEntityManager()->createQueryBuilder();

        $builder->select('dispatch')
            ->from('Shopware\Models\Dispatch\Dispatch', 'dispatch')
            ->leftJoin('Shopware\Models\Shop\Shop', 'shop', Join::WITH, 'dispatch.multiShopId = shop.id')
            ->andWhere('dispatch.multiShopId IS NOT NULL')
            ->andWhere('shop.id IS NULL');

        return $builder->getQuery();
    }

    /**
     * Helper function which set the orderBy path for the order list query.
     *
     * @param \Doctrine\ORM\QueryBuilder $builder
     * @param $modelPrefix
     * @param $orderBy
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function sortOrderQuery(\Doctrine\ORM\QueryBuilder $builder, $modelPrefix, $orderBy)
    {
        //order the query with the passed orderBy parameter
        if (!empty($orderBy)) {
            foreach ($orderBy as $order) {
                if (!isset($order['direction'])) {
                    $order['direction'] = 'ASC';
                }
                if (isset($order['property'])) {
                    $builder->addOrderBy($modelPrefix . '.' . $order['property'], $order['direction']);
                }
            }
        }

        return $builder;
    }
}
