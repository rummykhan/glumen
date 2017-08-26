<?php

namespace Awok\Domains\Data\Traits;

use Awok\Foundation\Eloquent\Model;
use Awok\Foundation\Http\Request;
use Awok\Foundation\Http\RequestFieldCollection;
use Awok\Foundation\Http\RequestFilter;
use Awok\Foundation\Http\RequestFilterCollection;
use Awok\Foundation\Http\RequestRelationField;
use Awok\Foundation\Http\RequestRelationFieldCollection;
use Awok\Foundation\Http\RequestSort;
use Awok\Foundation\Http\RequestSortCollection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;

trait EloquentRequestQueryable
{
    protected $model;

    private $perPage;

    private $fields;

    private $filters;

    private $sorting;

    private $relations;

    private $relationInstance = false;

    private $relationName = null;

    public function setModel($model)
    {
        if (is_string($model)) {
            $model = new $model;
        }
        $this->model = $model;
    }

    public function getModel()
    {
        return $this->model;
    }

    public function getRelations()
    {
        return $this->relations;
    }

    protected function setRelations(RequestRelationFieldCollection $relations)
    {
        $this->relations = $relations;
    }

    public function isRelationInstance()
    {
        return $this->relationInstance;
    }

    public function getRelationName()
    {
        return $this->relationName;
    }

    public function captureRequestQuery(Request $request)
    {

        if (! $this->getModel()) {
            throw new \Exception('No model set to use for query');
        }

        $this->setFields($request->getFields());
        $this->setFilters($request->getFilters());
        $this->setRelations($request->getRelations());
        $this->setSorting($request->getSort());
        $this->setPerPage($request->getPerPage());

        return true;
    }

    /**
     * returns pagination result
     *
     * @param string       $dataKey
     * @param              $results
     *
     * @return mixed
     */
    public function paginateResult($results, $dataKey = 'data')
    {
        return $results->paginate($this->getPerPage(), ['*'], $pageName = 'page', $page = null, $dataKey);
    }

    public function getPerPage()
    {
        return $this->perPage;
    }

    protected function setPerPage(int $perPage = 25)
    {
        $this->perPage = $perPage;
    }

    protected function buildQuery()
    {
        if ($this->getModel() instanceof Model) {
            $queryBuilder = $this->getModel()->newQuery();
        } elseif ($this->getModel() instanceof Relation) {
            $this->relationName     = $this->getModel()->getRelated()->getTable();
            $this->relationInstance = true;
            $queryBuilder           = $this->getModel()->getQuery();
        } elseif ($this->getModel() instanceof \Awok\Foundation\Eloquent\Builder) {
            $queryBuilder = $this->getModel();
        } else {
            throw new \InvalidArgumentException('Invalid Model/Builder/Relation supplied');
        }

        $this->appendSelect($queryBuilder);
        $this->appendFilters($queryBuilder);
        $this->appendRelations($queryBuilder);
        $this->appendSort($queryBuilder);

        return $queryBuilder;
    }

    protected function appendSelect(Builder $builder)
    {
        $selectFields = [];

        if (! $this->getFields()->count() > 0) {
            $builder->select(['*']);

            return true;
        }

        foreach ($this->getFields() as $field) {
            array_push($selectFields, $field->getName());
        }

        $builder->select($selectFields);

        return true;
    }

    /**
     * @return RequestFieldCollection|null
     */
    public function getFields()
    {
        return $this->fields;
    }

    protected function setFields(RequestFieldCollection $fields)
    {
        $this->fields = $fields;
    }

    protected function appendFilters(Builder $builder)
    {
        if (! $this->getFilters()->count() > 0) {
            return true;
        }

        /*** @var $filter RequestFilter */
        foreach ($this->getFilters() as $filter) {
            // Handle relational filters
            if ($filter->getField()->isRelational()) {
                // Handle non-relational direct filters
                $builder->whereHas($filter->getField()->getRelationName(), function ($query) use ($filter) {
                    $this->applyClause($query, $filter);
                });
            } else {
                $this->applyClause($builder, $filter);
            }
        }

        return true;
    }

    public function getFilters()
    {
        return $this->filters;
    }

    protected function setFilters(RequestFilterCollection $filters)
    {
        $this->filters = $filters;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder $builder
     * @param                                                                          $filter
     */
    protected function applyClause($builder, $filter)
    {
        $filterField = $filter->getField();
        if ($filterField->isRelational()) {
            $fieldNamePrefix = Arr::last($filterField->getRelationFragments()).'.';
        } else {
            $fieldNamePrefix = $this->isRelationInstance() ? $this->getRelationName().'.' : '';
        }

        $fieldName = $fieldNamePrefix.$filterField->getName();
        if (is_array($filter->getFilterValue())) {
            $not = false;
            if ($filter->getCompareSymbol() == '!=') {
                $not = true;
            }
            $builder->whereIn($fieldName, $filter->getFilterValue(), 'and', $not);
        } elseif (strtolower($filter->getFilterValue()) == 'null') {
            $not = false;
            if ($filter->getCompareSymbol() == '!=') {
                $not = true;
            }
            $builder->whereNull($fieldName, 'and', $not);
        } else {
            $builder->where($fieldName, $filter->getCompareSymbol(), $filter->getFilterValue());
        }
    }

    protected function appendSort(Builder $builder)
    {
        if (! $this->getSorting()->count() > 0) {
            return true;
        }

        /*** @var RequestSort $sort */
        foreach ($this->getSorting() as $sort) {
            // Handle relational filters
            if ($sort->getField()->isRelational()) {
                // Handle non-relational direct filters
                $builder->where($sort->getField()->getRelationName(), function ($query) use ($sort) {
                    $query->orderBy($sort->getField()->getName(), $sort->getDirection());
                });
            } else {
                $builder->orderBy($sort->getField()->getName(), $sort->getDirection());
            }
        }

        return true;
    }

    protected function appendRelations(Builder $builder)
    {
        if (! $this->getRelations()->count() > 0) {
            return true;
        }

        /** @var RequestRelationField $relation */
        foreach ($this->getRelations() as $relation) {
            if ($relation->isRelational()) {
                $relationName = $relation->getRelationName()/*.'.'.$relation->getName()*/
                ;
            } else {
                $relationName = $relation->getName();
            }

            $builder->with([
                $relationName => function ($query) use ($relation, $relationName) {
                    $referencedTableName = $query->getRelated()->getTable();
                    if ($relation->hasSubFields()) {
                        $subFields = array_map(function ($subField) use ($relationName, $referencedTableName) {
                            return $referencedTableName.'.'.$subField->getName();
                        }, iterator_to_array($relation->getSubFields()));
                        $select    = array_merge($subFields);
                    } else {
                        $select = [$referencedTableName.'.*'];
                    }

                    $query->select($select);
                },
            ]);
        }

        return true;
    }

    public function getSorting()
    {
        return $this->sorting;
    }

    protected function setSorting(RequestSortCollection $sort)
    {
        $this->sorting = $sort;
    }
}