<?php

namespace Glumen\Domains\Data\Jobs;

use Glumen\Domains\Data\Traits\EloquentRequestQueryable;
use Glumen\Foundation\Http\Request;
use Glumen\Foundation\Http\RequestFilterCollection;
use Glumen\Foundation\Job;

class FindEloquentObjectFromRequestJob extends Job
{
    use EloquentRequestQueryable;

    protected $model;

    protected $primaryKey;

    protected $objectID;

    public function __construct($model, int $objectID, $primaryKey = 'id')
    {
        $this->model      = $model;
        $this->primaryKey = $primaryKey;
        $this->objectID   = $objectID;
    }

    public function handle(Request $request)
    {
        $this->setModel($this->model);
        $this->captureRequestQuery($request);
        // Filtering is not allowed in case of single object queries
        $this->setFilters(new RequestFilterCollection());
        $result = $this->buildQuery()->where($this->primaryKey, '=', $this->objectID);

        return $result->firstOrFail();
    }
}